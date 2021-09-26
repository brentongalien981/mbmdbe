<?php

namespace App\Http\BmdHttpResponseCodes;

use Exception;



class GeneralHttpResponseCodes
{
    public const OK = ['code' => 'GENERAL-HTTP-RESPONSE-CODE-OK-1000', 'message' => 'OK', 'readableMessage' => 'Process successful!'];



    public static function getGeneralExceptionCode(Exception $e) {
        return [
            'code' => 'GENERAL_EXCEPTION_HTTP_RESPONSE_CODE-1001',
            'message' => 'GENERAL_EXCEPTION',
            'readableMessage' => 'General Exception: ' . ($e->getMessage() ?? 'BMD Exception'),
            'exceptionTrace' => [$e->getTrace()[0], $e->getTrace()[1], $e->getTrace()[2]]
        ];
    }
}