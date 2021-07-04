<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Models\IncompleteOrder;

class DailySummaryController extends Controller
{
    public function readDailySummaryData(Request $r)
    {
        $endDate = $r->statsEndDate . ' 23:59:59';

        $orders = Order::where('created_at', '>=', $r->statsStartDate)
            ->where('created_at', '<=', $endDate)
            ->get();


        $numOfOrderItems = OrderItem::where('created_at', '>=', $r->statsStartDate)
            ->where('created_at', '<=', $endDate)
            ->count();


        $numOfIncompleteOrders = IncompleteOrder::where('created_at', '>=', $r->statsStartDate)
            ->where('created_at', '<=', $endDate)
            ->count();


        $revenue = 0.0;
        foreach ($orders as $o) {
            $revenue += $o->charged_subtotal + $o->charged_shipping_fee + $o->charged_tax;
        }


        $purchases = Purchase::where('created_at', '>=', $r->statsStartDate)
            ->where('created_at', '<=', $endDate)
            ->get();

        $expenses = 0.0;
        foreach ($purchases as $p) {
            $expenses += $p->charged_subtotal + $p->charged_shipping_fee + $p->charged_tax + $p->charged_other_fee;
        }

        $expenses += 2.35;


        return [
            'isResultOk' => true,
            'objs' => [
                'numOfOrders' => $orders->count(),
                'numOfOrderItems' => $numOfOrderItems,
                'numOfIncompleteOrders' => $numOfIncompleteOrders,
                'revenue' => $revenue,
                'expenses' => $expenses
            ]
        ];
    }
}
