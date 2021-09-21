<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Http\BmdHelpers\OPIStatusToInventoryItemStatNameMapper;

class InventoryItem extends Model
{
    use HasFactory;



    public static function mapStatusNameToStatColumnName($statusName)
    {
        $iiColumnName = '';

        switch ($statusName) {
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
            case 'PACKAGED':
                $iiColumnName = 'packaged_quantity';
                break;
            case 'TO_BE_DISPATCHED':
                $iiColumnName = 'to_be_dispatched_quantity';
                break;
            case 'DISPATCHED':
                $iiColumnName = 'dispatched_quantity';
                break;
                // case 'EVALUATED_INCOMPLETELY_FOR_PURCHASE':                
                //     break;
            default:
                $iiColumnName = 'all_non_dispatched_status_quantity';
                break;
        }

        return $iiColumnName;
    }



    public static function updateStatsWithReferenceObj($refObj, $oldStatusCode)
    {
        // Get the ref-obj-type: order, purhcase, other.
        $refObjClass = get_class($refObj);
        $inventoryItem = self::where('size_availability_id', $refObj->size_availability_id)->get()[0];


        // Update inventory-item-stat-column to decrement.
        $inventoryItemColumnToDecrement = OPIStatusToInventoryItemStatNameMapper::map($refObjClass, $oldStatusCode);
        $decrementedQuantity = $inventoryItem->$inventoryItemColumnToDecrement - $refObj->quantity;
        if ($decrementedQuantity >= 0) {
            $inventoryItem->$inventoryItemColumnToDecrement -= $refObj->quantity;
        }


        // Update inventory-item-stat-column to increment.
        $inventoryItemColumnToIncrement = OPIStatusToInventoryItemStatNameMapper::map($refObjClass, $refObj->status_code);
        $inventoryItem->$inventoryItemColumnToIncrement += $refObj->quantity;


        $inventoryItem->save();
        return $inventoryItem;
    }
}
