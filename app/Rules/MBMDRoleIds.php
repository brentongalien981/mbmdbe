<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\BmdCacheObjects\RoleModelCollectionCacheObject;

class MBMDRoleIds implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        if (!isset($value) || count($value) == 0) { return false; }

        $roles = RoleModelCollectionCacheObject::getUpdatedModelCollection()->data;
        
        foreach ($value as $roleId) {

            $doesRoleIdExist = false;

            foreach ($roles as $r) {
                if ($roleId == $r->id) {
                    $doesRoleIdExist = true;
                    break;
                }
            }

            if (!$doesRoleIdExist) { return false; }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid role.';
    }
}
