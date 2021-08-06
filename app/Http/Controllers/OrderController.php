<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use App\Bmd\Generals\GeneralHelper;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\OrderResource;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\Resources\OrderItemResource;

class OrderController extends Controller
{
    private const NUM_OF_DISPLAYED_ORDERS_PER_PAGE = 10;



    public function index(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('viewAny', Order::class);


        $ordersQuery = $this->getOrdersQuery($r);
        $totalNumOfProductsForQuery = $ordersQuery->count();

        $numOfOrdersToSkip = ($r->pageNum - 1) * self::NUM_OF_DISPLAYED_ORDERS_PER_PAGE;

        $orders = $ordersQuery->skip($numOfOrdersToSkip)
            ->take(self::NUM_OF_DISPLAYED_ORDERS_PER_PAGE)
            ->get();

        $orders = OrderResource::collection($orders);


        return [
            'isResultOk' => true,
            'objs' => [
                'orders' => $orders,
                'paginationData' => [
                    'totalNumOfProductsForQuery' => $totalNumOfProductsForQuery
                ]
            ],
            // BMD-FOR-DEBUG
            'requestData' => [
                'r->request' => GeneralHelper::jsonifyObj($r->request),
            ]
        ];
    }



    private function getOrdersQuery(Request $r)
    {
        $orderIdFilterQueryParam = '%' . $r->orderIdFilter . '%';
        $userIdFilterQueryParam = '%' . $r->userIdFilter . '%';
        $stripePaymentIntentIdFilterQueryParam = '%' . $r->stripePaymentIntentIdFilter . '%';

        $firstNameFilterQueryParam = '%' . $r->firstNameFilter . '%';
        $lastNameFilterQueryParam = '%' . $r->lastNameFilter . '%';
        $phoneFilterQueryParam = '%' . $r->phoneFilter . '%';
        $emailFilterQueryParam = '%' . $r->emailFilter . '%';

        $streetFilterQueryParam = '%' . $r->streetFilter . '%';
        $cityFilterQueryParam = '%' . $r->cityFilter . '%';
        $provinceFilterQueryParam = '%' . $r->provinceFilter . '%';
        $countryFilterQueryParam = '%' . $r->countryFilter . '%';
        $postalCodeFilterQueryParam = '%' . $r->postalCodeFilter . '%';

        $statusFilterQueryParam = '%' . $r->statusFilter . '%';


        $ordersQuery = Order::where('id', 'like', $orderIdFilterQueryParam);

        if (trim($r->userIdFilter) != '') {
            $ordersQuery = $ordersQuery->where('user_id', 'like', $userIdFilterQueryParam);
        }

        $ordersQuery = $ordersQuery->where('stripe_payment_intent_id', 'like', $stripePaymentIntentIdFilterQueryParam)
            ->where('first_name', 'like', $firstNameFilterQueryParam)
            ->where('last_name', 'like', $lastNameFilterQueryParam)
            ->where('phone', 'like', $phoneFilterQueryParam)
            ->where('email', 'like', $emailFilterQueryParam)

            ->where('street', 'like', $streetFilterQueryParam)
            ->where('city', 'like', $cityFilterQueryParam)
            ->where('province', 'like', $provinceFilterQueryParam)
            ->where('country', 'like', $countryFilterQueryParam)
            ->where('postal_code', 'like', $postalCodeFilterQueryParam)

            ->where('status_code', 'like', $statusFilterQueryParam)
            ->where('projected_total_delivery_days', '>=', intval($r->deliveryDaysFilter))

            ->where('earliest_delivery_date', '>=', $r->earlyDeliveryDateFilter)
            ->where('latest_delivery_date', '>=', $r->lateDeliveryDateFilter)
            ->where('created_at', '>=', $r->createDateFilter)
            ->where('updated_at', '>=', $r->updateDateFilter)

            ->orderBy('created_at', 'desc');

        return $ordersQuery;
    }



    public function show(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('viewAny', Order::class);

        
        $v = $r->validate([
            'orderId' => 'required|string|size:36',
        ]);


        $o = Order::find($v['orderId']);


        return [
            'isResultOk' => true,
            'objs' => [
                'order' => new OrderResource($o) ?? [],
                'orderItems' => OrderItemResource::collection($o->orderItems) ?? [],
                'orderStatuses' => OrderStatus::orderBy('name', 'asc')->get()
            ]
        ];
    }

}
