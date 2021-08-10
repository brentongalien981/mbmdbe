<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Bmd\Generals\GeneralHelper;
use Illuminate\Support\Facades\Gate;
use App\Http\BmdHelpers\BmdAuthProvider;
use App\Models\Order;
use App\Models\OrderItem;

class OrderItemController extends Controller
{
    
    public function store(Request $r)
    {
        Gate::forUser(BmdAuthProvider::user())->authorize('update', Order::class); 

        $v = $this->validateRequestData($r, 'store');

        $savedOrderItem = $this->saveWithData($v, 'store');

        return [
            'isResultOk' => true,
            'objs' => [
                'savedOrderItem' => $savedOrderItem
            ]
        ];
    }



    private function validateRequestData(Request $r, $crudAction = 'create')
    {
        return $r->validate([
            'id' => 'required|integer',
            'orderId' => 'exists:orders,id',
            'productId' => 'exists:products,id',
            'productSellerId' => 'exists:product_seller,id',
            'sizeAvailabilityId' => 'exists:size_availabilities,id',
            'purchaseItemId' => 'nullable|exists:purchase_items,id',
            'status_code' => 'exists:order_item_status,code',
            'quantity' => 'integer',
            'price' => 'numeric'
        ]);
    }



    private function saveWithData($data, $crudAction = 'create')
    {
        $oi = null;

        if ($crudAction === 'create') {

            // $cart = new Cart();
            // $cart->user_id = $data['user_id'] ?? null;
            // $cart->stripe_payment_intent_id = $data['stripe_payment_intent_id'];
            // $cart->save();

            // $o = new Order();
            // $o->id = Str::uuid()->toString();
            // $o->cart_id = $cart->id;
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