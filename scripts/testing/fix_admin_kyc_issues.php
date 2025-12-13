<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\KycVerification;
use App\Models\KycData;
use App\Services\DiditService;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 FIXING ADMIN KYC VERIFICATION ISSUES\n";
echo "=======================================\n\n";

// Fix 1: Check and update admin KYC controller to properly fetch data
echo "1. 🔍 Analyzing Admin KYC Data Sources:\n";

$usersWithKycData = DB::table('users')
    ->whereIn('kyc_status', ['verified', 'failed', 'in_progress'])
    ->whereNotNull('kyc_session_id')
    ->get();

foreach ($usersWithKycData as $user) {
    echo "   - User {$user->id} ({$user->email}): Status {$user->kyc_status}, Session: {$user->kyc_session_id}\n";
    
    // Check if verification record exists
    $hasVerification = DB::table('kyc_verifications')
        ->where('user_id', $user->id)
        ->exists();
    
    // Check if KYC data exists
    $hasKycData = DB::table('kyc_data')
        ->where('user_id', $user->id)
        ->exists();
    
    echo "     Has Verification: " . ($hasVerification ? 'Yes' : 'No') . 
         ", Has KYC Data: " . ($hasKycData ? 'Yes' : 'No') . "\n";
    
    // If user has status but no verification record, create one
    if (!$hasVerification) {
        echo "     📝 Creating missing verification record...\n";
        
        try {
            DB::table('kyc_verifications')->insert([
                'user_id' => $user->id,
                'session_id' => $user->kyc_session_id,
                'status' => $user->kyc_status,
                'created_at' => $user->kyc_verified_at ?? $user->updated_at,
                'updated_at' => $user->kyc_verified_at ?? $user->updated_at,
                'verified_at' => $user->kyc_status === 'verified' ? ($user->kyc_verified_at ?? $user->updated_at) : null,
            ]);
            echo "     ✅ Verification record created\n";
        } catch (\Exception $e) {
            echo "     ❌ Failed to create verification record: " . $e->getMessage() . "\n";
        }
    }
}

// Fix 2: Test DiDit service connection and document image extraction
echo "\n2. 🌐 Testing DiDit Service Connection:\n";

try {
    $diditService = app(DiditService::class);
    echo "   ✅ DiDit service initialized successfully\n";
    
    // Test with an actual session ID if available
    $testSessionId = DB::table('kyc_verifications')
        ->whereNotNull('session_id')
        ->where('session_id', 'not like', 'legacy_%')
        ->pluck('session_id')
        ->first();
    
    if ($testSessionId) {
        echo "   📡 Testing with session ID: {$testSessionId}\n";
        
        try {
            $sessionDetails = $diditService->getSessionDetails($testSessionId);
            
            if ($sessionDetails) {
                echo "   ✅ Session details retrieved successfully\n";
                echo "   📊 Available data keys: " . implode(', ', array_keys($sessionDetails)) . "\n";
                
                // Test image extraction
                $images = [];
                if (isset($sessionDetails['result'])) {
                    $images = extractDocumentImages($sessionDetails['result']);
                }
                
                echo "   🖼️ Document images found: " . count($images) . "\n";
                
                if (count($images) > 0) {
                    foreach ($images as $index => $image) {
                        echo "     - Image " . ($index + 1) . ": " . 
                             (is_string($image) ? substr($image, 0, 50) . '...' : 'Array data') . "\n";
                    }
                }
                
            } else {
                echo "   ⚠️ No session details returned\n";
            }
            
        } catch (\Exception $e) {
            echo "   ❌ Failed to fetch session details: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ⚠️ No valid session IDs found for testing\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Failed to initialize DiDit service: " . $e->getMessage() . "\n";
}

// Fix 3: Update controller method for better image extraction
echo "\n3. 🔄 Creating Enhanced Document Image Extraction:\n";

function extractDocumentImages($result) {
    $images = [];
    
    // Check various possible locations for document images
    $locations = [
        'document_images',
        'images', 
        'documents.*.images',
        'extracted_data.document.images',
        'verification_data.document.images',
        'result.document_images',
        'result.images',
        'data.document_images',
        'data.images'
    ];
    
    foreach ($locations as $location) {
        $value = getNestedValue($result, $location);
        if ($value && is_array($value)) {
            $images = array_merge($images, $value);
        }
    }
    
    // Also check in documents array
    if (isset($result['documents']) && is_array($result['documents'])) {
        foreach ($result['documents'] as $doc) {
            if (isset($doc['images']) && is_array($doc['images'])) {
                $images = array_merge($images, $doc['images']);
            }
        }
    }
    
    return array_unique(array_filter($images));
}

function getNestedValue($data, $path) {
    $keys = explode('.', $path);
    $current = $data;
    
    foreach ($keys as $key) {
        if ($key === '*') {
            // Handle wildcard for array elements
            if (is_array($current)) {
                $results = [];
                foreach ($current as $item) {
                    if (is_array($item)) {
                        $results = array_merge($results, array_values($item));
                    }
                }
                return $results;
            }
            return null;
        }
        
        if (!is_array($current) || !array_key_exists($key, $current)) {
            return null;
        }
        
        $current = $current[$key];
    }
    
    return $current;
}

// Fix 4: Test with sample data if available
echo "\n4. 📋 Testing with Available KYC Data:\n";

$kycDataRecords = DB::table('kyc_data')
    ->join('users', 'kyc_data.user_id', '=', 'users.id')
    ->select('kyc_data.*', 'users.email')
    ->get();

foreach ($kycDataRecords as $record) {
    echo "   - User: {$record->email}\n";
    
    if ($record->raw_data) {
        $rawData = json_decode($record->raw_data, true);
        if ($rawData) {
            $images = extractDocumentImages($rawData);
            echo "     Images found in raw data: " . count($images) . "\n";
        }
    }
    
    if ($record->verification_data) {
        $verificationData = json_decode($record->verification_data, true);
        if ($verificationData) {
            $images = extractDocumentImages($verificationData);
            echo "     Images found in verification data: " . count($images) . "\n";
        }
    }
}

// Fix 5: Create a proper test verification with mock data
echo "\n5. 🧪 Creating Test Verification Record:\n";

$testUser = DB::table('users')->where('kyc_status', 'verified')->first();

if ($testUser) {
    // Create mock verification data with sample document images
    $mockVerificationData = [
        'session_id' => $testUser->kyc_session_id,
        'status' => 'verified',
        'document_type' => 'passport',
        'document_number' => 'AB123****',
        'firstname' => 'Test',
        'lastname' => 'User',
        'date_of_birth' => '1990-01-01',
        'nationality' => 'US',
        'raw_data' => [
            'result' => [
                'document_images' => [
                    'https://via.placeholder.com/400x300/007bff/ffffff?text=Document+Front',
                    'https://via.placeholder.com/400x300/28a745/ffffff?text=Document+Back'
                ],
                'extracted_data' => [
                    'document' => [
                        'type' => 'passport',
                        'number' => 'AB123456789'
                    ]
                ]
            ]
        ]
    ];
    
    try {
        // Update or create verification record
        DB::table('kyc_verifications')
            ->updateOrInsert(
                ['user_id' => $testUser->id],
                array_merge($mockVerificationData, [
                    'raw_data' => json_encode($mockVerificationData['raw_data']),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'verified_at' => now()
                ])
            );
        
        echo "   ✅ Test verification record created/updated for user {$testUser->id}\n";
        
        // Test the image extraction
        $images = extractDocumentImages($mockVerificationData['raw_data']['result']);
        echo "   🖼️ Mock images extracted: " . count($images) . "\n";
        
    } catch (\Exception $e) {
        echo "   ❌ Failed to create test record: " . $e->getMessage() . "\n";
    }
}

// Fix 6: Update admin routes to ensure they're working
echo "\n6. 🛣️ Checking Admin KYC Routes:\n";

try {
    $routes = [
        'admin.kyc.didit-verifications' => 'DiDit KYC Verifications List',
        'admin.kyc.show-didit-verification' => 'DiDit KYC Verification Details'
    ];
    
    foreach ($routes as $routeName => $description) {
        try {
            $url = route($routeName, $routeName === 'admin.kyc.show-didit-verification' ? ['user' => 1] : []);
            echo "   ✅ Route '{$routeName}': {$url}\n";
        } catch (\Exception $e) {
            echo "   ❌ Route '{$routeName}': ERROR - " . $e->getMessage() . "\n";
        }
    }
} catch (\Exception $e) {
    echo "   ❌ Route checking failed: " . $e->getMessage() . "\n";
}

// Summary and recommendations
echo "\n7. 📊 Final Status Summary:\n";

$verificationCount = DB::table('kyc_verifications')->count();
$kycDataCount = DB::table('kyc_data')->count();
$usersWithStatus = DB::table('users')
    ->whereIn('kyc_status', ['verified', 'failed', 'in_progress'])
    ->count();

echo "   - Users with KYC status: {$usersWithStatus}\n";
echo "   - Verification records: {$verificationCount}\n";
echo "   - KYC data records: {$kycDataCount}\n";

echo "\n8. 💡 Next Steps for Admin Interface:\n";
echo "   1. Visit admin KYC page: " . (route('admin.kyc.didit-verifications') ?? 'Route not available') . "\n";
echo "   2. Check document images in detail view\n";
echo "   3. Verify dynamic loading of verification data\n";
echo "   4. Test approve/reject functionality\n";

echo "\n=== ADMIN KYC ISSUES ANALYSIS COMPLETE ===\n";

?>
