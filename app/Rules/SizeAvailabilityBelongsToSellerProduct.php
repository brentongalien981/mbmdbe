<?php

namespace App\Rules;

use App\Models\ProductSeller;
use App\Models\SizeAvailability;
use Illuminate\Contracts\Validation\Rule;

class SizeAvailabilityBelongsToSellerProduct implements Rule
{
    public static function bmdValidate($extraValidationData)
    {
        $d = $extraValidationData;

        $sellerProduct = ProductSeller::find($d['sellerProductId']);
        $sizeAvailability = SizeAvailability::find($d['sizeAvailabilityId']);

        if ($sizeAvailability->seller__product_id === $sellerProduct->id) {
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
