# Testing Employer KYC Implementation

## Quick Test Guide

This guide will help you test the complete employer KYC verification system.

---

## Prerequisites

1. **Configure KYC Mode**: Choose which verification mode to use
2. **Reset KYC Status**: Clear existing verification data
3. **Test Workflow**: Login and complete verification

---

## Step 1: Configure KYC Mode

### Option A: Use Interactive Configuration Script (Recommended)

```bash
php scripts/kyc/configure_employer_kyc.php
```

This will show you:
- Current configuration
- 3 mode options (KYC-Only, Full Verification, Disabled)
- Interactive selection
- Automatic .env file update

### Option B: Manual Configuration

Edit your `.env` file:

**For KYC-Only Mode (Recommended for Testing):**
```env
EMPLOYER_KYC_ONLY=true
DISABLE_KYC_FOR_EMPLOYERS=false
```

**For Full Verification Mode:**
```env
EMPLOYER_KYC_ONLY=false
DISABLE_KYC_FOR_EMPLOYERS=false
```

**For Testing Mode (No Verification):**
```env
EMPLOYER_KYC_ONLY=false
DISABLE_KYC_FOR_EMPLOYERS=true
```

Then clear cache:
```bash
php artisan config:clear
php artisan cache:clear
```

---

## Step 2: Reset KYC Status

### List All Users
```bash
php scripts/kyc/reset_kyc.php list
```

This shows:
- All users with their KYC status
- User IDs (you'll need this for reset)
- KYC verification counts
- Document counts

### Reset Specific Employer
```bash
php scripts/kyc/reset_kyc.php [EMPLOYER_USER_ID]
```

Example:
```bash
php scripts/kyc/reset_kyc.php 5
```

This will:
- Reset KYC status to 'pending'
- Delete KYC verification records
- Delete KYC data entries
- Delete employer documents (if any)
- Delete uploaded files

### Reset All Users (Use with Caution)
```bash
php scripts/kyc/reset_kyc.php all
```

**Warning**: This resets KYC for ALL users in the system!

---

## Step 3: Test the Workflow

### 3.1 Login as Employer

1. Navigate to your application: `http://localhost/login`
2. Login with employer credentials
3. You'll be redirected to the employer dashboard

### 3.2 Verify Dashboard Shows KYC Banner

You should see:

**If NOT Verified:**
- ⚠️ Warning banner at the top of dashboard
- "Identity Verification Required" heading
- "Complete Verification Now" button
- Top-right button shows "Complete Verification to Post Jobs" instead of "Post New Job"

**Banner Colors:**
- Warning banner: Yellow/amber gradient background
- Icon: Orange gradient shield with exclamation
- Button: Yellow/amber with shadow

### 3.3 Complete KYC Verification

1. Click **"Complete Verification Now"** button
2. This should open the Didit KYC modal
3. Complete the verification process:
   - Provide identity information
   - Upload required documents (ID, selfie, etc.)
   - Complete liveness check
   - Submit verification

4. Wait for verification to complete
5. You'll be redirected back to the dashboard

### 3.4 Verify Success State

After successful KYC verification:

**Dashboard Changes:**
- ✅ Success banner appears (green gradient)
- "Identity Verified Successfully!" heading
- Message explains next steps based on mode:
  - **KYC-Only Mode**: "You can now post jobs and access all employer features"
  - **Full Verification Mode**: "You can now submit your business documents for approval"
- Top-right button now shows "Post New Job"
- Success banner is dismissible

### 3.5 Test Job Posting

1. Click **"Post New Job"** button
2. You should be able to access the job posting form

**KYC-Only Mode:**
- ✅ Job posting form should load immediately
- No middleware blocking

**Full Verification Mode:**
- ❌ You'll be redirected to document upload page
- Must upload and get admin approval for documents first

---

## Expected Behavior by Mode

### KYC-Only Mode (`EMPLOYER_KYC_ONLY=true`)

| State | Dashboard Banner | Post Job Button | Can Post Jobs |
|-------|------------------|-----------------|---------------|
| **Not Verified** | ⚠️ Warning: "Identity Verification Required" | "Complete Verification to Post Jobs" | ❌ No |
| **KYC In Progress** | ⚠️ Warning: "Complete your verification" | "Complete Verification to Post Jobs" | ❌ No |
| **KYC Verified** | ✅ Success: "Identity Verified Successfully!" | "Post New Job" | ✅ Yes |

**Workflow:**
1. Login → See warning banner
2. Click "Complete Verification"
3. Complete KYC
4. Immediately able to post jobs

### Full Verification Mode (`EMPLOYER_KYC_ONLY=false`)

| State | Dashboard Banner | Post Job Button | Can Post Jobs |
|-------|------------------|-----------------|---------------|
| **Not Verified** | ⚠️ Warning: "Identity Verification Required" | "Complete Verification to Post Jobs" | ❌ No |
| **KYC Complete, Docs Pending** | ⚠️ Warning: "Document Approval Pending" | "Submit Documents" | ❌ No |
| **Fully Verified** | ✅ Success: "Fully Verified!" | "Post New Job" | ✅ Yes |

**Workflow:**
1. Login → See warning banner
2. Click "Complete Verification"
3. Complete KYC
4. Banner changes to "Document Approval Pending"
5. Upload business documents
6. Wait for admin approval
7. After approval, can post jobs

### Disabled Mode (`DISABLE_KYC_FOR_EMPLOYERS=true`)

| State | Dashboard Banner | Post Job Button | Can Post Jobs |
|-------|------------------|-----------------|---------------|
| **Any** | No banner shown | "Post New Job" | ✅ Yes |

**Workflow:**
1. Login → No verification required
2. Can immediately post jobs
3. **⚠️ USE ONLY FOR DEVELOPMENT!**

---

## Troubleshooting

### Issue: Banner Not Showing

**Check:**
1. Dashboard file was updated correctly
2. Cache was cleared: `php artisan config:clear`
3. Browser cache is cleared (hard refresh: Ctrl+Shift+R)

### Issue: KYC Modal Not Opening

**Check:**
1. KYC components are included in employer layout
2. JavaScript console for errors
3. Didit SDK is loaded properly
4. KYC routes are registered

### Issue: Still Can't Post Jobs After KYC

**Check:**
1. Verify KYC status in database:
   ```bash
   php artisan tinker
   >>> User::find(EMPLOYER_ID)->kyc_status
   ```

2. Should return: `"verified"`

3. Check configuration:
   ```bash
   >>> config('app.employer_kyc_only')
   >>> config('app.disable_kyc_for_employers')
   ```

4. Check `canPostJobs()` method:
   ```bash
   >>> User::find(EMPLOYER_ID)->canPostJobs()
   ```

5. Should return: `true`

### Issue: Warning Banner Shows After Verification

**Possible Causes:**
1. KYC verification didn't complete properly
2. Session not showing success state
3. `canPostJobs()` returning false

**Fix:**
```bash
# Check user status
php artisan tinker
>>> $user = User::find(EMPLOYER_ID);
>>> $user->kyc_status;
>>> $user->isKycVerified();
>>> $user->canPostJobs();
>>> $user->getEmployerVerificationStatus();
```

---

## Visual Verification Checklist

### ✅ Before KYC Verification
- [ ] Dashboard shows warning banner
- [ ] Banner has yellow/amber gradient background
- [ ] Warning icon (shield with exclamation) visible
- [ ] "Complete Verification Now" button present
- [ ] Top-right shows "Complete Verification to Post Jobs"
- [ ] Clicking button opens KYC modal

### ✅ After KYC Verification (KYC-Only Mode)
- [ ] Warning banner is replaced with success banner
- [ ] Success banner has green gradient background
- [ ] Success icon (shield with check) visible
- [ ] Message says "You can now post jobs"
- [ ] Success banner has dismiss button (X)
- [ ] Top-right shows "Post New Job"
- [ ] Clicking "Post New Job" works

### ✅ After KYC Verification (Full Mode)
- [ ] Warning banner changes to "Document Approval Pending"
- [ ] Banner still yellow/amber (warning)
- [ ] Message explains document requirement
- [ ] "Submit Documents" button present
- [ ] Top-right still shows verification needed
- [ ] Clicking "Post New Job" redirects to documents page

---

## Testing Different Scenarios

### Scenario 1: Fresh Employer Registration
1. Register new employer account
2. Login for first time
3. Should immediately see warning banner
4. Complete KYC
5. Verify success banner appears

### Scenario 2: Employer with Pending KYC
1. Start KYC verification but don't complete
2. Logout and login again
3. Should see warning banner
4. Complete the pending verification
5. Verify success banner appears

### Scenario 3: Switching Modes
1. Set `EMPLOYER_KYC_ONLY=false` (Full mode)
2. Complete KYC
3. Should see "Document Approval Pending"
4. Change to `EMPLOYER_KYC_ONLY=true` (KYC-only)
5. Clear cache: `php artisan config:clear`
6. Refresh dashboard
7. Should now be able to post jobs immediately

---

## Database Verification

### Check KYC Status in Database

```bash
php artisan tinker
```

```php
// Get employer user
$employer = User::where('role', 'employer')->first();

// Check KYC fields
echo "KYC Status: " . $employer->kyc_status . "\n";
echo "KYC Verified At: " . ($employer->kyc_verified_at ?? 'NULL') . "\n";
echo "Can Post Jobs: " . ($employer->canPostJobs() ? 'YES' : 'NO') . "\n";

// Check verification status
$status = $employer->getEmployerVerificationStatus();
echo "Verification Status: " . $status['status'] . "\n";
echo "Message: " . $status['message'] . "\n";

// Check config
echo "KYC Only Mode: " . (config('app.employer_kyc_only') ? 'YES' : 'NO') . "\n";
echo "KYC Disabled: " . (config('app.disable_kyc_for_employers') ? 'YES' : 'NO') . "\n";
```

---

## Quick Commands Reference

```bash
# Configure KYC mode
php scripts/kyc/configure_employer_kyc.php

# List users
php scripts/kyc/reset_kyc.php list

# Reset specific employer
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

# Check user status
>>> User::find(5)->canPostJobs()
>>> User::find(5)->getEmployerVerificationStatus()
```

---

## Success Criteria

Your implementation is working correctly if:

1. ✅ **Unverified employers see warning banner on dashboard**
2. ✅ **"Post New Job" button is replaced with verification button**
3. ✅ **Clicking verification button opens KYC modal**
4. ✅ **After KYC completion, success banner appears**
5. ✅ **Verified employers can access job posting**
6. ✅ **Middleware blocks unverified employers from posting**
7. ✅ **Configuration modes work as expected**
8. ✅ **Reset script properly clears all KYC data**

---

## Next Steps After Testing

1. **Choose Production Mode**:
   - Use **Full Verification** (`EMPLOYER_KYC_ONLY=false`) for production
   - Provides additional security with document verification

2. **Set Up Admin Document Approval**:
   - Create admin interface for reviewing employer documents
   - Add notification system for document status changes

3. **Create User Guide**:
   - Document the KYC process for employers
   - Add help links in the verification banner

4. **Monitor KYC Completion Rates**:
   - Track how many employers complete verification
   - Identify drop-off points in the process

---

## Support

If you encounter issues:

1. Check the logs: `storage/logs/laravel.log`
2. Review [EMPLOYER_KYC_GUIDE.md](docs/EMPLOYER_KYC_GUIDE.md) for detailed documentation
3. Use the reset script to start fresh if needed

---

**Happy Testing! 🚀**
