<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Get all areas in Sta. Cruz, Davao del Sur
     */
    public function getAllAreas()
    {
        try {
            $areas = $this->locationService->getAllAreas();

            return response()->json([
                'success' => true,
                'data' => $areas
            ])->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        } catch (\Exception $e) {
            \Log::error('Error in getAllAreas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch locations'
            ], 500);
        }
    }

    /**
     * Search locations in Sta. Cruz, Davao del Sur
     */
    public function search(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:2|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $locations = $this->locationService->searchLocations($request->input('query'));

            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in search: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search locations'
            ], 500);
        }
    }

    /**
     * Get nearest area in Sta. Cruz, Davao del Sur based on coordinates
     */
    public function getNearestArea(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'lat' => 'required|numeric|between:6.70,6.85',
                'lng' => 'required|numeric|between:125.35,125.50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Coordinates must be within Sta. Cruz, Davao del Sur boundaries'
                ], 422);
            }

            $location = $this->locationService->getNearestArea(
                (float) $request->lat,
                (float) $request->lng
            );

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found within Sta. Cruz, Davao del Sur'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $location
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getNearestArea: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get nearest area'
            ], 500);
        }
    }

    /**
     * Get Mapbox configuration
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

    /**
     * Reverse geocode coordinates using Mapbox API
     */
    public function reverseGeocode(Request $request)
    {
        try {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;

            if (!$lat || !$lng) {
                return response()->json(['features' => []]);
            }

            // Use Mapbox Geocoding API for reverse geocoding
            $mapboxToken = config('mapbox.public_token');

            if ($mapboxToken) {
                $response = \Illuminate\Support\Facades\Http::get(
                    "https://api.mapbox.com/geocoding/v5/mapbox.places/{$lng},{$lat}.json",
                    [
                        'access_token' => $mapboxToken,
                        'types' => 'address,poi,locality,place,neighborhood',
                        'limit' => 1
                    ]
                );

                if ($response->successful()) {
                    $data = $response->json();

                    if (!empty($data['features'])) {
                        $feature = $data['features'][0];
                        $context = $feature['context'] ?? [];

                        // Extract address components from context
                        $city = '';
                        $region = '';
                        $country = '';

                        foreach ($context as $item) {
                            if (str_starts_with($item['id'], 'place')) {
                                $city = $item['text'];
                            } elseif (str_starts_with($item['id'], 'region')) {
                                $region = $item['text'];
                            } elseif (str_starts_with($item['id'], 'country')) {
                                $country = $item['text'];
                            }
                        }

                        return response()->json([
                            'features' => [
                                [
                                    'place_name' => $feature['place_name'] ?? '',
                                    'text' => $feature['text'] ?? '',
                                    'context' => [
                                        ['id' => 'place', 'text' => $city],
                                        ['id' => 'region', 'text' => $region],
                                        ['id' => 'country', 'text' => $country]
                                    ]
                                ]
                            ]
                        ]);
                    }
                }
            }

            // Fallback to nearest pre-defined area
            $nearest = $this->locationService->getNearestArea($lat, $lng);

            if ($nearest) {
                return response()->json([
                    'features' => [
                        [
                            'place_name' => $nearest['full_address'],
                            'context' => [
                                ['id' => 'place', 'text' => $nearest['name']],
                                ['id' => 'region', 'text' => 'Davao del Sur'],
                                ['id' => 'country', 'text' => 'Philippines']
                            ]
                        ]
                    ]
                ]);
            }

            return response()->json(['features' => []]);

        } catch (\Exception $e) {
            \Log::error('Reverse geocode error: ' . $e->getMessage());
            return response()->json(['features' => []]);
        }
    }
}