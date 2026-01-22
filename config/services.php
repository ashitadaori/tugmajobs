<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'persona' => [
        'api_key' => env('PERSONA_API_KEY'),
        'template_id' => env('PERSONA_TEMPLATE_ID'),
        'webhook_secret' => env('PERSONA_WEBHOOK_SECRET'),
    ],

    'didit' => [
        'auth_url' => env('DIDIT_AUTH_URL', 'https://auth.didit.me'),
        'base_url' => env('DIDIT_BASE_URL', 'https://verification.didit.me'),
        'api_key' => env('DIDIT_API_KEY'),
        'client_id' => env('DIDIT_CLIENT_ID'),
        'client_secret' => env('DIDIT_CLIENT_SECRET'),
        'workflow_id' => env('DIDIT_WORKFLOW_ID'),
        'callback_url' => env('DIDIT_CALLBACK_URL'),
        'redirect_url' => env('DIDIT_REDIRECT_URL'),
        'webhook_secret' => env('DIDIT_WEBHOOK_SECRET'),
    ],

    // Social Authentication Services
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    // PosterMyWall API Configuration
    'postermywall' => [
        'api_key' => env('POSTERMYWALL_API_KEY'),
        'client_id' => env('POSTERMYWALL_CLIENT_ID'),
        'client_secret' => env('POSTERMYWALL_CLIENT_SECRET'),
        'base_url' => env('POSTERMYWALL_BASE_URL', 'https://api.postermywall.com'),
    ],

];
