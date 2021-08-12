<?php

namespace App\Http\BmdHelpers;



class BmdOperationsTimeManager
{
    private const DAILY_PURCHASE_BUYING_TIME = ['hours' => 10, 'minutes' => 0, 'seconds' => 0];



    public static function isNowPassedTodaysPurchaseBuyingTime()
    {
        $datetimeNow = getdate();

        if ($datetimeNow['hours'] < self::DAILY_PURCHASE_BUYING_TIME['hours']) { return true; }
        else if ($datetimeNow['hours'] == self::DAILY_PURCHASE_BUYING_TIME['hours']) { 
            if ($datetimeNow['minutes'] < self::DAILY_PURCHASE_BUYING_TIME['minutes']) { return true; }
            return false;
        }
        
        return false;


    }
}