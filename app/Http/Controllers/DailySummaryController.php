<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DailySummaryController extends Controller
{
    public function readDailySummaryData(Request $r)
    {
        return [
            'msg' => ' In METHOD: readDailySummaryData()'
        ];
    }
}
