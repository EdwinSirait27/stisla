<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
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

        //
        Gate::define('isSU', function (User $user) {
            return ($user->role ?? '') === 'isSU';
        });
        Gate::define('isKasir', function (User $user) {
            return ($user->role ?? '') === 'Kasir';
        });
        Gate::define('isSupervisor', function (User $user) {
            return ($user->role ?? '') === 'Supervisor';
        });
        Gate::define('isSupervisor', function (User $user) {
            return ($user->role ?? '') === 'Supervisor';
        });
        
    }
}
