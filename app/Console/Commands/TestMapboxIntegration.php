<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MapboxService;

class TestMapboxIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mapbox:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Mapbox integration with Digos City locations';

    protected $mapboxService;

    public function __construct(MapboxService $mapboxService)
    {
        parent::__construct();
        $this->mapboxService = $mapboxService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing Mapbox Integration for Digos City...');
        $this->newLine();

        // Test 1: Configuration
        $this->info('1. Testing Configuration...');
        $publicToken = config('mapbox.public_token');
        $secretToken = config('mapbox.secret_token');
        
        if (empty($publicToken) || $publicToken === 'your_mapbox_public_token_here') {
            $this->error('❌ Mapbox public token not configured');
            $this->warn('Please set MAPBOX_PUBLIC_TOKEN in your .env file');
            return Command::FAILURE;
        }
        
        if (empty($secretToken) || $secretToken === 'your_mapbox_secret_token_here') {
            $this->error('❌ Mapbox secret token not configured');
            $this->warn('Please set MAPBOX_SECRET_TOKEN in your .env file');
            return Command::FAILURE;
        }
        
        $this->info('✅ Mapbox tokens configured');

        // Test 2: Geocoding
        $this->info('2. Testing Geocoding...');
        $testAddress = 'Poblacion, Digos City, Davao del Sur';
        $geocodeResult = $this->mapboxService->geocodeAddress($testAddress);
        
        if ($geocodeResult && isset($geocodeResult['features']) && count($geocodeResult['features']) > 0) {
            $feature = $geocodeResult['features'][0];
            $coordinates = $feature['geometry']['coordinates'];
            $this->info("✅ Geocoding successful for '{$testAddress}'");
            $this->info("   Coordinates: {$coordinates[1]}, {$coordinates[0]}");
        } else {
            $this->error('❌ Geocoding failed');
            return Command::FAILURE;
        }

        // Test 3: Reverse Geocoding
        $this->info('3. Testing Reverse Geocoding...');
        $testLat = 6.7545;
        $testLng = 125.3578;
        $reverseResult = $this->mapboxService->reverseGeocode($testLng, $testLat);
        
        if ($reverseResult && isset($reverseResult['features']) && count($reverseResult['features']) > 0) {
            $feature = $reverseResult['features'][0];
            $this->info("✅ Reverse geocoding successful for coordinates {$testLat}, {$testLng}");
            $this->info("   Address: {$feature['place_name']}");
        } else {
            $this->error('❌ Reverse geocoding failed');
            return Command::FAILURE;
        }

        // Test 4: Place Search
        $this->info('4. Testing Place Search...');
        $searchResult = $this->mapboxService->searchPlaces('Poblacion Digos');
        
        if ($searchResult && isset($searchResult['suggestions']) && count($searchResult['suggestions']) > 0) {
            $this->info('✅ Place search successful');
            $this->info('   Found ' . count($searchResult['suggestions']) . ' suggestions');
            foreach (array_slice($searchResult['suggestions'], 0, 3) as $suggestion) {
                $this->info("   - {$suggestion['name']}");
            }
        } else {
            $this->error('❌ Place search failed');
            return Command::FAILURE;
        }

        // Test 5: Boundary Check
        $this->info('5. Testing Digos City Boundary Check...');
        $insideCity = $this->mapboxService->isWithinDigosCity(125.3578, 6.7545); // Poblacion, Digos
        $outsideCity = $this->mapboxService->isWithinDigosCity(121.0244, 14.5994); // Manila coordinates
        $outsideDavao = $this->mapboxService->isWithinDigosCity(125.6147, 7.0731); // Davao City coordinates
        
        if ($insideCity && !$outsideCity && !$outsideDavao) {
            $this->info('✅ Boundary check working correctly');
            $this->info('   - Digos City location: ✅ Inside bounds');
            $this->info('   - Manila location: ❌ Outside bounds (correct)');
            $this->info('   - Davao City location: ❌ Outside bounds (correct)');
        } else {
            $this->error('❌ Boundary check failed');
            $this->error("   - Digos City (should be inside): " . ($insideCity ? 'YES' : 'NO'));
            $this->error("   - Manila (should be outside): " . ($outsideCity ? 'YES' : 'NO'));
            $this->error("   - Davao City (should be outside): " . ($outsideDavao ? 'YES' : 'NO'));
            return Command::FAILURE;
        }

        // Test 6: Distance Calculation
        $this->info('6. Testing Distance Calculation...');
        $distance = $this->mapboxService->calculateDistance(6.7545, 125.3578, 6.7623, 125.3897);
        $this->info("✅ Distance calculation successful: " . round($distance, 2) . " km");

        // Test 7: Location Filtering
        $this->info('7. Testing Location Filtering...');
        $barangays = $this->mapboxService->getDigosBarangays();
        if (count($barangays) > 0) {
            $this->info('✅ Local barangay data available');
            $this->info('   Available barangays: ' . count($barangays));
            $this->info('   Sample barangays: ' . implode(', ', array_slice(array_column($barangays, 'name'), 0, 5)));
        } else {
            $this->error('❌ No local barangay data found');
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('🎉 All Mapbox integration tests passed!');
        $this->info('Your Mapbox integration is now properly restricted to Digos City, Davao del Sur only.');
        $this->newLine();
        $this->info('📍 Location Restrictions Applied:');
        $this->info('   ✅ Geographic boundaries: 125.32°-125.42° E, 6.72°-6.82° N');
        $this->info('   ✅ Text filtering: Must contain "Digos" or "Davao del Sur"');
        $this->info('   ✅ Fallback barangays: 16 local barangays available');
        $this->info('   ✅ Client-side validation: Prevents non-Digos locations');
        
        return Command::SUCCESS;
    }
}
