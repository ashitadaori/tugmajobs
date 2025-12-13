@extends('layouts.admin')

@section('page_title', 'Submitted Documents')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.jobs.index') }}">Jobs</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.jobs.application.show', $application->id) }}">{{ $application->user->name }}</a></li>
                    <li class="breadcrumb-item active">Documents</li>
                </ol>
            </nav>
            <h4 class="mt-2">Submitted Documents</h4>
            <p class="text-muted mb-0">{{ $application->user->name }} - {{ $application->job->title }}</p>
        </div>
        <div>
            <a href="{{ route('admin.jobs.application.show', $application->id) }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Application
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Documents List -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Submitted Documents</h5>
                </div>
                <div class="card-body">
                    @if($application->submitted_documents && count($application->submitted_documents) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Document Name</th>
                                        <th>Original File</th>
                                        <th>Size</th>
                                        <th>Uploaded</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($application->submitted_documents as $index => $document)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $document['requirement_name'] ?? 'Document ' . ($index + 1) }}</strong>
                                            </td>
                                            <td>
                                                <span class="text-muted small">{{ $document['original_name'] ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if(isset($document['file_size']))
                                                    {{ number_format($document['file_size'] / 1024, 2) }} KB
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($document['uploaded_at']))
                                                    {{ \Carbon\Carbon::parse($document['uploaded_at'])->format('M d, Y h:i A') }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ asset('storage/' . $document['file_path']) }}"
                                                       class="btn btn-outline-primary" target="_blank"
                                                       title="View Document">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ asset('storage/' . $document['file_path']) }}"
                                                       class="btn btn-outline-secondary" download
                                                       title="Download Document">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-folder2-open text-muted" style="font-size: 4rem;"></i>
                            <h5 class="mt-3">No Documents Submitted</h5>
                            <p class="text-muted">The applicant has not submitted any documents yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Required Documents Checklist -->
            @if($application->job->jobRequirements && $application->job->jobRequirements->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Required Documents Checklist</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @foreach($application->job->jobRequirements as $requirement)
                                @php
                                    $isSubmitted = false;
                                    if($application->submitted_documents) {
                                        foreach($application->submitted_documents as $doc) {
                                            if(isset($doc['requirement_id']) && $doc['requirement_id'] == $requirement->id) {
                                                $isSubmitted = true;
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="{{ $isSubmitted ? 'text-success' : 'text-muted' }}">
                                            @if($isSubmitted)
                                                <i class="bi bi-check-circle-fill me-2"></i>
                                            @else
                                                <i class="bi bi-circle me-2"></i>
                                            @endif
                                            {{ $requirement->name }}
                                        </span>
                                        @if($requirement->is_required)
                                            <span class="badge bg-danger ms-2">Required</span>
                                        @else
                                            <span class="badge bg-secondary ms-2">Optional</span>
                                        @endif
                                    </div>
                                    <span class="badge {{ $isSubmitted ? 'bg-success' : 'bg-warning' }}">
                                        {{ $isSubmitted ? 'Submitted' : 'Pending' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Applicant Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Applicant</h5>
                </div>
                <div class="card-body text-center">
                    <img src="{{ $application->user->image ? asset('storage/' . $application->user->image) : asset('images/default-avatar.png') }}"
                         alt="{{ $application->user->name }}"
                         class="rounded-circle mb-3" width="80" height="80" style="object-fit: cover;">
                    <h5 class="mb-1">{{ $application->user->name }}</h5>
                    <p class="text-muted mb-3">{{ $application->user->email }}</p>
                    <a href="{{ route('admin.jobs.application.show', $application->id) }}" class="btn btn-outline-primary btn-sm">
                        View Full Application
                    </a>
                </div>
            </div>

            <!-- Application Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Application Status</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge {{ $application->getStageBadgeClass() }} me-2">
                            {{ $application->getStageName() }}
                        </span>
                        <span class="badge {{ $application->getStageStatusBadgeClass() }}">
                            {{ ucfirst($application->stage_status ?? 'pending') }}
                        </span>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: {{ $application->getProgressPercentage() }}%"></div>
                    </div>
                    <small class="text-muted">{{ $application->getProgressPercentage() }}% Complete</small>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    @if($application->stage === 'requirements' && $application->stage_status === 'pending' && $application->hasSubmittedRequirements())
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success" onclick="approveDocuments()">
                                <i class="bi bi-check-circle me-2"></i>Approve Documents
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="rejectDocuments()">
                                <i class="bi bi-x-circle me-2"></i>Reject Documents
                            </button>
                        </div>
                    @elseif($application->stage === 'requirements' && $application->stage_status === 'approved')
                        <div class="alert alert-success mb-0">
                            <i class="bi bi-check-circle me-2"></i>
                            Documents have been approved. You can now advance to the interview stage.
                        </div>
                    @else
                        <p class="text-muted mb-0">No actions available at this stage.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function approveDocuments() {
    if (confirm('Are you sure you want to approve these documents? This will allow you to advance the applicant to the interview stage.')) {
        submitStageAction('approve');
    }
}

function rejectDocuments() {
    const reason = prompt('Please provide a reason for rejection (optional):');
    if (reason !== null) {
        submitStageAction('reject', reason);
    }
}

function submitStageAction(action, notes = null) {
    const buttons = document.querySelectorAll('button');
    buttons.forEach(btn => btn.disabled = true);

    fetch(`{{ route('admin.jobs.application.updateStage', $application->id) }}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ action: action, notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            alert(data.message);
            window.location.href = '{{ route("admin.jobs.application.show", $application->id) }}';
        } else {
            alert(data.message || 'Failed to update application');
            buttons.forEach(btn => btn.disabled = false);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
        buttons.forEach(btn => btn.disabled = false);
    });
}
</script>
@endpush
@endsection
