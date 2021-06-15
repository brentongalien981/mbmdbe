<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function create(User $u)
    {
        $userManagerRole = Role::where('name', 'UserManager')->get()[0];

        foreach ($u->roles as $r) {
            if ($userManagerRole->id == $r->id) {
                return true;
            }
        }

        return false;
    }
}
