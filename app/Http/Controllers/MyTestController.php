<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MyTestController extends Controller
{
    public function read(Request $r)
    {
        $a = 1;
        $b = 2;
        $c = $a + $b;

        return [
            'msg' => 'MyTestController'
        ];


        // $c = new App\Http\Controllers\MyTestController();
        // $c->test()
    }



    public function test()
    {
        $a = 1;
        $b = 2;
        $c = $a + $b;

        return [
            'msg' => 'MyTestController'
        ];


        // $c = new App\Http\Controllers\MyTestController();
        // $c->test()
    }
}
