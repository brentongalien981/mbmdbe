<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;

class PurchaseItemController extends Controller
{
    public function store(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Purchase::class);

        // $v = $this->validateRequestData($r, 'store');

        // $savedPurchaseItem = $this->saveWithData($v, 'store');

        return [
            'isResultOk' => true,
            'objs' => [
                // 'savedPurchaseItem' => new OrderItemResource($savedPurchaseItem)
            ]
        ];
    }
}
