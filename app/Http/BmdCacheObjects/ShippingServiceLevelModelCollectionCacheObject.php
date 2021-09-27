<?php

namespace App\Http\BmdCacheObjects;

use App\Models\ShippingServiceLevel;


class ShippingServiceLevelModelCollectionCacheObject extends BmdModelCollectionCacheObject
{  
    protected $lifespanInMin = 1440;
    protected static $modelPath = ShippingServiceLevel::class;

}