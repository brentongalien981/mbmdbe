<?php

namespace App\Http\BmdHelpers;

use App\Models\OrderItem;
use App\Models\OrderItemStatus;

class OPIStatusToInventoryItemStatNameMapper
{

    public static function map($classPath, $statusCode)
    {
        $inventoryItemStatColumnName = null;

        switch ($classPath) {
            case OrderItem::class:
                $inventoryItemStatColumnName = self::mapOrderItemStatusToInventoryItemStatColumnName($statusCode);
                break;
        }

        return $inventoryItemStatColumnName;
    }



    private static function mapOrderItemStatusToInventoryItemStatColumnName($statusCode)
    {
        $orderItemStatus = OrderItemStatus::where('code', $statusCode)->get()[0];

        $iiColumnName = null;

        switch ($orderItemStatus->name) {
            case 'PURCHASE_INCOMPLETELY_RECEIVED':
                $iiColumnName = 'received_incomplete_quantity';
                break;
            case 'TO_BE_PURCHASED':
                $iiColumnName = 'to_be_purchased_quantity';
                break;
            case 'PURCHASED':
            case 'TO_BE_PURCHASE_RECEIVED':
                $iiColumnName = 'to_be_received_quantity';
                break;
            case 'PURCHASE_RECEIVED':
                $iiColumnName = 'received_quantity';
                break;
            case 'IN_STOCK':
                $iiColumnName = 'in_stock_quantity';
                break;
            case 'TO_BE_PACKAGED':
                $iiColumnName = 'to_be_packaged_quantity';
                break;
            case 'BEING_PACKAGED':
                $iiColumnName = 'being_packaged_quantity';
                break;
            case 'PACKAGED':
                $iiColumnName = 'packaged_quantity';
                break;
            case 'TO_BE_DISPATCHED':
                $iiColumnName = 'to_be_dispatched_quantity';
                break;
            case 'DISPATCHED':
                $iiColumnName = 'dispatched_quantity';
                break;
            case 'MISSING_ORDER_ITEM':
            case 'BROKEN_ORDER_ITEM':
            case 'OTHER_ORDER_ITEM_PROBLEMS':
                $iiColumnName = 'with_order_item_problems_quantity';
                break;
            case 'TOO_LATE_TO_DELIVER':
            case 'TOO_EXPENSIVE_TO_DELIVER':
                $iiColumnName = 'with_order_problems_quantity';
                break;
                // case 'EVALUATED_INCOMPLETELY_FOR_PURCHASE':                
                //     break;
            default:
                $iiColumnName = 'all_non_dispatched_status_quantity';
                break;
        }

        return $iiColumnName;
    }
}
