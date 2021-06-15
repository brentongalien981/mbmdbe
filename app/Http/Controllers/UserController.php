<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\BmdAuth;
use App\Rules\MBMDRoleIds;
use Illuminate\Http\Request;
use App\Models\AuthProviderType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Bmd\Constants\BmdGlobalConstants;
use App\Http\BmdHelpers\BmdAuthProvider;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function create(Request $r)
    {

        // BMD-ON-STAGING: Set stricter validation rules.
        $acceptedEmailDomainsValidationRule = 'email|min:5|max:64|unique:users';
        // $acceptedEmailDomainsValidationRule = 'email|min:8|max:64|unique:users';
        // $acceptedEmailDomainsValidationRule .= '|ends_with:' . implode(',', BmdGlobalConstants::MBMD_ACCEPTED_EMAIL_DOMAINS);

        // $passwordValidationRule = Password::min(8)
        //     ->letters()
        //     ->mixedCase()
        //     ->numbers()
        //     ->symbols()
        //     ->uncompromised();
        $passwordValidationRule = Password::min(4)
            ->letters()
            ->numbers();

        $v = $r->validate([
            'email' => $acceptedEmailDomainsValidationRule,
            'password' => $passwordValidationRule,
            'selectedRoleIds' => ['required', new MBMDRoleIds]
        ]);



        // Authorize
        Gate::forUser(BmdAuthProvider::user())->authorize('create', User::class);



        $overallProcessLogs[] = '';
        $isResultOk = false;

        try {

            DB::beginTransaction();
            $overallProcessLogs[] = 'began db-transaction';


            $user = User::create([
                'email' => $v['email'],
                'password' => Hash::make($v['password']),
            ]);
            $overallProcessLogs[] = 'created user';


            $oauthProps = self::createPasswordAccessPassportToken($v['email'], $v['password'], $r);
            $overallProcessLogs[] = 'dispatched oauth-token request';


            // Create BmdAuth obj.
            $bmdAuth = new BmdAuth();
            $bmdAuth->user_id = $user->id;
            $bmdAuth->token = $oauthProps['access_token'];
            $bmdAuth->refresh_token = $oauthProps['refresh_token'];
            $bmdAuth->expires_in = getdate()[0] + BmdAuth::NUM_OF_SECS_PER_MONTH;
            $bmdAuth->frontend_pseudo_expires_in = $bmdAuth->expires_in;
            $bmdAuth->auth_provider_type_id = AuthProviderType::BMD;
            $bmdAuth->save();
            $overallProcessLogs[] = 'created bmd-auth obj';

            $bmdAuth->saveToCache();
            $overallProcessLogs[] = 'saved bmd-auth to cache';


            //
            $user->roles()->sync($v['selectedRoleIds']);


            DB::commit();
            $overallProcessLogs[] = 'commited db-transaction';

            $isResultOk = true;

        } catch (Exception $e) {

            DB::rollBack();
            $overallProcessLogs[] = 'rolled-back db-transaction';

            $overallProcessLogs[] = 'MBMD Caught Exception ==> ' . $e->getMessage();
        }



        return [
            'isResultOk' => $isResultOk,
            'overallProcessLogs' => $overallProcessLogs, // BMD-ON-STAGING
            'objs' => [
                'bmdToken' => $bmdAuth->token,
                'bmdRefreshToken' => $bmdAuth->refresh_token,
                'expiresIn' => $bmdAuth->expires_in
            ],
        ];
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
}
