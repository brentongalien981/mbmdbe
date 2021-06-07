<?php

namespace App\Http\BmdCacheObjects;


class BmdResourceCollectionCacheObject extends BmdResourceCacheObject
{
    protected static $foreignKeyName = null;

    public static function getUpdatedCollection($foreignKeyId)
    {

        $fkId = $foreignKeyId;

        $resourceCollectionCOKey = static::getResourceCollectionCacheKeyPrefix($fkId);
        $resourceCollectionCO = new static($resourceCollectionCOKey);


        if ($resourceCollectionCO->shouldRefresh()) {

            $modelsCOKey = static::getModelsCollectionCacheKeyPrefix($fkId);
            $modelsCO = new BmdModelCollectionCacheObject($modelsCOKey);

            if ($modelsCO->shouldRefresh()) {
                $models = static::$modelPath::where(static::$foreignKeyName, $fkId)->get();
                $modelsCO->data = $models;
                $modelsCO->shouldForceRefresh = false;
                $modelsCO->save();
            }

            $resourceCollection = static::$jsonResourcePath::collection($modelsCO->data);
            $resourceCollectionCO->data = $resourceCollection;
            $resourceCollectionCO->shouldForceRefresh = false;
            $resourceCollectionCO->save();
        }


        return $resourceCollectionCO;
    }



    public static function getResourceCollectionCacheKeyPrefix($fkId)
    {
        return static::$jsonResourcePath . '-Collection?' . static::$foreignKeyName . '=' . $fkId;
    }



    public static function getModelsCollectionCacheKeyPrefix($fkId)
    {
        return static::$modelPath . '-Collection?' . static::$foreignKeyName . '=' . $fkId;
    }
}
