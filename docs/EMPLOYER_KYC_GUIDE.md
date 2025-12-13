# Employer KYC Verification Guide

## Overview

This guide explains how to configure and manage KYC verification for employers to enable job posting capabilities.

---

## Current System

Your job portal currently has TWO verification requirements for employers to post jobs:

1. **KYC Verification** (Identity verification via Didit)
2. **Document Approval** (Business documents reviewed by admin)

### Default Behavior

By default, employers must complete BOTH:
```php
public function canPostJobs(): bool
{
    return $this->isEmployer() &&
           $this->isKycVerified() &&          // KYC required
           $this->hasRequiredDocumentsApproved(); // Documents required
}
```

---

## Configuration Options

You have THREE options to configure employer verification:

### Option 1: KYC Only (Like Job Seekers) ⭐ RECOMMENDED FOR TESTING

Make it work exactly like jobseekers - only KYC verification required.

**Steps:**

1. **Update User Model** (`app/Models/User.php`):
   ```php
   public function canPostJobs(): bool
   {
       // KYC only - just like jobseekers
       return $this->isEmployer() && $this->isKycVerified();
   }
   ```

2. **Or use Environment Variable** (Better for flexibility):

   Add to `.env`:
   ```env
   # Set to true to require only KYC (no documents needed)
   EMPLOYER_KYC_ONLY=true
   ```

   Then update `canPostJobs()`:
   ```php
   public function canPostJobs(): bool
   {
       if (!$this->isEmployer()) {
           return false;
       }

       // Check if KYC-only mode is enabled
       if (config('app.employer_kyc_only', false)) {
           return $this->isKycVerified();
       }

       // Default: Require both KYC and documents
       return $this->isKycVerified() && $this->hasRequiredDocumentsApproved();
   }
   ```

### Option 2: Documents Only (No KYC)

Only require document approval (for regions where KYC isn't necessary).

```php
public function canPostJobs(): bool
{
    return $this->isEmployer() && $this->hasRequiredDocumentsApproved();
}
```

### Option 3: Neither (Disabled for Testing)

Disable all verification requirements.

**Add to `.env`:**
```env
# Temporarily disable KYC for employers (TESTING ONLY)
DISABLE_KYC_FOR_EMPLOYERS=true
```

This is already handled by your middleware:
```php
// In CheckEmployerKyc.php
if (config('app.disable_kyc_for_employers', false)) {
    return $next($request);
}
```

---

## Implementation: KYC-Only Mode

Let me show you how to implement Option 1 (KYC-only, like jobseekers):

### Step 1: Update Configuration

**File**: `config/app.php`

Add this to the config array:
```php
/*
|--------------------------------------------------------------------------
| Employer Verification Settings
|--------------------------------------------------------------------------
*/

// Require only KYC for employers (no documents needed)
'employer_kyc_only' => env('EMPLOYER_KYC_ONLY', false),

// Completely disable KYC checks for employers (testing only)
'disable_kyc_for_employers' => env('DISABLE_KYC_FOR_EMPLOYERS', false),
```

### Step 2: Update User Model

**File**: `app/Models/User.php`

Find the `canPostJobs()` method (around line 452) and update it:

```php
/**
 * Check if employer can post jobs.
 *
 * Modes:
 * - KYC Only: Only KYC verification required (like jobseekers)
 * - Full Verification: KYC + Document approval (default)
 * - Disabled: No checks (testing only)
 */
public function canPostJobs(): bool
{
    if (!$this->isEmployer()) {
        return false;
    }

    // Mode 1: All checks disabled (testing only)
    if (config('app.disable_kyc_for_employers', false)) {
        return true;
    }

    // Mode 2: KYC-only mode (like jobseekers)
    if (config('app.employer_kyc_only', false)) {
        return $this->isKycVerified();
    }

    // Mode 3: Full verification (default)
    // Requires both KYC and approved documents
    return $this->isKycVerified() && $this->hasRequiredDocumentsApproved();
}
```

### Step 3: Update Verification Status Method

**File**: `app/Models/User.php`

Find `getEmployerVerificationStatus()` (around line 462) and update:

```php
public function getEmployerVerificationStatus(): array
{
    if (!$this->isEmployer()) {
        return ['status' => 'not_employer', 'message' => 'Not an employer'];
    }

    // Check if all checks are disabled
    if (config('app.disable_kyc_for_employers', false)) {
        return ['status' => 'verified', 'message' => 'Verification disabled for testing'];
    }

    $kycVerified = $this->isKycVerified();

    // KYC-only mode
    if (config('app.employer_kyc_only', false)) {
        if ($kycVerified) {
            return ['status' => 'verified', 'message' => 'KYC verified - can post jobs'];
        } else {
            return ['status' => 'kyc_pending', 'message' => 'KYC verification required'];
        }
    }

    // Full verification mode (default)
    $documentsApproved = $this->hasRequiredDocumentsApproved();

    if ($kycVerified && $documentsApproved) {
        return ['status' => 'verified', 'message' => 'Fully verified - can post jobs'];
    } elseif (!$kycVerified) {
        return ['status' => 'kyc_pending', 'message' => 'KYC verification required'];
    } else {
        return ['status' => 'documents_pending', 'message' => 'Document approval required'];
    }
}
```

### Step 4: Configure Environment

**File**: `.env`

Choose your verification mode:

```env
# ============================================
# EMPLOYER VERIFICATION CONFIGURATION
# ============================================

# Option A: KYC Only (Recommended for most cases)
# - Employers only need to complete KYC verification
# - Works exactly like jobseeker verification
EMPLOYER_KYC_ONLY=true

# Option B: Full Verification (Production default)
# - Requires both KYC AND document approval
# - More secure but slower onboarding
# EMPLOYER_KYC_ONLY=false

# Option C: Disable All Checks (Testing only!)
# - No verification required at all
# - USE ONLY FOR DEVELOPMENT/TESTING
# DISABLE_KYC_FOR_EMPLOYERS=true
```

---

## Testing KYC for Employers

### 1. Enable KYC-Only Mode

Add to `.env`:
```env
EMPLOYER_KYC_ONLY=true
```

### 2. Reset Employer KYC Status

```bash
# List all users
php scripts/kyc/reset_kyc.php list

# Reset specific employer (replace ID)
php scripts/kyc/reset_kyc.php EMPLOYER_USER_ID

# Or reset all users
php scripts/kyc/reset_kyc.php all
```

### 3. Test the Flow

1. **Login as Employer**
   - Navigate to employer dashboard

2. **Verify KYC Status**
   - Check if "Complete KYC Verification" button appears
   - Status should show as "Not Verified" or "Pending"

3. **Complete KYC**
   - Click "Complete KYC Verification"
   - Follow Didit verification process
   - Complete identity verification

4. **Test Job Posting**
   - After KYC completion, navigate to "Post New Job"
   - You should be able to access the job posting form
   - No document upload required in KYC-only mode

---

## Comparison: Job Seeker vs Employer

| Feature | Job Seeker | Employer (KYC-Only) | Employer (Full) |
|---------|------------|---------------------|-----------------|
| **KYC Required** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Documents Required** | ❌ No | ❌ No | ✅ Yes |
| **Can Apply to Jobs** | After KYC | N/A | N/A |
| **Can Post Jobs** | N/A | After KYC | After KYC + Docs |
| **Admin Approval** | ❌ Auto | ❌ Auto | ✅ Manual |

---

## Troubleshooting

### Issue: Employer Still Can't Post Jobs After KYC

**Possible Causes:**

1. **Environment Variable Not Set**
   ```bash
   # Check if EMPLOYER_KYC_ONLY is set
   php artisan tinker
   >>> config('app.employer_kyc_only')
   ```

2. **Cache Not Cleared**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. **KYC Not Actually Verified**
   ```bash
   # Check KYC status
   php scripts/kyc/reset_kyc.php list
   ```

4. **Middleware Still Checking Documents**
   - Make sure you've updated the User model methods
   - Restart your web server

### Issue: KYC Modal Not Appearing

**Check:**

1. **KYC Routes Are Defined** (routes/web.php):
   ```php
   Route::middleware(['auth'])->prefix('kyc')->name('kyc.')->group(function () {
       Route::get('/start', ...);
       Route::post('/start', ...);
   });
   ```

2. **Layout Has KYC Components**:
   - `@include('components.kyc-modal')`
   - `@include('components.kyc-reminder-banner')`

3. **JavaScript Is Loaded**:
   - Check browser console for errors
   - Verify Didit SDK is loaded

---

## Security Recommendations

### For Production

**Use Full Verification (KYC + Documents):**
```env
EMPLOYER_KYC_ONLY=false
DISABLE_KYC_FOR_EMPLOYERS=false
```

**Why?**
- More secure
- Verifies business legitimacy
- Prevents fraudulent job postings
- Complies with business regulations

### For Development/Testing

**Use KYC-Only Mode:**
```env
EMPLOYER_KYC_ONLY=true
```

**Why?**
- Faster testing
- No admin approval needed
- Still maintains identity verification
- Easier to test job posting features

### For Local Development Only

**Disable All Checks (Use Sparingly):**
```env
DISABLE_KYC_FOR_EMPLOYERS=true
```

**⚠️ WARNING:** NEVER use this in production!

---

## Admin Interface

### Viewing Employer Verification Status

Admins can check employer verification in the admin panel:

1. **Admin Dashboard** → **Users** → **Employers**
2. Each employer shows:
   - KYC Status badge
   - Document approval status
   - Verification completion date
   - Can post jobs: Yes/No

### Approving Documents (Full Mode Only)

When `EMPLOYER_KYC_ONLY=false`:

1. **Admin Dashboard** → **Employer Documents**
2. Review submitted documents
3. Approve or reject each document type
4. Employer notified of approval status

---

## API Endpoints

### Check Verification Status

```javascript
// GET /employer/profile (or any authenticated employer route)
{
  "canPostJobs": true|false,
  "kyc_status": "verified|pending|in_progress|failed",
  "verification_status": {
    "status": "verified|kyc_pending|documents_pending",
    "message": "Status message"
  }
}
```

### Get Verification Requirements

```javascript
// GET /api/employer/verification-requirements
{
  "kyc_required": true,
  "documents_required": false, // Depends on config
  "kyc_completed": true,
  "documents_approved": false, // N/A in KYC-only mode
  "can_post_jobs": true
}
```

---

## Database Schema

### Users Table (KYC Fields)

```sql
kyc_status VARCHAR(50)           -- 'pending', 'in_progress', 'verified', 'failed'
kyc_session_id VARCHAR(255)      -- Didit session ID
kyc_completed_at TIMESTAMP       -- When verification completed
kyc_verified_at TIMESTAMP        -- When admin/system approved
kyc_data JSON                    -- Verification metadata
```

### Employer Documents Table (Full Mode Only)

```sql
user_id BIGINT                   -- Foreign key to users
document_type VARCHAR(50)        -- 'business_license', 'tax_id', etc.
file_path VARCHAR(255)           -- Storage path
status VARCHAR(50)               -- 'pending', 'approved', 'rejected'
submitted_at TIMESTAMP
reviewed_at TIMESTAMP
reviewer_id BIGINT               -- Admin who reviewed
```

---

## Migration Path

### From Full Verification to KYC-Only

If you have existing employers with pending documents:

1. **Update Config**:
   ```env
   EMPLOYER_KYC_ONLY=true
   ```

2. **Notify Existing Employers**:
   - Employers with KYC will immediately be able to post
   - No need to wait for document approval

3. **Optional: Clean Up Pending Documents**:
   ```bash
   php artisan tinker
   >>> use App\Models\EmployerDocument;
   >>> EmployerDocument::where('status', 'pending')->update(['status' => 'not_required']);
   ```

### From KYC-Only to Full Verification

1. **Update Config**:
   ```env
   EMPLOYER_KYC_ONLY=false
   ```

2. **Notify Employers**:
   - Employers will now need to submit documents
   - Create notification system for this change

3. **Grace Period** (Optional):
   ```php
   // In User.php
   public function canPostJobs(): bool
   {
       if (!$this->isEmployer()) return false;

       // Grace period: Allow KYC-verified employers for 30 days
       $gracePeriod = $this->kyc_verified_at?->addDays(30);
       if ($this->isKycVerified() && now()->lessThan($gracePeriod)) {
           return true;
       }

       return $this->isKycVerified() && $this->hasRequiredDocumentsApproved();
   }
   ```

---

## Summary

### Quick Start for KYC-Only Mode

1. **Add to `.env`:**
   ```env
   EMPLOYER_KYC_ONLY=true
   ```

2. **Clear cache:**
   ```bash
   php artisan config:clear
   ```

3. **Test:**
   ```bash
   php scripts/kyc/reset_kyc.php EMPLOYER_ID
   # Complete KYC verification
   # Try posting a job
   ```

### Key Files Modified

- `app/Models/User.php` - `canPostJobs()` and `getEmployerVerificationStatus()`
- `config/app.php` - Add config options
- `.env` - Add environment variables

### Configuration Summary

```env
# Choose ONE:

# Mode 1: KYC Only (Recommended)
EMPLOYER_KYC_ONLY=true

# Mode 2: Full Verification (Production)
EMPLOYER_KYC_ONLY=false

# Mode 3: Disabled (Testing Only)
DISABLE_KYC_FOR_EMPLOYERS=true
```

---

*Ready to implement? Let me know which mode you want to use and I'll update the code for you!*
