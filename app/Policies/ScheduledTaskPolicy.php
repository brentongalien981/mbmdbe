<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\ScheduledTask;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScheduledTaskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $this->generalProcess($user);
    }



    public function execute(User $user)
    {
        return $this->generalProcess($user);
    }



    public function generalProcess(User $user)
    {
        $orderManagerRole = Role::where('name', 'OrderManager')->get()[0];
        $inventoryManagerRole = Role::where('name', 'InventoryManager')->get()[0];

        $userRoleIds = [];

        foreach ($user->roles as $r) {
            $userRoleIds[] = $r->id;
        }

        if (!in_array($orderManagerRole->id, $userRoleIds)) { return false; }
        if (!in_array($inventoryManagerRole->id, $userRoleIds)) { return false; }

        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ScheduledTask  $scheduledTask
     * @return mixed
     */
    public function view(User $user, ScheduledTask $scheduledTask)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ScheduledTask  $scheduledTask
     * @return mixed
     */
    public function update(User $user, ScheduledTask $scheduledTask)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ScheduledTask  $scheduledTask
     * @return mixed
     */
    public function delete(User $user, ScheduledTask $scheduledTask)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ScheduledTask  $scheduledTask
     * @return mixed
     */
    public function restore(User $user, ScheduledTask $scheduledTask)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ScheduledTask  $scheduledTask
     * @return mixed
     */
    public function forceDelete(User $user, ScheduledTask $scheduledTask)
    {
        //
    }
}
