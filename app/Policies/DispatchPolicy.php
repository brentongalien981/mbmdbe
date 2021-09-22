<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DispatchPolicy
{
    use HandlesAuthorization;



    public function mbmdDoAny(User $user)
    {
        $dispatchManagerRole = Role::where('name', 'DispatchManager')->get()[0];

        $userRoleIds = [];

        foreach ($user->roles as $r) {
            $userRoleIds[] = $r->id;
        }

        if (in_array($dispatchManagerRole->id, $userRoleIds)) { return true; }

        return false;
    }



    public function checkPossibleShipping(User $user)
    {
        $orderManagerRole = Role::where('name', 'OrderManager')->get()[0];
        $inventoryManagerRole = Role::where('name', 'InventoryManager')->get()[0];
        $dispatchManagerRole = Role::where('name', 'DispatchManager')->get()[0];

        $userRoleIds = [];

        foreach ($user->roles as $r) {
            $userRoleIds[] = $r->id;
        }

        if (in_array($orderManagerRole->id, $userRoleIds)) { return true; }
        if (in_array($inventoryManagerRole->id, $userRoleIds)) { return true; }
        if (in_array($dispatchManagerRole->id, $userRoleIds)) { return true; }

        return false;
    }
}
