<?php

namespace App\Http\Resources;

use App\Models\PurchaseItemStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
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
            'purchaseId' => $this->purchase_id,
            'sellerProductId' => $this->seller_product_id,
            'sizeAvailabilityId' => $this->size_availability_id,    
            'quantity' => $this->quantity,
            'projectedPrice' => $this->projectedPrice,
            'actualPrice' => $this->actual_price,            

            'statusCode' => $this->status_code,
            'statusName' => PurchaseItemStatus::where('code', $this->status_code)->get()[0]->name,

            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
