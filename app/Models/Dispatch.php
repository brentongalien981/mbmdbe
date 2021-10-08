<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dispatch extends Model
{
    use HasFactory;



    public static function getAvailableDispatches()
    {

        $statuses = DispatchStatus::whereIn('name', ['EP_BATCH_CREATED', 'EP_BATCH_UPDATED'])->get()->pluck('code');

        return self::whereIn('status_code', $statuses)->orderBy('created_at', 'desc')->get();
    }



    public function orders()
    {
        return $this->hasMany(Order::class);
    }



    /**
     * Update all the status and stats of all related orders, order-items, and inventory-items.
     */
    public function onSuccessfulDispatch()
    {
        $dipatchedOrderStatusCode = OrderStatus::getCodeByName('DISPATCHED');
        $dipatchedOrderItemStatusCode = OrderItemStatus::getCodeByName('DISPATCHED');

        foreach ($this->orders as $o) {
            
            // Update orders.    
            $o->status_code = $dipatchedOrderStatusCode;
            $o->save();

            foreach ($o->orderItems as $oi) {

                // Update order-items
                $oldOrderItemStatusCode = $oi->status_code;
                $oi->status_code = $dipatchedOrderItemStatusCode;
                $oi->save();

                // Update inventory-item.
                $updatedInventoryItem = InventoryItem::updateStatsWithReferenceObj($oi, $oldOrderItemStatusCode);
            }
        }
    }
}
