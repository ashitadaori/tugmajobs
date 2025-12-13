<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class LocationService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.opencagedata.com/geocode/v1/json';

    // Sta. Cruz, Davao del Sur boundary coordinates (approximate)
    private const STACRUZ_BOUNDS = [
        'min_lat' => 6.70, // Southern boundary
        'max_lat' => 6.85, // Northern boundary
        'min_lng' => 125.35, // Western boundary
        'max_lng' => 125.50  // Eastern boundary
    ];

    // Pre-defined areas/barangays in Sta. Cruz, Davao del Sur
    private const STACRUZ_AREAS = [
        'Astorga' => ['lat' => 6.7234, 'lng' => 125.4123],
        'Bato' => ['lat' => 6.7345, 'lng' => 125.4234],
        'Coronon' => ['lat' => 6.7456, 'lng' => 125.4345],
        'Darong' => ['lat' => 6.7567, 'lng' => 125.4456],
        'Inawayan' => ['lat' => 6.7678, 'lng' => 125.4567],
        'Jose Rizal' => ['lat' => 6.7789, 'lng' => 125.4678],
        'Matutungan' => ['lat' => 6.7890, 'lng' => 125.4789],
        'Poblacion' => ['lat' => 6.7512, 'lng' => 125.4234],
        'Tagabuli' => ['lat' => 6.7623, 'lng' => 125.4345],
        'Tibolo' => ['lat' => 6.7734, 'lng' => 125.4456],
        'Sibulan' => ['lat' => 6.7845, 'lng' => 125.4567],
        'Saliducon' => ['lat' => 6.7656, 'lng' => 125.4178],
        'Zone 1' => ['lat' => 6.7502, 'lng' => 125.4224],
        'Zone 2' => ['lat' => 6.7522, 'lng' => 125.4244],
        'Zone 3' => ['lat' => 6.7532, 'lng' => 125.4254]
    ];

    public function __construct()
    {
        $this->apiKey = config('services.opencage.key');
    }

    /**
     * Get all available areas in Sta. Cruz, Davao del Sur
     */
    public function getAllAreas()
    {
        $areas = [];
        foreach (self::STACRUZ_AREAS as $name => $coordinates) {
            $areas[] = [
                'name' => $name,
                'full_address' => "$name, Sta. Cruz, Davao del Sur",
                'lat' => $coordinates['lat'],
                'lng' => $coordinates['lng']
            ];
        }
        return $areas;
    }

    /**
     * Search for locations within Sta. Cruz, Davao del Sur
     */
    public function searchLocations(string $query)
    {
        $query = strtolower(trim($query));
        $results = [];

        foreach (self::STACRUZ_AREAS as $name => $coordinates) {
            if (empty($query) || str_contains(strtolower($name), $query)) {
                $results[] = [
                    'name' => $name,
                    'full_address' => "$name, Sta. Cruz, Davao del Sur",
                    'lat' => $coordinates['lat'],
                    'lng' => $coordinates['lng']
                ];
            }
        }

        return $results;
    }

    /**
     * Validate if coordinates are within Sta. Cruz, Davao del Sur bounds
     */
    public function validateCoordinates(float $lat, float $lng): bool
    {
        return $lat >= self::STACRUZ_BOUNDS['min_lat'] &&
               $lat <= self::STACRUZ_BOUNDS['max_lat'] &&
               $lng >= self::STACRUZ_BOUNDS['min_lng'] &&
               $lng <= self::STACRUZ_BOUNDS['max_lng'];
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

        foreach (self::STACRUZ_AREAS as $name => $coordinates) {
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
                    'full_address' => "$name, Sta. Cruz, Davao del Sur",
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