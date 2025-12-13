<?php

namespace App\Exceptions;

use App\Services\LoggingService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        \Illuminate\Auth\AuthenticationException::class => 'warning',
        \Illuminate\Validation\ValidationException::class => 'info',
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class => 'warning',
        \Illuminate\Database\Eloquent\ModelNotFoundException::class => 'warning',
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'password_new',
        'password_old',
        'token',
        'api_key',
        'secret',
        'api_token',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // Enhanced logging for all exceptions
            if ($this->shouldReport($e)) {
                $loggingService = app(LoggingService::class);
                $loggingService->logException($e, [
                    'environment' => app()->environment(),
                    'app_version' => config('app.version', '1.0.0'),
                ]);
            }

            // Send to Sentry if configured
            if (app()->bound('sentry') && $this->shouldReport($e)) {
                app('sentry')->captureException($e);
            }
        });

        // Custom rendering for specific exceptions
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                    'error' => 'NOT_FOUND',
                ], 404);
            }

            return response()->view('errors.404', [
                'exception' => $e
            ], 404);
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested resource was not found',
                    'error' => 'MODEL_NOT_FOUND',
                ], 404);
            }

            return response()->view('errors.404', [
                'exception' => $e
            ], 404);
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'error' => 'UNAUTHENTICATED',
                ], 401);
            }

            return redirect()->guest(route('login'))
                ->with('error', 'Please login to continue');
        });

        $this->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSRF token mismatch',
                    'error' => 'TOKEN_MISMATCH',
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except($this->dontFlash))
                ->with('error', 'Your session has expired. Please try again.');
        });

        $this->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'error' => 'VALIDATION_ERROR',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Handle too many requests (rate limiting)
        $this->renderable(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests',
                    'error' => 'RATE_LIMIT_EXCEEDED',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
                ], 429);
            }

            return response()->view('errors.429', [
                'exception' => $e,
                'retryAfter' => $e->getHeaders()['Retry-After'] ?? null,
            ], 429);
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // Add custom error response for AJAX requests
        if ($request->expectsJson()) {
            return $this->renderJsonException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Render exception as JSON response
     *
     * @param $request
     * @param Throwable $e
     * @return JsonResponse
     */
    protected function renderJsonException($request, Throwable $e): JsonResponse
    {
        $statusCode = $this->getStatusCode($e);
        $response = [
            'success' => false,
            'message' => $this->getErrorMessage($e),
            'error' => $this->getErrorType($e),
        ];

        // Add additional details in debug mode
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(5)->map(function ($trace) {
                    return [
                        'file' => $trace['file'] ?? 'unknown',
                        'line' => $trace['line'] ?? 0,
                        'function' => $trace['function'] ?? 'unknown',
                    ];
                })->toArray(),
            ];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Get HTTP status code for exception
     *
     * @param Throwable $e
     * @return int
     */
    protected function getStatusCode(Throwable $e): int
    {
        if ($e instanceof HttpException) {
            return $e->getStatusCode();
        }

        if ($e instanceof ValidationException) {
            return 422;
        }

        if ($e instanceof AuthenticationException) {
            return 401;
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return 404;
        }

        return 500;
    }

    /**
     * Get user-friendly error message
     *
     * @param Throwable $e
     * @return string
     */
    protected function getErrorMessage(Throwable $e): string
    {
        if ($e instanceof ValidationException) {
            return 'Validation failed';
        }

        if ($e instanceof AuthenticationException) {
            return 'Unauthenticated';
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return 'Resource not found';
        }

        if ($e instanceof HttpException) {
            return $e->getMessage() ?: 'An error occurred';
        }

        if (config('app.debug')) {
            return $e->getMessage();
        }

        return 'An unexpected error occurred. Please try again later.';
    }

    /**
     * Get error type identifier
     *
     * @param Throwable $e
     * @return string
     */
    protected function getErrorType(Throwable $e): string
    {
        if ($e instanceof ValidationException) {
            return 'VALIDATION_ERROR';
        }

        if ($e instanceof AuthenticationException) {
            return 'UNAUTHENTICATED';
        }

        if ($e instanceof ModelNotFoundException) {
            return 'MODEL_NOT_FOUND';
        }

        if ($e instanceof NotFoundHttpException) {
            return 'NOT_FOUND';
        }

        if ($e instanceof HttpException) {
            return 'HTTP_EXCEPTION';
        }

        return 'SERVER_ERROR';
    }
}

