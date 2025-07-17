<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EncourageKycVerification
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->needsKycVerification()) {
            // Add a flash message to encourage KYC verification
            if (!session()->has('kyc_reminder_shown')) {
                session()->flash('kyc_reminder', [
                    'message' => 'Complete your identity verification to build trust and unlock all features.',
                    'action_url' => route('kyc.start.form'),
                    'action_text' => 'Verify Now'
                ]);
                session(['kyc_reminder_shown' => true]);
            }
        }

        return $next($request);
    }
}