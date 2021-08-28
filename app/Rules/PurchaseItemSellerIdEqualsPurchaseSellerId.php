<?php

namespace App\Rules;

use App\Models\ProductSeller;
use App\Models\Purchase;
use Illuminate\Contracts\Validation\Rule;

class PurchaseItemSellerIdEqualsPurchaseSellerId implements Rule
{
    public static function bmdValidate($extraValidationData)
    {
        $d = $extraValidationData;

        $purchase = Purchase::find($d['purchaseId']);
        $sellerProduct = ProductSeller::find($d['sellerProductId']);

        if ($purchase->seller_id === $sellerProduct->seller_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
