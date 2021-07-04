<?php

namespace App\Http\Controllers;

use App\Models\IncompleteOrder;
use App\Models\Order;
use App\Models\OrderItem;
use Exception;
use Illuminate\Http\Request;

class DailySummaryController extends Controller
{
    public function readDailySummaryData(Request $r)
    {
        $endDate = $r->statsEndDate . ' 23:59:59';

        $numOfOrders = Order::where('created_at', '>=', $r->statsStartDate)
            ->where('created_at', '<=', $endDate)
            ->count();


        $numOfOrderItems = OrderItem::where('created_at', '>=', $r->statsStartDate)
            ->where('created_at', '<=', $endDate)
            ->count();


        $numOfIncompleteOrders = IncompleteOrder::where('created_at', '>=', $r->statsStartDate)
            ->where('created_at', '<=', $endDate)
            ->count();



        return [
            'isResultOk' => true,
            'objs' => [
                'numOfOrders' => $numOfOrders,
                'numOfOrderItems' => $numOfOrderItems,
                'numOfIncompleteOrders' => $numOfIncompleteOrders
            ]
        ];
    }
}
