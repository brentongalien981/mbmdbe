<?php

namespace App\Http\Controllers;

use App\Bmd\Generals\GeneralHelper;
use Exception;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\OrderResource;
use App\Http\BmdHelpers\BmdAuthProvider;

class OrderController extends Controller
{
    private const NUM_OF_DISPLAYED_ORDERS_PER_PAGE = 10;

    public function index(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('viewAny', Order::class);


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


        $ordersWithQuery = Order::where('id', 'like', $orderIdFilterQueryParam);

        if (trim($r->userIdFilter) != '') {
            $ordersWithQuery = $ordersWithQuery->where('user_id', 'like', $userIdFilterQueryParam);
        }

        $ordersWithQuery = $ordersWithQuery->where('stripe_payment_intent_id', 'like', $stripePaymentIntentIdFilterQueryParam)
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


        $totalNumOfProductsForQuery = $ordersWithQuery->count();

        $numOfOrdersToSkip = ($r->pageNum - 1) * self::NUM_OF_DISPLAYED_ORDERS_PER_PAGE;

        $orders = $ordersWithQuery->skip($numOfOrdersToSkip)
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
            // BMD-DELETE
            'requestData' => [
                'orderIdFilter' => $r->orderIdFilter,
                'deliveryDaysFilter' => $r->deliveryDaysFilter,
                'xxx' => GeneralHelper::jsonifyObj($r->request),
                'ordersWithQuery' => $ordersWithQuery
            ]
        ];
    }
}
