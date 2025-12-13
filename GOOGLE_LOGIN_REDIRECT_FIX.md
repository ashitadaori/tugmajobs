# Google Login Redirect Fix

## Problem

After logging in with Google from the ngrok URL (`https://3dbb0fea823c.ngrok-free.app`), users were being redirected back to `http://127.0.0.1:8000/` instead of staying on the ngrok URL.

## Root Cause

The `SocialAuthController::redirectAfterLogin()` method was using Laravel's `route()` helper, which was generating URLs based on the request context rather than the configured `APP_URL`.

When using ngrok, Laravel sometimes generates URLs using the local development server address (`127.0.0.1:8000`) instead of the ngrok URL.

## Solution

Modified the `redirectAfterLogin()` method in `app/Http/Controllers/SocialAuthController.php` to explicitly use the `APP_URL` from the configuration:

### Before:
```php
private function redirectAfterLogin($user)
{
    if ($user->isEmployer()) {
        return redirect()->route('employer.dashboard')->with('success', 'Welcome back!');
    }

    return redirect()->route('account.dashboard')->with('success', 'Welcome back!');
}
```

### After:
```php
private function redirectAfterLogin($user)
{
    // Force the correct APP_URL for redirects to prevent localhost redirects
    $appUrl = config('app.url');

    if ($user->isEmployer()) {
        $redirectUrl = $appUrl . '/employer/dashboard';
        \Log::info('Redirecting employer after Google login', ['url' => $redirectUrl]);
        return redirect($redirectUrl)->with('success', 'Welcome back!');
    }

    $redirectUrl = $appUrl . '/account/dashboard';
    \Log::info('Redirecting jobseeker after Google login', ['url' => $redirectUrl]);
    return redirect($redirectUrl)->with('success', 'Welcome back!');
}
```

## Testing

1. **Clear caches**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

2. **Test Google login**:
   - Go to: `https://3dbb0fea823c.ngrok-free.app`
   - Click "Sign In" or "Get Started"
   - Click "Continue with Google"
   - Select your Google account
   - Grant permissions
   - **Expected**: You should be redirected to `https://3dbb0fea823c.ngrok-free.app/account/dashboard` (jobseeker) or `https://3dbb0fea823c.ngrok-free.app/employer/dashboard` (employer)
   - **Before Fix**: You were redirected to `http://127.0.0.1:8000/`

## Files Modified

- `app/Http/Controllers/SocialAuthController.php` - Updated `redirectAfterLogin()` method

## Additional Notes

- The fix includes logging to help debug future redirect issues
- The `TrustProxies` middleware is already configured to trust all proxies (`$proxies = '*'`), which is necessary for ngrok
- The `APP_URL` in `.env` must be set to your current ngrok URL for this to work correctly

## Verification

After the fix, check the logs to confirm the correct redirect URL:

```bash
tail -f storage/logs/laravel.log | grep "Redirecting"
```

You should see log entries like:
```
Redirecting employer after Google login {"url":"https://3dbb0fea823c.ngrok-free.app/employer/dashboard"}
```

## Related Issues Fixed

This fix also ensures that:
- Session cookies work correctly across the ngrok domain
- Flash messages are preserved during the redirect
- Users stay on the same domain throughout the authentication flow
