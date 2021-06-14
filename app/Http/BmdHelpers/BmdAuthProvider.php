<?php

namespace App\Http\BmdHelpers;

use App\Models\BmdAuth;
use Illuminate\Support\Facades\Cache;


class BmdAuthProvider
{

    private static $instance = null;
    private static $user = null;


    private function __construct()
    {
        // PRIVATE
    }



    public static function setInstance($token, $authProviderId)
    {
        if (!self::$instance) {

            // Read from cache.
            $bmdAuthCacheRecordKey = 'bmdAuth?token=' . $token . '&authProviderId=' . $authProviderId;
            $bmdAuthCacheRecordVal = Cache::store('redisreader')->get($bmdAuthCacheRecordKey);
            if ($bmdAuthCacheRecordVal) {
                self::$instance = $bmdAuthCacheRecordVal;
                return;
            }


            // Or read from db.
            $possibleAccounts = BmdAuth::where('token', $token)->where('auth_provider_type_id', $authProviderId)->get();

            if (isset($possibleAccounts) && count($possibleAccounts) === 1 && isset($possibleAccounts[0])) {
                self::$instance = $possibleAccounts[0];

                Cache::store('redisprimary')->put($bmdAuthCacheRecordKey, self::$instance, now()->addDays(30));
            }
        }
    }



    public static function getInstance()
    {
        return self::$instance;
    }



    public static function check()
    {
        if (
            isset(self::$instance)
            && self::$instance->frontend_pseudo_expires_in > time()
            && self::$instance->expires_in > time()
        ) {
            return true;
        }
        return false;
    }


    public static function user()
    {
        return self::$instance->user;
    }



    public static function bmdAuth()
    {
        return self::$instance;
    }
}
