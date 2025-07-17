<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mapbox Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Mapbox integration in TugmaJobs
    | Focused on Digos City, Davao del Sur location services
    |
    */

    'public_token' => env('MAPBOX_PUBLIC_TOKEN'),
    'secret_token' => env('MAPBOX_SECRET_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Digos City Geographic Boundaries
    |--------------------------------------------------------------------------
    |
    | More precise bounding box coordinates for Digos City, Davao del Sur
    | Format: [longitude_min, latitude_min, longitude_max, latitude_max]
    |
    */
    'digos_bounds' => [
        'southwest' => [125.32, 6.72],
        'northeast' => [125.42, 6.82],
        'bbox' => '125.32,6.72,125.42,6.82'
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Filters
    |--------------------------------------------------------------------------
    */
    'search_filters' => [
        'country' => 'PH',
        'region' => 'Davao del Sur',
        'locality' => 'Digos City',
        'types' => 'address,poi,locality',
        'limit' => 5
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Map Settings
    |--------------------------------------------------------------------------
    */
    'default_center' => [
        'lng' => 125.4,
        'lat' => 6.8
    ],
    'default_zoom' => 13,

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    */
    'geocoding_url' => 'https://api.mapbox.com/geocoding/v5/mapbox.places',
    'search_url' => 'https://api.mapbox.com/search/searchbox/v1',
];