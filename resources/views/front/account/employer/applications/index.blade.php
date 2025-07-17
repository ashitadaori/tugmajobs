@extends('front.layouts.employer-layout')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card bg-white rounded-3 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 mb-2">Job Applications</h1>
                        <p class="text-muted mb-0">Manage and track all applications for your job postings</p>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="btn-group">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            <select class="form-select" id="jobFilter">
                                <option value="">All Jobs</option>
                                @foreach($jobs as $job)
                                    <option value="{{ $job->id }}">{{ $job->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($applications->isEmpty())
                <div class="text-center py-5">
                    <img src="{{ asset('images/empty-applications.svg') }}" alt="No Applications" class="mb-4" style="width: 200px;">
                    <h5 class="mb-2">No Applications Yet</h5>
                    <p class="text-muted mb-4">When candidates apply to your jobs, they'll appear here.</p>
                    <a href="{{ route('employer.jobs.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Post a New Job
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Applicant</th>
                                <th>Job Title</th>
                                <th>Applied Date</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applications as $application)
                                <tr>
                                    <td style="min-width: 250px;">
                                        <div class="d-flex align-items-center">
                                            @if($application->user->profile_photo)
                                                <img src="{{ asset('profile_img/' . $application->user->profile_photo) }}" 
                                                     alt="Profile" 
                                                     class="rounded-circle me-3"
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="avatar-wrapper me-3">
                                                    <span class="avatar-text">{{ substr($application->user->name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-1">{{ $application->user->name }}</h6>
                                                <span class="text-muted small">{{ $application->user->email }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="min-width: 200px;">
                                        <div>
                                            <h6 class="mb-1">{{ $application->job->title }}</h6>
                                            <span class="text-muted small">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                {{ $application->job->location }}
                                            </span>
                                        </div>
                                    </td>
                                    <td style="min-width: 150px;">
                                        <div>
                                            <span class="d-block">{{ $application->created_at->format('M d, Y') }}</span>
                                            <small class="text-muted">{{ $application->created_at->diffForHumans() }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center" style="min-width: 120px;">
                                        <span class="badge rounded-pill bg-{{ $application->status === 'pending' ? 'warning' : ($application->status === 'approved' ? 'success' : 'danger') }}-subtle">
                                            {{ ucfirst($application->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center" style="min-width: 120px;">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#viewApplicationModal{{ $application->id }}" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="{{ asset('storage/' . $application->resume) }}" class="btn btn-sm btn-light" target="_blank" title="View Resume">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" title="More Actions">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <button class="dropdown-item" onclick="updateStatus('{{ $application->id }}', 'approved')">
                                                        <i class="bi bi-check-circle text-success me-2"></i>Approve
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" onclick="updateStatus('{{ $application->id }}', 'rejected')">
                                                        <i class="bi bi-x-circle text-danger me-2"></i>Reject
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Application Details Modal -->
                                <div class="modal fade" id="viewApplicationModal{{ $application->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title">Application Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Applicant Info -->
                                                <div class="card bg-light border-0 mb-3">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center mb-3">
                                                            @if($application->user->profile_photo)
                                                                <img src="{{ asset('profile_img/' . $application->user->profile_photo) }}" 
                                                                     alt="Profile" 
                                                                     class="rounded-circle me-3"
                                                                     style="width: 48px; height: 48px; object-fit: cover;">
                                                            @else
                                                                <div class="avatar-wrapper me-3" style="width: 48px; height: 48px;">
                                                                    <span class="avatar-text">{{ substr($application->user->name, 0, 1) }}</span>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="mb-1">{{ $application->user->name }}</h6>
                                                                <p class="mb-0 text-muted">{{ $application->user->email }}</p>
                                                            </div>
                                                        </div>
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <p class="mb-1 small text-muted">Phone</p>
                                                                <p class="mb-0">{{ $application->user->phone ?? 'Not provided' }}</p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p class="mb-1 small text-muted">Location</p>
                                                                <p class="mb-0">{{ $application->user->location ?? 'Not provided' }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Job Details -->
                                                <div class="card bg-light border-0 mb-3">
                                                    <div class="card-body">
                                                        <h6 class="card-title mb-3">Job Details</h6>
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <p class="mb-1 small text-muted">Position</p>
                                                                <p class="mb-0">{{ $application->job->title }}</p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p class="mb-1 small text-muted">Location</p>
                                                                <p class="mb-0">{{ $application->job->location }}</p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p class="mb-1 small text-muted">Applied Date</p>
                                                                <p class="mb-0">{{ $application->created_at->format('M d, Y') }}</p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p class="mb-1 small text-muted">Status</p>
                                                                <span class="badge rounded-pill bg-{{ $application->status === 'pending' ? 'warning' : ($application->status === 'approved' ? 'success' : 'danger') }}-subtle">
                                                                    {{ ucfirst($application->status) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Cover Letter -->
                                                @if($application->cover_letter)
                                                <div class="card bg-light border-0 mb-3">
                                                    <div class="card-body">
                                                        <h6 class="card-title mb-3">Cover Letter</h6>
                                                        <p class="mb-0">{{ $application->cover_letter }}</p>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                <a href="{{ asset('storage/' . $application->resume) }}" class="btn btn-primary" target="_blank">
                                                    <i class="bi bi-file-pdf me-2"></i>View Resume
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($applications->hasPages())
                <div class="d-flex justify-content-center border-top p-4">
                    {{ $applications->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.welcome-card {
    background: linear-gradient(to right, var(--bs-primary-bg-subtle), var(--bs-white));
    border-left: 4px solid var(--bs-primary);
}

.avatar-wrapper {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--bs-primary-bg-subtle);
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-text {
    color: var(--bs-primary);
    font-weight: 500;
}

.table > :not(caption) > * > * {
    padding: 1rem;
}

.bg-warning-subtle {
    background-color: #fff3cd;
    color: #997404;
}

.bg-success-subtle {
    background-color: #d1e7dd;
    color: #0f5132;
}

.bg-danger-subtle {
    background-color: #f8d7da;
    color: #842029;
}

.btn-light {
    background-color: var(--bs-light);
    border-color: var(--bs-border-color);
}

.btn-light:hover {
    background-color: var(--bs-light);
    border-color: var(--bs-border-color);
}

.modal-content {
    border: none;
    border-radius: 0.5rem;
}

.card {
    border-radius: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
function updateStatus(applicationId, status) {
    // Add your status update logic here
    console.log(`Updating application ${applicationId} to ${status}`);
}
</script>
@endpush
@endsection 