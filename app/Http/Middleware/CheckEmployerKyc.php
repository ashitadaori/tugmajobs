<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckEmployerKyc
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
        // Only apply KYC restrictions to employers
        if (Auth::check() && Auth::user()->isEmployer()) {
            $user = Auth::user();
            
            // Allow verified employers to proceed
            if ($user->kyc_status === 'verified') {
                Log::info('Verified employer allowed to post jobs', [
                    'user_id' => Auth::id(),
                    'route' => $request->route()->getName(),
                    'kyc_status' => $user->kyc_status,
                    'verified_at' => $user->kyc_verified_at
                ]);
                return $next($request);
            }
            
            // For development/testing - allow if KYC is disabled via config
            if (config('app.disable_kyc_for_employers', false)) {
                Log::info('KYC checks disabled for employers via config', [
                    'user_id' => Auth::id(),
                    'route' => $request->route()->getName()
                ]);
                return $next($request);
            }
            
            // Check detailed verification status for unverified employers
            $verificationStatus = $user->getEmployerVerificationStatus();
            
            // Block unverified employers
            if (!$user->canPostJobs()) {
                Log::info('Employer verification check failed', [
                    'user_id' => Auth::id(),
                    'route' => $request->route()->getName(),
                    'kyc_status' => $user->kyc_status,
                    'verification_status' => $verificationStatus['status'],
                    'has_required_documents' => method_exists($user, 'hasRequiredDocumentsApproved') ? $user->hasRequiredDocumentsApproved() : false
                ]);
                
                $message = match($verificationStatus['status']) {
                    'kyc_pending' => 'You must complete KYC verification before posting jobs.',
                    'documents_pending' => 'You must submit and get approval for all required documents before posting jobs.',
                    default => 'You must complete verification requirements before posting jobs.'
                };
                
                // For AJAX requests, return JSON response
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'error' => 'Verification required',
                        'message' => $message,
                        'kyc_status' => $user->kyc_status,
                        'verification_status' => $verificationStatus['status'],
                        'show_modal' => $verificationStatus['status'] === 'kyc_pending',
                        'redirect_to_documents' => $verificationStatus['status'] === 'documents_pending'
                    ], 403);
                }
                
                // For regular requests, redirect appropriately
                if ($verificationStatus['status'] === 'kyc_pending') {
                    return redirect()->back()
                        ->with('show_kyc_modal', true)
                        ->with('error', $message);
                } else {
                    return redirect()->route('employer.documents.index')
                        ->with('error', $message);
                }
            }
        }

        return $next($request);
    }
}