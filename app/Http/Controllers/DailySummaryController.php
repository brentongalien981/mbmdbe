<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Models\IncompleteOrder;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;

class DailySummaryController extends Controller
{
    public function readDailySummaryData(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('view-dailySummary');


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



        // Finance Graph Data
        $financeGraphData = null;
        if ($r->shouldIncludeFinanceGraphData) {
            $financeGraphData = $this->getFinanceGraphData($r);
        }



        return [
            'isResultOk' => true,
            'objs' => [
                'numOfOrders' => $orders->count(),
                'numOfOrderItems' => $numOfOrderItems,
                'numOfIncompleteOrders' => $numOfIncompleteOrders,
                'revenue' => $revenue,
                'expenses' => $expenses,
                'financeGraphData' => $financeGraphData
            ]
        ];
    }



    public function getFinanceGraphData(Request $r)
    {
        $startDate = $r->graphStartDate;
        $endDate = $r->graphEndDate . ' 23:59:59';

        $orders = Order::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at', 'ASC')
            ->get();


        $periodTimingMode = 1; // daily
        $revenuesByTimingMode = [];
        $ordersCount = $orders->count();

        $revenueInOneTimingMode = 0.0;
        $ithTimingModeStartDate = $orders[0]->created_at;

        for ($i = 0; $i < $ordersCount; $i++) {
            
            if (($i != 0) && ($i % $periodTimingMode == 0)) {

                $ithTimingModeEndDate = $orders[$i]->created_at;

                $revenuesByTimingMode[] = [
                    'startDate' => $ithTimingModeStartDate,
                    'endDate' => $ithTimingModeEndDate,
                    'revenue' => $revenueInOneTimingMode
                ];

                $ithTimingModeStartDate = $ithTimingModeEndDate;
                $revenueInOneTimingMode = 0.0;
            }

            $o = $orders[$i];
            $revenueInOneTimingMode = $o->charged_subtotal + $o->charged_shipping_fee + $o->charged_tax;
        }


        return [
            'revenuesByTimingMode' => $revenuesByTimingMode
        ];
    }
}
