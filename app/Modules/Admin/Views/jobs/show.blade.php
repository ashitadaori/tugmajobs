@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">Job Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.jobs.index') }}">Jobs</a></li>
                    <li class="breadcrumb-item active">Job Details</li>
                </ol>
            </nav>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Job Status and Actions Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Job Status</h5>
                    <p class="text-muted mb-0">Current status: 
                        <span class="badge bg-{{ $job->status === 'active' ? 'success' : ($job->status === 'pending' ? 'warning' : 'danger') }} ms-2">
                            {{ ucfirst($job->status) }}
                        </span>
                    </p>
                </div>
                @if($job->status === 'pending')
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.jobs.approve', $job) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check-circle me-2"></i> Approve Job
                            </button>
                        </form>
                        <button type="button" 
                                class="btn btn-danger"
                                data-bs-toggle="modal" 
                                data-bs-target="#rejectModal">
                            <i class="fas fa-times-circle me-2"></i> Reject Job
                        </button>
                    </div>
                @elseif($job->status === 'rejected')
                    <div class="text-danger">
                        <strong>Rejection Reason:</strong> {{ $job->rejection_reason }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Job Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Job Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h2 class="h4">{{ $job->title }}</h2>
                        <div class="text-muted mb-2">{{ optional($job->employer)->company_name ?? 'N/A' }}</div>
                        <div class="d-flex gap-3 mb-3">
                            <span><i class="fas fa-map-marker-alt me-1"></i> {{ $job->location ?? 'Location not specified' }}</span>
                            <span><i class="fas fa-briefcase me-1"></i> {{ optional($job->jobType)->name ?? 'Job type not specified' }}</span>
                            <span><i class="fas fa-clock me-1"></i> Posted {{ $job->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="mb-3">
                            @if($job->category)
                                <span class="badge bg-primary">{{ $job->category->name }}</span>
                            @endif
                            @if($job->experience)
                                <span class="badge bg-info">{{ $job->experience }}</span>
                            @endif
                            @if($job->is_featured)
                                <span class="badge bg-warning">Featured</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Job Description</h5>
                        <div class="formatted-content">
                            {!! nl2br(e($job->description)) !!}
                        </div>
                    </div>

                    @if($job->requirements)
                        <div class="mb-4">
                            <h5>Requirements</h5>
                            <div class="formatted-content">
                                {!! nl2br(e($job->requirements)) !!}
                            </div>
                        </div>
                    @endif

                    @if($job->benefits)
                        <div class="mb-4">
                            <h5>Benefits</h5>
                            <div class="formatted-content">
                                {!! nl2br(e($job->benefits)) !!}
                            </div>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <h5>Salary Range</h5>
                            <p>{{ $job->salary_range ?? 'Not specified' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Vacancies</h5>
                            <p>{{ $job->vacancy ?? 0 }} Position(s)</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Application Deadline</h5>
                            <p>{{ $job->deadline ? $job->deadline->format('M d, Y') : 'No deadline set' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Employer Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Employer Information</h5>
                </div>
                <div class="card-body">
                    @if($job->employer)
                        <div class="d-flex align-items-center mb-3">
                            @if($job->employer->logo)
                                <img src="{{ asset('storage/' . $job->employer->logo) }}" 
                                     alt="{{ $job->employer->company_name }}" 
                                     class="rounded-circle me-3"
                                     style="width: 64px; height: 64px; object-fit: cover;">
                            @endif
                            <div>
                                <h6 class="mb-1">{{ $job->employer->company_name }}</h6>
                                <div class="text-muted">{{ $job->employer->industry ?? 'Industry not specified' }}</div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            {{ optional($job->employer->user)->email ?? 'Email not available' }}
                        </div>
                        @if($job->employer->website)
                            <div class="mb-2">
                                <i class="fas fa-globe me-2"></i>
                                <a href="{{ $job->employer->website }}" target="_blank">{{ $job->employer->website }}</a>
                            </div>
                        @endif
                        @if($job->employer->phone)
                            <div class="mb-2">
                                <i class="fas fa-phone me-2"></i>
                                {{ $job->employer->phone }}
                            </div>
                        @endif
                        @if($job->employer->address)
                            <div class="mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                {{ $job->employer->address }}
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">Employer information not available</p>
                    @endif
                </div>
            </div>

            <!-- Application Statistics -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Application Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>Total Applications</div>
                        <div class="h5 mb-0">{{ $job->applications->count() }}</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>New Applications</div>
                        <div class="h5 mb-0">{{ $job->applications->where('status', 'pending')->count() }}</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Views</div>
                        <div class="h5 mb-0">{{ optional($job->views)->count() ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Reject Job: {{ $job->title }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.jobs.reject', $job) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        Please provide a detailed reason for rejecting this job. This information will be sent to the employer.
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Rejection Reason</label>
                        <textarea name="rejection_reason" 
                                  class="form-control @error('rejection_reason') is-invalid @enderror" 
                                  rows="4" 
                                  required
                                  placeholder="Please explain why this job posting is being rejected..."></textarea>
                        @error('rejection_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Be clear and professional. The employer will use this feedback to make necessary corrections.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times-circle me-2"></i>Reject Job
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.formatted-content {
    white-space: pre-line;
    line-height: 1.6;
}
.required:after {
    content: " *";
    color: red;
}
</style>
@endpush
@endsection 