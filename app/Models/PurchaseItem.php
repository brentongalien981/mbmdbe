<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;



    public static function saveWithData($data, $crudAction = 'store')
    {
        $obj = null;
        $d = $data;

        if ($crudAction === 'store') {
            $obj = new self;
        } else {
            $obj = self::find($data['id']);
        }


        $obj->purchase_id = $d['purchaseId'];
        $obj->seller_product_id = $d['sellerProductId'];
        $obj->size_availability_id = $d['sizeAvailabilityId'];
        $obj->quantity = $d['quantity'];
        $obj->projected_price = $d['projectedPrice'];
        $obj->actual_price = $d['actualPrice'] ?? null;
        $obj->status_code = $d['statusCode'];

        $obj->save();


        return $obj;
    }



    public function updateStatusOfRelatedOrderItems()
    {
        $orderItems = OrderItem::where('purchase_item_id', $this->id)->get();

        foreach ($orderItems as $oi) {
            $oi->status_code = $this->status_code;
            $oi->save();
        }
    }



    public function updateStatsOfRelatedInventoryItem()
    {
        $ii = InventoryItem::where('size_availability_id', $this->size_availability_id)->get()[0];
        $status = PurchaseItemStatus::where('code', $this->status_code)->get()[0];
        $iiStatColumnToIncrease = InventoryItem::mapStatusNameToStatColumnName($status->name);

        $ii->$iiStatColumnToIncrease = $ii->$iiStatColumnToIncrease + $this->quantity;
        $ii->save();

        return $ii;
    }
}
