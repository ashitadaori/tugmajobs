<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DiditService;
use App\Services\MockDiditService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Use mock Didit service in local environment
        $this->app->singleton(DiditService::class, function ($app) {
            if ($app->environment('local') && config('app.debug')) {
                return new MockDiditService();
            }
            
            return new DiditService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}