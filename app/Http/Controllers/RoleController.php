<?php

namespace App\Http\Controllers;

use App\Http\BmdCacheObjects\RoleModelCollectionCacheObject;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function getRoles(Request $r) {

        $rolesCO = RoleModelCollectionCacheObject::getUpdatedModelCollection();


        return [
            'objs' => [
                'roles' => $rolesCO->data
            ]
        ];
    }
}
