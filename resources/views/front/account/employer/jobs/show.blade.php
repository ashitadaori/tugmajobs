@extends('front.layouts.employer-layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card bg-white rounded-3 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 mb-2">{{ $job->title }}</h1>
                        <p class="text-muted mb-0">Posted {{ $job->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('employer.jobs.edit', $job->id) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-2"></i>Edit Job
                        </a>
                        <form action="{{ route('employer.jobs.delete', $job->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this job?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-2"></i>Delete Job
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Job Details -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Job Details</h5>
                    
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Description</h6>
                        <div class="formatted-content">
                            {!! nl2br(e($job->description)) !!}
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Requirements</h6>
                        <div class="formatted-content">
                            {!! nl2br(e($job->requirements)) !!}
                        </div>
                    </div>

                    @if($job->benefits)
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Benefits</h6>
                        <div class="formatted-content">
                            {!! nl2br(e($job->benefits)) !!}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Job Info -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Job Information</h5>
                    
                    <div class="info-item mb-3">
                        <h6 class="text-muted mb-2">Category</h6>
                        <p class="mb-0">{{ $job->category->name }}</p>
                    </div>

                    <div class="info-item mb-3">
                        <h6 class="text-muted mb-2">Job Type</h6>
                        <p class="mb-0">{{ $job->jobType->name }}</p>
                    </div>

                    <div class="info-item mb-3">
                        <h6 class="text-muted mb-2">Location</h6>
                        <p class="mb-0">{{ $job->location }}</p>
                    </div>

                    @if($job->salary_min || $job->salary_max)
                    <div class="info-item mb-3">
                        <h6 class="text-muted mb-2">Salary Range</h6>
                        <p class="mb-0">
                            @if($job->salary_min && $job->salary_max)
                                ₱{{ number_format($job->salary_min) }} - ₱{{ number_format($job->salary_max) }}
                            @elseif($job->salary_min)
                                From ₱{{ number_format($job->salary_min) }}
                            @else
                                Up to ₱{{ number_format($job->salary_max) }}
                            @endif
                        </p>
                    </div>
                    @endif

                    <div class="info-item mb-3">
                        <h6 class="text-muted mb-2">Experience Level</h6>
                        <p class="mb-0">{{ ucfirst($job->experience_level) }}</p>
                    </div>

                    <div class="info-item mb-3">
                        <h6 class="text-muted mb-2">Status</h6>
                        <span class="badge bg-{{ $job->status == 'published' ? 'success' : ($job->status == 'draft' ? 'warning' : 'danger') }}">
                            {{ ucfirst($job->status) }}
                        </span>
                    </div>

                    <div class="info-item">
                        <h6 class="text-muted mb-2">Applications</h6>
                        <p class="mb-0">{{ $job->applications_count }} applications received</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">Quick Actions</h5>
                    <div class="d-grid gap-2">
                        <a href="{{ route('employer.applications.index', ['job' => $job->id]) }}" class="btn btn-outline-primary">
                            <i class="bi bi-people me-2"></i>View Applications
                        </a>
                        <a href="{{ route('employer.jobs.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Jobs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.welcome-card {
    background: linear-gradient(to right, var(--bs-primary-bg-subtle), var(--bs-white));
    border-left: 4px solid var(--bs-primary);
}

.formatted-content {
    white-space: pre-line;
}

.info-item:last-child {
    margin-bottom: 0;
}
</style>
@endpush 