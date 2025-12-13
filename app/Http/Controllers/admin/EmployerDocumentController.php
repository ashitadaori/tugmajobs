<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployerDocument;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployerDocumentController extends Controller
{
    /**
     * Display a listing of documents.
     */
    public function index()
    {
        $documents = EmployerDocument::with(['user', 'reviewer'])
                        ->orderBy('submitted_at', 'desc')
                        ->paginate(10);

        return view('admin.employers.documents', compact('documents'));
    }

    /**
     * Show the form for creating a new document.
     */
    public function create()
    {
        $documentTypes = EmployerDocument::getDocumentTypes();

        return view('admin.employers.create-document', compact('documentTypes'));
    }

    /**
     * Store a newly created document in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string|max:255',
            'document_name' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $filePath = $request->file('file')->store('employer_documents', 'public');

        EmployerDocument::create([
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

        return redirect()->route('admin.employers.documents.index')
                         ->with('success', 'Document submitted for review.');
    }

    /**
     * Display the specified document.
     */
    public function show(EmployerDocument $document)
    {
        return view('admin.employers.document-detail', compact('document'));
    }

    /**
     * Approve the specified document.
     */
    public function approve(EmployerDocument $document)
    {
        // Only allow pending documents to be approved
        if (!$document->isPending()) {
            return back()->with('error', 'This document has already been reviewed.');
        }

        $document->approve(Auth::user());

        return back()->with('success', 'Document approved successfully.');
    }

    /**
     * Reject the specified document.
     */
    public function reject(Request $request, EmployerDocument $document)
    {
        // Only allow pending documents to be rejected
        if (!$document->isPending()) {
            return back()->with('error', 'This document has already been reviewed.');
        }

        $request->validate([
            'admin_notes' => 'required|string|min:10|max:500'
        ], [
            'admin_notes.required' => 'Please provide a reason for rejection.',
            'admin_notes.min' => 'Please provide a more detailed reason (at least 10 characters).',
            'admin_notes.max' => 'Rejection reason is too long (maximum 500 characters).'
        ]);

        $document->reject(Auth::user(), $request->admin_notes);

        return back()->with('success', 'Document rejected successfully. The employer will be notified.');
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy(EmployerDocument $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document deleted successfully.');
    }
}


