<?php

namespace App\Http\Controllers;

use App\Models\PurchaseStatus;
use Illuminate\Http\Request;

class PurchaseStatusController extends Controller
{
    public function index(Request $r)
    {
        
        return [
            'isResultOk' => true,
            'objs' => [
                'purchaseStatuses' => PurchaseStatus::orderBy('name', 'asc')->get()
            ]
        ];
    }
}
