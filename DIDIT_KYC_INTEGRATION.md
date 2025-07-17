# Didit KYC Integration Guide

This document explains how to integrate and use the Didit KYC (Know Your Customer) verification system in your Laravel application.

## Overview

The integration provides:
- Identity verification using business.didit.me API
- Secure document verification
- Real-time webhook notifications
- User-friendly verification flow
- Status tracking and management

## Configuration

### 1. Environment Variables

Add the following variables to your `.env` file:

```env
# Didit KYC Configuration
DIDIT_AUTH_URL=https://business.didit.me
DIDIT_BASE_URL=https://business.didit.me
DIDIT_API_KEY=your_didit_api_key_here
DIDIT_CLIENT_ID=your_didit_client_id_here
DIDIT_CLIENT_SECRET=your_didit_client_secret_here
DIDIT_WORKFLOW_ID=your_workflow_id_here
DIDIT_CALLBACK_URL=${APP_URL}/kyc/webhook
DIDIT_REDIRECT_URL=${APP_URL}/kyc/success
DIDIT_WEBHOOK_SECRET=your_webhook_secret_here
```

### 2. Get Your Credentials

1. Sign up at [business.didit.me](https://business.didit.me)
2. Create a new application/project
3. Get your API credentials:
   - **API Key** (required)
   - **Client ID** (required)
   - **Client Secret** (required)
   - **Workflow ID** (optional - see below)
   - **Webhook Secret** (optional but recommended)

#### Finding Your Workflow ID

The Workflow ID is optional. If not provided, Didit will use your default workflow. To find it:

1. **Login to business.didit.me**
2. **Look for one of these sections:**
   - "Workflows"
   - "Verification Templates" 
   - "Verification Flows"
   - "Integration" or "API" section
3. **Copy the Workflow ID** (usually in UUID format like: `d1820700-83be-4b2d-9d76-45b2a646ef10`)
4. **Alternative:** Contact Didit support and ask for your default workflow ID

**Note:** You can start testing without the Workflow ID - just leave it as the placeholder value.

### 3. Configure Webhook URL

In your Didit dashboard, set the webhook URL to:
```
https://yourdomain.com/kyc/webhook
```

## Usage

### Starting KYC Verification

Users can start KYC verification by visiting:
```
/kyc/start
```

Or programmatically redirect them:
```php
return redirect()->route('kyc.start');
```

### Checking Verification Status

```php
// In a controller
$status = app(DiditService::class)->getSessionStatus($sessionId);
```

### Handling Webhook Events

The system automatically handles webhook events:
- `session.completed` - Verification successful
- `session.failed` - Verification failed
- `session.expired` - Session expired

## API Endpoints

### User Routes (Authenticated)
- `GET /kyc/` - KYC start page
- `POST /kyc/start` - Start verification process
- `GET /kyc/success` - Success page
- `GET /kyc/failure` - Failure page
- `POST /kyc/check-status` - Check verification status

### Webhook Route (Public)
- `POST /kyc/webhook` - Didit webhook handler

## Testing

### Test the Integration

Run the test command to verify your configuration:

```bash
php artisan didit:test
```

This will:
1. Check all configuration values
2. Test authentication with Didit API
3. Test session creation
4. Provide detailed feedback

### Manual Testing

1. Ensure your `.env` variables are set correctly
2. Visit `/kyc/start` while logged in
3. Complete the verification process
4. Check logs for webhook events

## Customization

### Custom Verification Data

You can customize the data sent to Didit:

```php
// In KycController@startVerification
$sessionData = [
    'vendor_data' => 'custom-user-' . $user->id,
    'metadata' => [
        'user_id' => $user->id,
        'user_type' => 'premium',
        'custom_field' => 'value',
    ],
    'contact_details' => [
        'email' => $user->email,
        'phone' => $user->phone,
        'email_lang' => 'en',
    ]
];
```

### Custom Webhook Handling

Extend the `DiditService` to add custom webhook handling:

```php
// In DiditService
protected function handleSessionCompleted(array $event): void
{
    Log::info('KYC verification completed', $event);
    
    // Your custom logic here
    $userId = $event['metadata']['user_id'] ?? null;
    if ($userId) {
        User::where('id', $userId)->update(['kyc_verified' => true]);
    }
}
```

### Custom Views

The views are located in `resources/views/kyc/`:
- `start.blade.php` - Verification start page
- `success.blade.php` - Success page
- `failure.blade.php` - Failure page
- `pending.blade.php` - Pending verification page

## Security Considerations

1. **Webhook Signature Verification**: Always verify webhook signatures
2. **HTTPS**: Use HTTPS for all webhook URLs
3. **Environment Variables**: Keep credentials in environment variables
4. **Logging**: Monitor logs for suspicious activity
5. **Rate Limiting**: Consider rate limiting for KYC endpoints

## Troubleshooting

### Common Issues

1. **Invalid Signature Error**
   - Check webhook secret configuration
   - Ensure webhook URL is correct

2. **Authentication Failed**
   - Verify client ID and secret
   - Check API endpoint URLs

3. **Session Creation Failed**
   - Verify API key
   - Check workflow ID
   - Ensure callback URLs are accessible

### Debug Mode

Enable detailed logging by setting:
```env
LOG_LEVEL=debug
```

### Check Logs

Monitor these log files:
- `storage/logs/laravel.log` - General application logs
- Look for entries with "Didit" in the message

## Support

For technical issues:
1. Check the logs first
2. Run `php artisan didit:test`
3. Verify all configuration values
4. Contact Didit support if API issues persist

## API Documentation

For detailed API documentation, visit:
- [Didit API Documentation](https://business.didit.me/docs)
- [Webhook Events Reference](https://business.didit.me/docs/webhooks)