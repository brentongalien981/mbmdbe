<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GeneralManagementPolicy
{
    use HandlesAuthorization;


    public static function canUpdate(User $u)
    {
        $orderManagerRole = Role::where('name', 'OrderManager')->get()[0];

        $userRoleIds = [];

        foreach ($u->roles as $r) {
            $userRoleIds[] = $r->id;
        }

        if (in_array($orderManagerRole->id, $userRoleIds)) { return true; }

        return false;
    }
}
