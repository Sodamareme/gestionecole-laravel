<?php

namespace App\Providers;

use App\Repository\FirebaseUserRepository;
use App\Repository\UserRepositoryInterface;
use App\Services\FirebaseService;
use Illuminate\Support\ServiceProvider;
use App\Services\DataStorageInterface;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use App\Repository\ReferentielRepositoryInterface;
use App\Repository\ReferentielRepository;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DataStorageInterface::class, FirebaseService::class);
        $this->app->bind(UserRepositoryInterface::class, FirebaseUserRepository::class);
        
    $this->app->singleton(Auth::class, function ($app) {
        $firebase = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));

        return $firebase->createAuth();
    });
    $this->app->bind(ReferentielRepositoryInterface::class, ReferentielRepository::class);
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
