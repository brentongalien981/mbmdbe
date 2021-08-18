<?php

namespace App\Http\Resources;

use App\Models\PurchaseStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
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
            'sellerId' => $this->seller_id,
            'projectedSubtotal' => $this->projected_subtotal,
            'projectedShippingFee' => $this->projected_shipping_fee,
            'projectedOtherFee' => $this->projected_other_fee,
            'projectedTax' => $this->projected_tax,

            'chargedSubtotal' => $this->charged_subtotal,
            'chargedShippingFee' => $this->charged_shipping_fee,
            'chargedOtherFee' => $this->charged_other_fee,
            'chargedTax' => $this->charged_tax,

            'statusCode' => $this->status_code,
            'statusName' => PurchaseStatus::where('code', $this->status_code)->get()[0]->name,

            'estimatedDeliveryDate' => $this->estimated_delivery_date,
            'orderIdFromSellerSite' => $this->order_id_from_seller_site,
            'shippingIdFromCarrier' => $this->shippingIdFromCarrier,
            'notes' => $this->notes,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,

            'purchaseItems' => PurchaseItemResource::collection($this->purchaseItems)
        ];
    }
}
