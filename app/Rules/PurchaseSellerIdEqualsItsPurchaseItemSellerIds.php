<?php

namespace App\Rules;

use App\Models\ProductSeller;
use App\Models\Purchase;
use Illuminate\Contracts\Validation\Rule;

class PurchaseSellerIdEqualsItsPurchaseItemSellerIds implements Rule
{
    public static function bmdValidate($extraValidationData)
    {
        $d = $extraValidationData;

        $purchase = Purchase::find($d['purchaseId']);

        foreach ($purchase->purchaseItems as $pi) {

            $piSellerProduct = ProductSeller::find($pi->seller_product_id);

            if ($d['newSellerId'] != $piSellerProduct->seller_id) {
                return false;
            }
        }

        return true;
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
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must refernce the same seller as the purchase-items.';
    }
}
