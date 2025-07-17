<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    public function index(Request $request)
    {
        $query = KycDocument::with('user')->latest();
        
        // Filter by user if provided
        if ($request->has('user')) {
            $query->where('user_id', $request->user);
        }
        
        $documents = $query->paginate(20);
        return view('admin.kyc.index', compact('documents'));
    }

    public function verify(KycDocument $document)
    {
        $document->update([
            'status' => 'verified'
        ]);

        $document->user->jobSeekerProfile()->update([
            'is_kyc_verified' => true
        ]);

        return redirect()->back()
            ->with('success', 'Document verified successfully.');
    }

    public function reject(Request $request, KycDocument $document)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $document->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        return redirect()->back()
            ->with('success', 'Document rejected successfully.');
    }

    public function download(KycDocument $document)
    {
        return Storage::download($document->document_file);
    }
} 