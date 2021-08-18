<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $r)
    {
        // BMD-TODO
        // Gate::forUser(BmdAuthProvider::user())->authorize('viewAny', Order::class);


        $purchases = [];

        
        return [
            'isResultOk' => true,
            'objs' => [
                'purchases' => $purchases,
                // 'paginationData' => [
                //     'totalNumOfProductsForQuery' => $totalNumOfProductsForQuery
                // ]
            ]
        ];
    }
}
