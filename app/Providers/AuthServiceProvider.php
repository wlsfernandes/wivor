<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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

        // Define gate for admin
        Gate::define('access-admin', function ($user) {
            return $user->roles->contains('name', 'admin');
        });
        // student access
        Gate::define('access-photographer', function ($user) {
            return $user->roles->contains('name', 'photographer');
        });
        // teacher access
        Gate::define('access-customer', callback: function ($user) {
            return $user->roles->contains('name', 'customer');
        });


    }
}
