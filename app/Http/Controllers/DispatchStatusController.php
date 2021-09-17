<?php

namespace App\Http\Controllers;

use App\Models\DispatchStatus;
use Illuminate\Http\Request;

class DispatchStatusController extends Controller
{
    public function index(Request $r)
    {
        
        return [
            'isResultOk' => true,
            'objs' => [
                'dispatchStatuses' => DispatchStatus::orderBy('name', 'asc')->get()
            ]
        ];
    }
}
