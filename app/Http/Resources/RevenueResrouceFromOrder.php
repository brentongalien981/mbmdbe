<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RevenueResrouceFromOrder extends JsonResource
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
            'chargedSubtotal' => $this->charged_subtotal,
            'chargedShippingFee' => $this->charged_shipping_fee,
            'chargedTax' => $this->charged_tax,
            'createdAt' => $this->created_at
        ];
    }
}
