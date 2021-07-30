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



    public static function getTodaysDateInStr()
    {
        $dateObjToday = getdate();

        return $dateObjToday['year'] . '-' . $dateObjToday['mon'] . '-' . $dateObjToday['mday'];
    }



    public static function getDateInStrWithData($startDateInStr, $numDaysToAdd = 0)
    {
        $unixTimestamp = strtotime($startDateInStr);
        return self::getDateInStrWithUnixTimestamp($unixTimestamp, $numDaysToAdd);
    }



    public static function getDateInStrWithDbTimestamp($dbTimestamp, $numDaysToAdd = 0)
    {
        $unixTimestamp = strtotime($dbTimestamp);
        return self::getDateInStrWithUnixTimestamp($unixTimestamp, $numDaysToAdd);
    }



    public static function getDateInStrWithUnixTimestamp($unixTimestamp, $numDaysToAdd = 0)
    {
        $numOfSecToAdd = $numDaysToAdd * BmdGlobalConstants::NUM_OF_SEC_IN_DAY;
        $date = getdate($unixTimestamp + $numOfSecToAdd);

        return $date['year'] . '-' . self::getMonthNumWithZeroPadding($date) . '-' .  self::getDayNumWithZeroPadding($date);
    }



    public static function getDateTimeInStrWithDbTimestamp($dbTimestamp, $numDaysToAdd = 0)
    {
        $unixTimestamp = strtotime($dbTimestamp);
        return self::getDateTimeInStrWithUnixTimestamp($unixTimestamp, $numDaysToAdd);
    }



    public static function getDateTimeInStrWithUnixTimestamp($unixTimestamp, $numDaysToAdd = 0)
    {
        $numOfSecToAdd = $numDaysToAdd * BmdGlobalConstants::NUM_OF_SEC_IN_DAY;
        $date = getdate($unixTimestamp + $numOfSecToAdd);

        $dateTimeInStr = $date['year'] . '-' . self::getMonthNumWithZeroPadding($date) . '-' .  self::getDayNumWithZeroPadding($date);
        $dateTimeInStr .= ' ' . self::padWithZeroForTensDigit($date['hours']) . ':' . self::padWithZeroForTensDigit($date['minutes']) . ':' . self::padWithZeroForTensDigit($date['seconds']);

        return $dateTimeInStr;
    }



    public static function padWithZeroForTensDigit($num) 
    {
        if ($num <= 9) {
            return '0' . $num;
        }
        return $num;
    }



    public static function getMonthNumWithZeroPadding($date)
    {
        $monthNum = $date['mon'];
        if ($monthNum <= 9) {
            return '0' . $monthNum;
        }
        return $monthNum;
    }



    public static function getDayNumWithZeroPadding($date)
    {
        if ($date['mday'] <= 9) {
            return '0' . $date['mday'];
        }
        return $date['mday'];
    }



    /**
     * Undocumented function
     *
     * @param string $start
     * @param string $end
     * @return int
     */
    public static function getNumDaysBetweenDates($start, $end) {

        $ithDayOfTheYearForStartDate = getdate(strtotime($start))['yday'];
        $ithDayOfTheYearForEndDate = getdate(strtotime($end))['yday'];

        return $ithDayOfTheYearForEndDate - $ithDayOfTheYearForStartDate;
    }



    public static function jsonifyObj($obj)
    {
        $jsonifiedObj = [];

        foreach ($obj as $k => $v) {
            $jsonifiedObj[$k] = $v;
        }

        return $jsonifiedObj;
    }
}