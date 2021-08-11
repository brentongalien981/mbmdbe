<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Bmd\Generals\GeneralHelper;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Http\Resources\OrderItemResource;
use App\Models\Order;
use App\Models\OrderItem;
use Exception;

class OrderItemController extends Controller
{
    
    public function store(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('store', Order::class); 

        $v = $this->validateRequestData($r, 'store');

        $savedOrderItem = $this->saveWithData($v, 'store');

        return [
            'isResultOk' => true,
            'objs' => [
                'savedOrderItem' => new OrderItemResource($savedOrderItem)
            ]
        ];
    }



    public function update(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('update', Order::class); 

        $v = $this->validateRequestData($r, 'update');

        $savedOrderItem = $this->saveWithData($v, 'update');

        return [
            'isResultOk' => true,
            'objs' => [
                'savedOrderItem' => new OrderItemResource($savedOrderItem)
            ]
        ];
    }



    private function validateRequestData(Request $r, $crudAction = 'store')
    {
        $idValidationRule = ($crudAction === 'store' ? 'nullable' : 'required|integer');

        return $r->validate([
            'id' => $idValidationRule,
            'orderId' => 'exists:orders,id',
            'productId' => 'exists:products,id',
            'productSellerId' => 'exists:product_seller,id',
            'sizeAvailabilityId' => 'exists:size_availabilities,id',
            'purchaseItemId' => 'nullable|exists:purchase_items,id',
            'status_code' => 'exists:order_item_status,code',
            'quantity' => 'required|integer',
            'price' => 'required|numeric'
        ]);
    }



    private function saveWithData($data, $crudAction = 'store')
    {
        $oi = null;

        if ($crudAction === 'store') {
            $oi = new OrderItem();
        } else {
            $oi = OrderItem::find($data['id']);
        }


        $oi->order_id = $data['orderId'];
        $oi->product_id = $data['productId'];
        $oi->product_seller_id = $data['productSellerId'];
        $oi->size_availability_id = $data['sizeAvailabilityId'];
        $oi->purchase_item_id = $data['purchaseItemId'] ?? null;
        $oi->price = $data['price'];
        $oi->quantity = $data['quantity'];
        $oi->status_code = $data['status_code'];
        $oi->save();

        return $oi;
    }
}