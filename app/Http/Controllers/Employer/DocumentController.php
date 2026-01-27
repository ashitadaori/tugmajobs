<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployerDocument;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    /**
     * Display a listing of employer's documents.
     */
    public function index()
    {
        $user = Auth::user();
        $documents = $user->employerDocuments()->orderBy('submitted_at', 'desc')->get();
        $documentTypes = EmployerDocument::getDocumentTypes();
        
        // Group documents by type for easier display
        $documentsByType = $documents->groupBy('document_type');
        
        return view('front.account.employer.documents.index', compact('documents', 'documentTypes', 'documentsByType'));
    }

    /**
     * Show the form for uploading a new document.
     */
    public function create()
    {
        $documentTypes = EmployerDocument::getDocumentTypes();
        return view('front.account.employer.documents.create', compact('documentTypes'));
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string|in:' . implode(',', array_keys(EmployerDocument::getDocumentTypes())),
            'document_name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Check if user already has a document of this type pending or approved
        $existingDocument = Auth::user()->employerDocuments()
            ->where('document_type', $request->document_type)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingDocument) {
            return back()->withErrors(['document_type' => 'You already have a document of this type submitted or approved.']);
        }

        $filePath = $request->file('file')->store('employer_documents', 'public');

        $document = EmployerDocument::create([
            'user_id' => Auth::id(),
            'document_type' => $request->document_type,
            'document_name' => $request->document_name,
            'file_path' => $filePath,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'file_size' => $request->file('file')->getSize(),
            'mime_type' => $request->file('file')->getMimeType(),
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        // Notify all admins about the new document submission
        try {
            $user = Auth::user();
            $documentTypeName = EmployerDocument::getDocumentTypes()[$request->document_type]['label'] ?? $request->document_type;
            $admins = User::where('role', 'admin')->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'New Employer Document Submitted',
                    'message' => "{$user->name} has submitted a {$documentTypeName} for verification. Please review the document.",
                    'type' => 'admin_employer_document',
                    'data' => [
                        'document_id' => $document->id,
                        'employer_id' => $user->id,
                        'employer_name' => $user->name,
                        'document_type' => $request->document_type,
                    ],
                    'action_url' => route('admin.employers.documents.index'),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to create notification for admin', ['error' => $e->getMessage()]);
        }

        // If AJAX request, return JSON response to stay on the same page
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully and submitted for review.',
                'document' => $document
            ]);
        }

        return redirect()->route('employer.documents.index')
                         ->with('success', 'Document uploaded successfully and submitted for review.');
    }

    /**
     * Display the specified document.
     */
    public function show(EmployerDocument $document)
    {
        // Make sure user can only view their own documents
        abort_unless($document->user_id === Auth::id(), 403);

        return view('front.account.employer.documents.show', compact('document'));
    }

    /**
     * Show the form for editing the specified document.
     */
    public function edit(EmployerDocument $document)
    {
        // Make sure user can only edit their own documents and only if rejected
        abort_unless($document->user_id === Auth::id() && $document->isRejected(), 403);

        $documentTypes = EmployerDocument::getDocumentTypes();
        return view('front.account.employer.documents.edit', compact('document', 'documentTypes'));
    }

    /**
     * Update the specified document.
     */
    public function update(Request $request, EmployerDocument $document)
    {
        // Make sure user can only update their own documents and only if rejected
        abort_unless($document->user_id === Auth::id() && $document->isRejected(), 403);

        $request->validate([
            'document_name' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $updateData = [
            'document_name' => $request->document_name,
            'status' => 'pending', // Reset to pending after update
            'submitted_at' => now(),
            'admin_notes' => null, // Clear previous admin notes
            'reviewed_at' => null,
            'reviewed_by' => null,
        ];

        // If new file is uploaded
        if ($request->hasFile('file')) {
            // Delete old file
            Storage::disk('public')->delete($document->file_path);
            
            // Store new file
            $filePath = $request->file('file')->store('employer_documents', 'public');
            
            $updateData = array_merge($updateData, [
                'file_path' => $filePath,
                'file_name' => $request->file('file')->getClientOriginalName(),
                'file_size' => $request->file('file')->getSize(),
                'mime_type' => $request->file('file')->getMimeType(),
            ]);
        }

        $document->update($updateData);

        // Notify all admins about the resubmitted document
        try {
            $user = Auth::user();
            $documentTypeName = EmployerDocument::getDocumentTypes()[$document->document_type]['label'] ?? $document->document_type;
            $admins = User::where('role', 'admin')->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Employer Document Resubmitted',
                    'message' => "{$user->name} has resubmitted their {$documentTypeName} for verification after revision. Please review the updated document.",
                    'type' => 'admin_employer_document',
                    'data' => [
                        'document_id' => $document->id,
                        'employer_id' => $user->id,
                        'employer_name' => $user->name,
                        'document_type' => $document->document_type,
                        'is_resubmission' => true,
                    ],
                    'action_url' => route('admin.employers.documents.index'),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to create resubmission notification for admin', ['error' => $e->getMessage()]);
        }

        // If AJAX request, return JSON response to stay on the same page
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Document updated and resubmitted for review.',
                'document' => $document->fresh()
            ]);
        }

        return redirect()->route('employer.documents.index')
                         ->with('success', 'Document updated and resubmitted for review.');
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy(EmployerDocument $document)
    {
        // Make sure user can only delete their own documents and only if rejected
        abort_unless($document->user_id === Auth::id() && ($document->isRejected() || $document->isPending()), 403);

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return redirect()->route('employer.documents.index')
                         ->with('success', 'Document deleted successfully.');
    }

    /**
     * Download the specified document.
     */
    public function download(EmployerDocument $document)
    {
        // Make sure user can only download their own documents
        abort_unless($document->user_id === Auth::id(), 403);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'File not found.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Get verification progress for employer.
     */
    public function getVerificationProgress()
    {
        $user = Auth::user();
        $documentTypes = EmployerDocument::getDocumentTypes();
        
        $progress = [
            'kyc_verified' => $user->isKycVerified(),
            'documents' => [],
            'can_post_jobs' => $user->canPostJobs(),
            'overall_status' => $user->getEmployerVerificationStatus()
        ];

        foreach ($documentTypes as $type => $config) {
            $document = $user->employerDocuments()
                            ->where('document_type', $type)
                            ->orderBy('submitted_at', 'desc')
                            ->first();

            $progress['documents'][$type] = [
                'required' => $config['required'],
                'label' => $config['label'],
                'status' => $document ? $document->status : 'not_submitted',
                'document' => $document,
            ];
        }

        return response()->json($progress);
    }
}
