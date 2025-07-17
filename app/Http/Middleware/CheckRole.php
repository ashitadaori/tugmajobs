<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            \Log::debug('User not authenticated');
            return redirect()->route('login');
        }

        $user = Auth::user();
        \Log::debug('User role check', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'required_role' => $role,
            'is_employer' => $user->isEmployer(),
            'is_jobseeker' => $user->isJobSeeker()
        ]);
        
        if ($role === 'employer' && !$user->isEmployer()) {
            \Log::debug('Access denied for employer');
            return redirect()->route('home')->with('error', 'Access denied. Employer access only.');
        }

        if ($role === 'jobseeker' && !$user->isJobSeeker()) {
            \Log::debug('Access denied for jobseeker');
            return redirect()->route('home')->with('error', 'Access denied. Job seeker access only.');
        }

        return $next($request);
    }
} 