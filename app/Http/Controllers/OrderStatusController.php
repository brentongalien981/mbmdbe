<?php

namespace App\Http\Controllers;

use App\Models\OrderStatus;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function index(Request $r)
    {
        
        return [
            'isResultOk' => true,
            'objs' => [
                'orderStatuses' => OrderStatus::orderBy('name', 'asc')->get()
            ]
        ];
    }
}
