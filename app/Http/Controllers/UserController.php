<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function create(Request $r) {
        
        return [
            'msg' => 'In CLASS: UserController, METHOD: create()',
            'email' => $r->email,
            'selectedRoleIds' => $r->selectedRoleIds
        ];
    }
}
