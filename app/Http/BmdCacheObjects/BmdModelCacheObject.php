<?php

namespace App\Http\BmdCacheObjects;


class BmdModelCacheObject extends BmdCacheObject
{    
    public static function getUpdatedModelCacheObjWithId($modelId) {
        $modelCacheObjKey = static::getModelCacheKeyPrefix() . $modelId;
        $modelCacheObj = new static($modelCacheObjKey);

        if ($modelCacheObj->shouldRefresh()) {
            $modelObj = static::$modelPath::find($modelId);
            $modelCacheObj->data = $modelObj;
            $modelCacheObj->shouldForceRefresh = false;
            $modelCacheObj->save();

        }


        return $modelCacheObj;
    }



    public static function sayShit() {
        echo static::$modelPath;
    }

}