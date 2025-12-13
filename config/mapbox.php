<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mapbox Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Mapbox integration in TugmaJobs
    | Focused on Sta. Cruz, Davao del Sur location services
    |
    */

    'public_token' => env('MAPBOX_PUBLIC_TOKEN'),
    'secret_token' => env('MAPBOX_SECRET_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Sta. Cruz Geographic Boundaries
    |--------------------------------------------------------------------------
    |
    | More precise bounding box coordinates for Sta. Cruz, Davao del Sur
    | Format: [longitude_min, latitude_min, longitude_max, latitude_max]
    | Centered at approximately: Latitude 6.8370, Longitude 125.4130
    |
    */
    'stacruz_bounds' => [
        'southwest' => [125.30, 6.75],
        'northeast' => [125.55, 6.95],
        'bbox' => '125.30,6.75,125.55,6.95'
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Filters
    |--------------------------------------------------------------------------
    */
    'search_filters' => [
        'country' => 'PH',
        'region' => 'Davao del Sur',
        'locality' => 'Sta. Cruz',
        'types' => 'address,poi,locality',
        'limit' => 5
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Map Settings
    |--------------------------------------------------------------------------
    | Default center is set to Sta. Cruz, Davao del Sur
    */
    'default_center' => [
        'lng' => 125.4130,
        'lat' => 6.8370
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