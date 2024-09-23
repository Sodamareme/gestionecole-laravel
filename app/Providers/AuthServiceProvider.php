<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Services\AuthentificationServiceInterface;
use App\Services\AuthentificationPassport;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\UserPolicy;
use Laravel\Passport\Passport;
use App\Models\Apprenant;
use App\Models\Role;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        User::class => UserPolicy::class,
    ];


    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    // Définir les scopes disponibles
    Passport::tokensCan([
        'view-user-data' => 'Voir les données utilisateur',
        'admin' => 'Accès administrateur',
    ]);
        Gate::define('manage-users', function ($user) {
            return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_MANAGER]);
        });

        Gate::define('manage-apprenants', function ($user) {
            return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_MANAGER, User::ROLE_CM]);
        });
    }
}
