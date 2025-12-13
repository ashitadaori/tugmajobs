<?php

namespace App\Http\Middleware;

use App\Models\MaintenanceSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Admins bypass maintenance mode
        if ($user && ($user->role === 'admin' || $user->role === 'superadmin')) {
            return $next($request);
        }

        // Check jobseeker maintenance
        if ($user && $user->role === 'jobseeker') {
            if (MaintenanceSetting::isMaintenanceActive('jobseeker')) {
                $message = MaintenanceSetting::getMaintenanceMessage('jobseeker');
                session()->flash('maintenance_warning', $message);
                
                // Block access to restricted routes
                $restrictedRoutes = [
                    'account.job.my-job-application',
                    'account.jobApplicationDetail',
                    'account.analytics',
                    'jobDetail',  // Fixed: correct route name
                    'account.saveJob',
                    'account.applyJob',
                ];
                
                // Check if current route matches any restricted route
                foreach ($restrictedRoutes as $route) {
                    if ($request->routeIs($route)) {
                        return redirect()->route('account.dashboard')
                            ->with('error', 'This feature is temporarily unavailable due to maintenance. Please try again later.');
                    }
                }
            }
        }

        // Check employer maintenance
        if ($user && $user->role === 'employer') {
            if (MaintenanceSetting::isMaintenanceActive('employer')) {
                $message = MaintenanceSetting::getMaintenanceMessage('employer');
                session()->flash('maintenance_warning', $message);
                
                // Block access to restricted routes for employers
                $restrictedRoutes = [
                    // Job Management
                    'employer.jobs.create',
                    'employer.jobs.store',
                    'employer.jobs.edit',
                    'employer.jobs.update',
                    'employer.jobs.delete',
                    
                    // Application Management
                    'employer.applications.index',
                    'employer.applications.show',
                    'employer.applications.shortlisted',
                    'employer.applications.updateStatus',
                    'employer.applications.toggleShortlist',
                    
                    // Analytics
                    'employer.analytics.index',
                    'employer.analytics.overview',
                    'employer.analytics.jobs',
                    'employer.analytics.applicants',
                    'employer.analytics.export',
                    'employer.analytics.data',
                    'employer.analytics.sources',
                    
                    // Company Profile
                    'employer.profile.edit',
                    'employer.profile.update',
                ];
                
                // Check if current route matches any restricted route
                foreach ($restrictedRoutes as $route) {
                    if ($request->routeIs($route)) {
                        return redirect()->route('employer.dashboard')
                            ->with('error', 'This feature is temporarily unavailable due to maintenance. Please try again later.');
                    }
                }
            }
        }

        return $next($request);
    }
}
