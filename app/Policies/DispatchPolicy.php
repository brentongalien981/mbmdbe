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
}
