<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Authentication\Repositories\AuthRepository;
use App\Modules\Authentication\Services\AuthService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthRepository::class);
        $this->app->bind(AuthService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
