# Google OAuth Setup & Troubleshooting Guide

## Overview

This guide explains how to set up and maintain Google OAuth authentication for your job portal, especially when using ngrok for development.

---

## Quick Fix (If Google Login Isn't Working)

**Problem**: Google login button doesn't work or shows errors.

**Solution**: Your callback URLs are out of sync. Run this command:

```bash
php scripts/utilities/update_all_callback_urls.php
php artisan config:clear
```

Then update Google Cloud Console with the new redirect URI (see instructions below).

---

## Initial Setup

### 1. Google Cloud Console Setup

#### A. Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the **Google+ API** and **Google OAuth API**

#### B. Create OAuth 2.0 Credentials

1. Navigate to **APIs & Services** → **Credentials**
2. Click **Create Credentials** → **OAuth client ID**
3. Select **Application type**: Web application
4. Configure:
   - **Name**: TugmaJobs (or your app name)
   - **Authorized JavaScript origins**:
     ```
     http://localhost
     https://your-domain.com
     https://your-ngrok-url.ngrok-free.app
     ```
   - **Authorized redirect URIs**:
     ```
     http://localhost/auth/google/callback
     https://your-domain.com/auth/google/callback
     https://your-ngrok-url.ngrok-free.app/auth/google/callback
     ```

5. Click **Create**
6. Copy your **Client ID** and **Client Secret**

### 2. Laravel Configuration

#### A. Install Laravel Socialite (if not already installed)

```bash
composer require laravel/socialite
```

#### B. Configure .env File

Add the following to your `.env` file:

```env
# Google OAuth Configuration
GOOGLE_CLIENT_ID=your-client-id-here.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret-here
GOOGLE_REDIRECT_URI=https://your-current-url.com/auth/google/callback
```

**Important**: The `GOOGLE_REDIRECT_URI` must match your current APP_URL!

#### C. Run Database Migrations

```bash
php artisan migrate
```

This creates the necessary social auth fields in the users table.

#### D. Clear Config Cache

```bash
php artisan config:clear
php artisan cache:clear
```

---

## Working with ngrok

### The ngrok URL Problem

When using ngrok, the URL changes every time you restart it. This breaks Google OAuth because:
- The redirect URI in `.env` becomes outdated
- Google Cloud Console still has the old URL

### Solution 1: Use the Auto-Update Script (Recommended)

We've created a utility script that automatically updates all callback URLs:

```bash
# Update all callback URLs to match current APP_URL
php scripts/utilities/update_all_callback_urls.php

# Clear config cache
php artisan config:clear
```

This script updates:
- `GOOGLE_REDIRECT_URI`
- `DIDIT_CALLBACK_URL`
- `DIDIT_REDIRECT_URL`

### Solution 2: Manual Update

If you prefer manual updates:

1. **Update .env file**:
   ```env
   # Change this line to match your current ngrok URL
   GOOGLE_REDIRECT_URI=https://your-new-ngrok-url.ngrok-free.app/auth/google/callback
   ```

2. **Clear config cache**:
   ```bash
   php artisan config:clear
   ```

3. **Update Google Cloud Console** (see next section)

### Solution 3: Use ngrok Reserved Domain (Best for Development)

1. Get a free ngrok account at https://ngrok.com
2. Get a static subdomain (free tier allows one)
3. Use the same URL every time:
   ```bash
   ngrok http 80 --domain=your-static-subdomain.ngrok-free.app
   ```

---

## Updating Google Cloud Console

**You must do this every time you change the redirect URI!**

### Steps:

1. Go to [Google Cloud Console Credentials](https://console.cloud.google.com/apis/credentials)
2. Click on your OAuth 2.0 Client ID
3. Under **Authorized redirect URIs**, click **+ ADD URI**
4. Add your new callback URL:
   ```
   https://your-current-url.ngrok-free.app/auth/google/callback
   ```
5. Click **SAVE**

**Note**: You can have multiple redirect URIs (e.g., localhost, staging, production). Add all of them!

---

## Testing Google OAuth

### 1. Run Diagnostic Test

```bash
php scripts/debug/test_google_oauth.php
```

This checks:
- ✅ Environment variables
- ✅ Config values
- ✅ Database schema
- ✅ Socialite installation
- ✅ Routes registration
- ✅ URL matching

### 2. Test in Browser

#### For Jobseekers:
```
https://your-url.com/auth/google?role=jobseeker
```

#### For Employers:
```
https://your-url.com/auth/google?role=employer
```

### 3. Check Login Flow

1. Click the **"Continue with Google"** button on the login/signup modal
2. You should be redirected to Google's consent screen
3. Select your Google account
4. Grant permissions
5. You should be redirected back to your site
6. You should be logged in automatically

---

## Common Issues & Solutions

### Issue 1: "redirect_uri_mismatch" Error

**Error Message**:
```
Error 400: redirect_uri_mismatch
The redirect URI in the request, https://xxx.ngrok-free.app/auth/google/callback, does not match
```

**Cause**: The redirect URI in Google Cloud Console doesn't match the one in your `.env`.

**Solution**:
1. Run the update script:
   ```bash
   php scripts/utilities/update_all_callback_urls.php
   php artisan config:clear
   ```
2. Update Google Cloud Console with the new URI
3. Try again

---

### Issue 2: Google Login Button Does Nothing

**Possible Causes**:
- JavaScript error
- Route not registered
- Missing `?role=jobseeker` parameter

**Solution**:
1. Check browser console for errors
2. Verify routes are registered:
   ```bash
   php artisan route:list | grep google
   ```
3. Check the auth modal file includes the role parameter:
   ```html
   <a href="{{ route('social.redirect', ['provider' => 'google', 'role' => 'jobseeker']) }}">
   ```

---

### Issue 3: "CSRF Token Mismatch"

**Cause**: Session not properly configured.

**Solution**:
1. Check `.env`:
   ```env
   SESSION_DRIVER=file
   SESSION_LIFETIME=120
   ```
2. Clear sessions:
   ```bash
   php artisan session:flush
   ```
3. Restart browser

---

### Issue 4: User Created But Not Logged In

**Cause**: Authentication not completing after user creation.

**Solution**:
Check the `SocialAuthController@handleProviderCallback` method is calling:
```php
Auth::login($user);
```

---

### Issue 5: "Client ID not set"

**Error in logs**:
```
Google OAuth Configuration error: Client ID not set
```

**Solution**:
1. Verify `.env` has:
   ```env
   GOOGLE_CLIENT_ID=your-id-here
   GOOGLE_CLIENT_SECRET=your-secret-here
   ```
2. Clear config:
   ```bash
   php artisan config:clear
   ```

---

## How It Works

### 1. User Clicks "Continue with Google"

```html
<a href="{{ route('social.redirect', ['provider' => 'google', 'role' => 'jobseeker']) }}">
    <i class="fab fa-google me-2"></i>
    Continue with Google
</a>
```

### 2. Redirects to Google OAuth

Route: `GET /auth/google?role=jobseeker`

Controller: `SocialAuthController@redirectToProvider`

Actions:
- Stores the intended role in session (`jobseeker` or `employer`)
- Redirects to Google's consent screen
- Requests scopes: `openid`, `email`, `profile`

### 3. User Grants Permissions

- User sees Google's consent screen
- User selects account and grants permissions
- Google redirects back to your callback URL

### 4. Callback Handles Authentication

Route: `GET /auth/google/callback`

Controller: `SocialAuthController@handleProviderCallback`

Actions:
- Receives user data from Google
- Checks if user exists by email
- If exists: Updates social info and logs in
- If new: Creates user with intended role and logs in
- Redirects to appropriate dashboard

### 5. Database Storage

User model fields:
```php
google_id              // Google's unique user ID
google_token           // Access token
google_refresh_token   // Refresh token (if available)
profile_image          // Google profile picture URL
email_verified_at      // Auto-verified from Google
```

---

## File Structure

### Controllers
- `app/Http/Controllers/SocialAuthController.php` - Handles OAuth flow

### Routes
- `routes/web.php` - Registers social auth routes:
  - `GET /auth/{provider}` - Redirect to provider
  - `GET /auth/{provider}/callback` - Handle callback
  - `GET /auth/error` - Handle errors

### Configuration
- `config/services.php` - Service credentials
- `.env` - Environment variables

### Database
- `database/migrations/2025_01_20_000000_add_social_auth_fields_to_users_table.php`

### Views
- `resources/views/components/auth-modal.blade.php` - Login/signup modal with Google button
- `resources/views/components/employer-auth-modal.blade.php` - Employer version

### Utilities
- `scripts/utilities/update_all_callback_urls.php` - Auto-update callback URLs
- `scripts/debug/test_google_oauth.php` - Diagnostic tool

---

## Security Best Practices

### 1. Never Commit Credentials

Add to `.gitignore`:
```
.env
.env.backup
.env.local
```

### 2. Use Environment Variables

Never hardcode credentials in code:
```php
// ❌ Bad
$clientId = '12345-abcdef.apps.googleusercontent.com';

// ✅ Good
$clientId = config('services.google.client_id');
```

### 3. Validate Redirect URIs

Google Cloud Console should ONLY have URIs you control:
```
✅ https://your-domain.com/auth/google/callback
✅ https://your-subdomain.ngrok-free.app/auth/google/callback
❌ https://random-unknown-url.com/callback
```

### 4. Use HTTPS in Production

OAuth requires HTTPS in production. ngrok provides HTTPS automatically.

### 5. Implement CSRF Protection

Laravel's CSRF protection is automatic. Don't disable it:
```php
// ❌ Don't do this
Route::post('/auth/google/callback')->withoutMiddleware('csrf');
```

---

## Production Deployment

### 1. Update .env for Production

```env
APP_URL=https://your-production-domain.com
GOOGLE_REDIRECT_URI=https://your-production-domain.com/auth/google/callback
```

### 2. Add Production URI to Google Console

Add your production domain to authorized redirect URIs.

### 3. Use Environment-Specific Credentials

Consider separate OAuth apps for:
- Development (localhost + ngrok)
- Staging
- Production

### 4. Monitor OAuth Logs

Check logs for authentication issues:
```bash
tail -f storage/logs/laravel.log | grep "OAuth"
```

---

## Workflow: Starting Development

When you start working each day:

1. **Start ngrok**:
   ```bash
   ngrok http 80
   ```

2. **Update APP_URL** in `.env`:
   ```env
   APP_URL=https://new-ngrok-url.ngrok-free.app
   ```

3. **Run the update script**:
   ```bash
   php scripts/utilities/update_all_callback_urls.php
   php artisan config:clear
   ```

4. **Update Google Cloud Console** with the new redirect URI

5. **Test**:
   ```bash
   php scripts/debug/test_google_oauth.php
   ```

---

## Alternative: Use Static ngrok Domain

To avoid updating URLs daily:

1. Sign up for free ngrok account
2. Get a static domain (one free with account)
3. Always use the same domain:
   ```bash
   ngrok http 80 --domain=your-static.ngrok-free.app
   ```

4. Set it once in `.env`:
   ```env
   APP_URL=https://your-static.ngrok-free.app
   ```

5. Add it once to Google Console

6. Never need to update again!

---

## Debugging Checklist

When Google login isn't working, check:

- [ ] `.env` has all Google credentials
- [ ] `GOOGLE_REDIRECT_URI` matches current `APP_URL`
- [ ] Google Cloud Console has current redirect URI
- [ ] Config cache is cleared (`php artisan config:clear`)
- [ ] Database migration is run
- [ ] Laravel Socialite is installed
- [ ] Routes are registered (`php artisan route:list | grep google`)
- [ ] No JavaScript errors in browser console
- [ ] Session is working (test regular login first)

---

## Quick Commands Reference

```bash
# Update all callback URLs
php scripts/utilities/update_all_callback_urls.php

# Test OAuth configuration
php scripts/debug/test_google_oauth.php

# Clear config cache
php artisan config:clear

# View routes
php artisan route:list | grep google

# Check logs
tail -f storage/logs/laravel.log

# Flush sessions
php artisan session:flush
```

---

## Support

If you're still having issues:

1. Run the diagnostic:
   ```bash
   php scripts/debug/test_google_oauth.php
   ```

2. Check Laravel logs:
   ```bash
   tail -100 storage/logs/laravel.log
   ```

3. Check browser console for JavaScript errors

4. Verify Google Cloud Console settings

---

**Google OAuth should now be working! 🎉**

For KYC-related callback URLs (Didit), see the separate KYC documentation.
