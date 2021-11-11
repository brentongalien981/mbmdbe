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



    public function getHttpInfo(Request $r)
    {
        $theHeaders = getallheaders();

        return [
            'msg' => 'In CLASS: TestController, METHOD: getHttpInfo()',
            'SERVER[HTTP_HOST]' => $_SERVER['HTTP_HOST'],
            'SERVER[REMOTE_ADDR]' => $_SERVER['REMOTE_ADDR'],
            'SERVER[SERVER_NAME]' => $_SERVER['SERVER_NAME'],
            'SERVER[SERVER_ADDR]' => $_SERVER['SERVER_ADDR'],
            'X-Forwarded-For (ISP)' => $theHeaders['X-Forwarded-For'] ?? null,
            'User-Agent (BROWSER)' => $theHeaders['User-Agent'] ?? null,
            'Host (CLOSEST-NODE-TO-SERVER)' => $theHeaders['Host'] ?? null,
            'Origin (HOST-OF-FRONTEND)' => $theHeaders['Origin'] ?? null,
            'Referer (HOST-OF-FRONTEND?)' => $theHeaders['Referer'] ?? null,
            'MY_NUMBER' => env('MY_NUMBER'),
            'MY_RANDOM_CONTAINER_NUMBER' => env('MY_RANDOM_CONTAINER_NUMBER'),
            'DB_HOST1' => env('DB_HOST1')
        ];
    }



    public function getHttpInfo2(Request $r)
    {
        $theHeaders = getallheaders();

        return [
            'isResultOk' => true,
            'objs' => [
                'msg' => 'In CLASS: TestController, METHOD: getHttpInfo()',
                'SERVER[HTTP_HOST]' => $_SERVER['HTTP_HOST'],
                'SERVER[REMOTE_ADDR]' => $_SERVER['REMOTE_ADDR'],
                'SERVER[SERVER_NAME]' => $_SERVER['SERVER_NAME'],
                'SERVER[SERVER_ADDR]' => $_SERVER['SERVER_ADDR'],
                'X-Forwarded-For (ISP)' => $theHeaders['X-Forwarded-For'] ?? null,
                'User-Agent (BROWSER)' => $theHeaders['User-Agent'] ?? null,
                'Host (CLOSEST-NODE-TO-SERVER)' => $theHeaders['Host'] ?? null,
                'Origin (HOST-OF-FRONTEND)' => $theHeaders['Origin'] ?? null,
                'Referer (HOST-OF-FRONTEND?)' => $theHeaders['Referer'] ?? null,
                'MY_NUMBER' => env('MY_NUMBER'),
                'MY_RANDOM_CONTAINER_NUMBER' => env('MY_RANDOM_CONTAINER_NUMBER'),
                'DB_HOST1' => env('DB_HOST1')
            ]
        ];
    }



    public function getSumOfTwoHighestNums() {

        $integers = [];

        $firstHighestNum = $integers[0] ?? 0;
        $secondHighestNum = $integers[0] ?? 0;

        foreach ($integers as $i) {
            if ($i >= $firstHighestNum) {
                $firstHighestNum = $i;
            }
        }

    }
}
