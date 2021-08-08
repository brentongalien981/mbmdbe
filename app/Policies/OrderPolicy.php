<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;



    public function viewAny(User $u)
    {

        $orderManagerRole = Role::where('name', 'OrderManager')->get()[0];

        $userRoleIds = [];

        foreach ($u->roles as $r) {
            $userRoleIds[] = $r->id;
        }

        if (in_array($orderManagerRole->id, $userRoleIds)) { return true; }

        return false;
    }



    public function update(User $user)
    {
        return $this->viewAny($user);
    }
}
