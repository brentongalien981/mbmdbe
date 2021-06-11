<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BmdAuth extends Model
{
    use HasFactory;


    public const NUM_OF_SECS_PER_MONTH = 60 * 60 * 24 * 30;
    public const TOKEN_EXPIRY_GRACE_PERIOD_IN_SEC = 3 * 60;

    public const PSEUDO_SESSION_STATUS_IDLE = 9000;
    public const PSEUDO_SESSION_STATUS_FLAGGED_EXPIRING = 9002;



    public static function getGracePeriodExpiryInSec()
    {
        return getdate()[0] + self::TOKEN_EXPIRY_GRACE_PERIOD_IN_SEC;
    }



    public function getCacheKey()
    {
        $bmdAuthCacheRecordKey = 'bmdAuth?token=' . $this->token . '&authProviderId=' . $this->auth_provider_type_id;
        return $bmdAuthCacheRecordKey;
    }



    public function deleteOldCacheRecord()
    {
        $bmdAuthCacheRecordKey = 'bmdAuth?token=' . $this->token . '&authProviderId=' . $this->auth_provider_type_id;
        Cache::store('redisprimary')->forget($bmdAuthCacheRecordKey);
    }


    public function saveToCache($stayLoggedIn = false)
    {
        $bmdAuthCacheRecordKey = 'bmdAuth?token=' . $this->token . '&authProviderId=' . $this->auth_provider_type_id;
        $this->stayLoggedIn = $stayLoggedIn;
        Cache::store('redisprimary')->put($bmdAuthCacheRecordKey, $this, now()->addDays(30));
    }


    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
