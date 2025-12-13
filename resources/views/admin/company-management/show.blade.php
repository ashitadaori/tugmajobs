@extends('layouts.admin')

@section('page_title', $company->name)

@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <a href="{{ route('admin.company-management.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Companies
        </a>
    </div>

    <div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body text-white">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    @if($company->logo_url)
                        <img src="{{ $company->logo_url }}" alt="{{ $company->name }}"
                             class="rounded-circle border border-white border-3"
                             style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center border border-white border-3"
                             style="width: 120px; height: 120px; font-size: 3rem; font-weight: bold;">
                            {{ $company->initials }}
                        </div>
                    @endif
                </div>
                <div class="col-md-10">
                    <h2 class="mb-2">{{ $company->name }}</h2>
                    <div class="row">
                        @if($company->email)
                            <div class="col-md-3">
                                <i class="bi bi-envelope"></i> {{ $company->email }}
                            </div>
                        @endif
                        @if($company->phone)
                            <div class="col-md-3">
                                <i class="bi bi-telephone"></i> {{ $company->phone }}
                            </div>
                        @endif
                        @if($company->location)
                            <div class="col-md-3">
                                <i class="bi bi-geo-alt"></i> {{ $company->location }}
                            </div>
                        @endif
                        <div class="col-md-3">
                            <i class="bi bi-calendar"></i> Created {{ $company->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary mb-0">{{ $company->jobs->count() }}</h3>
                    <p class="text-muted mb-0">Total Jobs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success mb-0">{{ $company->jobs->where('status', 1)->count() }}</h3>
                    <p class="text-muted mb-0">Active Jobs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info mb-0">{{ $company->jobs->sum(function($job) { return $job->applications->count(); }) }}</h3>
                    <p class="text-muted mb-0">Total Applications</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="{{ $company->is_active ? 'text-success' : 'text-danger' }} mb-0">
                        <i class="bi bi-{{ $company->is_active ? 'check-circle-fill' : 'x-circle-fill' }}"></i>
                    </h3>
                    <p class="text-muted mb-0">{{ $company->is_active ? 'Active' : 'Inactive' }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($company->description || $company->industry || $company->company_size)
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Company Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($company->industry)
                        <div class="col-md-6 mb-3">
                            <strong>Industry:</strong><br>
                            {{ $company->industry }}
                        </div>
                    @endif
                    @if($company->company_size)
                        <div class="col-md-6 mb-3">
                            <strong>Company Size:</strong><br>
                            {{ $company->company_size }} employees
                        </div>
                    @endif
                    @if($company->founded_year)
                        <div class="col-md-6 mb-3">
                            <strong>Founded:</strong><br>
                            {{ $company->founded_year }}
                        </div>
                    @endif
                    @if($company->website)
                        <div class="col-md-6 mb-3">
                            <strong>Website:</strong><br>
                            <a href="{{ $company->website }}" target="_blank">{{ $company->website }}</a>
                        </div>
                    @endif
                </div>
                @if($company->description)
                    <div class="mt-3">
                        <strong>About Company:</strong>
                        <p class="mt-2">{{ $company->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-briefcase me-2"></i>Job Postings ({{ $company->jobs->count() }})</h5>
            <a href="{{ route('admin.jobs.create') }}?company_id={{ $company->id }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle"></i> Add Job
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Job Title</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Posted</th>
                            <th class="text-center">Applicants</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($company->jobs as $job)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $job->title }}</div>
                                    <small class="text-muted">{{ $job->category->name ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <i class="bi bi-geo-alt text-muted"></i> {{ $job->location }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $job->jobType->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    @if($job->status == 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $job->created_at->format('M d, Y') }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info text-white">
                                        {{ $job->applications->count() }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.jobs.show', $job->id) }}" 
                                           class="btn btn-outline-primary" title="View Job">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.jobs.applicants', $job->id) }}" 
                                           class="btn btn-outline-success" title="View Applicants">
                                            <i class="bi bi-people"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted mt-2 mb-0">No jobs posted yet</p>
                                    <a href="{{ route('admin.jobs.create') }}?company_id={{ $company->id }}" class="btn btn-success btn-sm mt-2">
                                        <i class="bi bi-plus-circle"></i> Post First Job
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
