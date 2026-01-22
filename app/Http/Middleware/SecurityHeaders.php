<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
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
        $response = $next($request);

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Strict Transport Security (HTTPS only)
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy
        // Note: 'unsafe-inline' and 'unsafe-eval' are needed for some JS frameworks
        // In production, consider using nonce-based CSP or removing inline scripts
        $isProduction = config('app.env') === 'production';

        // Base script sources - always needed
        $scriptSrc = "'self' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com https://api.mapbox.com";

        // In development, allow unsafe-inline and unsafe-eval for easier debugging
        // In production, these are still needed due to inline scripts in templates
        // TODO: Migrate inline scripts to external files to remove unsafe-inline
        $scriptSrc .= " 'unsafe-inline' 'unsafe-eval'";

        $csp = "default-src 'self'; " .
               "script-src {$scriptSrc}; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://api.mapbox.com; " .
               "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data:; " .
               "img-src 'self' data: https: blob:; " .
               "connect-src 'self' https://api.mapbox.com https://events.mapbox.com https://api.openai.com wss://*.mapbox.com; " .
               "frame-ancestors 'self'; " .
               "base-uri 'self'; " .
               "form-action 'self';";

        $response->headers->set('Content-Security-Policy', $csp);

        // Permissions Policy (formerly Feature Policy)
        $response->headers->set('Permissions-Policy',
            'geolocation=(self), ' .
            'microphone=(), ' .
            'camera=(), ' .
            'payment=()'
        );

        return $response;
    }
}
