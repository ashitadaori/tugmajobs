# Error Tracking & Logging Guide

## Overview

Comprehensive error tracking and logging system with structured logging, multiple channels, performance monitoring, and security event tracking.

---

## Features

### 1. Enhanced Exception Handling ✅

**File**: `app/Exceptions/Handler.php`

**Capabilities**:
- **Auto Logging**: All exceptions logged with full context
- **Sentry Integration**: Optional Sentry error tracking
- **Custom Error Responses**: User-friendly error pages and JSON responses
- **Security**: Sensitive data filtering
- **Debug Mode**: Detailed stack traces in development

**Exception Types Handled**:
- `NotFoundHttpException` - 404 errors
- `ModelNotFoundException` - Database record not found
- `AuthenticationException` - Unauthorized access
- `TokenMismatchException` - CSRF token errors
- `ValidationException` - Form validation failures
- `ThrottleRequestsException` - Rate limiting

### 2. Centralized Logging Service ✅

**File**: `app/Services/LoggingService.php`

**Methods**:

| Method | Description | Example |
|--------|-------------|---------|
| `logException()` | Log exceptions with context | Automatic via Handler |
| `logSecurityEvent()` | Log security events | Failed logins, unauthorized access |
| `logPerformance()` | Log performance metrics | Page load times, API response times |
| `logSlowQuery()` | Log slow database queries | Queries > 1 second |
| `logBusinessEvent()` | Log business events | Job applications, profile updates |
| `logUserAction()` | Log user actions | Login, logout, data changes |
| `logApiCall()` | Log API requests/responses | External API calls |
| `logCacheOperation()` | Log cache operations | Cache hits/misses |
| `logJob()` | Log queue job execution | Job started, completed, failed |

**Usage Examples**:

```php
use App\Services\LoggingService;

// Inject via constructor
public function __construct(LoggingService $loggingService)
{
    $this->loggingService = $loggingService;
}

// Log exception
try {
    // Code that might throw
} catch (\Exception $e) {
    $this->loggingService->logException($e, ['context' => 'additional info']);
    throw $e;
}

// Log security event
$this->loggingService->logSecurityEvent('Failed login attempt', [
    'email' => $email,
    'ip' => $request->ip(),
]);

// Log business event
$this->loggingService->logBusinessEvent('Job application submitted', [
    'job_id' => $job->id,
    'applicant_id' => $user->id,
]);

// Log user action
$this->loggingService->logUserAction('Profile updated', [
    'changes' => $request->only(['name', 'email']),
]);

// Log performance
$start = microtime(true);
// ... do work ...
$duration = (microtime(true) - $start) * 1000;
$this->loggingService->logPerformance('job_search_query', $duration, 'ms');
```

### 3. Request/Response Logging ✅

**File**: `app/Http/Middleware/LogRequests.php`

**Features**:
- Logs all HTTP requests and responses
- Tracks response time
- Logs memory usage
- Automatic slow request detection (> 2 seconds)
- Sensitive data filtering
- Request ID for tracing

**Logged Information**:
- Request: Method, URL, IP, User Agent, User ID
- Response: Status code, Duration, Memory usage
- Performance: Response time metrics

**Configuration**:
```php
// Exclude URIs from logging
protected $except = [
    'telescope*',
    'horizon*',
    '_debugbar*',
    'health-check',
    'ping',
];
```

### 4. Multiple Log Channels ✅

**File**: `config/logging.php`

**Channels**:

| Channel | Purpose | Retention | Level |
|---------|---------|-----------|-------|
| `daily` | General application logs | 14 days | debug |
| `security` | Security events | 30 days | warning |
| `performance` | Performance metrics | 7 days | info |
| `database` | Database queries | 7 days | debug |
| `business` | Business events | 30 days | info |
| `user_actions` | User audit trail | 90 days | info |
| `api` | API requests/responses | 14 days | debug |
| `cache` | Cache operations | 3 days | debug |
| `jobs` | Queue job execution | 14 days | info |
| `slack` | Critical errors | N/A | critical |

**Usage**:
```php
// Log to specific channel
Log::channel('security')->warning('Unauthorized access attempt');
Log::channel('business')->info('New job posted');
Log::channel('performance')->info('Slow page load', ['duration' => 2500]);
```

### 5. Artisan Commands ✅

**Log Management Commands**:

```bash
# Clear all logs
php artisan log:clear

# Clear specific channel
php artisan log:clear --channel=cache

# Clear logs older than 7 days
php artisan log:clear --days=7

# Force clear without confirmation
php artisan log:clear --force

# View log statistics
php artisan log:stats

# View stats for specific channel
php artisan log:stats --channel=security

# Output stats as JSON
php artisan log:stats --json
```

---

## Installation & Setup

### Step 1: Register Middleware (Optional)

To enable request/response logging, add middleware to `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ... existing middleware
    \App\Http\Middleware\LogRequests::class, // Add this
];
```

**Note**: This will log ALL requests. For production, consider adding only to specific route groups.

### Step 2: Configure Environment Variables

Add to `.env`:

```env
# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug

# Slack Notifications (optional)
LOG_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL

# Sentry (optional)
SENTRY_LARAVEL_DSN=https://your-sentry-dsn@sentry.io/project-id
SENTRY_TRACES_SAMPLE_RATE=0.2
```

### Step 3: Install Sentry (Optional)

For advanced error tracking with Sentry:

```bash
composer require sentry/sentry-laravel
```

Publish config:
```bash
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

Configure in `config/sentry.php`:
```php
return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.0),
    'send_default_pii' => false, // Don't send personal info
    'environment' => env('APP_ENV'),
];
```

### Step 4: Create Log Directory Structure

Logs are automatically created, but you can ensure proper permissions:

```bash
mkdir -p storage/logs
chmod -R 775 storage/logs
chown -R www-data:www-data storage/logs
```

---

## Usage Examples

### Example 1: Log Exception in Controller

```php
namespace App\Http\Controllers;

use App\Services\LoggingService;
use Exception;

class JobController extends Controller
{
    protected $loggingService;

    public function __construct(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    public function apply($jobId)
    {
        try {
            $job = Job::findOrFail($jobId);

            // Application logic
            $application = $this->createApplication($job);

            // Log business event
            $this->loggingService->logBusinessEvent('Job application created', [
                'job_id' => $job->id,
                'job_title' => $job->title,
                'application_id' => $application->id,
            ]);

            return redirect()->route('jobs.show', $job)
                ->with('success', 'Application submitted successfully');

        } catch (Exception $e) {
            // Exception is automatically logged by Handler
            // But you can add additional context
            $this->loggingService->logException($e, [
                'job_id' => $jobId,
                'action' => 'job_application',
            ]);

            return redirect()->back()
                ->with('error', 'Failed to submit application. Please try again.');
        }
    }
}
```

### Example 2: Log Security Event

```php
// In LoginController
public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        // Log successful login
        app(LoggingService::class)->logUserAction('User logged in', [
            'email' => $request->email,
            'ip' => $request->ip(),
        ]);

        return redirect()->intended('dashboard');
    }

    // Log failed login
    app(LoggingService::class)->logSecurityEvent('Failed login attempt', [
        'email' => $request->email,
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ]);

    return back()->withErrors(['email' => 'Invalid credentials']);
}
```

### Example 3: Log Performance Metrics

```php
// In a Service class
public function searchJobs(array $criteria)
{
    $start = microtime(true);

    // Perform search
    $results = Job::query()
        ->when($criteria['category'] ?? null, function($q, $category) {
            return $q->where('category_id', $category);
        })
        ->when($criteria['location'] ?? null, function($q, $location) {
            return $q->where('location', 'like', "%{$location}%");
        })
        ->get();

    // Calculate and log performance
    $duration = (microtime(true) - $start) * 1000;

    app(LoggingService::class)->logPerformance('job_search', $duration, 'ms', [
        'criteria' => $criteria,
        'results_count' => $results->count(),
    ]);

    return $results;
}
```

### Example 4: Log Slow Database Queries

In `AppServiceProvider.php`:

```php
use Illuminate\Support\Facades\DB;
use App\Services\LoggingService;

public function boot()
{
    // Log slow queries (> 1000ms)
    DB::listen(function ($query) {
        if ($query->time > 1000) {
            app(LoggingService::class)->logSlowQuery(
                $query->sql,
                $query->time / 1000,
                $query->bindings
            );
        }
    });
}
```

### Example 5: Log API Calls

```php
// When calling external APIs
public function fetchJobData($jobId)
{
    $start = microtime(true);

    try {
        $response = Http::get("https://api.example.com/jobs/{$jobId}");
        $duration = (microtime(true) - $start) * 1000;

        app(LoggingService::class)->logApiCall(
            "/jobs/{$jobId}",
            'GET',
            $response->status(),
            $duration,
            [
                'external_api' => 'example.com',
                'success' => $response->successful(),
            ]
        );

        return $response->json();

    } catch (Exception $e) {
        $duration = (microtime(true) - $start) * 1000;

        app(LoggingService::class)->logApiCall(
            "/jobs/{$jobId}",
            'GET',
            500,
            $duration,
            [
                'external_api' => 'example.com',
                'error' => $e->getMessage(),
            ]
        );

        throw $e;
    }
}
```

---

## Log File Structure

### Directory Layout

```
storage/logs/
├── laravel.log              # General application log
├── laravel-2025-11-14.log   # Daily rotation
├── security-2025-11-14.log  # Security events
├── performance-2025-11-14.log # Performance metrics
├── database-2025-11-14.log  # Database queries
├── business-2025-11-14.log  # Business events
├── user_actions-2025-11-14.log # User audit trail
├── api-2025-11-14.log       # API calls
├── cache-2025-11-14.log     # Cache operations
└── jobs-2025-11-14.log      # Queue jobs
```

### Log Entry Format

```json
[2025-11-14 10:30:15] production.ERROR: Database connection failed
{
    "exception": "PDOException",
    "message": "SQLSTATE[HY000] [2002] Connection refused",
    "file": "/var/www/app/Database/Connection.php",
    "line": 142,
    "code": 2002,
    "trace": [...],
    "url": "https://example.com/jobs",
    "method": "GET",
    "ip": "192.168.1.1",
    "user_id": 42,
    "user_email": "john@example.com",
    "environment": "production",
    "app_version": "1.0.0"
}
```

---

## Monitoring & Alerts

### Slack Integration

Configure Slack webhook in `.env`:

```env
LOG_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
```

Critical errors will automatically be sent to Slack:

```php
// In config/logging.php
'stack' => [
    'driver' => 'stack',
    'channels' => ['daily', 'slack'], // Slack added
],

'slack' => [
    'driver' => 'slack',
    'url' => env('LOG_SLACK_WEBHOOK_URL'),
    'username' => 'Laravel Log',
    'emoji' => ':boom:',
    'level' => 'critical', // Only send critical errors
],
```

### Log Rotation

Daily logs rotate automatically. Configure retention in `config/logging.php`:

```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => 'debug',
    'days' => 14, // Keep 14 days
],
```

### Automated Cleanup

Add to cron (Laravel Scheduler):

```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Clear old logs daily at 2 AM
    $schedule->command('log:clear --days=30 --force')
        ->daily()
        ->at('02:00');
}
```

---

## Best Practices

### 1. Use Appropriate Log Levels

```php
// DEBUG: Detailed debug information
Log::debug('User clicked button', ['button_id' => 'submit']);

// INFO: Interesting events
Log::info('User registered', ['user_id' => $user->id]);

// NOTICE: Normal but significant events
Log::notice('Payment method changed', ['user_id' => $user->id]);

// WARNING: Exceptional occurrences that are not errors
Log::warning('API rate limit approaching', ['remaining' => 10]);

// ERROR: Runtime errors that do not require immediate action
Log::error('File upload failed', ['filename' => $file->name]);

// CRITICAL: Critical conditions
Log::critical('Database connection lost');

// ALERT: Action must be taken immediately
Log::alert('Disk space critically low');

// EMERGENCY: System is unusable
Log::emergency('Application crashed');
```

### 2. Add Context to Logs

```php
// Bad
Log::error('Error occurred');

// Good
Log::error('User registration failed', [
    'email' => $email,
    'error' => $e->getMessage(),
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

### 3. Don't Log Sensitive Data

```php
// Bad - exposes passwords
Log::info('User login', ['password' => $password]);

// Good - filters sensitive data
Log::info('User login', [
    'email' => $email,
    'ip' => $request->ip(),
]);
```

### 4. Use Specific Channels

```php
// Use appropriate channels
Log::channel('security')->warning('Suspicious activity');
Log::channel('performance')->info('Slow query detected');
Log::channel('business')->info('New order placed');
```

### 5. Monitor Log File Sizes

```bash
# Check log stats regularly
php artisan log:stats

# Set up alerts for large log files
if [ $(du -sm storage/logs/laravel.log | cut -f1) -gt 100 ]; then
    echo "Log file exceeds 100MB"
fi
```

---

## Troubleshooting

### Problem: Logs Not Being Written

**Solution**:
```bash
# Check permissions
ls -la storage/logs

# Fix permissions
chmod -R 775 storage/logs
chown -R www-data:www-data storage/logs

# Check disk space
df -h
```

### Problem: Too Many Log Files

**Solution**:
```bash
# Clear old logs
php artisan log:clear --days=7

# Check log stats
php artisan log:stats
```

### Problem: Log Files Too Large

**Solution**:
```php
// Reduce log level in production
// In .env
LOG_LEVEL=warning

// Or adjust retention
// In config/logging.php
'days' => 7, // Keep only 7 days
```

### Problem: Sentry Not Capturing Errors

**Solution**:
```bash
# Verify Sentry is installed
composer show sentry/sentry-laravel

# Check DSN in .env
SENTRY_LARAVEL_DSN=https://...

# Test Sentry
php artisan tinker
> app('sentry')->captureMessage('Test message');
```

---

## Performance Impact

### Logging Overhead

- **Request logging**: ~1-2ms per request
- **Exception logging**: ~5-10ms per exception
- **Channel logging**: ~0.5-1ms per log entry

### Mitigation Strategies

1. **Use appropriate log levels**:
   ```php
   // Production
   LOG_LEVEL=warning

   // Development
   LOG_LEVEL=debug
   ```

2. **Disable verbose logging in production**:
   ```php
   if (app()->environment('production')) {
       // Don't log debug info
   }
   ```

3. **Use async logging** (with queue):
   ```php
   dispatch(new LogEventJob($event, $context));
   ```

4. **Log aggregation**: Send logs to external service (Papertrail, Loggly)

---

## Summary

✅ **Enhanced Exception Handler** - Comprehensive error handling
✅ **Logging Service** - Centralized logging with 9 specialized methods
✅ **Request Logging** - Automatic HTTP request/response tracking
✅ **Multiple Channels** - 9 dedicated log channels
✅ **Artisan Commands** - log:clear and log:stats
✅ **Security** - Sensitive data filtering
✅ **Performance** - Response time tracking
✅ **Sentry Ready** - Optional Sentry integration
✅ **Slack Integration** - Critical error alerts
✅ **Documentation** - Complete usage guide

---

## Files Created

1. `app/Services/LoggingService.php` - Centralized logging service
2. `app/Exceptions/Handler.php` - Enhanced exception handler (modified)
3. `app/Http/Middleware/LogRequests.php` - Request/response logging
4. `app/Console/Commands/LogClear.php` - Log cleanup command
5. `app/Console/Commands/LogStats.php` - Log statistics command
6. `config/logging.php` - Enhanced logging config (modified)
7. `docs/ERROR_TRACKING_GUIDE.md` - This guide

---

*Error tracking and logging system complete!*
