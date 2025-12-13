@extends('layouts.admin')

@section('page_title', 'Job Applicants - ' . $job->title)

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('admin.jobs.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Jobs
        </a>
    </div>

    <!-- Job Info Header -->
    <div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="card-body">
            <h3 class="mb-2" style="color: white;">{{ $job->title }}</h3>
            <div class="d-flex gap-3 flex-wrap">
                <span><i class="bi bi-building"></i> {{ $job->employer->name ?? 'N/A' }}</span>
                <span><i class="bi bi-geo-alt"></i> {{ $job->location }}</span>
                <span><i class="bi bi-briefcase"></i> {{ $job->jobType->name ?? 'N/A' }}</span>
                <span><i class="bi bi-people"></i> {{ $job->applications->count() }} Applicants</span>
            </div>
        </div>
    </div>

    <!-- Applicants List -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Applicants ({{ $job->applications->count() }})</h5>
        </div>
        <div class="card-body">
            @forelse($job->applications as $application)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                <img src="{{ $application->user->image ? asset('storage/' . $application->user->image) : asset('images/default-avatar.png') }}" 
                                     alt="{{ $application->user->name }}" 
                                     class="rounded-circle"
                                     style="width: 80px; height: 80px; object-fit: cover;">
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-1">{{ $application->user->name }}</h5>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-envelope"></i> {{ $application->user->email }}<br>
                                    <i class="bi bi-telephone"></i> {{ $application->user->mobile ?? 'N/A' }}
                                </p>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> Applied {{ $application->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge {{ $application->getStageBadgeClass() }} px-3 py-2 mb-1">
                                    {{ $application->getStageName() }}
                                </span>
                                @if($application->stage_status)
                                    <br>
                                    <small class="badge {{ $application->getStageStatusBadgeClass() }}">
                                        {{ ucfirst($application->stage_status) }}
                                    </small>
                                @endif
                            </div>
                            <div class="col-md-2 text-center">
                                <a href="{{ route('admin.jobs.application.show', $application->id) }}"
                                   class="btn btn-sm btn-primary mb-2 w-100">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                @if($application->resume)
                                    <a href="{{ asset('storage/' . $application->resume) }}"
                                       class="btn btn-sm btn-outline-secondary mb-2 w-100"
                                       target="_blank">
                                        <i class="bi bi-file-pdf"></i> Resume
                                    </a>
                                @endif
                            </div>
                        </div>
                        @if($application->cover_letter)
                            <div class="mt-3 pt-3 border-top">
                                <strong>Cover Letter:</strong>
                                <p class="mb-0 mt-2">{{ $application->cover_letter }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-2">No applicants yet for this job</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
