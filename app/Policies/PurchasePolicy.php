<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchasePolicy
{
    use HandlesAuthorization;



    
    public function mbmdDoAny(User $user)
    {
        $purchaseManagerRole = Role::where('name', 'PurchaseManager')->get()[0];

        $userRoleIds = [];

        foreach ($user->roles as $r) {
            $userRoleIds[] = $r->id;
        }

        if (in_array($purchaseManagerRole->id, $userRoleIds)) { return true; }

        return false;
    }
}
