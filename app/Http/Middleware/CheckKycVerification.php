<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckKycVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !Auth::user()->isKycVerified()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Identity verification required',
                    'verification_url' => route('kyc.start.form')
                ], 403);
            }
            
            return redirect()->route('kyc.start.form')->with('error', 
                'You need to complete identity verification before accessing this feature.');
        }

        return $next($request);
    }
}