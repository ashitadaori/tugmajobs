<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Throwable;

/**
 * Centralized Logging Service
 *
 * Provides enhanced logging capabilities with context, user tracking,
 * performance monitoring, and structured logging.
 */
class LoggingService
{
    /**
     * Log levels
     */
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_NOTICE = 'notice';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';
    const LEVEL_ALERT = 'alert';
    const LEVEL_EMERGENCY = 'emergency';

    /**
     * Log channels
     */
    const CHANNEL_STACK = 'stack';
    const CHANNEL_DAILY = 'daily';
    const CHANNEL_SLACK = 'slack';
    const CHANNEL_SINGLE = 'single';
    const CHANNEL_STDERR = 'stderr';
    const CHANNEL_SYSLOG = 'syslog';

    /**
     * Log an exception with full context
     *
     * @param Throwable $exception
     * @param array $additionalContext
     * @param string $level
     * @return void
     */
    public function logException(Throwable $exception, array $additionalContext = [], string $level = self::LEVEL_ERROR): void
    {
        $context = array_merge([
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'trace' => $this->getCleanTrace($exception),
            'previous' => $exception->getPrevious() ? get_class($exception->getPrevious()) : null,
        ], $this->getRequestContext(), $additionalContext);

        Log::log($level, $exception->getMessage(), $context);
    }

    /**
     * Log a security event
     *
     * @param string $event
     * @param array $context
     * @return void
     */
    public function logSecurityEvent(string $event, array $context = []): void
    {
        $fullContext = array_merge([
            'event_type' => 'security',
            'event_name' => $event,
            'timestamp' => now()->toIso8601String(),
            'severity' => 'high',
        ], $this->getRequestContext(), $this->getUserContext(), $context);

        Log::channel('security')->warning($event, $fullContext);
    }

    /**
     * Log a performance metric
     *
     * @param string $metric
     * @param float $value
     * @param string $unit
     * @param array $context
     * @return void
     */
    public function logPerformance(string $metric, float $value, string $unit = 'ms', array $context = []): void
    {
        $fullContext = array_merge([
            'metric_type' => 'performance',
            'metric_name' => $metric,
            'value' => $value,
            'unit' => $unit,
            'timestamp' => now()->toIso8601String(),
        ], $context);

        Log::channel('performance')->info($metric, $fullContext);
    }

    /**
     * Log a database query issue
     *
     * @param string $query
     * @param float $executionTime
     * @param array $bindings
     * @return void
     */
    public function logSlowQuery(string $query, float $executionTime, array $bindings = []): void
    {
        Log::channel('database')->warning('Slow query detected', [
            'query' => $query,
            'execution_time' => $executionTime,
            'execution_time_ms' => round($executionTime * 1000, 2),
            'bindings' => $bindings,
            'threshold_exceeded' => true,
        ]);
    }

    /**
     * Log a business event
     *
     * @param string $event
     * @param array $context
     * @return void
     */
    public function logBusinessEvent(string $event, array $context = []): void
    {
        $fullContext = array_merge([
            'event_type' => 'business',
            'event_name' => $event,
            'timestamp' => now()->toIso8601String(),
        ], $this->getUserContext(), $context);

        Log::channel('business')->info($event, $fullContext);
    }

    /**
     * Log a user action
     *
     * @param string $action
     * @param array $context
     * @return void
     */
    public function logUserAction(string $action, array $context = []): void
    {
        $fullContext = array_merge([
            'action_type' => 'user_action',
            'action_name' => $action,
            'timestamp' => now()->toIso8601String(),
        ], $this->getUserContext(), $context);

        Log::channel('user_actions')->info($action, $fullContext);
    }

    /**
     * Log an API request/response
     *
     * @param string $endpoint
     * @param string $method
     * @param int $statusCode
     * @param float $responseTime
     * @param array $context
     * @return void
     */
    public function logApiCall(string $endpoint, string $method, int $statusCode, float $responseTime, array $context = []): void
    {
        $fullContext = array_merge([
            'api_endpoint' => $endpoint,
            'http_method' => $method,
            'status_code' => $statusCode,
            'response_time' => $responseTime,
            'response_time_ms' => round($responseTime * 1000, 2),
            'timestamp' => now()->toIso8601String(),
        ], $context);

        $level = $statusCode >= 500 ? self::LEVEL_ERROR : ($statusCode >= 400 ? self::LEVEL_WARNING : self::LEVEL_INFO);
        Log::channel('api')->log($level, "API Call: {$method} {$endpoint}", $fullContext);
    }

    /**
     * Log a cache operation
     *
     * @param string $operation
     * @param string $key
     * @param bool $hit
     * @param array $context
     * @return void
     */
    public function logCacheOperation(string $operation, string $key, bool $hit = null, array $context = []): void
    {
        $fullContext = array_merge([
            'operation' => $operation,
            'cache_key' => $key,
            'cache_hit' => $hit,
            'timestamp' => now()->toIso8601String(),
        ], $context);

        Log::channel('cache')->debug("Cache {$operation}: {$key}", $fullContext);
    }

    /**
     * Log a job execution
     *
     * @param string $jobClass
     * @param string $status
     * @param array $context
     * @return void
     */
    public function logJob(string $jobClass, string $status, array $context = []): void
    {
        $fullContext = array_merge([
            'job_class' => $jobClass,
            'job_status' => $status,
            'timestamp' => now()->toIso8601String(),
        ], $context);

        $level = $status === 'failed' ? self::LEVEL_ERROR : self::LEVEL_INFO;
        Log::channel('jobs')->log($level, "Job {$status}: {$jobClass}", $fullContext);
    }

    /**
     * Get request context
     *
     * @return array
     */
    protected function getRequestContext(): array
    {
        if (!app()->runningInConsole()) {
            $request = request();

            return [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'request_id' => $request->header('X-Request-ID') ?? uniqid('req_'),
            ];
        }

        return [
            'console' => true,
            'command' => $_SERVER['argv'] ?? null,
        ];
    }

    /**
     * Get user context
     *
     * @return array
     */
    protected function getUserContext(): array
    {
        if (Auth::check()) {
            $user = Auth::user();

            return [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role ?? null,
                'user_name' => $user->name,
            ];
        }

        return [
            'user_id' => null,
            'authenticated' => false,
        ];
    }

    /**
     * Get a clean stack trace (limit depth)
     *
     * @param Throwable $exception
     * @param int $limit
     * @return array
     */
    protected function getCleanTrace(Throwable $exception, int $limit = 10): array
    {
        $trace = $exception->getTrace();
        $cleanTrace = [];

        foreach (array_slice($trace, 0, $limit) as $frame) {
            $cleanTrace[] = [
                'file' => $frame['file'] ?? 'unknown',
                'line' => $frame['line'] ?? 0,
                'function' => $frame['function'] ?? 'unknown',
                'class' => $frame['class'] ?? null,
            ];
        }

        return $cleanTrace;
    }

    /**
     * Create a log context helper
     *
     * @param array $context
     * @return array
     */
    public static function context(array $context = []): array
    {
        return array_merge([
            'timestamp' => now()->toIso8601String(),
            'environment' => app()->environment(),
        ], $context);
    }

    /**
     * Log with full context (helper method)
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @param string|null $channel
     * @return void
     */
    public function log(string $level, string $message, array $context = [], ?string $channel = null): void
    {
        $fullContext = array_merge(
            $this->getRequestContext(),
            $this->getUserContext(),
            $context
        );

        if ($channel) {
            Log::channel($channel)->log($level, $message, $fullContext);
        } else {
            Log::log($level, $message, $fullContext);
        }
    }

    /**
     * Check if logging is enabled for a specific level
     *
     * @param string $level
     * @return bool
     */
    public function isEnabled(string $level): bool
    {
        $configuredLevel = config('logging.level', 'debug');
        $levels = [
            'debug' => 0,
            'info' => 1,
            'notice' => 2,
            'warning' => 3,
            'error' => 4,
            'critical' => 5,
            'alert' => 6,
            'emergency' => 7,
        ];

        return ($levels[$level] ?? 0) >= ($levels[$configuredLevel] ?? 0);
    }
}
