<?php

namespace App\Http\Controllers;

use App\Bmd\Generals\GeneralHelper;
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


        $period = 1; // BMD-TODO: Could be yearly, monthly, weekly, daily...
        $revenuesByPeriod = [];

        $periodsFirstOrder = $orders[0] ?? null;
        $i = 0;
        $revenueForPeriod = 0.0;

        $ordersCount = $orders->count();

        $isEndOfPeriod = false;
        $isLastOrderForPeriod = false;
        $previousOrder = null;
        $dateOfOrdersThisPeriod = [];


        foreach ($orders as $o) {

            $dateObjForPeriodsFirstOrder = getdate(strtotime($periodsFirstOrder->created_at));
            $dateObjForCurrentOrder = getdate(strtotime($o->created_at));

            $dateInterval = $dateObjForCurrentOrder['yday'] - $dateObjForPeriodsFirstOrder['yday'];


            // Cases when to append to revenuesByPeriod.
            if (($i != 0) && ($dateInterval != 0) && ($dateInterval % $period == 0)) {
                $isEndOfPeriod = true;
            }
            if ((!$isEndOfPeriod) && ($ordersCount == $i + 1)) {
                $isLastOrderForPeriod = true;
                $revenueForPeriod += $o->charged_subtotal + $o->charged_shipping_fee + $o->charged_tax;
                $previousOrder = $o;
                $dateOfOrdersThisPeriod[] = $o->created_at;
            }


            if ($isEndOfPeriod || $isLastOrderForPeriod) {

                if (!isset($previousOrder)) {
                    $previousOrder = $o;
                } // Just a base case.

                $revenuesByPeriod[] = [
                    'startDate' => GeneralHelper::getDateInStrWithDbTimestamp($periodsFirstOrder->created_at),
                    'endDate' => GeneralHelper::getDateInStrWithDbTimestamp($previousOrder->created_at),
                    'revenue' => $revenueForPeriod,
                    'dateOfOrdersThisPeriod' => $dateOfOrdersThisPeriod
                ];

                // Refresh values for new period.
                $revenueForPeriod = 0.0;
                $periodsFirstOrder = $o;
                $isEndOfPeriod = false;
                $dateOfOrdersThisPeriod = [];
            }


            $revenueForPeriod += $o->charged_subtotal + $o->charged_shipping_fee + $o->charged_tax;
            $previousOrder = $o;
            $dateOfOrdersThisPeriod[] = $o->created_at;
            ++$i;
        }


        return [
            'revenuesByPeriod' => $revenuesByPeriod
        ];
    }
}
