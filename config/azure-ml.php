<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Azure Machine Learning Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Azure ML workspace credentials and endpoint settings.
    | These values are used to connect to your Azure ML workspace for
    | K-means clustering operations.
    |
    */

    // Azure ML Workspace Settings
    'workspace_name' => env('AZURE_ML_WORKSPACE_NAME', 'tugmajobs'),
    'subscription_id' => env('AZURE_ML_SUBSCRIPTION_ID'),
    'resource_group' => env('AZURE_ML_RESOURCE_GROUP', 'AzureML-RG'),
    'region' => env('AZURE_ML_REGION', 'eastasia'),

    // Azure Authentication
    'tenant_id' => env('AZURE_TENANT_ID'),
    'client_id' => env('AZURE_CLIENT_ID'),
    'client_secret' => env('AZURE_CLIENT_SECRET'),

    // Endpoint Configuration (after deployment)
    'endpoint_url' => env('AZURE_ML_ENDPOINT_URL'),
    'endpoint_key' => env('AZURE_ML_ENDPOINT_KEY'),

    // Clustering Settings
    'clustering' => [
        'default_k' => env('AZURE_ML_DEFAULT_K', 5),
        'max_iterations' => env('AZURE_ML_MAX_ITERATIONS', 100),
        'tolerance' => env('AZURE_ML_TOLERANCE', 0.0001),
        'algorithm' => env('AZURE_ML_ALGORITHM', 'lloyd'), // lloyd, elkan, auto
        'init_method' => env('AZURE_ML_INIT_METHOD', 'k-means++'),
    ],

    // Feature Scaling
    'scaling' => [
        'enabled' => env('AZURE_ML_SCALING_ENABLED', true),
        'method' => env('AZURE_ML_SCALING_METHOD', 'standard'), // standard, minmax, robust
    ],

    // Caching
    'cache' => [
        'enabled' => env('AZURE_ML_CACHE_ENABLED', true),
        'ttl' => env('AZURE_ML_CACHE_TTL', 3600), // 1 hour
        'prefix' => 'azure_ml_',
    ],

    // Fallback Settings
    'fallback' => [
        'enabled' => env('AZURE_ML_FALLBACK_ENABLED', true),
        'use_local_clustering' => true,
    ],

    // Logging
    'logging' => [
        'enabled' => env('AZURE_ML_LOGGING_ENABLED', true),
        'channel' => env('AZURE_ML_LOG_CHANNEL', 'stack'),
    ],

    // Timeout Settings (in seconds)
    'timeout' => [
        'connection' => env('AZURE_ML_CONNECTION_TIMEOUT', 30),
        'request' => env('AZURE_ML_REQUEST_TIMEOUT', 120),
    ],
];
