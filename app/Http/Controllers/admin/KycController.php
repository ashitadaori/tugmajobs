<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use App\Models\KycVerification;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\DiditService;

class KycController extends Controller
{
    /**
     * Display list of manual KYC documents for review
     */
    public function manualDocuments(Request $request)
    {
        $query = KycDocument::with('user')->latest();

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by user type (employer/jobseeker)
        if ($request->has('user_type') && $request->user_type !== '') {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('role', $request->user_type);
            });
        }

        // Search by user name or email
        if ($request->has('search') && $request->search !== '') {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('email', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Filter by document type
        if ($request->has('document_type') && $request->document_type !== '') {
            $query->where('document_type', $request->document_type);
        }

        $documents = $query->paginate(20)->appends($request->query());

        // Get distinct document types for filter dropdown
        $documentTypes = KycDocument::distinct()->pluck('document_type')->filter()->sort()->values();

        // Count pending documents for badge
        $pendingCount = KycDocument::where('status', 'pending')->count();

        return view('admin.kyc.manual-documents', compact('documents', 'documentTypes', 'pendingCount'));
    }

    /**
     * Show detailed view of a manual KYC document
     */
    public function showManualDocument(KycDocument $document)
    {
        $document->load('user');

        // Parse document files
        $files = [];
        if ($document->document_file) {
            $decoded = json_decode($document->document_file, true);
            if (is_array($decoded)) {
                $files = $decoded;
            } else {
                // Legacy single file format
                $files['document'] = $document->document_file;
            }
        }

        return view('admin.kyc.show-manual-document', compact('document', 'files'));
    }

    /**
     * Verify/Approve a manual KYC document
     */
    public function verifyManualDocument(KycDocument $document)
    {
        try {
            // Load user relationship
            $document->load('user');

            // Update document status
            $document->update([
                'status' => 'verified'
            ]);

            // Update user's main KYC status (this is where KYC verification is tracked)
            $document->user->update([
                'kyc_status' => 'verified',
                'kyc_verified_at' => now(),
            ]);

            // Notify the user that they have been verified
            try {
                Notification::create([
                    'user_id' => $document->user_id,
                    'title' => 'Congratulations! You are now verified',
                    'message' => 'Your identity has been successfully verified. You now have full access to all features on TugmaJobs. Start exploring job opportunities today!',
                    'type' => 'kyc_verified',
                    'data' => [
                        'document_id' => $document->id,
                        'verified_at' => now()->toDateTimeString(),
                    ],
                    'action_url' => $document->user->role === 'employer'
                        ? route('employer.dashboard')
                        : route('account.dashboard'),
                ]);
            } catch (\Exception $notifError) {
                Log::warning('Failed to create KYC verification notification', ['error' => $notifError->getMessage()]);
            }

            Log::info('Manual KYC document verified', [
                'document_id' => $document->id,
                'user_id' => $document->user_id,
                'admin_id' => auth()->id(),
            ]);

            return redirect()->route('admin.kyc.manual-documents')
                ->with('success', 'Document verified successfully. User has been notified.');

        } catch (\Exception $e) {
            Log::error('Failed to verify manual KYC document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to verify document: ' . $e->getMessage());
        }
    }

    /**
     * Reject a manual KYC document
     */
    public function rejectManualDocument(Request $request, KycDocument $document)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        try {
            // Load user relationship
            $document->load('user');

            $document->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason
            ]);

            // Update user's KYC status to failed
            $document->user->update([
                'kyc_status' => 'failed',
            ]);

            // Notify the user that their document was rejected
            try {
                Notification::create([
                    'user_id' => $document->user_id,
                    'title' => 'Verification Document Needs Revision',
                    'message' => "Your identity verification document requires some changes. Reason: {$request->rejection_reason}. Please submit a new document to complete verification.",
                    'type' => 'kyc_rejected',
                    'data' => [
                        'document_id' => $document->id,
                        'rejection_reason' => $request->rejection_reason,
                    ],
                    'action_url' => route('kyc.manual.upload'),
                ]);
            } catch (\Exception $notifError) {
                Log::warning('Failed to create KYC rejection notification', ['error' => $notifError->getMessage()]);
            }

            Log::info('Manual KYC document rejected', [
                'document_id' => $document->id,
                'user_id' => $document->user_id,
                'admin_id' => auth()->id(),
                'reason' => $request->rejection_reason,
            ]);

            return redirect()->route('admin.kyc.manual-documents')
                ->with('success', 'Document rejected successfully. User has been notified.');

        } catch (\Exception $e) {
            Log::error('Failed to reject manual KYC document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to reject document: ' . $e->getMessage());
        }
    }

    /**
     * Download a manual KYC document file
     */
    public function downloadManualDocument(KycDocument $document)
    {
        $files = json_decode($document->document_file, true);

        if (is_array($files)) {
            // For JSON format, download all files as zip or first file
            $firstFile = $files['front'] ?? $files['back'] ?? $files['selfie'] ?? reset($files);
            if ($firstFile && Storage::disk('private')->exists($firstFile)) {
                return Storage::disk('private')->download($firstFile);
            }
        } else {
            // Legacy single file format
            if ($document->document_file && Storage::disk('private')->exists($document->document_file)) {
                return Storage::disk('private')->download($document->document_file);
            }
        }

        return redirect()->back()->with('error', 'File not found.');
    }

    /**
     * View a specific manual KYC document image
     */
    public function viewManualDocumentImage(KycDocument $document, string $type)
    {
        $files = json_decode($document->document_file, true);

        if (!is_array($files) || !isset($files[$type])) {
            abort(404, 'Image not found');
        }

        $filePath = $files[$type];

        // Check if file is null (e.g., back image might be optional)
        if (!$filePath) {
            abort(404, 'Image not uploaded');
        }

        if (!Storage::disk('private')->exists($filePath)) {
            abort(404, 'File not found');
        }

        $mimeType = Storage::disk('private')->mimeType($filePath);

        return response(Storage::disk('private')->get($filePath), 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
        ]);
    }

    // Legacy methods for backward compatibility
    public function index(Request $request)
    {
        return $this->manualDocuments($request);
    }

    public function verify(KycDocument $document)
    {
        return $this->verifyManualDocument($document);
    }

    public function reject(Request $request, KycDocument $document)
    {
        return $this->rejectManualDocument($request, $document);
    }

    public function download(KycDocument $document)
    {
        return $this->downloadManualDocument($document);
    }

    /**
     * Show DiDit KYC verifications
     */
    public function diditVerifications(Request $request)
    {
        $query = User::with([
            'kycVerifications' => function($q) {
                $q->latest();
            },
            'kycData' => function($q) {
                $q->latest();
            }
        ])->where(function($q) {
            // Show users who have KYC verifications, KYC data, OR are verified/in_progress
            $q->whereHas('kycVerifications')
              ->orWhereHas('kycData')
              ->orWhereIn('kyc_status', ['verified', 'in_progress', 'failed', 'pending_review']);
        });
        
        // Filter by status if provided
        if ($request->has('status') && $request->status !== '') {
            $query->where(function($q) use ($request) {
                // Check status in both kyc_verifications and kyc_data tables
                $q->whereHas('kycVerifications', function($subQ) use ($request) {
                    $subQ->where('status', $request->status);
                })->orWhereHas('kycData', function($subQ) use ($request) {
                    $subQ->where('status', $request->status);
                })->orWhere('kyc_status', $request->status);
            });
        }
        
        // Filter by user name/email if provided
        if ($request->has('search') && $request->search !== '') {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('email', 'LIKE', '%' . $request->search . '%');
            });
        }
        
        $users = $query->paginate(20)->appends($request->query());
        
        return view('admin.kyc.didit-verifications', compact('users'));
    }

    /**
     * Show detailed DiDit KYC verification for a user
     */
    public function showDiditVerification(User $user, DiditService $diditService)
    {
        $verification = $user->kycVerifications()->latest()->first();
        $kycData = $user->kycData()->latest()->first();
        
        // Check if we have either verification data or kyc data
        if (!$verification && !$kycData) {
            return redirect()->route('admin.kyc.didit-verifications')
                ->with('error', 'No KYC verification found for this user.');
        }
        
        // Get session_id from either source
        $sessionId = ($verification ? $verification->session_id : null) ?? ($kycData ? $kycData->session_id : null);
        
        $documentImages = [];
        $sourceUsed = 'none';
        
        // Priority 1: Use KycData stored image URLs (most reliable)
        if ($kycData && ($kycData->front_image_url || $kycData->back_image_url || $kycData->portrait_image_url)) {
            if ($kycData->front_image_url) {
                $documentImages['front'] = $kycData->front_image_url;
            }
            if ($kycData->back_image_url) {
                $documentImages['back'] = $kycData->back_image_url;
            }
            if ($kycData->portrait_image_url) {
                // The portrait_image_url should now contain the actual live selfie (not cropped from ID)
                $documentImages['portrait'] = $kycData->portrait_image_url;
            }
            $sourceUsed = 'kyc_data_columns';
            Log::info('Using images from KycData columns', ['count' => count($documentImages)]);
        }
        
        // Priority 2: Extract from KycData raw_payload (webhook data)
        elseif ($kycData && $kycData->raw_payload) {
            $rawPayload = is_string($kycData->raw_payload) ? json_decode($kycData->raw_payload, true) : $kycData->raw_payload;
            if (isset($rawPayload['decision']['id_verification'])) {
                $idVerification = $rawPayload['decision']['id_verification'];
                if (isset($idVerification['front_image'])) {
                    $documentImages['front'] = $idVerification['front_image'];
                }
                if (isset($idVerification['back_image'])) {
                    $documentImages['back'] = $idVerification['back_image'];
                }
                if (isset($idVerification['portrait_image'])) {
                    $documentImages['portrait'] = $idVerification['portrait_image'];
                }
                $sourceUsed = 'kyc_data_payload';
                Log::info('Using images from KycData payload', ['count' => count($documentImages)]);
            }
        }
        
        // Priority 3: Extract from KycVerification raw_data
        elseif ($verification && $verification->raw_data) {
            $rawData = is_string($verification->raw_data) ? json_decode($verification->raw_data, true) : $verification->raw_data;
            if ($rawData) {
                $documentImages = $this->extractDocumentImages($rawData);
                $sourceUsed = 'verification_raw_data';
                Log::info('Using images from verification raw data', ['count' => count($documentImages)]);
            }
        }
        
        // Priority 4: Try DiDit API (least reliable due to session expiration)
        elseif ($sessionId) {
            try {
                $sessionDetails = $diditService->getSessionDetails($sessionId);
                if ($sessionDetails && isset($sessionDetails['result'])) {
                    $documentImages = $this->extractDocumentImages($sessionDetails['result']);
                    $sourceUsed = 'didit_api';
                    Log::info('Using images from DiDit API', ['count' => count($documentImages)]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch DiDit session details', [
                    'session_id' => $sessionId,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Priority 5: Fallback to mock images for development
        if (empty($documentImages) && config('app.env') === 'local') {
            $documentImages = [
                'front' => 'https://via.placeholder.com/400x250/007bff/ffffff?text=Front+ID+Image',
                'back' => 'https://via.placeholder.com/400x250/28a745/ffffff?text=Back+ID+Image',
                'portrait' => 'https://via.placeholder.com/300x400/fd7e14/ffffff?text=Portrait+Image'
            ];
            $sourceUsed = 'mock_images';
            Log::info('Using mock images for development');
        }
        
        // Determine the status to use
        $status = 'pending'; // Default
        if ($kycData && $kycData->status) {
            $status = $kycData->status;
        } elseif ($verification && $verification->status) {
            $status = $verification->status;
        } elseif ($user->kyc_status) {
            $status = $user->kyc_status;
        }
        
        Log::info('Final verification view data', [
            'user_id' => $user->id,
            'source_used' => $sourceUsed,
            'images_count' => count($documentImages),
            'image_types' => array_keys($documentImages),
            'status' => $status,
            'session_id' => $sessionId
        ]);
        
        return view('admin.kyc.show-didit-verification', [
            'user' => $user,
            'verification' => $verification,
            'kycData' => $kycData,
            'documentImages' => $documentImages,
            'sessionId' => $sessionId,
            'status' => $status
        ]);
    }

    /**
     * Extract categorized document images from DiDit session result
     * Returns properly categorized verification photos: document front/back and selfie
     */
    private function extractDocumentImages($result)
    {
        $categorizedImages = [
            'document_front' => [],
            'document_back' => [],
            'selfie' => [],
            'other' => []
        ];
        
        // Check for categorized images in DiDit response
        $this->extractCategorizedImages($result, $categorizedImages);
        
        // Flatten to single array with metadata for display
        $images = [];
        
        // Add document front images
        foreach ($categorizedImages['document_front'] as $image) {
            $images[] = [
                'url' => $image,
                'type' => 'Document Front',
                'category' => 'document',
                'description' => 'Front side of identity document'
            ];
        }
        
        // Add document back images
        foreach ($categorizedImages['document_back'] as $image) {
            $images[] = [
                'url' => $image,
                'type' => 'Document Back',
                'category' => 'document',
                'description' => 'Back side of identity document'
            ];
        }
        
        // Add selfie verification images
        foreach ($categorizedImages['selfie'] as $image) {
            $images[] = [
                'url' => $image,
                'type' => 'Selfie Verification',
                'category' => 'selfie',
                'description' => 'Live selfie taken during verification'
            ];
        }
        
        // Add any other verification images
        foreach ($categorizedImages['other'] as $image) {
            $images[] = [
                'url' => $image,
                'type' => 'Verification Image',
                'category' => 'other',
                'description' => 'Additional verification photo'
            ];
        }
        
        return $images;
    }
    
    /**
     * Extract and categorize images from DiDit response data
     */
    private function extractCategorizedImages($data, &$categorizedImages)
    {
        // Check various DiDit response structures
        $imageLocations = [
            // Main verification images
            ['path' => 'verification_images.document_front', 'type' => 'document_front'],
            ['path' => 'verification_images.document_back', 'type' => 'document_back'],
            ['path' => 'verification_images.selfie', 'type' => 'selfie'],
            ['path' => 'verification_images.face', 'type' => 'selfie'],
            
            // DiDit standard structure
            ['path' => 'images.document.front', 'type' => 'document_front'],
            ['path' => 'images.document.back', 'type' => 'document_back'],
            ['path' => 'images.face', 'type' => 'selfie'],
            ['path' => 'images.selfie', 'type' => 'selfie'],
            
            // Alternative structures
            ['path' => 'captured_images.document_front', 'type' => 'document_front'],
            ['path' => 'captured_images.document_back', 'type' => 'document_back'],
            ['path' => 'captured_images.selfie', 'type' => 'selfie'],
            
            // Result structure
            ['path' => 'result.images.document.front', 'type' => 'document_front'],
            ['path' => 'result.images.document.back', 'type' => 'document_back'],
            ['path' => 'result.images.face', 'type' => 'selfie'],
            
            // Document array structure
            ['path' => 'documents.0.images.front', 'type' => 'document_front'],
            ['path' => 'documents.0.images.back', 'type' => 'document_back'],
        ];
        
        foreach ($imageLocations as $location) {
            $images = $this->getNestedValue($data, $location['path']);
            if ($images) {
                if (is_array($images)) {
                    $categorizedImages[$location['type']] = array_merge(
                        $categorizedImages[$location['type']], 
                        array_filter($images)
                    );
                } elseif (is_string($images)) {
                    $categorizedImages[$location['type']][] = $images;
                }
            }
        }
        
        // Check for generic image arrays and try to categorize by filename/metadata
        $genericImagePaths = [
            'images',
            'document_images',
            'captured_images',
            'verification_data.images',
            'result.images'
        ];
        
        foreach ($genericImagePaths as $path) {
            $images = $this->getNestedValue($data, $path);
            if ($images && is_array($images)) {
                $this->categorizeGenericImages($images, $categorizedImages);
            }
        }
        
        // Remove duplicates from each category
        foreach ($categorizedImages as $type => $images) {
            $categorizedImages[$type] = array_unique($images);
        }
    }
    
    /**
     * Categorize generic image arrays by analyzing URLs and metadata
     */
    private function categorizeGenericImages($images, &$categorizedImages)
    {
        foreach ($images as $image) {
            $imageUrl = is_array($image) ? ($image['url'] ?? $image['src'] ?? '') : $image;
            $imageType = is_array($image) ? ($image['type'] ?? '') : '';
            
            if (empty($imageUrl)) continue;
            
            // Categorize based on URL keywords or metadata
            $url = strtolower($imageUrl);
            $type = strtolower($imageType);
            
            if (strpos($url, 'front') !== false || strpos($type, 'front') !== false || 
                strpos($url, 'document_front') !== false || strpos($type, 'document_front') !== false) {
                $categorizedImages['document_front'][] = $imageUrl;
            } elseif (strpos($url, 'back') !== false || strpos($type, 'back') !== false ||
                     strpos($url, 'document_back') !== false || strpos($type, 'document_back') !== false) {
                $categorizedImages['document_back'][] = $imageUrl;
            } elseif (strpos($url, 'selfie') !== false || strpos($type, 'selfie') !== false ||
                     strpos($url, 'face') !== false || strpos($type, 'face') !== false ||
                     strpos($url, 'portrait') !== false || strpos($type, 'portrait') !== false) {
                $categorizedImages['selfie'][] = $imageUrl;
            } else {
                // If we can't categorize, add to other
                $categorizedImages['other'][] = $imageUrl;
            }
        }
    }
    
    /**
     * Get nested array value by dot notation path
     */
    private function getNestedValue($array, $path)
    {
        $keys = explode('.', $path);
        $current = $array;
        
        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return null;
            }
            $current = $current[$key];
        }
        
        return $current;
    }

    /**
     * Approve DiDit KYC verification
     */
    public function approveDiditVerification(User $user)
    {
        $verification = $user->kycVerifications()->latest()->first();

        if (!$verification) {
            return redirect()->back()->with('error', 'No KYC verification found for this user.');
        }

        // Update verification status
        $verification->update([
            'status' => 'verified',
            'verified_at' => now()
        ]);

        // Update user KYC status
        $user->update([
            'kyc_status' => 'verified',
            'kyc_verified_at' => now()
        ]);

        // Notify the user that they have been verified
        try {
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Congratulations! You are now verified',
                'message' => 'Your identity has been successfully verified. You now have full access to all features on TugmaJobs. Start exploring opportunities today!',
                'type' => 'kyc_verified',
                'data' => [
                    'verified_at' => now()->toDateTimeString(),
                ],
                'action_url' => $user->role === 'employer'
                    ? route('employer.dashboard')
                    : route('account.dashboard'),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create DiDit KYC approval notification', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'KYC verification approved successfully. User has been notified.');
    }

    /**
     * Reject DiDit KYC verification
     */
    public function rejectDiditVerification(Request $request, User $user)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $verification = $user->kycVerifications()->latest()->first();

        if (!$verification) {
            return redirect()->back()->with('error', 'No KYC verification found for this user.');
        }

        // Update verification status
        $verification->update([
            'status' => 'failed'
        ]);

        // Update user KYC status
        $user->update([
            'kyc_status' => 'failed'
        ]);

        // Notify the user that their KYC was rejected
        try {
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Verification Needs Attention',
                'message' => "Your identity verification requires some changes. Reason: {$request->rejection_reason}. Please re-submit your documents to complete verification.",
                'type' => 'kyc_rejected',
                'data' => [
                    'rejection_reason' => $request->rejection_reason,
                ],
                'action_url' => route('kyc.start.form'),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create DiDit KYC rejection notification', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'KYC verification rejected successfully. User has been notified.');
    }
    
    /**
     * Reset KYC verification for a user (allows them to re-verify)
     */
    public function resetKyc(User $user)
    {
        try {
            Log::info('Admin resetting KYC for user', [
                'user_id' => $user->id,
                'admin_id' => auth()->id()
            ]);

            // Delete all KYC verifications for this user
            $user->kycVerifications()->delete();

            // Delete all KYC data for this user
            $user->kycData()->delete();

            // Reset user's KYC status to pending
            $user->update([
                'kyc_status' => 'pending',
                'kyc_verified_at' => null,
                'kyc_completed_at' => null
            ]);

            Log::info('KYC reset completed successfully', [
                'user_id' => $user->id
            ]);

            return redirect()->route('admin.kyc.didit-verifications')
                ->with('success', 'KYC verification has been reset for ' . $user->name . '. They can now re-verify their identity.');

        } catch (\Exception $e) {
            Log::error('Failed to reset KYC', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to reset KYC verification: ' . $e->getMessage());
        }
    }

    /**
     * Refresh verification data from DiDit API
     */
    public function refreshVerification(User $user, DiditService $diditService)
    {
        try {
            $verification = $user->kycVerifications()->latest()->first();
            $kycData = $user->kycData()->latest()->first();
            
            // Get session_id from either source
            $sessionId = ($verification ? $verification->session_id : null) ?? ($kycData ? $kycData->session_id : null);
            
            if (!$sessionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No session ID found for this user'
                ], 404);
            }
            
            Log::info('Refreshing verification data', [
                'user_id' => $user->id,
                'session_id' => $sessionId
            ]);
            
            // Fetch fresh data from DiDit API
            $sessionDetails = $diditService->getSessionDetails($sessionId);
            
            if (!$sessionDetails || !isset($sessionDetails['result'])) {
                throw new \Exception('No valid session data returned from DiDit API');
            }
            
            // Extract and categorize images from fresh data
            $documentImages = $this->extractDocumentImages($sessionDetails['result']);
            
            // Update verification record with fresh data if available
            if ($verification) {
                $verification->update([
                    'raw_data' => json_encode($sessionDetails['result']),
                    'verification_data' => json_encode($sessionDetails),
                    'updated_at' => now()
                ]);
                Log::info('Updated verification record with fresh data', [
                    'verification_id' => $verification->id
                ]);
            }
            
            // Update KycData with fresh image URLs if we have them
            if ($kycData && !empty($documentImages)) {
                $updateData = ['updated_at' => now()];
                
                // Extract image URLs by category
                foreach ($documentImages as $image) {
                    if ($image['category'] === 'document' && $image['type'] === 'Document Front') {
                        $updateData['front_image_url'] = $image['url'];
                    } elseif ($image['category'] === 'document' && $image['type'] === 'Document Back') {
                        $updateData['back_image_url'] = $image['url'];
                    } elseif ($image['category'] === 'selfie') {
                        $updateData['portrait_image_url'] = $image['url'];
                    }
                }
                
                if (count($updateData) > 1) { // More than just updated_at
                    $kycData->update($updateData);
                    Log::info('Updated KycData with fresh image URLs', [
                        'kyc_data_id' => $kycData->id,
                        'updated_fields' => array_keys($updateData)
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Verification data refreshed successfully',
                'images_count' => count($documentImages),
                'session_id' => $sessionId
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to refresh verification data', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh verification data: ' . $e->getMessage()
            ], 500);
        }
    }
}
