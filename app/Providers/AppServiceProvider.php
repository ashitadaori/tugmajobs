<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use App\Services\DiditService;
use App\Services\MockDiditService;
use App\Contracts\KycServiceInterface;
use App\Models\Job;
use App\Observers\JobObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind KycServiceInterface - use real DiditService by default
        // Use mock only when explicitly enabled via DIDIT_USE_MOCK=true
        $this->app->bind(KycServiceInterface::class, function ($app) {
            if (env('DIDIT_USE_MOCK', false)) {
                return new MockDiditService();
            }
            
            return new DiditService();
        });
        
        // Also bind DiditService directly for backward compatibility
        $this->app->bind(DiditService::class, function ($app) {
            return $app->make(KycServiceInterface::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Job Observer for automatic content analysis
        Job::observe(JobObserver::class);

        // Force the application URL from config to prevent localhost redirects
        if (config('app.url')) {
            \URL::forceRootUrl(config('app.url'));
        }

        // Force HTTPS for all URLs when APP_URL uses https
        if (str_starts_with(config('app.url'), 'https')) {
            \URL::forceScheme('https');
        }

        // Set default pagination view (no SVG arrows)
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.simple-admin');
        \Illuminate\Pagination\Paginator::defaultSimpleView('vendor.pagination.simple-admin');
        
        // Load navigation helper functions
        require_once app_path('Helpers/NavigationHelper.php');
        
        // View composer for employer layouts
        View::composer(['front.layouts.employer-*', 'front.account.employer.*'], function ($view) {
            if (auth()->check() && auth()->user()->role === 'employer') {
                $user = auth()->user();
                $employerProfile = \App\Models\Employer::where('user_id', $user->id)->first();

                $view->with([
                    'employerProfile' => $employerProfile,
                    'user' => $user
                ]);
            }
        });
        
        // View composer for admin sidebar
        View::composer('admin.sidebar', function ($view) {
            $pendingJobsCount = 0;
            $kycPendingCount = 0;
            
            if (auth()->check() && in_array(auth()->user()->role, ['admin', 'superadmin'])) {
                $pendingJobsCount = \App\Models\Job::where('status', 'pending')->count();
                $kycPendingCount = \App\Models\User::where('kyc_status', 'pending')->count();
            }
            
            $view->with([
                'pendingJobsCount' => $pendingJobsCount,
                'kycPendingCount' => $kycPendingCount
            ]);
        });
    }
}