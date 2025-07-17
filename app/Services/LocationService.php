<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class LocationService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.opencagedata.com/geocode/v1/json';

    // Digos City boundary coordinates (approximate)
    private const DIGOS_BOUNDS = [
        'min_lat' => 6.7500, // Southern boundary
        'max_lat' => 6.7900, // Northern boundary
        'min_lng' => 125.3500, // Western boundary
        'max_lng' => 125.3900  // Eastern boundary
    ];

    // Pre-defined areas/barangays in Digos City
    private const DIGOS_AREAS = [
        'Aplaya' => ['lat' => 6.7489, 'lng' => 125.3714],
        'Binaton' => ['lat' => 6.7623, 'lng' => 125.3897],
        'Cogon' => ['lat' => 6.7512, 'lng' => 125.3567],
        'Colorado' => ['lat' => 6.7534, 'lng' => 125.3678],
        'Dawis' => ['lat' => 6.7567, 'lng' => 125.3645],
        'Dulangan' => ['lat' => 6.7589, 'lng' => 125.3723],
        'Goma' => ['lat' => 6.7612, 'lng' => 125.3834],
        'Igpit' => ['lat' => 6.7645, 'lng' => 125.3756],
        'Mahayag' => ['lat' => 6.7678, 'lng' => 125.3867],
        'Matti' => ['lat' => 6.7523, 'lng' => 125.3589],
        'Poblacion' => ['lat' => 6.7545, 'lng' => 125.3578],
        'San Jose' => ['lat' => 6.7556, 'lng' => 125.3634],
        'San Miguel' => ['lat' => 6.7578, 'lng' => 125.3712],
        'Sinawilan' => ['lat' => 6.7634, 'lng' => 125.3845],
        'Soong' => ['lat' => 6.7667, 'lng' => 125.3789],
        'Tres De Mayo' => ['lat' => 6.7689, 'lng' => 125.3856]
    ];

    public function __construct()
    {
        $this->apiKey = config('services.opencage.key');
    }

    /**
     * Get all available areas in Digos City
     */
    public function getAllAreas()
    {
        $areas = [];
        foreach (self::DIGOS_AREAS as $name => $coordinates) {
            $areas[] = [
                'name' => $name,
                'full_address' => "$name, Digos City, Davao del Sur",
                'lat' => $coordinates['lat'],
                'lng' => $coordinates['lng']
            ];
        }
        return $areas;
    }

    /**
     * Search for locations within Digos City
     */
    public function searchLocations(string $query)
    {
        $query = strtolower(trim($query));
        $results = [];

        foreach (self::DIGOS_AREAS as $name => $coordinates) {
            if (empty($query) || str_contains(strtolower($name), $query)) {
                $results[] = [
                    'name' => $name,
                    'full_address' => "$name, Digos City, Davao del Sur",
                    'lat' => $coordinates['lat'],
                    'lng' => $coordinates['lng']
                ];
            }
        }

        return $results;
    }

    /**
     * Validate if coordinates are within Digos City bounds
     */
    public function validateCoordinates(float $lat, float $lng): bool
    {
        return $lat >= self::DIGOS_BOUNDS['min_lat'] &&
               $lat <= self::DIGOS_BOUNDS['max_lat'] &&
               $lng >= self::DIGOS_BOUNDS['min_lng'] &&
               $lng <= self::DIGOS_BOUNDS['max_lng'];
    }

    /**
     * Get nearest area based on coordinates
     */
    public function getNearestArea(float $lat, float $lng)
    {
        if (!$this->validateCoordinates($lat, $lng)) {
            return null;
        }

        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach (self::DIGOS_AREAS as $name => $coordinates) {
            $distance = $this->calculateDistance(
                $lat,
                $lng,
                $coordinates['lat'],
                $coordinates['lng']
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = [
                    'name' => $name,
                    'full_address' => "$name, Digos City, Davao del Sur",
                    'lat' => $coordinates['lat'],
                    'lng' => $coordinates['lng'],
                    'distance' => round($distance, 2)
                ];
            }
        }

        return $nearest;
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latDiff = deg2rad($lat2 - $lat1);
        $lngDiff = deg2rad($lng2 - $lng1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDiff / 2) * sin($lngDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get location details by coordinates
     */
    public function getLocationByCoordinates(float $lat, float $lng)
    {
        $cacheKey = "location_coords_{$lat}_{$lng}";

        return Cache::remember($cacheKey, 3600, function () use ($lat, $lng) {
            $response = Http::get($this->baseUrl, [
                'q' => "{$lat},{$lng}",
                'key' => $this->apiKey,
                'limit' => 1,
                'no_annotations' => 1
            ]);

            if ($response->successful() && !empty($response->json()['results'])) {
                $result = $response->json()['results'][0];
                return [
                    'formatted' => $result['formatted'],
                    'components' => $result['components'] ?? []
                ];
            }

            return null;
        });
    }
} 