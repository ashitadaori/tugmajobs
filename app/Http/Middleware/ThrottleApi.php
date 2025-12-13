<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ThrottleApi
{
    /**
     * Handle an incoming request.
     *
     * This middleware extends Laravel's built-in throttling with custom limits
     * based on user authentication status and endpoint sensitivity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  int  $maxAttempts  Maximum number of attempts
     * @param  int  $decayMinutes  Time window in minutes
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);

        $maxAttempts = $this->resolveMaxAttempts($request, $maxAttempts);

        if (app('cache')->has($key . ':lockout')) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => app('cache')->get($key . ':lockout')
            ], 429);
        }

        $attempts = app('cache')->get($key, 0);

        if ($attempts >= $maxAttempts) {
            app('cache')->put($key . ':lockout', time() + ($decayMinutes * 60), $decayMinutes);

            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $decayMinutes * 60
            ], 429);
        }

        app('cache')->put($key, $attempts + 1, $decayMinutes);

        return $next($request);
    }

    /**
     * Resolve the request signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request)
    {
        if ($user = $request->user()) {
            return sha1($user->id . '|' . $request->ip());
        }

        return sha1($request->ip());
    }

    /**
     * Resolve the maximum number of attempts based on the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $maxAttempts
     * @return int
     */
    protected function resolveMaxAttempts(Request $request, $maxAttempts)
    {
        // Authenticated users get higher limits
        if ($request->user()) {
            return $maxAttempts * 2;
        }

        return $maxAttempts;
    }
}
