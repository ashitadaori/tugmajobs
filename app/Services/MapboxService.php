<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MapboxService
{
    private $publicToken;
    private $secretToken;
    private $digosBounds;

    public function __construct()
    {
        $this->publicToken = config('mapbox.public_token');
        $this->secretToken = config('mapbox.secret_token');
        $this->digosBounds = config('mapbox.digos_bounds.bbox');
    }

    /**
     * Geocode an address to coordinates
     */
    public function geocodeAddress($address)
    {
        try {
            // Add Digos City context to the search
            $searchQuery = $address . ', Digos City, Davao del Sur, Philippines';
            
            $response = Http::get(config('mapbox.geocoding_url') . '/' . urlencode($searchQuery) . '.json', [
                'access_token' => $this->publicToken,
                'bbox' => $this->digosBounds,
                'country' => 'PH',
                'types' => 'address,poi,locality',
                'limit' => 5
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Filter results to only include Digos City locations
                if (isset($data['features'])) {
                    $data['features'] = $this->filterDigosCityResults($data['features']);
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
                'types' => 'address,poi'
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Mapbox reverse geocoding error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Search for places within Digos City
     */
    public function searchPlaces($query)
    {
        try {
            // Add Digos City context to the search query
            $searchQuery = $query . ' Digos City Davao del Sur';
            
            $response = Http::get(config('mapbox.geocoding_url') . '/' . urlencode($searchQuery) . '.json', [
                'access_token' => $this->publicToken,
                'bbox' => $this->digosBounds,
                'country' => 'PH',
                'types' => 'address,poi,locality',
                'limit' => 10
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Filter results to only include Digos City locations
                if (isset($data['features'])) {
                    $data['features'] = $this->filterDigosCityResults($data['features']);
                    
                    // Convert to suggestions format for consistency
                    $suggestions = [];
                    foreach ($data['features'] as $feature) {
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
                }
                
                return $data;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Mapbox search error', ['error' => $e->getMessage()]);
            return null;
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
     * Check if coordinates are within Digos City bounds
     */
    public function isWithinDigosCity($longitude, $latitude)
    {
        $bounds = config('mapbox.digos_bounds');
        
        return $longitude >= $bounds['southwest'][0] && 
               $longitude <= $bounds['northeast'][0] && 
               $latitude >= $bounds['southwest'][1] && 
               $latitude <= $bounds['northeast'][1];
    }

    /**
     * Filter results to only include Digos City locations
     */
    private function filterDigosCityResults($features)
    {
        $filtered = [];
        
        foreach ($features as $feature) {
            $placeName = strtolower($feature['place_name'] ?? '');
            $coordinates = $feature['geometry']['coordinates'] ?? null;
            
            // Check if the place name contains Digos City or Davao del Sur
            $isDigosCity = strpos($placeName, 'digos') !== false || 
                          strpos($placeName, 'davao del sur') !== false;
            
            // Check if coordinates are within bounds
            $withinBounds = false;
            if ($coordinates && count($coordinates) >= 2) {
                $withinBounds = $this->isWithinDigosCity($coordinates[0], $coordinates[1]);
            }
            
            // Include only if it's clearly in Digos City or within our bounds
            if ($isDigosCity || $withinBounds) {
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
     * Get predefined Digos City barangays as fallback
     */
    public function getDigosBarangays()
    {
        return [
            ['name' => 'Aplaya', 'lat' => 6.7489, 'lng' => 125.3714],
            ['name' => 'Binaton', 'lat' => 6.7623, 'lng' => 125.3897],
            ['name' => 'Cogon', 'lat' => 6.7512, 'lng' => 125.3567],
            ['name' => 'Colorado', 'lat' => 6.7534, 'lng' => 125.3678],
            ['name' => 'Dawis', 'lat' => 6.7567, 'lng' => 125.3645],
            ['name' => 'Dulangan', 'lat' => 6.7589, 'lng' => 125.3723],
            ['name' => 'Goma', 'lat' => 6.7612, 'lng' => 125.3834],
            ['name' => 'Igpit', 'lat' => 6.7645, 'lng' => 125.3756],
            ['name' => 'Mahayag', 'lat' => 6.7678, 'lng' => 125.3867],
            ['name' => 'Matti', 'lat' => 6.7523, 'lng' => 125.3589],
            ['name' => 'Poblacion', 'lat' => 6.7545, 'lng' => 125.3578],
            ['name' => 'San Jose', 'lat' => 6.7556, 'lng' => 125.3634],
            ['name' => 'San Miguel', 'lat' => 6.7578, 'lng' => 125.3712],
            ['name' => 'Sinawilan', 'lat' => 6.7634, 'lng' => 125.3845],
            ['name' => 'Soong', 'lat' => 6.7667, 'lng' => 125.3789],
            ['name' => 'Tres De Mayo', 'lat' => 6.7689, 'lng' => 125.3856]
        ];
    }
}