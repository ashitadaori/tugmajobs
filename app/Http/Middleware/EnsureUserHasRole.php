<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        // If user is not authenticated, let other middleware handle it
        if (!$user) {
            return $next($request);
        }
        
        // If user doesn't have a role, redirect to role selection
        if (empty($user->role)) {
            return redirect()->route('auth.select-role')
                ->with('info', 'Please select your account type to continue.');
        }
        
        return $next($request);
    }
}