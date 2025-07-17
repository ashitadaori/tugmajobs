# KYC Verification System Integration Guide

This document provides a comprehensive guide to the KYC (Know Your Customer) verification system integrated into the job portal platform.

## Overview

The KYC verification system allows both job seekers and employers to verify their identities, building trust within the platform. Verified users receive a badge that appears throughout the system, increasing credibility and engagement.

## Features

- **Identity Verification**: Secure verification through Didit API
- **Verification Badges**: Visual indicators of verified users
- **Dashboard Integration**: KYC status cards in user dashboards
- **Reminder System**: Gentle nudges for unverified users
- **Middleware Protection**: Route protection for sensitive features

## User Experience

### For Job Seekers

1. **Dashboard Notification**: Job seekers see their verification status on their dashboard
2. **Verification Process**: Simple flow to complete identity verification
3. **Benefits**: Verified job seekers are 5x more likely to get hired
4. **Badge Display**: Verification badge appears on applications and profile

### For Employers

1. **Company Verification**: Employers verify their company identity
2. **Trust Building**: Verified employers receive 3x more applications
3. **Badge Display**: Verification badge appears on job listings
4. **Enhanced Credibility**: Stand out from unverified employers

## Technical Implementation

### Database Structure

The KYC system uses the following fields in the `users` table:

- `kyc_status`: Current verification status (pending, in_progress, verified, failed, expired)
- `kyc_session_id`: ID of the current verification session
- `kyc_completed_at`: Timestamp when verification was completed
- `kyc_verified_at`: Timestamp when verification was confirmed
- `kyc_data`: JSON data containing verification details

### Components

1. **KYC Status Card** (`resources/views/components/kyc-status-card.blade.php`)
   - Displays current verification status
   - Provides appropriate actions based on status
   - Customizable for different contexts

2. **Verified Badge** (`resources/views/components/verified-badge.blade.php`)
   - Visual indicator of verified status
   - Configurable size and appearance
   - Used throughout the system

3. **KYC Reminder Banner** (`resources/views/components/kyc-reminder-banner.blade.php`)
   - Encourages users to complete verification
   - Dismissible with session storage
   - Tailored messaging for different user types

### Middleware

1. **EncourageKycVerification** (`app/Http/Middleware/EncourageKycVerification.php`)
   - Adds gentle reminders for unverified users
   - Applied globally to web routes

2. **CheckKycVerification** (`app/Http/Middleware/CheckKycVerification.php`)
   - Enforces verification for protected routes
   - Redirects unverified users to verification page
   - Applied selectively to sensitive features

### Services

**DiditService** (`app/Services/DiditService.php`)
- Handles API communication with Didit
- Creates verification sessions
- Processes webhook events
- Updates user verification status

## Integration Points

### Views with Verification Badges

- Job listings (`resources/views/front/modern-jobs.blade.php`)
- Job details (`resources/views/front/modern-job-detail.blade.php`)
- User profiles
- Applications

### Protected Routes

Add the `kyc.verified` middleware to routes that require verification:

```php
Route::middleware(['auth', 'kyc.verified'])->group(function () {
    // Routes that require KYC verification
    Route::post('/jobs/{job}/apply', [JobApplicationController::class, 'store']);
    Route::post('/jobs/create', [EmployerController::class, 'storeJob']);
});
```

## Customization

### Styling

The verification components use Bootstrap classes and custom CSS. You can customize the appearance by modifying:

- `resources/views/components/verified-badge.blade.php`
- `resources/views/components/kyc-status-card.blade.php`
- `resources/views/components/kyc-reminder-banner.blade.php`

### Messaging

Customize the messaging in the components to match your brand voice and user expectations.

## Testing

1. **Test Verification Flow**:
   - Register as a new user
   - Visit dashboard and start verification
   - Complete the verification process
   - Confirm badge appears

2. **Test Protected Routes**:
   - Try accessing protected routes as unverified user
   - Verify redirection to verification page
   - Complete verification and retry

3. **Test Webhook Handling**:
   - Use the Didit test environment
   - Trigger verification events
   - Confirm status updates correctly

## Troubleshooting

### Common Issues

1. **Verification Not Starting**:
   - Check Didit API credentials
   - Verify workflow ID configuration
   - Check logs for API errors

2. **Status Not Updating**:
   - Verify webhook URL is accessible
   - Check webhook signature verification
   - Confirm webhook events are being processed

3. **Badges Not Displaying**:
   - Check user's `kyc_status` in database
   - Verify component inclusion in views
   - Clear cache and session data

## Security Considerations

1. **Data Protection**:
   - Sensitive verification data is stored securely
   - Only necessary information is displayed

2. **Webhook Security**:
   - All webhooks verify signatures
   - Invalid signatures are rejected

3. **Session Management**:
   - Verification sessions are tracked and managed
   - Expired sessions are handled gracefully

## Conclusion

The KYC verification system enhances trust and security within the platform. By encouraging users to verify their identities, we create a more reliable ecosystem for job seekers and employers alike.