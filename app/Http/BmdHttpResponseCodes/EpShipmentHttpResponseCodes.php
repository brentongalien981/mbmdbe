<?php

namespace App\Http\BmdHttpResponseCodes;

use Exception;



class EpShipmentHttpResponseCodes
{
    public const BMD_EP_ADDRESS_EXCEPTION = [
        'code' => 'BMD_EP_ADDRESS_EXCEPTION-1001', 
        'message' => 'BMD_EP_ADDRESS_EXCEPTION', 
        'readableMessage' => 'BMD-EP-Address Exception'
    ];



    public static function getFormattedBmdEpAddressException(Exception $e) {

        $returnVal = self::BMD_EP_ADDRESS_EXCEPTION;

        $returnVal['exceptionTrace'] = [
            $e->getTrace()[0], $e->getTrace()[1], $e->getTrace()[2]
        ];

        return $returnVal;
    }



    public static function getNullBmdPredefinedPackageExceptionWithTrace(Exception $e) {

        return [
            'code' => 'NullBmdPredefinedPackageException-1002',
            'message' => 'NullBmdPredefinedPackageException',
            'readableMessage' => 'NullBmdPredefinedPackageException: ' . $e->getMessage(),
            'exceptionTrace' => [$e->getTrace()[0], $e->getTrace()[1], $e->getTrace()[2]]
        ];
    }


    
    public static function getCouldNotFindShipmentRatesExceptionWithTrace(Exception $e) {

        return [
            'code' => 'CouldNotFindShipmentRatesException-1003',
            'message' => 'CouldNotFindShipmentRatesException',
            'readableMessage' => 'CouldNotFindShipmentRatesException: ' . $e->getMessage(),
            'exceptionTrace' => [$e->getTrace()[0], $e->getTrace()[1], $e->getTrace()[2]]
        ];
    }


    
    public static function getNotAllowedOrderStatusForProcessException(Exception $e) {

        return [
            'code' => 'NotAllowedOrderStatusForProcess-1004',
            'message' => 'NotAllowedOrderStatusForProcess',
            'readableMessage' => 'NotAllowedOrderStatusForProcess: ' . $e->getMessage(),
            'exceptionTrace' => [$e->getTrace()[0], $e->getTrace()[1], $e->getTrace()[2]]
        ];
    }
}