<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request): ?string
    {
        if (!$request->expectsJson()) {
            // Determine the appropriate message based on the route
            $path = $request->path();
            $message = 'Please login to continue';
            
            if (str_contains($path, 'jobs')) {
                $message = 'Please login or register to browse jobs';
            } elseif (str_contains($path, 'companies')) {
                $message = 'Please login or register to view companies';
            } elseif (str_contains($path, 'apply')) {
                $message = 'Please login or register to apply for jobs';
            }
            
            // Store the message in session to be displayed as toast
            session()->flash('info', $message);
            
            return route('home');
        }
        
        return null;
    }
}
