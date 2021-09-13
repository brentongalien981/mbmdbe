<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;

class DispatchController extends Controller
{
    public function store(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('mbmdDoAny', Dispatch::class);


        // $v = $this->validateRequestData($r, 'create');
        // $p = Purchase::saveWithData($v, 'create');


        return [
            'isResultOk' => true,
            'objs' => [
                // 'purchase' => new PurchaseResource($p)
            ]
        ];
    }
}
