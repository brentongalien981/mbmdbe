<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Cart;
use App\Models\Order;
use EasyPost\EasyPost;
use EasyPost\Shipment;
use App\Models\OrderStatus;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\OrderItemStatus;
use App\Bmd\Generals\GeneralHelper;
use App\Bmd\Generals\GeneralHelper2;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\OrderResource;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\Resources\DispatchResource;
use App\Http\Resources\OrderItemResource;
use App\Models\Dispatch;

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

        $entireProcessComments = [];


        $o = Order::find($v['orderId']);


        $actualEpShipment = null;
        try {
            // BMD-ON-ITER: Development, Staging
            EasyPost::setApiKey(env('EASYPOST_TK'));

            if ($o->ep_shipment_id) {
                $actualEpShipment = Shipment::retrieve($o->ep_shipment_id) ?? null;
            }
        } catch (\Throwable $th) {
            $entireProcessComments[] = $th->getMessage();
        }


        return [
            'isResultOk' => true,
            'objs' => [
                'order' => new OrderResource($o) ?? [],
                'orderItems' => OrderItemResource::collection($o->orderItems) ?? [],
                'orderStatuses' => OrderStatus::orderBy('name', 'asc')->get(),
                'orderItemStatuses' => OrderItemStatus::orderBy('name', 'asc')->get(),
                'actualEpShipment' => GeneralHelper2::pseudoJsonify($actualEpShipment),
                'dispatches' => DispatchResource::collection(Dispatch::getAvailableDispatches()),
                'entireProcessComments' => $entireProcessComments
            ]
        ];
    }



    private function validateRequestData(Request $r, $crudAction = 'create')
    {
        $idValidationRule = ($crudAction === 'create' ? 'nullable|string|size:36' : 'required|string|size:36');

        return $r->validate([
            'id' => $idValidationRule,
            'user_id' => 'nullable|integer',
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
            'latest_delivery_date' => 'required|date'
        ]);
    }



    private function saveOrderWithData($data, $crudAction = 'create')
    {
        $o = null;

        if ($crudAction === 'create') {

            $cart = new Cart();
            $cart->user_id = $data['user_id'] ?? null;
            $cart->stripe_payment_intent_id = $data['stripe_payment_intent_id'];
            $cart->save();

            $o = new Order();
            $o->id = Str::uuid()->toString();
            $o->cart_id = $cart->id;
        } else {
            $o = Order::find($data['id']);
        }


        $o->user_id = $data['user_id'] ?? null;
        $o->stripe_payment_intent_id = $data['stripe_payment_intent_id'];
        $o->status_code = $data['status_code'];
        $o->first_name = $data['first_name'];
        $o->last_name = $data['last_name'];
        $o->street = $data['street'];
        $o->city = $data['city'];
        $o->province = $data['province'];
        $o->country = $data['country'];
        $o->postal_code = $data['postal_code'];
        $o->phone = $data['phone'];
        $o->email = $data['email'];
        $o->charged_subtotal = $data['charged_subtotal'];
        $o->charged_shipping_fee = $data['charged_shipping_fee'];
        $o->charged_tax = $data['charged_tax'];
        $o->projected_total_delivery_days = $data['projected_total_delivery_days'];
        $o->earliest_delivery_date = $data['earliest_delivery_date'];
        $o->latest_delivery_date = $data['latest_delivery_date'];
        $o->save();

        return $o;
    }



    public function refresh(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('update', Order::class);

        $v = $r->validate([
            'orderId' => 'required|exists:orders,id',
        ]);


        $o = Order::find($v['orderId']);
        $o->updateStatusBasedOnOrderItemsStatuses();

        return [
            'isResultOk' => true,
            'objs' => [
                'order' => new OrderResource($o)
            ]
        ];
    }



    public function store(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('update', Order::class);

        $v = $this->validateRequestData($r);
        $o = $this->saveOrderWithData($v);

        return [
            'isResultOk' => true,
            'objs' => [
                'order' => $o
            ]
        ];
    }



    public function update(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('update', Order::class);


        $v = $this->validateRequestData($r, 'update');
        $o = $this->saveOrderWithData($v, 'update');


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
