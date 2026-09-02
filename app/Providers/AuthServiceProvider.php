<?php

namespace App\Providers;

use App\Models\Event;
use App\Policies\EventPolicy;
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
        Event::class => EventPolicy::class,
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
        Gate::define('photographer-account', function ($user) {
            return $user->hasRole('photographer') && $user->photographer !== null;
        });
        Gate::define('access-photographer', function ($user) {
            return $user->canAccessPhotographerArea();
        });
        // teacher access
        Gate::define('access-customer', callback: function ($user) {
            return $user->roles->contains('name', 'customer');
        });


    }
}
