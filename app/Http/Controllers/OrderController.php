<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private const NUM_OF_DISPLAYED_ORDERS_PER_PAGE = 10;

    public function index(Request $r)
    {
        $ordersWithQuery = Order::orderBy('created_at', 'desc');
        $totalNumOfProductsForQuery = $ordersWithQuery->count();
        $orders = $ordersWithQuery->take(self::NUM_OF_DISPLAYED_ORDERS_PER_PAGE)->get();
        $orders = OrderResource::collection($orders);
    

        return [
            'isResultOk' => true,
            'objs' => [
                'orders' => $orders,
                'paginationData' => [
                    'totalNumOfProductsForQuery' => $totalNumOfProductsForQuery,
                    'pageNum' => 1
                ]
            ]
        ];
    }
}
