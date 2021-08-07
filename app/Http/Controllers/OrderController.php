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



    public function update(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('manageUpdate', Order::class);


        $v = $r->validate([
            'id' => 'required|string|size:36',
            'user_id' => 'nullable|integer',
            'cart_id' => 'required|integer',
            'stripe_payment_intent_id' => 'string|max:64',
            'status_code' => 'required|integer',
            'first_name' => 'required|string|max:128',
            'last_name' => 'required|string|max:128',
            'street' => 'required|string|max:128',
            'city' => 'required|string|max:64',
            'province' => 'required|string|max:32',
            'country' => 'required|string|max:32',
            'postal_code' => 'required|string|max:16',
            'phone' => 'required|string|max:16',
            'email' => 'required|string|max:128',

            'charged_subtotal' => 'required|numeric',
            'charged_shipping_fee' => 'required|numeric',
            'charged_tax' => 'required|numeric',
            'projected_total_delivery_days' => 'required|integer|max:64',

            'earliest_delivery_date' => 'required|date',
            'latest_delivery_date' => 'required|date',
            'created_at' => 'required|date',
            'updated_at' => 'required|date'
        ]);


        $o = Order::find($v['id']);
        $o->user_id = $v['user_id'] ?? null;
        $o->cart_id = $v['cart_id'];
        $o->stripe_payment_intent_id = $v['stripe_payment_intent_id'];
        $o->status_code = $v['status_code'];
        $o->first_name = $v['first_name'];
        $o->last_name = $v['last_name'];
        $o->street = $v['street'];
        $o->city = $v['city'];
        $o->province = $v['province'];
        $o->country = $v['country'];
        $o->postal_code = $v['postal_code'];
        $o->phone = $v['phone'];
        $o->email = $v['email'];
        $o->charged_subtotal = $v['charged_subtotal'];
        $o->charged_shipping_fee = $v['charged_shipping_fee'];
        $o->charged_tax = $v['charged_tax'];
        $o->projected_total_delivery_days = $v['projected_total_delivery_days'];
        $o->earliest_delivery_date = $v['earliest_delivery_date'];
        $o->latest_delivery_date = $v['latest_delivery_date'];
        $o->created_at = $v['created_at'];
        $o->save();


        return [
            'isResultOk' => true,
            // // BMD-FOR-DEBUG
            // 'requestData' => [
            //     'r->request' => GeneralHelper::jsonifyObj($r->request),
            //     'r->id' => $r->id,
            //     'r->status_code' => $r->status_code
            // ],
            // 'resultData' => [
            //     'v' => $v,
            //     'o' => $o
            // ]

        ];
    }
}

