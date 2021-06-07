<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function getRoles(Request $r) {

        // BMD-ISH
        // Set cache config.


        // Return roles.

        return [
            'objs' => [
                'roles' => [
                    ['id' => 1, 'name' => 'rolename1'],
                    ['id' => 2, 'name' => 'rolename2']
                ]
            ]
        ];
    }
}
