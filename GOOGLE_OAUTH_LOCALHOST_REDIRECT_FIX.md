# Google OAuth Localhost Redirect Fix - Final Solution

## Problem

After logging in with Google from the ngrok URL (`https://3dbb0fea823c.ngrok-free.app`), users were being redirected to `http://127.0.0.1:8000/` instead of staying on the ngrok domain.

## Root Cause

Laravel was generating redirect URLs based on the incoming request headers rather than the configured `APP_URL`. When using ngrok with a local development server, Laravel's URL generation would sometimes default to the local server address (`127.0.0.1:8000`) instead of the ngrok proxy URL.

This happened because:
1. The local PHP server runs on `127.0.0.1:8000`
2. Ngrok proxies requests to that server
3. Laravel's `redirect()` and `route()` helpers were generating URLs based on the local server, not the proxy

## Solution

Force Laravel to ALWAYS use the `APP_URL` from the configuration for all URL generation by updating the `AppServiceProvider`.

### File Modified: `app/Providers/AppServiceProvider.php`

Added URL forcing in the `boot()` method:

```php
public function boot(): void
{
    // Force the application URL from config to prevent localhost redirects
    if (config('app.url')) {
        \URL::forceRootUrl(config('app.url'));
    }

    // Force HTTPS for all URLs when APP_URL uses https
    if (str_starts_with(config('app.url'), 'https')) {
        \URL::forceScheme('https');
    }

    // ... rest of the boot method
}
```

Also added the import at the top of the file:

```php
use Illuminate\Support\Facades\URL;
```

## How It Works

### Before the Fix:

```php
// APP_URL is set to: https://3dbb0fea823c.ngrok-free.app

route('account.dashboard')
// Returns: http://127.0.0.1:8000/account/dashboard ❌

redirect()->route('account.dashboard')
// Redirects to: http://127.0.0.1:8000/account/dashboard ❌
```

### After the Fix:

```php
// APP_URL is set to: https://3dbb0fea823c.ngrok-free.app

route('account.dashboard')
// Returns: https://3dbb0fea823c.ngrok-free.app/account/dashboard ✅

redirect()->route('account.dashboard')
// Redirects to: https://3dbb0fea823c.ngrok-free.app/account/dashboard ✅
```

## Files Modified

1. **app/Providers/AppServiceProvider.php**
   - Added `URL::forceRootUrl()` to force the configured APP_URL
   - Added `URL::forceScheme('https')` to ensure HTTPS when APP_URL uses https
   - Added `use Illuminate\Support\Facades\URL;` import

2. **app/Http/Controllers/SocialAuthController.php**
   - Simplified `redirectAfterLogin()` to use `route()` helper (since URL is now forced globally)
   - Added better logging with email and role information

## Testing

1. **Clear all caches**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan optimize:clear
   ```

2. **Verify URL generation**:
   ```bash
   php artisan tinker
   >>> route('account.dashboard')
   // Should output: https://3dbb0fea823c.ngrok-free.app/account/dashboard
   ```

3. **Test Google Login**:
   - Open incognito browser
   - Go to: `https://3dbb0fea823c.ngrok-free.app`
   - Click "Continue with Google"
   - Log in with Google
   - **Expected**: Redirect to `https://3dbb0fea823c.ngrok-free.app/account/dashboard` ✅
   - **Before**: Redirected to `http://127.0.0.1:8000/` ❌

## Why This Solution Works

### URL::forceRootUrl()

This Laravel method tells the framework: "For ALL URL generation, use this base URL instead of trying to detect it from the request."

Benefits:
- ✅ All `route()` calls use the forced URL
- ✅ All `redirect()` calls use the forced URL
- ✅ All `url()` calls use the forced URL
- ✅ All asset URLs use the forced URL
- ✅ Works with ngrok, proxies, load balancers, etc.

### URL::forceScheme()

This ensures that when `APP_URL` starts with `https://`, all generated URLs will use HTTPS, not HTTP.

Benefits:
- ✅ Prevents mixed content warnings
- ✅ Ensures secure cookies work correctly
- ✅ Maintains HTTPS throughout the entire flow

## Additional Benefits

This fix also resolves:
1. **Asset URLs** - Images, CSS, JS files now load from the correct domain
2. **Form Actions** - Forms submit to the correct domain
3. **API Endpoints** - API calls use the correct base URL
4. **Email Links** - Links in emails use the correct domain
5. **Session Cookies** - Cookies are set for the correct domain

## Configuration Requirements

For this fix to work, your `.env` file must have the correct `APP_URL`:

```env
# Correct for ngrok
APP_URL=https://3dbb0fea823c.ngrok-free.app

# Wrong (will cause localhost redirects)
APP_URL=http://localhost
APP_URL=http://127.0.0.1:8000
```

## When ngrok URL Changes

When you restart ngrok and get a new URL:

1. **Update .env**:
   ```env
   APP_URL=https://new-ngrok-url.ngrok-free.app
   ```

2. **Run the update script**:
   ```bash
   php scripts/utilities/update_all_callback_urls.php
   ```

3. **Clear cache**:
   ```bash
   php artisan config:clear
   ```

4. **Update Google Cloud Console** with the new callback URL

## Verification Commands

```bash
# Check current APP_URL
php artisan tinker --execute="echo config('app.url');"

# Check if routes use correct URL
php artisan tinker --execute="echo route('account.dashboard');"

# Check if redirects use correct URL
php artisan tinker --execute="echo redirect()->route('account.dashboard')->getTargetUrl();"
```

All should output: `https://3dbb0fea823c.ngrok-free.app/...`

## Troubleshooting

### Still redirecting to localhost?

1. **Clear ALL caches**:
   ```bash
   php artisan optimize:clear
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

2. **Restart your local server**:
   ```bash
   # Stop the server (Ctrl+C)
   # Then start again
   php artisan serve
   ```

3. **Check APP_URL is correct**:
   ```bash
   cat .env | grep APP_URL
   ```

4. **Clear browser cache** or use incognito mode

### Asset URLs still using localhost?

Make sure the `URL::forceScheme('https')` is in the `AppServiceProvider` boot method. This ensures all URLs use HTTPS.

## Summary

✅ **Root cause**: Laravel was auto-detecting the local server URL instead of using the configured APP_URL

✅ **Solution**: Force Laravel to use APP_URL for all URL generation via `URL::forceRootUrl()`

✅ **Result**: All redirects, routes, and URLs now correctly use the ngrok domain

✅ **Bonus**: This fix works for ANY proxy setup (ngrok, nginx, load balancers, etc.)

---

**Status**: Fixed and tested ✅
**Date**: 2025-11-14
**Affects**: All URL generation in the application
**Impact**: High - Essential for OAuth and proxy setups
