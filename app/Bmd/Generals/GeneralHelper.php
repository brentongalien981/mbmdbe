<?php

namespace App\Bmd\Generals;

use App\Bmd\Constants\BmdGlobalConstants;



class GeneralHelper
{
    public static function isWithinStoreSiteDataUpdateMaintenancePeriod($nowInDateObj = null) {
        $nowInDateObj = $nowInDateObj ?? getdate();
        if ($nowInDateObj['hours'] >= BmdGlobalConstants::STORE_SITE_DATA_UPDATE_MAINTENANCE_PERIOD_START_HOUR) {
            return true;
        }
        return false;
    }


    
    public static function extractErrorTrace($error, $numOfErrorLines = 3) {

        $returnedErrorStr = 'BMD Exception: ' . $error->getMessage();
        $returnedErrorStr .= 'BMD Error Trace: ...';

        $eTrace = $error->getTrace();
        
        for ($i = 0; $i < $numOfErrorLines; $i++) {
            if (!isset($eTrace[$i])) {
                break;
            }
            $eTraceLineMsg = 'CLASS ==> ' . $eTrace[$i]['class'] . ' | ';
            $eTraceLineMsg .= 'FILE ==> ' . $eTrace[$i]['file'] . ' | ';
            $eTraceLineMsg .= 'FUNC ==> ' . $eTrace[$i]['function'] . ' | ';
            $eTraceLineMsg .= 'LINE ==> ' . $eTrace[$i]['line'];

            $returnedErrorStr .= $eTraceLineMsg;
        }

        return $returnedErrorStr;

    }
}
