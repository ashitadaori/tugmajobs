# Employer KYC Setup - Quick Reference

## ✅ What's Been Implemented

Your job portal now supports **flexible employer verification** with three modes:

1. **KYC-Only Mode** (like jobseekers) ⭐
2. **Full Verification Mode** (KYC + documents)
3. **Disabled Mode** (testing only)

---

## 🚀 Quick Setup (3 Steps)

### Step 1: Configure Mode

**Option A - Use Configuration Script (Easiest):**
```bash
cd d:\capstoneeeeeee\Capstone\job-portal-main
php scripts/kyc/configure_employer_kyc.php
```
Follow the prompts to select your mode.

**Option B - Manual Configuration:**

Edit `.env` file and add:

```env
# For KYC-Only Mode (Recommended for Testing)
EMPLOYER_KYC_ONLY=true
DISABLE_KYC_FOR_EMPLOYERS=false

# OR for Full Verification Mode (Production)
# EMPLOYER_KYC_ONLY=false
# DISABLE_KYC_FOR_EMPLOYERS=false

# OR for Disabled Mode (Development Only)
# EMPLOYER_KYC_ONLY=false
# DISABLE_KYC_FOR_EMPLOYERS=true
```

### Step 2: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 3: Test It

```bash
# List users
php scripts/kyc/reset_kyc.php list

# Reset an employer's KYC
php scripts/kyc/reset_kyc.php [EMPLOYER_USER_ID]

# Login as employer and test job posting
```

---

## 📊 Mode Comparison

| Mode | KYC Required | Documents Required | Use Case |
|------|--------------|-------------------|----------|
| **KYC-Only** | ✅ Yes | ❌ No | Testing, Fast onboarding |
| **Full Verification** | ✅ Yes | ✅ Yes | Production, High security |
| **Disabled** | ❌ No | ❌ No | Local development only |

---

## 🔧 Configuration Details

### KYC-Only Mode (Recommended for Testing)

**Setup:**
```env
EMPLOYER_KYC_ONLY=true
```

**Behavior:**
- Employer completes KYC verification
- **Immediately** can post jobs (no waiting for admin)
- Works exactly like jobseeker verification
- No document upload required

**Perfect for:**
- Development and testing
- Quick onboarding
- Regions without strict business verification

### Full Verification Mode (Production Default)

**Setup:**
```env
EMPLOYER_KYC_ONLY=false
```

**Behavior:**
- Employer completes KYC verification
- Must also submit business documents
- Admin reviews and approves documents
- **Then** can post jobs

**Perfect for:**
- Production environments
- Compliance requirements
- High-trust business platforms

### Disabled Mode (Development Only)

**Setup:**
```env
DISABLE_KYC_FOR_EMPLOYERS=true
```

**Behavior:**
- **NO** verification required
- Employers can post immediately after registration
- ⚠️ **NEVER** use in production

**Perfect for:**
- Local development
- Quick testing without KYC setup
- Feature development

---

## 🧪 Testing Workflow

### Test KYC-Only Mode

1. **Enable mode:**
   ```bash
   php scripts/kyc/configure_employer_kyc.php
   # Choose option 1
   ```

2. **Clear cache:**
   ```bash
   php artisan config:clear
   ```

3. **Reset employer:**
   ```bash
   php scripts/kyc/reset_kyc.php list
   php scripts/kyc/reset_kyc.php [EMPLOYER_ID]
   ```

4. **Test flow:**
   - Login as employer
   - Dashboard shows "Complete KYC" button
   - Click and complete KYC verification
   - Navigate to "Post New Job"
   - ✅ Should work immediately after KYC!

---

## 📝 Files Modified

### 1. User Model
**File:** `app/Models/User.php`

**Changes:**
- `canPostJobs()` - Now supports 3 modes
- `getEmployerVerificationStatus()` - Returns appropriate status for each mode

### 2. Configuration
**File:** `config/app.php`

**Added:**
```php
'employer_kyc_only' => env('EMPLOYER_KYC_ONLY', false),
'disable_kyc_for_employers' => env('DISABLE_KYC_FOR_EMPLOYERS', false),
```

### 3. Environment Variables
**File:** `.env`

**Add:**
```env
EMPLOYER_KYC_ONLY=true  # or false
DISABLE_KYC_FOR_EMPLOYERS=false  # or true for testing
```

---

## 🐛 Troubleshooting

### Issue: Employer Still Can't Post Jobs

**Solution:**
```bash
# 1. Check config is loaded
php artisan tinker
>>> config('app.employer_kyc_only')
# Should return true if KYC-only mode

# 2. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 3. Check KYC status
php scripts/kyc/reset_kyc.php list
# Verify employer's KYC status is 'verified'

# 4. Restart web server
# If using Laravel dev server: Ctrl+C and restart
```

### Issue: Configuration Not Taking Effect

**Solution:**
```bash
# The config cache might be stale
php artisan config:clear
php artisan optimize:clear

# Verify .env changes
cat .env | grep EMPLOYER_KYC
```

### Issue: KYC Modal Not Showing

**Solution:**
1. Check if routes are defined in `routes/web.php`
2. Verify KYC components are included in layouts
3. Check browser console for JavaScript errors

---

## 🔄 Switching Between Modes

### From Full Verification → KYC-Only

```bash
# 1. Update config
php scripts/kyc/configure_employer_kyc.php
# Choose option 1 (KYC-Only)

# 2. Clear cache
php artisan config:clear

# 3. Notify existing employers
# Employers with verified KYC can now post immediately!
```

### From KYC-Only → Full Verification

```bash
# 1. Update config
php scripts/kyc/configure_employer_kyc.php
# Choose option 2 (Full Verification)

# 2. Clear cache
php artisan config:clear

# 3. Create notification for employers
# Employers will now need to submit documents
```

---

## 📚 Additional Resources

- **Complete Guide:** `docs/EMPLOYER_KYC_GUIDE.md`
- **KYC Reset Tool:** `scripts/kyc/reset_kyc.php`
- **Configuration Script:** `scripts/kyc/configure_employer_kyc.php`

---

## ⚡ Quick Commands Reference

```bash
# Configure mode
php scripts/kyc/configure_employer_kyc.php

# List all users
php scripts/kyc/reset_kyc.php list

# Reset specific user
php scripts/kyc/reset_kyc.php [USER_ID]

# Reset all users
php scripts/kyc/reset_kyc.php all

# Clear cache
php artisan config:clear
php artisan cache:clear

# Check config
php artisan tinker
>>> config('app.employer_kyc_only')
>>> config('app.disable_kyc_for_employers')
```

---

## 🎯 Recommended Settings

### For Development/Testing:
```env
EMPLOYER_KYC_ONLY=true
DISABLE_KYC_FOR_EMPLOYERS=false
```

### For Production:
```env
EMPLOYER_KYC_ONLY=false  # or true, depending on your requirements
DISABLE_KYC_FOR_EMPLOYERS=false
```

### For Local Development (No KYC):
```env
EMPLOYER_KYC_ONLY=false
DISABLE_KYC_FOR_EMPLOYERS=true
```

---

**🎉 You're all set! Employer KYC verification now works just like jobseeker verification!**
