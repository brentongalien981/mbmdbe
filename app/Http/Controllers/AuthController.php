<?php

namespace App\Http\Controllers;

use App\Http\BmdHttpResponseCodes\GeneralHttpResponseCodes;
use Exception;
use App\Models\Role;
use App\Models\User;
use App\Models\BmdAuth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AuthProviderType;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use function PHPUnit\Framework\isEmpty;

class AuthController extends Controller
{
    public const LOGIN_RESULT_CODE_INVALID_PASSWORD = -1;
    public const LOGIN_RESULT_CODE_INVALID_BMD_AUTH_PROVIDER = -2;
    public const LOGIN_RESULT_CODE_FAIL = -3;
    public const LOGIN_RESULT_CODE_SUCCESS = 1;


    

    private static function revokeAllPassportTokens($userId)
    {
        $tokens = DB::table('oauth_access_tokens')->where('user_id', $userId)->get();

        foreach ($tokens as $t) {
            $tokenRepository = app('Laravel\Passport\TokenRepository');
            $refreshTokenRepository = app('Laravel\Passport\RefreshTokenRepository');

            // Revoke an access token...
            $tokenRepository->revokeAccessToken($t->id);

            // Revoke all of the token's refresh tokens...
            $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($t->id);
        }
    }



    private static function createPasswordAccessPassportToken($email, $password, $request)
    {
        $request->request->add([
            'grant_type' => 'password',
            'client_id' => env('PASSPORT_GRANT_PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSPORT_GRANT_PASSWORD_CLIENT_SECRET'),
            'username' => $email,
            'password' => $password,
            'scope' => '*',
        ]);

        $tokenRequest = Request::create(
            url('oauth/token'),
            'post'
        );

        $response = Route::dispatch($tokenRequest);;


        $rawObjs = json_decode($response->original);

        $oauthProps = [];
        foreach ($rawObjs as $k => $v) {
            $oauthProps[$k] = $v;
        }

        return $oauthProps;
    }


    
    public function signIn(Request $r)
    {

        $v = $r->validate([
            'email' => 'email|exists:users',
            'password' => 'max:32'
        ]);

        $possibleUser = User::where('email', $v['email'])->get()[0];
        if (!$possibleUser) { abort(403, 'User not found'); }        
        if (!isset($possibleUser->roles) || count($possibleUser->roles) === 0) { abort(403, 'Non-manager User!'); }

        $isResultOk = false;
        $bmdAuth = null;
        $overallProcessLogs = [];
        $resultCode = 0;


        try {

            // Check if BmdAuth has auth-provider-type Bmd.
            $bmdAuth = BmdAuth::where('user_id', $possibleUser->id)->get()[0] ?? null;
            if (!isset($bmdAuth) || 
                $bmdAuth->auth_provider_type_id != AuthProviderType::BMD) {

                $resultCode = self::LOGIN_RESULT_CODE_INVALID_BMD_AUTH_PROVIDER;
                throw new Exception('Invalid bmd-auth provider');
            }


            if (Hash::check($v['password'], $possibleUser->password)) {
                $overallProcessLogs[] = 'password ok';

                // Revoke all user's old tokens.
                self::revokeAllPassportTokens($possibleUser->id);
                $overallProcessLogs[] = 'user-tokens revoked';

                // Create a new oauth-token record for user.
                $oauthProps = self::createPasswordAccessPassportToken($v['email'], $v['password'], $r);
                $overallProcessLogs[] = 'created new user-token';

                // Delete the old bmd-auth cache-record
                $bmdAuth->deleteOldCacheRecord();
                $overallProcessLogs[] = 'deleted old-bmd-auth cache-record';


                // Update BmdAuth's token.
                $bmdAuth->token = $oauthProps['access_token'];
                $bmdAuth->refresh_token = $oauthProps['refresh_token'];
                $bmdAuth->expires_in = getdate()[0] + BmdAuth::NUM_OF_SECS_PER_MONTH;
                $bmdAuth->frontend_pseudo_expires_in = $bmdAuth->expires_in;
                $bmdAuth->save();
                $overallProcessLogs[] = 'updated bmd-auth record';

                $stayLoggedIn = true;
                $bmdAuth->saveToCache($stayLoggedIn);
                $overallProcessLogs[] = 'saved bmd-auth to cache';


                $resultCode = self::LOGIN_RESULT_CODE_SUCCESS;
                $isResultOk = true;
            } else {
                $overallProcessLogs[] = 'invalid password';
                $resultCode = self::LOGIN_RESULT_CODE_INVALID_PASSWORD;
                $bmdAuth = null;
            }
        } catch (Exception $e) {
            $overallProcessLogs[] = 'BMD CaughtError: ' . $e->getMessage();
            // $resultCode = GeneralHttpResponseCodes::getGeneralExceptionCode($e);
            $bmdAuth = null;
        }



        return [
            'isResultOk' => $isResultOk,
            'overallProcessLogs' => $overallProcessLogs, // BMD-ON-STAGING
            'resultCode' => $resultCode,
            'objs' => [
                'email' => $possibleUser->email,
                'bmdToken' => $bmdAuth ? $bmdAuth->token : null,
                'bmdRefreshToken' => $bmdAuth ? $bmdAuth->refresh_token : null,
                'expiresIn' => $bmdAuth ? $bmdAuth->expires_in : null,
                'authProviderId' => $bmdAuth ? $bmdAuth->auth_provider_type_id : null
            ]
        ];
    }


    
    public function loginAsDemoUser(Request $r) 
    {
        throw new Exception('Not Allowed In Production Mode!');
        
        if (env('APP_ENV') === 'production') {
            throw new Exception('Not Allowed In Production Mode!');
        }

        
        $isResultOk = false;
        $resultCode = 0;
        $bmdAuth = null;
        $randomEmail = 'manager' . Str::random(10) . '@asbdev.com';

        try {
            
            DB::beginTransaction();
            
            $randomPassword = Str::random(16);

            $user = User::create([
                'email' => $randomEmail,
                'password' => Hash::make($randomPassword),
            ]);


            $oauthProps = self::createPasswordAccessPassportToken($randomEmail, $randomPassword, $r);


            // Create BmdAuth obj.
            $bmdAuth = new BmdAuth();
            $bmdAuth->user_id = $user->id;
            $bmdAuth->token = $oauthProps['access_token'];
            $bmdAuth->refresh_token = $oauthProps['refresh_token'];
            $bmdAuth->expires_in = getdate()[0] + BmdAuth::NUM_OF_SECS_PER_MONTH;
            $bmdAuth->frontend_pseudo_expires_in = $bmdAuth->expires_in;
            $bmdAuth->auth_provider_type_id = AuthProviderType::BMD;
            $bmdAuth->save();

            $bmdAuth->saveToCache();


            // Associate user roles.
            $allRoledIds = Role::all()->pluck('id')->toArray();
            $user->roles()->sync($allRoledIds);


            DB::commit();

            $resultCode = self::LOGIN_RESULT_CODE_SUCCESS;
            $isResultOk = true;

        } catch (Exception $e) {            
            DB::rollBack();
            $bmdAuth = null;
            $resultCode = GeneralHttpResponseCodes::getGeneralExceptionCode($e);
        }



        return [
            'isResultOk' => $isResultOk,
            'resultCode' => $resultCode,
            'objs' => [
                'email' => $randomEmail,
                'bmdToken' => $bmdAuth ? $bmdAuth->token : null,
                'bmdRefreshToken' => $bmdAuth ? $bmdAuth->refresh_token : null,
                'expiresIn' => $bmdAuth ? $bmdAuth->expires_in : null,
                'authProviderId' => $bmdAuth ? $bmdAuth->auth_provider_type_id : null
            ]
        ];
    }
}
