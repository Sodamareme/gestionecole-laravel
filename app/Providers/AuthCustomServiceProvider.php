<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AuthenticationServiceInterface;
use App\Services\AuthentificationPassport;
use App\Services\AuthentificationSanctum;
class AuthCustomServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AuthenticationServiceInterface::class, AuthentificationPassport::class);
        // Vous pouvez changer AuthentificationPassport par AuthentificationSanctum si vous voulez utiliser Sanctum
    }

    public function boot()
    {
        // Code de démarrage si nécessaire
    }
}
