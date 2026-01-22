<?php

namespace App\Http\Controllers;

use App\Models\KycDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ManualKycController extends Controller
{
    /**
     * Philippine ID types that are not supported by Didit
     * but can be manually verified
     */
    public const PHILIPPINE_ID_TYPES = [
        'philhealth' => 'PhilHealth ID',
        'umid' => 'UMID (Unified Multi-Purpose ID)',
        'sss' => 'SSS ID',
        'postal_id' => 'Postal ID',
        'voters_id' => "Voter's ID (COMELEC)",
        'prc_id' => 'PRC ID (Professional Regulation Commission)',
        'owwa_id' => 'OWWA ID',
        'ofw_id' => 'OFW ID',
        'senior_citizen_id' => 'Senior Citizen ID',
        'pwd_id' => 'PWD ID',
        'barangay_id' => 'Barangay ID/Certificate',
        'nbi_clearance' => 'NBI Clearance',
        'police_clearance' => 'Police Clearance',
        'tin_id' => 'TIN ID',
        'pagibig_id' => 'Pag-IBIG ID',
        'school_id' => 'School ID (with registration documents)',
        'company_id' => 'Company ID (with employment certificate)',
        'other' => 'Other Government-Issued ID',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the manual KYC upload form
     */
    public function showUploadForm()
    {
        $user = Auth::user();

        // Check if user already has a pending manual submission
        $pendingSubmission = KycDocument::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        // Check if user is already verified
        if ($user->kyc_status === 'verified') {
            return redirect()->route('kyc.start.form')
                ->with('info', 'Your identity is already verified.');
        }

        return view('kyc.manual-upload', [
            'idTypes' => self::PHILIPPINE_ID_TYPES,
            'pendingSubmission' => $pendingSubmission,
            'user' => $user,
        ]);
    }

    /**
     * Handle the manual document upload
     */
    public function upload(Request $request)
    {
        $user = Auth::user();

        // Validate the request
        $validated = $request->validate([
            'document_type' => ['required', 'string', Rule::in(array_keys(self::PHILIPPINE_ID_TYPES))],
            'document_number' => 'required|string|max:100',
            'document_front' => 'required|image|mimes:jpeg,jpg,png|max:5120', // 5MB max
            'document_back' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'selfie' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            'additional_notes' => 'nullable|string|max:500',
        ], [
            'document_front.required' => 'Please upload the front side of your ID.',
            'document_front.image' => 'The front ID must be an image file.',
            'document_front.max' => 'The front ID image must not exceed 5MB.',
            'selfie.required' => 'Please upload a selfie holding your ID.',
            'selfie.image' => 'The selfie must be an image file.',
        ]);

        try {
            Log::info('Manual KYC upload started', [
                'user_id' => $user->id,
                'document_type' => $validated['document_type'],
            ]);

            // Create storage directory if it doesn't exist
            $storagePath = 'kyc-documents/' . $user->id;

            // Store the front document
            $frontPath = $request->file('document_front')->store($storagePath, 'private');

            // Store the back document (optional)
            $backPath = null;
            if ($request->hasFile('document_back')) {
                $backPath = $request->file('document_back')->store($storagePath, 'private');
            }

            // Store the selfie
            $selfiePath = $request->file('selfie')->store($storagePath, 'private');

            // Create the KYC document record
            // Combine paths into a JSON structure for document_file
            $documentFiles = json_encode([
                'front' => $frontPath,
                'back' => $backPath,
                'selfie' => $selfiePath,
            ]);

            $kycDocument = KycDocument::create([
                'user_id' => $user->id,
                'document_type' => self::PHILIPPINE_ID_TYPES[$validated['document_type']] ?? $validated['document_type'],
                'document_number' => $validated['document_number'],
                'document_file' => $documentFiles,
                'status' => 'pending',
            ]);

            // Update user's KYC status to pending review
            $user->update([
                'kyc_status' => 'pending_review',
            ]);

            Log::info('Manual KYC document uploaded successfully', [
                'user_id' => $user->id,
                'kyc_document_id' => $kycDocument->id,
                'document_type' => $validated['document_type'],
            ]);

            // Create notification for admin (if notification system exists)
            try {
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'type' => 'kyc_submission',
                    'data' => json_encode([
                        'title' => 'New Manual KYC Submission',
                        'message' => "User {$user->name} has submitted a manual KYC verification request using {$kycDocument->document_type}.",
                        'document_id' => $kycDocument->id,
                    ]),
                ]);
            } catch (\Exception $e) {
                // Notification creation is optional
                Log::warning('Failed to create KYC submission notification', ['error' => $e->getMessage()]);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your documents have been submitted for review. You will be notified once the verification is complete.',
                    'document_id' => $kycDocument->id,
                ]);
            }

            return redirect()->route('kyc.manual.status')
                ->with('success', 'Your documents have been submitted for review. You will be notified once the verification is complete.');

        } catch (\Exception $e) {
            Log::error('Manual KYC upload failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Failed to upload documents. Please try again.',
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to upload documents. Please try again.');
        }
    }

    /**
     * Show the status of manual KYC submission
     */
    public function status()
    {
        $user = Auth::user();

        $submissions = KycDocument::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('kyc.manual-status', [
            'submissions' => $submissions,
            'user' => $user,
            'idTypes' => self::PHILIPPINE_ID_TYPES,
        ]);
    }

    /**
     * Cancel a pending manual KYC submission
     */
    public function cancel(KycDocument $document)
    {
        $user = Auth::user();

        // Ensure the document belongs to the user
        if ($document->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        // Only allow cancellation of pending documents
        if ($document->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending submissions can be cancelled.');
        }

        try {
            // Delete the uploaded files
            $files = json_decode($document->document_file, true);
            if (is_array($files)) {
                foreach ($files as $filePath) {
                    if ($filePath && Storage::disk('private')->exists($filePath)) {
                        Storage::disk('private')->delete($filePath);
                    }
                }
            } elseif ($document->document_file) {
                Storage::disk('private')->delete($document->document_file);
            }

            // Delete the document record
            $document->delete();

            // Reset user's KYC status if no other pending submissions
            $otherPending = KycDocument::where('user_id', $user->id)
                ->where('status', 'pending')
                ->exists();

            if (!$otherPending && $user->kyc_status === 'pending_review') {
                $user->update(['kyc_status' => 'pending']);
            }

            Log::info('Manual KYC submission cancelled', [
                'user_id' => $user->id,
                'document_id' => $document->id,
            ]);

            return redirect()->route('kyc.start.form')
                ->with('success', 'Your submission has been cancelled.');

        } catch (\Exception $e) {
            Log::error('Failed to cancel manual KYC submission', [
                'user_id' => $user->id,
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to cancel submission. Please try again.');
        }
    }

    /**
     * View uploaded document image (for user to verify their upload)
     */
    public function viewDocument(KycDocument $document, string $type)
    {
        $user = Auth::user();

        // Ensure the document belongs to the user or user is admin
        if ($document->user_id !== $user->id && !in_array($user->role, ['admin', 'superadmin'])) {
            abort(403, 'Unauthorized');
        }

        $files = json_decode($document->document_file, true);

        if (!is_array($files) || !isset($files[$type])) {
            // Fall back to single file if not JSON
            if ($type === 'front' && $document->document_file) {
                $filePath = $document->document_file;
            } else {
                abort(404, 'Document not found');
            }
        } else {
            $filePath = $files[$type];
        }

        if (!$filePath || !Storage::disk('private')->exists($filePath)) {
            abort(404, 'Document not found');
        }

        return response()->file(Storage::disk('private')->path($filePath));
    }
}
