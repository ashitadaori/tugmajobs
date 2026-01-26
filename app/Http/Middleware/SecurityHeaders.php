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
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Security Headers

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Strict Transport Security (HSTS) - Enforces HTTPS
        // Max-age is set to 1 year (31536000 seconds)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // Content Security Policy (CSP)
        // This is a permissive policy to start with, allowing inline scripts and styles
        // You should tighten this up over time by removing 'unsafe-inline' and 'unsafe-eval'
        $csp = "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; " .
            "style-src 'self' 'unsafe-inline' https:; " .
            "img-src 'self' data: https:; " .
            "font-src 'self' https: data:; " .
            "connect-src 'self' https:; " .
            "frame-src 'self' https:;";

        $response->headers->set('Content-Security-Policy', $csp);

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
