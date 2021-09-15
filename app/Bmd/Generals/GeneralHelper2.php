<?php

namespace App\Bmd\Generals;



class GeneralHelper2
{
    public static function pseudoJsonify($var)
    {
        switch (gettype($var)) {
            case 'array':
            case 'object':
                return self::jsonifyObj($var);
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
            default:
                return $var;
        }
    }



    public static function jsonifyObj($obj)
    {
        $jsonifiedObj = [];

        foreach ($obj as $k => $v) {
            $simplifiedV = self::pseudoJsonify($v);
            $jsonifiedObj[$k] = $simplifiedV;
        }

        return $jsonifiedObj;
    }
}