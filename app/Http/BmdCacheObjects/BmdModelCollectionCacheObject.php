<?php

namespace App\Http\BmdCacheObjects;


class BmdModelCollectionCacheObject extends BmdModelCacheObject
{  
    public static function getUpdatedModelCollection()
    {

        $modelCollectionCacheKey = static::$modelPath . '-ModelCollection';
        $modelCollectionCO = new static($modelCollectionCacheKey);


        if ($modelCollectionCO->shouldRefresh()) {

            $modelCollection = static::$modelPath::all();
            $modelCollectionCO->data = $modelCollection;
            $modelCollectionCO->save();
        }


        return $modelCollectionCO;
    }
}