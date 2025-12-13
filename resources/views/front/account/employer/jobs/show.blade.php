@extends('layouts.employer')

@section('page_title', 'Job Details')

@section('content')
<div class="ep-card">
    <div class="ep-card-header">
        <h3 class="ep-card-title">
            <i class="bi bi-briefcase"></i>
            Job Details
        </h3>
        <div class="d-flex gap-2">
            <a href="{{ route('employer.jobs.edit', $job->id) }}" class="ep-btn ep-btn-primary ep-btn-sm">
                <i class="bi bi-pencil"></i> Edit Job
            </a>
            <a href="{{ route('employer.jobs.index') }}" class="ep-btn ep-btn-outline ep-btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Jobs
            </a>
        </div>
    </div>
    <div class="ep-card-body">
        <!-- Job Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h3 class="mb-2">{{ $job->title }}</h3>
                <p class="text-muted mb-0">
                    <i class="bi bi-clock me-1"></i> Posted {{ $job->created_at->diffForHumans() }}
                    @if($job->jobType)
                        <span class="mx-2">|</span>
                        <i class="bi bi-briefcase me-1"></i> {{ $job->jobType->name }}
                    @endif
                    @if($job->location)
                        <span class="mx-2">|</span>
                        <i class="bi bi-geo-alt me-1"></i> {{ $job->location }}
                    @endif
                </p>
            </div>
            <div class="col-md-4 text-end">
                @php
                    $statusClass = match($job->status) {
                        'active', 1 => 'success',
                        'pending', 0 => 'warning',
                        'rejected', 2 => 'danger',
                        'expired', 3 => 'secondary',
                        default => 'secondary'
                    };
                    $statusText = match($job->status) {
                        'active', 1 => 'Active',
                        'pending', 0 => 'Pending Approval',
                        'rejected', 2 => 'Rejected',
                        'expired', 3 => 'Expired',
                        default => ucfirst($job->status)
                    };
                @endphp
                <span class="ep-badge ep-badge-{{ $statusClass }}">
                    {{ $statusText }}
                </span>
                @if($job->featured)
                    <span class="ep-badge ep-badge-warning ms-1">
                        <i class="bi bi-star-fill"></i> Featured
                    </span>
                @endif
            </div>
        </div>

        <!-- Job Description -->
        <div class="mb-4">
            <h5 class="mb-3"><i class="bi bi-file-text me-2"></i>Job Description</h5>
            <div class="formatted-content bg-light p-3 rounded">
                {!! nl2br(e($job->description)) !!}
            </div>
        </div>

        <!-- Requirements -->
        @if($job->requirements)
        <div class="mb-4">
            <h5 class="mb-3"><i class="bi bi-list-check me-2"></i>Requirements</h5>
            <div class="formatted-content bg-light p-3 rounded">
                {!! nl2br(e($job->requirements)) !!}
            </div>
        </div>
        @endif

        <!-- Benefits -->
        @if($job->benefits)
        <div class="mb-4">
            <h5 class="mb-3"><i class="bi bi-gift me-2"></i>Benefits</h5>
            <div class="formatted-content bg-light p-3 rounded">
                {!! nl2br(e($job->benefits)) !!}
            </div>
        </div>
        @endif

        <!-- Job Details & Statistics -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Job Details</h5>
                <ul class="list-unstyled">
                    @if($job->salary_min || $job->salary_max)
                        <li class="mb-2">
                            <strong><i class="bi bi-cash me-2"></i>Salary Range:</strong>
                            @if($job->salary_min && $job->salary_max)
                                PHP {{ number_format($job->salary_min) }} - {{ number_format($job->salary_max) }}
                            @elseif($job->salary_min)
                                From PHP {{ number_format($job->salary_min) }}
                            @elseif($job->salary_max)
                                Up to PHP {{ number_format($job->salary_max) }}
                            @endif
                        </li>
                    @endif
                    <li class="mb-2">
                        <strong><i class="bi bi-geo-alt me-2"></i>Location:</strong> {{ $job->location ?? 'Not specified' }}
                    </li>
                    @if($job->jobType)
                    <li class="mb-2">
                        <strong><i class="bi bi-briefcase me-2"></i>Job Type:</strong> {{ $job->jobType->name }}
                    </li>
                    @endif
                    @if($job->category)
                    <li class="mb-2">
                        <strong><i class="bi bi-tag me-2"></i>Category:</strong> {{ $job->category->name }}
                    </li>
                    @endif
                    @if($job->experience_level)
                    <li class="mb-2">
                        <strong><i class="bi bi-bar-chart me-2"></i>Experience Level:</strong> {{ ucfirst($job->experience_level) }}
                    </li>
                    @endif
                    @if($job->vacancy)
                    <li class="mb-2">
                        <strong><i class="bi bi-people me-2"></i>Vacancies:</strong> {{ $job->vacancy }}
                    </li>
                    @endif
                    @if($job->deadline)
                        <li class="mb-2">
                            <strong><i class="bi bi-calendar-event me-2"></i>Application Deadline:</strong>
                            {{ \Carbon\Carbon::parse($job->deadline)->format('M d, Y') }}
                        </li>
                    @endif
                </ul>
            </div>
            <div class="col-md-6">
                <h5 class="mb-3"><i class="bi bi-graph-up me-2"></i>Statistics</h5>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <strong><i class="bi bi-eye me-2"></i>Total Views:</strong> {{ $job->views ?? 0 }}
                    </li>
                    <li class="mb-2">
                        <strong><i class="bi bi-people me-2"></i>Total Applications:</strong> {{ $job->applications()->count() }}
                    </li>
                    <li class="mb-2">
                        <strong><i class="bi bi-bookmark me-2"></i>Saved by:</strong> {{ $job->savedJobs()->count() }} candidates
                    </li>
                </ul>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-4 pt-4 border-top">
            <h5 class="mb-3"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('employer.jobs.applicants', $job->id) }}" class="ep-btn ep-btn-info">
                    <i class="bi bi-people"></i> View Applications ({{ $job->applications()->count() }})
                </a>
                <a href="{{ route('employer.jobs.edit', $job->id) }}" class="ep-btn ep-btn-primary">
                    <i class="bi bi-pencil"></i> Edit Job
                </a>
                <form action="{{ route('employer.jobs.delete', $job->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this job? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ep-btn ep-btn-danger">
                        <i class="bi bi-trash"></i> Delete Job
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
