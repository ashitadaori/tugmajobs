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
     * Get all areas in Digos City
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
     * Search locations in Digos City
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

            $locations = $this->locationService->searchLocations($request->query);

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
     * Get nearest area in Digos City based on coordinates
     */
    public function getNearestArea(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'lat' => 'required|numeric|between:6.75,6.79',
                'lng' => 'required|numeric|between:125.35,125.39'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Coordinates must be within Digos City boundaries'
                ], 422);
            }

            $location = $this->locationService->getNearestArea(
                (float) $request->lat,
                (float) $request->lng
            );

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found within Digos City'
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
} 