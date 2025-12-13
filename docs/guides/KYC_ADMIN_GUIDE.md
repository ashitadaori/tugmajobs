# Admin KYC Verification Interface Guide

## Problem Summary
You were unable to see the KYC verification documents and images in the admin interface. The issue was that there were no completed KYC records with image data in the database.

## Current Status
✅ **FIXED**: I have created test KYC data with sample images to test the admin interface.

## How to Access Admin KYC Interface

### 1. Login as Admin
Make sure you're logged in as an admin user.

### 2. Navigate to KYC Verifications
Access the KYC verification interface at:
```
http://localhost/admin/kyc/didit-verifications
```

Or navigate through the admin dashboard:
- Admin Dashboard → KYC Management → DiDit Verifications

### 3. View KYC Details
Click on the "View Details" (eye icon) button next to any user to see:
- Personal information from KYC verification
- Document information (ID type, number, etc.)
- **Verification Images**: Front ID, Back ID, and Live Selfie photos
- Raw verification data

## Database Structure

### KYC Data Storage
KYC verification images are stored in the `kyc_data` table with these columns:
- `front_image_url` - Front side of ID document
- `back_image_url` - Back side of ID document  
- `portrait_image_url` - Live selfie photo
- `raw_payload` - Complete verification data from DiDit

### Image Display Logic
The admin controller (`app/Http/Controllers/admin/KycController.php`) retrieves images with this priority:
1. **KycData columns** (most reliable)
2. **KycData raw_payload** (webhook data)
3. **KycVerification raw_data** (fallback)
4. **DiDit API call** (least reliable due to session expiration)

## Test Data Created

I created test KYC data for user "khenrick herana" with:
- Status: `in_progress`
- Session ID: `test_session_1754698046`
- Sample images (placeholder URLs)
- Complete personal and document information

## Features Available in Admin Interface

### 1. KYC Verification List
- Filter by status (pending, verified, failed, etc.)
- Search by user name or email
- View user information and KYC status
- Quick actions (view, approve, reject)

### 2. Individual KYC Details
- User profile information
- Verification status and timestamps
- Document information (type, number, nationality)
- Personal information (name, DOB, address)
- **Verification Images** with click-to-enlarge modal
- Raw verification data (collapsible sections)

### 3. Admin Actions
- **Approve** verification
- **Reject** verification with reason
- **Refresh** verification data from DiDit API

## Image Modal Features
- Click any verification image to view full size
- Download images directly
- Proper categorization:
  - Document Front (blue header)
  - Document Back (green header)
  - Live Selfie (orange header)

## Troubleshooting

### If No Images Show:
1. Check if KYC data exists: Run `php check_kyc_images.php`
2. Verify image URLs are not empty in database
3. Check if images are accessible (URLs return valid images)

### If Admin Access Denied:
1. Ensure user has `role = 'admin'` in users table
2. Check admin middleware is working
3. Clear cache: `php artisan cache:clear`

### If Images Don't Load:
1. Check image URLs are valid
2. Verify CORS settings if images are from external domains
3. Check browser console for errors

## Database Queries for Debugging

```sql
-- Check KYC data
SELECT id, user_id, status, front_image_url, back_image_url, portrait_image_url 
FROM kyc_data;

-- Check users with KYC status
SELECT id, name, email, kyc_status 
FROM users 
WHERE kyc_status IS NOT NULL;

-- Get admin users
SELECT id, name, email, role 
FROM users 
WHERE role = 'admin';
```

## Next Steps

1. **Access the admin interface** at the URL above
2. **Test the image display** by clicking on verification images
3. **Test admin actions** (approve/reject KYC)
4. **Create more test data** if needed using `create_test_kyc_data.php`

## Real KYC Integration

For production use with real KYC verifications:
1. Ensure the DiDit webhook is properly configured
2. Verify image URLs from DiDit are accessible
3. Test the complete KYC flow from user initiation to admin approval
4. Monitor the `kyc_data` table for proper data storage

The system is designed to handle real DiDit KYC data, but the images depend on:
- Proper webhook payload structure
- Valid image URLs from DiDit
- Correct data extraction in the webhook handler
