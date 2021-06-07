<?php

namespace App\Http\BmdCacheObjects;


class BmdResourceCacheObject extends BmdCacheObject
{
    protected static $jsonResourcePath = null;


    
    public static function getUpdatedResourceCacheObjWithId($modelId) {
        $resourceCacheObjKey = static::getResourceCacheKeyPrefix() . $modelId;
        $resourceCacheObj = new static($resourceCacheObjKey);

        if ($resourceCacheObj->shouldRefresh()) {

            $modelCacheObjKey = static::getModelCacheKeyPrefix() . $modelId;
            $modelCacheObj = new static($modelCacheObjKey);

            if ($modelCacheObj->shouldRefresh()) {
                $modelObj = static::$modelPath::find($modelId);
                $modelCacheObj->data = $modelObj;
                $modelCacheObj->shouldForceRefresh = false;
                $modelCacheObj->save();
            }

            $resourceObj = new static::$jsonResourcePath($modelCacheObj->data);
            $resourceCacheObj->data = $resourceObj;
            $resourceCacheObj->shouldForceRefresh = false;
            $resourceCacheObj->save();
        }


        return $resourceCacheObj;
    }



    public static function getResourceCacheKeyPrefix() {
        return static::$jsonResourcePath . '?id=';
    }

}