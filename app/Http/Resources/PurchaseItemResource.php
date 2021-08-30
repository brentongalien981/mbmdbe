<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\ProductSeller;
use App\Models\SizeAvailability;
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
        $sellerProduct = ProductSeller::find($this->seller_product_id);


        return [
            'id' => $this->id,
            'purchaseId' => $this->purchase_id,
            'sellerProductId' => $this->seller_product_id,
            'sizeAvailabilityId' => $this->size_availability_id,    
            'quantity' => $this->quantity,
            'projectedPrice' => $this->projected_price,
            'actualPrice' => $this->actual_price,            

            'statusCode' => $this->status_code,
            'statusName' => PurchaseItemStatus::where('code', $this->status_code)->get()[0]->name,

            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,

            // Extras
            'productName' => Product::find($sellerProduct->product_id)->name ?? '',
            'size' => SizeAvailability::find($this->size_availability_id)->size ?? null,
            'sellerProductLink' => $sellerProduct->link ?? null,
        ];
    }
}
