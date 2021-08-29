<?php

namespace App\Rules;

use App\Models\Purchase;
use Illuminate\Contracts\Validation\Rule;

class UniqueSizeAvailabilityForPurchase implements Rule
{
    public static function bmdValidate($extraValidationData)
    {
        $d = $extraValidationData;

        $purchase = Purchase::find($d['purchaseId']);

        foreach ($purchase->purchaseItems as $pi) {

            // If updating purchase-item, no need to check its own size-availability-id.
            if ($pi->id == $d['purchaseItemId']) {
                continue;
            }

            if ($pi->size_availability_id == $d['sizeAvailabilityId']) {
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
