<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register module specific bindings
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load module views
        $this->loadViewsFrom(__DIR__ . '/../Modules/Admin/Views', 'admin');
        $this->loadViewsFrom(__DIR__ . '/../Modules/Employer/Views', 'employer');
        $this->loadViewsFrom(__DIR__ . '/../Modules/JobSeeker/Views', 'jobseeker');
    }
} 