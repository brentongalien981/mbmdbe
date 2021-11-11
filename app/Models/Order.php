<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;



    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;



    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';



    protected $guarded = [];



    public static function getYesterdaysOrders()
    {

        $numOfSecInDay = 86400;
        $dateObjToday = getdate();
        $dateObjYesterday = getdate($dateObjToday[0] - $numOfSecInDay);

        $startDateInStr = $dateObjYesterday['year'] . '-' . $dateObjYesterday['mon'] . '-' . $dateObjYesterday['mday'];
        $endDateInStr = $startDateInStr;


        return self::getOrdersByInclusivePeriod($startDateInStr, $endDateInStr);
    }



    public static function getReadableDate($dateTime)
    {
        $d = getdate(strtotime($dateTime));

        $str = $d['weekday'] . ', ' . $d['month'] . ' ' . $d['mday'] . ', ' . $d['year'];
        return $str;
    }



    public static function getOrdersByInclusivePeriod($from, $to)
    {
        $to .= ' 23:59:59';

        $orders = self::where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->get();

        return $orders;
    }



    public static function updateOrdersStatusesWithDatePeriod($from, $to)
    {
        $orders = self::getOrdersByInclusivePeriod($from, $to);

        foreach ($orders as $o) {
            $o->updateStatusBasedOnOrderItemsStatuses();
        }
    }



    public static function updateYesterdaysOrdersStatus()
    {
        $yesterdaysOrders = self::getYesterdaysOrders();

        foreach ($yesterdaysOrders as $o) {
            $o->updateStatusBasedOnOrderItemsStatuses();
        }
    }



    public function updateStatusBasedOnOrderItemsStatuses()
    {
        if (!$this->canUpdateStatusBasedOnOrderItemsStatuses()) { return; }

        $orderItems = $this->orderItems;
        $numOfOrderItems = count($orderItems);
        $numOfOrderItemsWithToBePurchasedStatus = 0;


        $toBePurchasedStatus = OrderItemStatus::where('name', OrderItemStatus::NAME_FOR_STATUS_TO_BE_PURCHASED)->get()[0];
        
        foreach ($orderItems as $oi) {
            if ($oi->status_code == $toBePurchasedStatus->code) {
                ++$numOfOrderItemsWithToBePurchasedStatus;
            }
        }


        if ($numOfOrderItemsWithToBePurchasedStatus == $numOfOrderItems) {
            $this->status_code = OrderStatus::getCodeByName('TO_BE_PURCHASED');
        } else if ($numOfOrderItemsWithToBePurchasedStatus == 0) {
            $this->status_code = OrderStatus::getCodeByName('BEING_EVALUATED_FOR_PURCHASE');
        } else {
            $this->status_code = OrderStatus::getCodeByName('EVALUATED_INCOMPLETELY_FOR_PURCHASE');
        }

        $this->save();
    }



    public function canUpdateStatusBasedOnOrderItemsStatuses()
    {
        $allowedOrderStatuses = OrderStatus::whereIn('name', [
            'ORDER_CONFIRMED',
            'ORDER_DETAILS_EMAILED_TO_USER',
            'BEING_EVALUATED_FOR_PURCHASE',
            'EVALUATED_INCOMPLETELY_FOR_PURCHASE',                        
        ])->get()->pluck('code');


        if (in_array($this->status_code, $allowedOrderStatuses)) {
            return true;
        }

        return false;
    }



    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
