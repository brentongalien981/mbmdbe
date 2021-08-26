<?php

namespace App\Http\BmdHttpResponseCodes;



class PurchaseHttpResponseCodes
{
    public const PURCHASE_SELLER_SHOULD_EQUAL_PURCHASE_ITEMS_SELLERS = [
        'code' => 'PURCHASE_SELLER_SHOULD_EQUAL_PURCHASE_ITEMS_SELLERS-1001', 
        'message' => 'PURCHASE_SELLER_SHOULD_EQUAL_PURCHASE_ITEMS_SELLERS', 
        'readableMessage' => 'The seller(id) must reference the same seller as the purchase-items.'
    ];
}