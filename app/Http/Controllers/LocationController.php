<?php

namespace App\Http\Controllers;

use App\Services\MapboxService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    protected $mapboxService;

    public function __construct(MapboxService $mapboxService)
    {
        $this->mapboxService = $mapboxService;
    }

    /**
     * Search for places in Sta. Cruz, Davao del Sur
     */
    public function searchPlaces(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return response()->json(['error' => 'Query parameter is required'], 400);
        }

        // Get Mapbox results
        $mapboxResults = $this->mapboxService->searchPlaces($query);
        $suggestions = [];
        
        // Add Mapbox suggestions if available
        if ($mapboxResults && isset($mapboxResults['suggestions'])) {
            $suggestions = $mapboxResults['suggestions'];
        }
        
        // Add local barangay suggestions as fallback
        $localBarangays = $this->getMatchingBarangays($query);
        foreach ($localBarangays as $barangay) {
            $suggestions[] = [
                'name' => $barangay['name'],
                'place_name' => $barangay['name'] . ', Sta. Cruz, Davao del Sur, Philippines',
                'full_address' => $barangay['name'] . ', Sta. Cruz, Davao del Sur',
                'geometry' => [
                    'coordinates' => [$barangay['lng'], $barangay['lat']]
                ],
                'coordinates' => [
                    'longitude' => $barangay['lng'],
                    'latitude' => $barangay['lat']
                ]
            ];
        }
        
        // Remove duplicates and limit results
        $suggestions = $this->removeDuplicateSuggestions($suggestions);
        $suggestions = array_slice($suggestions, 0, 8);
        
        return response()->json(['suggestions' => $suggestions]);
    }

    /**
     * Get matching barangays based on query
     */
    private function getMatchingBarangays($query)
    {
        $barangays = $this->mapboxService->getStaCruzBarangays();
        $query = strtolower($query);

        return array_filter($barangays, function($barangay) use ($query) {
            return strpos(strtolower($barangay['name']), $query) !== false;
        });
    }

    /**
     * Remove duplicate suggestions based on name similarity
     */
    private function removeDuplicateSuggestions($suggestions)
    {
        $unique = [];
        $names = [];
        
        foreach ($suggestions as $suggestion) {
            $name = strtolower($suggestion['name']);
            if (!in_array($name, $names)) {
                $names[] = $name;
                $unique[] = $suggestion;
            }
        }
        
        return $unique;
    }

    /**
     * Geocode an address
     */
    public function geocode(Request $request)
    {
        $address = $request->get('address');
        
        if (empty($address)) {
            return response()->json(['error' => 'Address parameter is required'], 400);
        }

        $results = $this->mapboxService->geocodeAddress($address);
        
        if ($results) {
            return response()->json($results);
        }

        return response()->json(['error' => 'Geocoding failed'], 500);
    }

    /**
     * Reverse geocode coordinates using Mapbox API
     */
    public function reverseGeocode(Request $request)
    {
        $lat = (float) $request->get('lat');
        $lng = (float) $request->get('lng');

        \Log::info('Reverse geocode request', ['lat' => $lat, 'lng' => $lng]);

        if (!$lat || !$lng) {
            return response()->json(['features' => []]);
        }

        // Try MapboxService first
        $results = $this->mapboxService->reverseGeocode($lng, $lat);

        if ($results && !empty($results['features'])) {
            \Log::info('MapboxService returned results', ['count' => count($results['features'])]);
            return response()->json($results);
        }

        \Log::warning('MapboxService returned no results, trying direct API call');

        // Fallback: Direct Mapbox API call
        try {
            $mapboxToken = config('mapbox.public_token');

            if ($mapboxToken) {
                $url = "https://api.mapbox.com/geocoding/v5/mapbox.places/{$lng},{$lat}.json";
                \Log::info('Calling Mapbox API directly', ['url' => $url]);

                $response = \Illuminate\Support\Facades\Http::get($url, [
                    'access_token' => $mapboxToken,
                    'types' => 'address,poi,locality,place,neighborhood',
                    'limit' => 1
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    \Log::info('Direct API call successful', [
                        'features_count' => count($data['features'] ?? []),
                        'first_place_name' => $data['features'][0]['place_name'] ?? 'none'
                    ]);
                    return response()->json($data);
                } else {
                    \Log::error('Direct API call failed', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                }
            } else {
                \Log::error('Mapbox token is not configured');
            }
        } catch (\Exception $e) {
            \Log::error('Reverse geocode fallback error: ' . $e->getMessage());
        }

        // Return empty features instead of error
        return response()->json(['features' => []]);
    }

    /**
     * Get Mapbox configuration for frontend
     */
    public function getConfig()
    {
        return response()->json([
            'public_token' => config('mapbox.public_token'),
            'default_center' => config('mapbox.default_center'),
            'default_zoom' => config('mapbox.default_zoom'),
            'stacruz_bounds' => config('mapbox.stacruz_bounds')
        ]);
    }
}