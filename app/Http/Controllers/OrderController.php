<?php

namespace App\Http\Controllers;

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

        $ordersWithQuery = Order::orderBy('created_at', 'desc');
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
                    'totalNumOfProductsForQuery' => $totalNumOfProductsForQuery,
                    // BMD-DELETE
                    'pageNum' => $r->pageNum,
                    'numOfOrdersToSkip' => $numOfOrdersToSkip
                ]
            ]
        ];
    }
}
