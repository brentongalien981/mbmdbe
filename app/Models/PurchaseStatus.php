<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseStatus extends Model
{
    use HasFactory;


    public const NAME_FOR_STATUS_PURCHASE_INCOMPLETELY_RECEIVED = 'PURCHASE_INCOMPLETELY_RECEIVED';
    public const NAME_FOR_STATUS_EVALUATED_INCOMPLETELY_FOR_PURCHASE = 'EVALUATED_INCOMPLETELY_FOR_PURCHASE';
    public const NAME_FOR_STATUS_DEFAULT = 'DEFAULT';
    public const NAME_FOR_STATUS_TO_BE_PURCHASED = 'TO_BE_PURCHASED';
    public const NAME_FOR_STATUS_PURCHASED = 'PURCHASED';
    public const NAME_FOR_STATUS_TO_BE_PURCHASE_RECEIVED = 'TO_BE_PURCHASE_RECEIVED';
    public const NAME_FOR_STATUS_PURCHASE_RECEIVED = 'PURCHASE_RECEIVED';
    public const NAME_FOR_STATUS_IN_STOCK = 'IN_STOCK';
    public const NAME_FOR_STATUS_TO_BE_PACKAGED = 'TO_BE_PACKAGED';
    public const NAME_FOR_STATUS_PACKAGED = 'PACKAGED';
    public const NAME_FOR_STATUS_TO_BE_DISPATCHED = 'TO_BE_DISPATCHED';
    public const NAME_FOR_STATUS_DISPATCHED = 'DISPATCHED';

    protected $table = 'purchase_status';
}
