<?php

namespace App\Http\BmdHttpResponseCodes;



class PurchaseItemHttpResponseCodes
{
    public const PURCHASE_ITEM_SELLER_SHOULD_EQUAL_PURCHASE_SELLER = [
        'code' => 'PURCHASE_ITEM_SELLER_SHOULD_EQUAL_PURCHASE_SELLER-1001', 
        'message' => 'PURCHASE_ITEM_SELLER_SHOULD_EQUAL_PURCHASE_SELLER', 
        'readableMessage' => 'The purchase-item-seller(id) must reference the same seller as the purchase.'
    ];

    public const SIZE_AVAILABILITY_DOES_NOT_BELONG_TO_SELLER_PRODUCT = [
        'code' => 'SIZE_AVAILABILITY_DOES_NOT_BELONG_TO_SELLER_PRODUCT-1001', 
        'message' => 'SIZE_AVAILABILITY_DOES_NOT_BELONG_TO_SELLER_PRODUCT', 
        'readableMessage' => 'The size-availability-id does not belong to the seller-product.'
    ];
}