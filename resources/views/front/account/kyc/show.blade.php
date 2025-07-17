@extends('front.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Document Details</h2>
                <a href="{{ route('account.kyc.index') }}" class="btn btn-secondary">Back to Documents</a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-4">Document Information</h5>
                            <table class="table">
                                <tr>
                                    <th width="200">Document Type:</th>
                                    <td>{{ $document->document_type }}</td>
                                </tr>
                                <tr>
                                    <th>Document Number:</th>
                                    <td>{{ $document->document_number }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @switch($document->status)
                                            @case('pending')
                                                <span class="badge bg-warning">Pending</span>
                                                @break
                                            @case('verified')
                                                <span class="badge bg-success">Verified</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                                @break
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <th>Submitted Date:</th>
                                    <td>{{ $document->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                @if($document->status === 'rejected' && $document->rejection_reason)
                                <tr>
                                    <th>Rejection Reason:</th>
                                    <td class="text-danger">{{ $document->rejection_reason }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-4">Document Preview</h5>
                            <div class="document-preview p-3 border rounded">
                                @if(pathinfo($document->document_file, PATHINFO_EXTENSION) === 'pdf')
                                    <div class="text-center">
                                        <i class="fas fa-file-pdf fa-4x text-danger mb-3"></i>
                                        <p class="mb-3">PDF Document</p>
                                        <a href="{{ route('account.kyc.download', $document) }}" class="btn btn-primary">
                                            <i class="fas fa-download me-2"></i>Download Document
                                        </a>
                                    </div>
                                @else
                                    <img src="{{ Storage::url($document->document_file) }}" alt="Document Preview" class="img-fluid">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 