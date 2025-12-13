<?php

namespace App\Http\Middleware;

use App\Services\LoggingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Log HTTP Requests and Responses
 *
 * Logs all incoming requests and outgoing responses for monitoring and debugging.
 */
class LogRequests
{
    /**
     * The logging service
     *
     * @var LoggingService
     */
    protected $loggingService;

    /**
     * URIs that should not be logged
     *
     * @var array
     */
    protected $except = [
        'telescope*',
        'horizon*',
        '_debugbar*',
        'health-check',
        'ping',
    ];

    /**
     * Create a new middleware instance
     *
     * @param LoggingService $loggingService
     */
    public function __construct(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip logging for excluded URIs
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $startTime = microtime(true);
        $requestId = (string) Str::uuid();

        // Add request ID to request
        $request->headers->set('X-Request-ID', $requestId);

        // Log request
        $this->logRequest($request, $requestId);

        // Process request
        $response = $next($request);

        // Calculate response time
        $responseTime = microtime(true) - $startTime;

        // Log response
        $this->logResponse($request, $response, $responseTime, $requestId);

        // Add request ID to response headers
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }

    /**
     * Log the incoming request
     *
     * @param Request $request
     * @param string $requestId
     * @return void
     */
    protected function logRequest(Request $request, string $requestId): void
    {
        $context = [
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
        ];

        // Add user info if authenticated
        if ($request->user()) {
            $context['user_id'] = $request->user()->id;
            $context['user_email'] = $request->user()->email;
        }

        // Add request body for non-GET requests (excluding sensitive data)
        if (!$request->isMethod('GET')) {
            $context['request_data'] = $this->filterSensitiveData($request->except([
                'password',
                'password_confirmation',
                'current_password',
                'token',
                'api_key',
                'secret',
            ]));
        }

        $this->loggingService->log(
            LoggingService::LEVEL_INFO,
            sprintf('Incoming request: %s %s', $request->method(), $request->path()),
            $context,
            'api'
        );
    }

    /**
     * Log the outgoing response
     *
     * @param Request $request
     * @param $response
     * @param float $responseTime
     * @param string $requestId
     * @return void
     */
    protected function logResponse(Request $request, $response, float $responseTime, string $requestId): void
    {
        $statusCode = $response->status();
        $level = $this->getLogLevel($statusCode);

        $context = [
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => $statusCode,
            'response_time' => round($responseTime * 1000, 2) . 'ms',
            'response_time_seconds' => $responseTime,
            'memory_usage' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB',
        ];

        // Log errors with response content
        if ($statusCode >= 400) {
            $context['response_content'] = $this->getResponseContent($response);
        }

        // Log slow requests as warnings
        if ($responseTime > 2.0) {
            $level = LoggingService::LEVEL_WARNING;
            $context['slow_request'] = true;
            $context['threshold'] = '2 seconds';
        }

        $this->loggingService->log(
            $level,
            sprintf('Response: %s %s - %d (%sms)',
                $request->method(),
                $request->path(),
                $statusCode,
                round($responseTime * 1000, 2)
            ),
            $context,
            'api'
        );

        // Log performance metric
        $this->loggingService->logPerformance(
            sprintf('%s %s', $request->method(), $request->path()),
            $responseTime * 1000,
            'ms',
            ['status_code' => $statusCode]
        );
    }

    /**
     * Determine if request should be skipped from logging
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldSkip(Request $request): bool
    {
        foreach ($this->except as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get appropriate log level based on status code
     *
     * @param int $statusCode
     * @return string
     */
    protected function getLogLevel(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return LoggingService::LEVEL_ERROR;
        }

        if ($statusCode >= 400) {
            return LoggingService::LEVEL_WARNING;
        }

        return LoggingService::LEVEL_INFO;
    }

    /**
     * Filter sensitive data from request
     *
     * @param array $data
     * @return array
     */
    protected function filterSensitiveData(array $data): array
    {
        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'api_key',
            'secret',
            'api_token',
            'credit_card',
            'cvv',
            'ssn',
        ];

        foreach ($sensitiveKeys as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = '***REDACTED***';
            }
        }

        return $data;
    }

    /**
     * Get response content (limited)
     *
     * @param $response
     * @return string|null
     */
    protected function getResponseContent($response): ?string
    {
        $content = $response->getContent();

        if (strlen($content) > 1000) {
            return substr($content, 0, 1000) . '... (truncated)';
        }

        return $content;
    }
}
