@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">KYC Verification Queue</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Document Type</th>
                            <th>Document Number</th>
                            <th>Submitted Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $document)
                        <tr>
                            <td>
                                <div class="applicant-info">
                                    <div class="applicant-name">{{ $document->user->name }}</div>
                                    <div class="applicant-email">{{ $document->user->email }}</div>
                                </div>
                            </td>
                            <td>{{ $document->document_type }}</td>
                            <td>{{ $document->document_number }}</td>
                            <td>{{ $document->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    @php
                                        $files = json_decode($document->document_file, true);
                                    @endphp

                                    <!-- View Documents Button (for JSON format) -->
                                    @if(is_array($files))
                                        <div class="btn-group" role="group">
                                            @if(!empty($files['front']))
                                                <a href="{{ route('kyc.manual.view', [$document, 'front']) }}"
                                                   class="btn btn-sm btn-outline-info"
                                                   title="View Front" target="_blank">
                                                    <i class="fas fa-image"></i> Front
                                                </a>
                                            @endif
                                            @if(!empty($files['back']))
                                                <a href="{{ route('kyc.manual.view', [$document, 'back']) }}"
                                                   class="btn btn-sm btn-outline-info"
                                                   title="View Back" target="_blank">
                                                    <i class="fas fa-image"></i> Back
                                                </a>
                                            @endif
                                            @if(!empty($files['selfie']))
                                                <a href="{{ route('kyc.manual.view', [$document, 'selfie']) }}"
                                                   class="btn btn-sm btn-outline-warning"
                                                   title="View Selfie" target="_blank">
                                                    <i class="fas fa-camera"></i> Selfie
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <!-- Legacy Download Button -->
                                        <a href="{{ route('admin.kyc.document.download', $document) }}"
                                           class="btn btn-sm btn-info"
                                           title="Download Document">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    @endif

                                    <!-- Approve Button -->
                                    <form action="{{ route('admin.kyc.verify', $document) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="btn btn-sm btn-success" 
                                                title="Approve Document">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>

                                    <!-- Reject Button -->
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectModal{{ $document->id }}"
                                            title="Reject Document">
                                        <i class="fas fa-times"></i>
                                    </button>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $document->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reject Document</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.kyc.reject', $document) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Rejection Reason</label>
                                                            <textarea name="rejection_reason" 
                                                                      class="form-control" 
                                                                      rows="3" 
                                                                      required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No pending KYC documents found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $documents->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 