@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Job Preview</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.jobs.pending') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <form action="{{ route('admin.jobs.approve', $job) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Approve Job
                </button>
            </form>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="fas fa-times"></i> Reject Job
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Job Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Job Details</h6>
                </div>
                <div class="card-body">
                    <h2 class="h4 mb-3">{{ $job->title }}</h2>
                    
                    <div class="mb-4">
                        <h5 class="h6 text-gray-700">Description</h5>
                        <div class="border-start ps-3">
                            {!! nl2br(e($job->description)) !!}
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="h6 text-gray-700">Requirements</h5>
                        <div class="border-start ps-3">
                            {!! nl2br(e($job->requirements)) !!}
                        </div>
                    </div>

                    @if($job->benefits)
                    <div class="mb-4">
                        <h5 class="h6 text-gray-700">Benefits</h5>
                        <div class="border-start ps-3">
                            {!! nl2br(e($job->benefits)) !!}
                        </div>
                    </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <h5 class="h6 text-gray-700">Salary Range</h5>
                            <p class="mb-0">
                                @if($job->salary_min && $job->salary_max)
                                    ₱{{ number_format($job->salary_min) }} - ₱{{ number_format($job->salary_max) }}
                                @elseif($job->salary_min)
                                    From ₱{{ number_format($job->salary_min) }}
                                @elseif($job->salary_max)
                                    Up to ₱{{ number_format($job->salary_max) }}
                                @else
                                    Not specified
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="h6 text-gray-700">Location</h5>
                            <p class="mb-0">
                                {{ $job->location }}
                                @if($job->is_remote)
                                    <span class="badge bg-info ms-2">Remote</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="h6 text-gray-700">Job Type</h5>
                            <p class="mb-0">{{ $job->jobType->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="h6 text-gray-700">Category</h5>
                            <p class="mb-0">{{ $job->category->name }}</p>
                        </div>
                        @if($job->experience_level)
                        <div class="col-md-6">
                            <h5 class="h6 text-gray-700">Experience Level</h5>
                            <p class="mb-0">{{ $job->experience_level }}</p>
                        </div>
                        @endif
                        @if($job->education_level)
                        <div class="col-md-6">
                            <h5 class="h6 text-gray-700">Education Level</h5>
                            <p class="mb-0">{{ $job->education_level }}</p>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <h5 class="h6 text-gray-700">Application Deadline</h5>
                            <p class="mb-0">
                                @if($job->deadline)
                                    {{ $job->deadline->format('M d, Y') }}
                                @else
                                    No deadline set
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Employer Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Employer Details</h6>
                </div>
                <div class="card-body">
                    <h5 class="h6 text-gray-700">Company Name</h5>
                    <p class="mb-3">{{ $job->employer->name }}</p>

                    <h5 class="h6 text-gray-700">Email</h5>
                    <p class="mb-3">{{ $job->employer->email }}</p>

                    <h5 class="h6 text-gray-700">Posted On</h5>
                    <p class="mb-0">{{ $job->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.jobs.reject', $job) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Job: {{ $job->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason" class="form-label">Reason for Rejection</label>
                        <textarea name="rejection_reason" id="rejection_reason" rows="4" 
                            class="form-control @error('rejection_reason') is-invalid @enderror" 
                            required></textarea>
                        <div class="form-text">
                            Please provide a clear reason for rejecting this job posting. This will be sent to the employer.
                        </div>
                        @error('rejection_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Job</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 