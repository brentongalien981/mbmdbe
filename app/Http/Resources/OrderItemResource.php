<?php

namespace App\Http\Resources;

use App\Models\OrderItemStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'orderId' => $this->order_id,

            'productId' => $this->product_id,
            'productSellerId' => $this->product_seller_id,
            'sizeAvailabilityId' => $this->size_availability_id,
            'purchaseItemId' => $this->purchase_item_id,
            'price' => $this->price,
            'quantity' => $this->quantity,

            'status_code' => $this->status_code,
            'status_name' => OrderItemStatus::where('code', $this->status_code)->get()[0]->name,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
