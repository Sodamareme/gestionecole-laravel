<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Services\AuthentificationServiceInterface;
use App\Services\AuthentificationPassport;
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

    public function register()
    {
        // Lié à l'implémentation par défaut AuthentificationPassport
        $this->app->bind(AuthentificationServiceInterface::class, AuthentificationPassport::class);
    }

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('manage-users', function ($user) {
            return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_MANAGER]);
        });

        Gate::define('manage-apprenants', function ($user) {
            return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_MANAGER, User::ROLE_CM]);
        });
    }
}
