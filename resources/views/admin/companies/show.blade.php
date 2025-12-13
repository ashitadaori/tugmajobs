@extends('layouts.admin')

@section('page_title', $company->name . ' - Company Details')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Companies
        </a>
    </div>

    <!-- Company Header Card -->
    <div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body text-white">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    @if($company->employerProfile && $company->employerProfile->company_logo)
                        <img src="{{ $company->employerProfile->logo_url }}" 
                             alt="{{ $company->name }}"
                             class="rounded-circle border border-white border-3"
                             style="width: 120px; height: 120px; object-fit: cover;"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                        <div class="rounded-circle bg-white text-primary d-none align-items-center justify-content-center border border-white border-3"
                             style="width: 120px; height: 120px; font-size: 3rem; font-weight: bold;">
                            {{ strtoupper(substr($company->name, 0, 1)) }}
                        </div>
                    @else
                        <div class="rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center border border-white border-3"
                             style="width: 120px; height: 120px; font-size: 3rem; font-weight: bold;">
                            {{ strtoupper(substr($company->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="col-md-10">
                    <h2 class="mb-2">{{ $company->name }}</h2>
                    @if($company->employerProfile && $company->employerProfile->company_name)
                        <h5 class="mb-3 opacity-75">{{ $company->employerProfile->company_name }}</h5>
                    @endif
                    <div class="row">
                        <div class="col-md-3">
                            <i class="bi bi-envelope"></i> {{ $company->email }}
                        </div>
                        @if($company->mobile)
                            <div class="col-md-3">
                                <i class="bi bi-telephone"></i> {{ $company->mobile }}
                            </div>
                        @endif
                        @if($company->employerProfile && $company->employerProfile->location)
                            <div class="col-md-3">
                                <i class="bi bi-geo-alt"></i> {{ $company->employerProfile->location }}
                            </div>
                        @endif
                        <div class="col-md-3">
                            <i class="bi bi-calendar"></i> Joined {{ $company->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary mb-0">{{ $company->jobs->count() }}</h3>
                    <p class="text-muted mb-0 small">Total Jobs</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success mb-0">{{ $company->jobs->where('status', 1)->count() }}</h3>
                    <p class="text-muted mb-0 small">Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning mb-0">{{ $company->jobs->where('status', 0)->count() }}</h3>
                    <p class="text-muted mb-0 small">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <h3 class="text-danger mb-0">{{ $company->jobs->where('status', 2)->count() }}</h3>
                    <p class="text-muted mb-0 small">Rejected</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-info mb-0">{{ $company->jobs->sum('applications_count') }}</h3>
                    <p class="text-muted mb-0">Total Applications</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Profile Info -->
    @if($company->employerProfile)
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Company Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($company->employerProfile->website)
                        <div class="col-md-6 mb-3">
                            <strong>Website:</strong><br>
                            <a href="{{ $company->employerProfile->website }}" target="_blank">
                                {{ $company->employerProfile->website }}
                            </a>
                        </div>
                    @endif
                    @if($company->employerProfile->company_size)
                        <div class="col-md-6 mb-3">
                            <strong>Company Size:</strong><br>
                            {{ $company->employerProfile->company_size }}
                        </div>
                    @endif
                    @if($company->employerProfile->industry)
                        <div class="col-md-6 mb-3">
                            <strong>Industry:</strong><br>
                            {{ $company->employerProfile->industry }}
                        </div>
                    @endif
                    @if($company->employerProfile->founded_year)
                        <div class="col-md-6 mb-3">
                            <strong>Founded:</strong><br>
                            {{ $company->employerProfile->founded_year }}
                        </div>
                    @endif
                </div>
                @if($company->employerProfile->about)
                    <div class="mt-3">
                        <strong>About Company:</strong>
                        <p class="mt-2">{{ $company->employerProfile->about }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Jobs Posted by This Company -->
    <div class="card" id="jobs-section">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-briefcase me-2"></i>Jobs Posted ({{ $company->jobs->count() }})</h5>
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
                                    @if($job->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($job->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($job->status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($job->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $job->created_at->format('M d, Y') }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info text-white" style="font-size: 0.9rem;">
                                        {{ $job->applications_count }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.jobs.show', $job->id) }}" 
                                           class="btn btn-outline-primary"
                                           title="View Job Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.jobs.applicants', $job->id) }}" 
                                           class="btn btn-outline-success"
                                           title="View Applicants">
                                            <i class="bi bi-people"></i> {{ $job->applications_count }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted mt-2 mb-0">No jobs posted yet</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 0.5rem;
}

.card-header {
    border-bottom: 1px solid #e9ecef;
}
</style>
@endsection
