# Admin KYC Verification System - DiDit Integration

## Overview

This implementation adds a comprehensive admin interface for viewing and managing KYC (Know Your Customer) verifications processed through DiDit. Admin users can now view user identification documents, personal information extracted from documents, and approve/reject verification requests.

## Features Added

### 1. Admin KYC Verifications List
- **URL**: `/admin/kyc/didit-verifications`
- **Purpose**: Lists all users who have submitted KYC verifications through DiDit
- **Features**:
  - Filter by verification status (pending, in_progress, verified, failed, expired)
  - Search users by name or email
  - View user profile information
  - Display document type and masked document numbers
  - Show personal information (name, date of birth, nationality)
  - Quick approve/reject actions

### 2. Detailed KYC Verification View
- **URL**: `/admin/kyc/user/{user}/verification`
- **Purpose**: Shows comprehensive details of a user's KYC verification
- **Information Displayed**:
  - User profile and account information
  - KYC verification session details
  - Document information (type, masked number)
  - Personal information extracted from documents
  - **Document images submitted by the user**
  - Raw verification data from DiDit
  - Status and timestamps

### 3. Document Image Viewing
- **Feature**: View actual document images submitted during KYC verification
- **Capabilities**:
  - Display document images in a responsive grid
  - Click to view full-size images in modal
  - Download document images
  - Support for multiple image formats and sources

### 4. Admin Actions
- **Approve Verification**: Mark KYC as verified and update user status
- **Reject Verification**: Mark KYC as failed with rejection reason
- **Status Management**: Track verification lifecycle

## Files Modified/Created

### Controller
- `app/Http/Controllers/Admin/KycController.php` - Enhanced with DiDit functionality

### Views
- `resources/views/admin/kyc/didit-verifications.blade.php` - KYC list view
- `resources/views/admin/kyc/show-didit-verification.blade.php` - Detailed KYC view

### Routes
- `routes/admin.php` - Added new DiDit KYC routes

### Sidebar
- `resources/views/admin/sidebar.blade.php` - Updated to point to new KYC page

### Services
- `app/Services/DiditService.php` - Added `getSessionDetails()` method

## Technical Implementation

### 1. Data Flow
1. User completes KYC verification through DiDit
2. DiDit sends webhook with verification data
3. `KycVerificationService` processes and stores data in `kyc_verifications` table
4. Admin can view and manage verifications through admin interface

### 2. Document Image Extraction
The system attempts to extract document images from various locations in the DiDit response:
- `result.document_images`
- `result.images`
- `result.documents[].images`
- `result.extracted_data.document.images`
- `result.verification_data.document.images`

### 3. Security Features
- Document numbers are masked (showing only last 4 digits)
- Proper authentication and authorization checks
- CSRF protection on all forms
- Input validation and sanitization

### 4. User Experience
- Responsive design for mobile and desktop
- Interactive image viewing with modal popups
- Filtering and search capabilities
- Clear status indicators and badges
- Breadcrumb navigation

## Usage Instructions

### For Administrators

1. **Access KYC Verifications**
   - Navigate to Admin Panel → KYC Verifications
   - View list of all users with KYC submissions

2. **Filter and Search**
   - Use status dropdown to filter by verification status
   - Search by user name or email address

3. **View Details**
   - Click "View Details" (eye icon) to see comprehensive verification info
   - Review user information, documents, and images

4. **Approve/Reject**
   - Use approve button (✓) to approve verification
   - Use reject button (✗) to reject with reason
   - Confirm actions when prompted

5. **View Document Images**
   - Click on any document image to view full size
   - Use download button to save images locally

## Routes Added

```php
// DiDit KYC Management Routes
Route::get('/didit-verifications', [KycController::class, 'diditVerifications'])
    ->name('kyc.didit-verifications');
    
Route::get('/user/{user}/verification', [KycController::class, 'showDiditVerification'])
    ->name('kyc.show-didit-verification');
    
Route::patch('/user/{user}/approve', [KycController::class, 'approveDiditVerification'])
    ->name('kyc.approve-didit-verification');
    
Route::patch('/user/{user}/reject', [KycController::class, 'rejectDiditVerification'])
    ->name('kyc.reject-didit-verification');
```

## Database Schema Used

The implementation uses the existing `kyc_verifications` table:
- `user_id` - Foreign key to users table
- `session_id` - DiDit session identifier
- `status` - Verification status
- `document_type` - Type of document submitted
- `document_number` - Document number (stored securely)
- `firstname`, `lastname` - Extracted personal information
- `date_of_birth` - Date of birth from document
- `gender` - Gender information
- `address` - Address information
- `nationality` - Nationality
- `raw_data` - Complete raw data from DiDit
- `verification_data` - Processed verification data
- `verified_at` - Timestamp when approved

## Error Handling

- Graceful handling of missing DiDit session data
- Fallback display when document images are not available
- Proper error messages for failed operations
- Logging of errors for debugging

## Future Enhancements

Potential improvements that could be added:
1. Bulk approval/rejection actions
2. Advanced filtering options (date ranges, document types)
3. Export functionality for verification reports
4. Integration with notification system
5. Audit trail for admin actions
6. Document comparison with user profile data

## Notes

- The system maintains backward compatibility with existing KYC functionality
- All sensitive data is properly protected and masked in the UI
- The interface is designed to be intuitive for admin users
- Performance optimized with proper eager loading and pagination
