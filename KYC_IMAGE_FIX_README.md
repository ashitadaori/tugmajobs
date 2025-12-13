# KYC Document Image Storage Fix

## Problem Identified

The KYC verification images (document front, document back, and live selfie) were disappearing after some time because:

1. **Temporary URLs**: The Didit API provides temporary image URLs that expire after a certain period (typically 30-90 days)
2. **No Local Storage**: The application was storing these temporary URLs directly in the database without downloading the actual images
3. **URL Expiration**: When Didit's URLs expired, the images became inaccessible

## Solution Implemented

The fix implements a permanent storage solution that:

1. **Downloads Images**: When the KYC webhook is received from Didit, images are now downloaded immediately
2. **Stores Permanently**: Images are stored in Laravel's public storage disk at `storage/app/public/kyc/{user_id}/{session_id}/`
3. **Updates Database**: The database is updated with permanent local URLs instead of temporary Didit URLs
4. **Fallback Safety**: If download fails, the original URL is kept as a fallback

## Files Modified

### 1. KycWebhookController.php
- **Location**: `app/Http/Controllers/KycWebhookController.php`
- **Changes**:
  - Added `Storage` and `Http` facade imports
  - Modified image extraction logic to download and store images permanently
  - Added `downloadAndStoreImage()` helper method that:
    - Downloads images from Didit URLs
    - Stores them in `storage/app/public/kyc/{user_id}/{session_id}/`
    - Returns the permanent public URL
    - Falls back to original URL if download fails

### 2. BackfillKycImages Command
- **Location**: `app/Console/Commands/BackfillKycImages.php`
- **Purpose**: Backfill existing KYC records with permanent image storage
- **Features**:
  - Processes existing KYC records that still have external URLs
  - Downloads images from Didit URLs before they expire
  - Updates database with permanent local URLs
  - Progress bar and detailed summary
  - Configurable limit and force options

## How to Use

### For New KYC Verifications

**No action required!** The webhook handler now automatically downloads and stores images permanently when new KYC verifications are completed.

### For Existing KYC Records (Backfill)

Run the backfill command to download and store images for existing KYC records:

```bash
# Process up to 50 records (default)
php artisan kyc:backfill-images

# Process up to 100 records
php artisan kyc:backfill-images --limit=100

# Process ALL records with external URLs
php artisan kyc:backfill-images --limit=1000

# Force reprocess all records (even those already stored locally)
php artisan kyc:backfill-images --force
```

**Important**: Run the backfill command as soon as possible for existing records, as the Didit URLs may expire soon!

## Storage Structure

Images are stored in the following structure:

```
storage/
  app/
    public/
      kyc/
        {user_id}/
          {session_id}/
            front_{timestamp}.jpg      # Document front image
            back_{timestamp}.jpg       # Document back image
            portrait_{timestamp}.jpg   # Live selfie image
            video_{timestamp}.mp4      # Liveness video (if available)
```

## Public Access

Make sure the storage is linked for public access:

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`, making the KYC images accessible via URLs like:
```
https://yourapp.com/storage/kyc/123/session-abc/front_1234567890.jpg
```

## Verification

To verify the fix is working:

1. **Check Logs**: Look for entries like "KYC image downloaded and stored successfully" in your logs
2. **Check Database**: The `kyc_data` table should have local URLs like `/storage/kyc/...` instead of `https://didit.me/...`
3. **Check Storage**: Files should exist in `storage/app/public/kyc/{user_id}/{session_id}/`
4. **Admin Panel**: Images should display correctly in the admin KYC verification view

## Monitoring

Monitor the following for issues:

- **Storage Space**: KYC images will consume disk space (typically 100-500 KB per verification)
- **Download Failures**: Check logs for "Failed to download KYC image" errors
- **Webhook Logs**: Review "Downloading KYC image" and "KYC image downloaded and stored successfully" log entries

## Rollback

If you need to rollback this change:

1. The original Didit URLs are preserved as fallback if download fails
2. No existing functionality is broken
3. Images are still accessible via Didit URLs until they expire

## Future Improvements

Consider implementing:

1. **Image Optimization**: Compress images to save storage space
2. **CDN Integration**: Serve images from a CDN for better performance
3. **Automatic Cleanup**: Delete images after a retention period (e.g., 5 years)
4. **Backup Strategy**: Regular backups of KYC images to cloud storage (S3, etc.)
5. **Scheduled Backfill**: Run backfill command daily via cron to catch any missed images

## Support

If you encounter issues:

1. Check Laravel logs in `storage/logs/laravel.log`
2. Verify storage permissions: `storage/app/public` should be writable
3. Ensure HTTP client can download from Didit URLs (check firewall/network)
4. Run backfill command with verbose output to see detailed progress

## Technical Details

### Webhook Flow (New Verifications)

1. Didit sends webhook with temporary image URLs
2. `KycWebhookController::saveKycData()` extracts image URLs
3. `downloadAndStoreImage()` downloads each image
4. Images stored in `storage/app/public/kyc/{user_id}/{session_id}/`
5. Database updated with permanent local URLs
6. Original Didit URLs preserved in `raw_payload` for reference

### Backfill Flow (Existing Records)

1. Command queries `kyc_data` for records with external URLs
2. For each record, downloads front/back/portrait images
3. Stores images in same structure as webhook handler
4. Updates database with local URLs
5. Shows progress bar and summary
