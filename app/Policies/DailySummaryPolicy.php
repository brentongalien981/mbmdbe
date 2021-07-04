<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DailySummaryPolicy
{
    use HandlesAuthorization;

    public function view(User $u)
    {
        $orderManagerRole = Role::where('name', 'OrderManager')->get()[0];
        $salesManagerRole = Role::where('name', 'SalesManager')->get()[0];
        $financeManagerRole = Role::where('name', 'FinanceManager')->get()[0];

        $userRoleIds = [];

        foreach ($u->roles as $r) {
            $userRoleIds[] = $r->id;
        }

        if (in_array($orderManagerRole->id, $userRoleIds)) { return true; }
        if (in_array($salesManagerRole->id, $userRoleIds)) { return true; }
        if (in_array($financeManagerRole->id, $userRoleIds)) { return true; }

        return false;
    }
}
