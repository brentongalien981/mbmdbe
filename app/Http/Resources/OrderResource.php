<?php

namespace App\Http\Resources;

use App\Models\OrderStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'user_id' => $this->user_id,// ?? 'n/a',
            'ep_shipment_id' => $this->ep_shipment_id,
            'stripe_payment_intent_id' => $this->stripe_payment_intent_id,
            'dispatch_id' => $this->dispatch_id,
            'cart_id' => $this->cart_id,

            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'street' => $this->street,
            'city' => $this->city,
            'province' => $this->province,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'phone' => $this->phone,
            'email' => $this->email,

            'status_code' => $this->status_code,
            'status_name' => OrderStatus::where('code', $this->status_code)->get()[0]->name,

            'charged_subtotal' => $this->charged_subtotal,
            'charged_shipping_fee' => $this->charged_shipping_fee,
            'charged_tax' => $this->charged_tax,

            'earliest_delivery_date' => $this->earliest_delivery_date,
            'latest_delivery_date' => $this->latest_delivery_date,
            'projected_total_delivery_days' => $this->projected_total_delivery_days,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
