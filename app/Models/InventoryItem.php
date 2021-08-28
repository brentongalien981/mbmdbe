<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
