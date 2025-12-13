<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In to TugmaJobs</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #3b82f6;
            font-size: 28px;
            margin: 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .content h2 {
            color: #1f2937;
            font-size: 24px;
            margin-bottom: 15px;
        }
        .content p {
            color: #4b5563;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .sign-in-button {
            display: inline-block;
            background-color: #d91b5c;
            color: #ffffff !important;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .sign-in-button:hover {
            background-color: #b8154a;
        }
        .alternative-link {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 6px;
            word-break: break-all;
        }
        .alternative-link p {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 10px;
        }
        .alternative-link a {
            color: #3b82f6;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        .footer p {
            color: #9ca3af;
            font-size: 14px;
            margin: 5px 0;
        }
        .expiry-notice {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .expiry-notice p {
            color: #92400e;
            font-size: 14px;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="logo">
            <h1>TugmaJobs</h1>
        </div>

        <div class="content">
            <h2>Sign in to your account</h2>
            <p>Hello!</p>
            <p>Click the button below to sign in to your TugmaJobs account. This link is secure and will sign you in automatically.</p>
        </div>

        <div class="button-container">
            <a href="{{ $loginUrl }}" class="sign-in-button">Sign In to TugmaJobs</a>
        </div>

        <div class="expiry-notice">
            <p><strong>⏰ This link will expire in {{ $expiresInMinutes }} minutes.</strong></p>
        </div>

        <div class="alternative-link">
            <p>If the button doesn't work, copy and paste this link into your browser:</p>
            <a href="{{ $loginUrl }}">{{ $loginUrl }}</a>
        </div>

        <div class="footer">
            <p>If you didn't request this email, you can safely ignore it.</p>
            <p>This is an automated message, please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} TugmaJobs. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
