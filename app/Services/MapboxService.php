<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MapboxService
{
    private $publicToken;
    private $secretToken;
    private $stacruzBounds;

    public function __construct()
    {
        $this->publicToken = config('mapbox.public_token');
        $this->secretToken = config('mapbox.secret_token');
        $this->stacruzBounds = config('mapbox.stacruz_bounds.bbox');
    }

    /**
     * Geocode an address to coordinates
     */
    public function geocodeAddress($address)
    {
        try {
            // Add Sta. Cruz context to the search
            $searchQuery = $address . ', Sta. Cruz, Davao del Sur, Philippines';

            // First try with bbox restriction
            $response = Http::get(config('mapbox.geocoding_url') . '/' . urlencode($searchQuery) . '.json', [
                'access_token' => $this->publicToken,
                'bbox' => $this->stacruzBounds,
                'country' => 'PH',
                'types' => 'address,poi,locality,place',
                'limit' => 10
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Filter results to only include Sta. Cruz locations
                if (isset($data['features'])) {
                    $data['features'] = $this->filterStaCruzResults($data['features']);
                }

                // If no results with bbox, try without bbox restriction
                if (empty($data['features'])) {
                    $response = Http::get(config('mapbox.geocoding_url') . '/' . urlencode($searchQuery) . '.json', [
                        'access_token' => $this->publicToken,
                        'country' => 'PH',
                        'types' => 'address,poi,locality,place',
                        'limit' => 10
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        // Filter results to only include Sta. Cruz locations
                        if (isset($data['features'])) {
                            $data['features'] = $this->filterStaCruzResults($data['features']);
                        }
                    }
                }

                return $data;
            }

            Log::error('Mapbox geocoding failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Mapbox geocoding error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Reverse geocode coordinates to address
     */
    public function reverseGeocode($longitude, $latitude)
    {
        try {
            $response = Http::get(config('mapbox.geocoding_url') . "/{$longitude},{$latitude}.json", [
                'access_token' => $this->publicToken,
                'types' => 'address,poi,locality,place,neighborhood'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Mapbox reverse geocode response', [
                    'lng' => $longitude,
                    'lat' => $latitude,
                    'features_count' => count($data['features'] ?? [])
                ]);
                return $data;
            }

            Log::warning('Mapbox reverse geocode failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Mapbox reverse geocoding error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Search for places within Sta. Cruz
     */
    public function searchPlaces($query)
    {
        try {
            // Add Sta. Cruz context to the search query
            $searchQuery = $query . ' Sta. Cruz Davao del Sur';

            // First try with bbox restriction
            $response = Http::get(config('mapbox.geocoding_url') . '/' . urlencode($searchQuery) . '.json', [
                'access_token' => $this->publicToken,
                'bbox' => $this->stacruzBounds,
                'country' => 'PH',
                'types' => 'address,poi,locality,place',
                'limit' => 10
            ]);

            $features = [];

            if ($response->successful()) {
                $data = $response->json();

                // Filter results to only include Sta. Cruz locations
                if (isset($data['features'])) {
                    $features = $this->filterStaCruzResults($data['features']);
                }
            }

            // If no results with bbox, try without bbox restriction
            if (empty($features)) {
                $response = Http::get(config('mapbox.geocoding_url') . '/' . urlencode($searchQuery) . '.json', [
                    'access_token' => $this->publicToken,
                    'country' => 'PH',
                    'types' => 'address,poi,locality,place',
                    'limit' => 10
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['features'])) {
                        $features = $this->filterStaCruzResults($data['features']);
                    }
                }
            }

            // Convert to suggestions format for consistency
            $suggestions = [];
            foreach ($features as $feature) {
                $suggestions[] = [
                    'name' => $this->extractLocationName($feature['place_name']),
                    'place_name' => $feature['place_name'],
                    'full_address' => $feature['place_name'],
                    'geometry' => $feature['geometry'],
                    'coordinates' => [
                        'longitude' => $feature['geometry']['coordinates'][0],
                        'latitude' => $feature['geometry']['coordinates'][1]
                    ]
                ];
            }

            return ['suggestions' => $suggestions];
        } catch (\Exception $e) {
            Log::error('Mapbox search error', ['error' => $e->getMessage()]);
            return ['suggestions' => []];
        }
    }

    /**
     * Calculate distance between two points
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c; // Distance in kilometers
    }

    /**
     * Check if coordinates are within Sta. Cruz bounds
     */
    public function isWithinStaCruz($longitude, $latitude)
    {
        $bounds = config('mapbox.stacruz_bounds');

        return $longitude >= $bounds['southwest'][0] &&
               $longitude <= $bounds['northeast'][0] &&
               $latitude >= $bounds['southwest'][1] &&
               $latitude <= $bounds['northeast'][1];
    }

    /**
     * Filter results to only include Sta. Cruz locations
     */
    private function filterStaCruzResults($features)
    {
        $filtered = [];

        foreach ($features as $feature) {
            $placeName = strtolower($feature['place_name'] ?? '');
            $coordinates = $feature['geometry']['coordinates'] ?? null;

            // Must be specifically in Sta. Cruz or Santa Cruz (not just any Davao del Sur location)
            $isStaCruz = (strpos($placeName, 'sta. cruz') !== false ||
                         strpos($placeName, 'sta cruz') !== false ||
                         strpos($placeName, 'santa cruz') !== false);

            // Check if coordinates are within Sta. Cruz bounds
            $withinBounds = false;
            if ($coordinates && count($coordinates) >= 2) {
                $withinBounds = $this->isWithinStaCruz($coordinates[0], $coordinates[1]);
            }

            // Include only if it's clearly in Sta. Cruz (by name) AND/OR within our geographic bounds
            // Prioritize name match to ensure it's Sta. Cruz municipality specifically
            if ($isStaCruz && $withinBounds) {
                // Best case: name matches AND coordinates are within bounds
                $filtered[] = $feature;
            } elseif ($isStaCruz && strpos($placeName, 'davao del sur') !== false) {
                // Name matches and is in Davao del Sur (trust the name even if coords slightly off)
                $filtered[] = $feature;
            } elseif ($withinBounds && strpos($placeName, 'davao del sur') !== false) {
                // Coordinates match and is in Davao del Sur (could be Sta. Cruz area)
                $filtered[] = $feature;
            }
        }

        return $filtered;
    }

    /**
     * Extract a clean location name from the full place name
     */
    private function extractLocationName($placeName)
    {
        // Split by comma and get the first part (usually the most specific location)
        $parts = explode(',', $placeName);
        $name = trim($parts[0]);
        
        // If it's just a number or very short, try to get a better name
        if (strlen($name) < 3 || is_numeric($name)) {
            $name = isset($parts[1]) ? trim($parts[1]) : $name;
        }
        
        return $name;
    }

    /**
     * Get predefined Sta. Cruz barangays as fallback
     * All 18 barangays of Santa Cruz, Davao del Sur
     * Municipal center: 6.8340, 125.4154
     */
    public function getStaCruzBarangays()
    {
        return [
            ['name' => 'Astorga', 'lat' => 6.8210, 'lng' => 125.3980],
            ['name' => 'Bato', 'lat' => 6.8450, 'lng' => 125.4050],
            ['name' => 'Coronon', 'lat' => 6.8520, 'lng' => 125.4200],
            ['name' => 'Darong', 'lat' => 6.9362, 'lng' => 125.4715],
            ['name' => 'Inawayan', 'lat' => 6.8280, 'lng' => 125.4350],
            ['name' => 'Jose Rizal', 'lat' => 6.8150, 'lng' => 125.4100],
            ['name' => 'Matutungan', 'lat' => 6.8600, 'lng' => 125.4450],
            ['name' => 'Melilia', 'lat' => 6.8380, 'lng' => 125.3900],
            ['name' => 'Saliducon', 'lat' => 6.8700, 'lng' => 125.4300],
            ['name' => 'Sibulan', 'lat' => 6.8900, 'lng' => 125.4500],
            ['name' => 'Sinoron', 'lat' => 6.8100, 'lng' => 125.4250],
            ['name' => 'Tagabuli', 'lat' => 6.8550, 'lng' => 125.3850],
            ['name' => 'Tibolo', 'lat' => 6.8650, 'lng' => 125.4600],
            ['name' => 'Tuban', 'lat' => 6.8750, 'lng' => 125.4100],
            ['name' => 'Zone I (Poblacion)', 'lat' => 6.8340, 'lng' => 125.4154],
            ['name' => 'Zone II (Poblacion)', 'lat' => 6.8350, 'lng' => 125.4160],
            ['name' => 'Zone III (Poblacion)', 'lat' => 6.8330, 'lng' => 125.4145],
            ['name' => 'Zone IV (Poblacion)', 'lat' => 6.8345, 'lng' => 125.4170]
        ];
    }
}