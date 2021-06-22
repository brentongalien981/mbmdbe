<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;


    public static function updateYesterdaysOrdersStatus() {
        $yesterdaysOrders = self::getYesterdaysOrders();

        foreach ($yesterdaysOrders as $o) {
            $o->updateStatus();
        }
    }



    public function updateStatus() {

        $orderItems = $this->orderItems;
        $numOfOrderItems = count($orderItems);
        $numOfOrderItemsWithToBePurchasedStatus = 0;


        foreach ($orderItems as $oi) {
            $toBePurchasedStatus = OrderItemStatus::where('name', OrderItemStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0];
            if ($oi->status_code == $toBePurchasedStatus->code) {
                ++$numOfOrderItemsWithToBePurchasedStatus;
            }
        }


        if ($numOfOrderItemsWithToBePurchasedStatus == $numOfOrderItems) {
            $this->status_code = OrderStatus::where('name', OrderStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0]->code;
        } else if ($numOfOrderItemsWithToBePurchasedStatus == 0) {
            $this->status_code = OrderStatus::where('name', OrderStatus::NAME_FOR_STATUS_DEFAULT)->get()[0]->code;
        } else {
            $this->status_code = OrderStatus::where('name', OrderStatus::NAME_FOR_STATUS_EVALUATED_INCOMPLETELY_FOR_PURCHASE)->get()[0]->code;
        }

        $this->save();
    }



    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
