<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitByAction
{
    /**
     * Rate limits for different actions
     */
    protected array $limits = [
        'login' => ['attempts' => 5, 'decay' => 60],           // 5 per minute
        'register' => ['attempts' => 3, 'decay' => 60],        // 3 per minute
        'password_reset' => ['attempts' => 3, 'decay' => 60],  // 3 per minute
        'job_apply' => ['attempts' => 10, 'decay' => 60],      // 10 per minute
        'job_create' => ['attempts' => 5, 'decay' => 60],      // 5 per minute
        'message_send' => ['attempts' => 20, 'decay' => 60],   // 20 per minute
        'profile_update' => ['attempts' => 10, 'decay' => 60], // 10 per minute
        'search' => ['attempts' => 30, 'decay' => 60],         // 30 per minute
        'api_general' => ['attempts' => 60, 'decay' => 60],    // 60 per minute
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $action = 'api_general'): Response
    {
        $key = $this->resolveRequestKey($request, $action);
        $limit = $this->limits[$action] ?? $this->limits['api_general'];

        if (RateLimiter::tooManyAttempts($key, $limit['attempts'])) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $seconds,
            ], 429, [
                'Retry-After' => $seconds,
                'X-RateLimit-Limit' => $limit['attempts'],
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        RateLimiter::hit($key, $limit['decay']);

        $response = $next($request);

        // Add rate limit headers to response
        $remaining = RateLimiter::remaining($key, $limit['attempts']);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $limit['attempts'],
            'X-RateLimit-Remaining' => max(0, $remaining),
        ]);
    }

    /**
     * Resolve the request key for rate limiting
     */
    protected function resolveRequestKey(Request $request, string $action): string
    {
        $identifier = $request->user()?->id ?? $request->ip();
        return "rate_limit:{$action}:{$identifier}";
    }
}
