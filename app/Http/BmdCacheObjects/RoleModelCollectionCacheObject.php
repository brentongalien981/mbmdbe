<?php

namespace App\Http\BmdCacheObjects;

use App\Models\Role;


class RoleModelCollectionCacheObject extends BmdModelCollectionCacheObject
{  
    protected $lifespanInMin = 1440;
    protected static $modelPath = Role::class;
    

}