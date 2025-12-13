<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobseekerKycVerification
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures jobseekers have completed KYC verification
     * before they can apply for jobs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Only check for authenticated users
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to continue');
        }

        $user = Auth::user();

        // Only enforce KYC for jobseekers
        if ($user->role === 'jobseeker') {
            // Check if KYC is verified
            if (!$user->isKycVerified()) {
                // Handle AJAX requests differently
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You must complete KYC verification before applying for jobs.',
                        'redirect' => route('kyc.index')
                    ], 403);
                }

                // For regular requests, redirect to KYC page with error message
                return redirect()
                    ->route('kyc.index')
                    ->with('error', 'You must complete KYC verification before you can apply for jobs. Please verify your identity to continue.');
            }
        }

        return $next($request);
    }
}
