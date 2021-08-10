<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\DailySummaryPolicy;
use App\Policies\GeneralManagementPolicy;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        
        Passport::routes();


        Gate::define('view-dailySummary', [DailySummaryPolicy::class, 'view']);

        Gate::define('update-as-manager', function (User $u) {
            return GeneralManagementPolicy::canUpdate($u);
        });
    }
}
