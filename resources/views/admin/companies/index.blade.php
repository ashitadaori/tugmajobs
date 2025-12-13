@extends('layouts.admin')

@section('page_title', 'Companies & Jobs Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1">Companies & Jobs Management</h2>
            <p class="text-muted mb-0">View all companies and their job postings</p>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.companies.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-10">
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Search by company name, email..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Companies Grid -->
    <div class="row">
        @forelse($companies as $company)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm hover-card">
                    <div class="card-body">
                        <!-- Company Logo/Avatar -->
                        <div class="text-center mb-3">
                            @php
                                // Force fresh data from database
                                $freshProfile = \App\Models\EmployerProfile::where('user_id', $company->id)->first();
                                $logoPath = $freshProfile->company_logo ?? null;
                            @endphp
                            
                            @if($logoPath)
                                @php
                                    // Handle different path formats
                                    if (str_starts_with($logoPath, 'storage/')) {
                                        $logoUrl = asset($logoPath);
                                    } else {
                                        $logoUrl = asset('storage/' . $logoPath);
                                    }
                                @endphp
                                <img src="{{ $logoUrl }}" 
                                     alt="{{ $company->name }}"
                                     class="rounded-circle border border-2"
                                     style="width: 100px; height: 100px; object-fit: cover;"
                                     onerror="console.error('Logo failed:', this.src); this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                                <div class="rounded-circle bg-primary text-white d-none align-items-center justify-content-center"
                                     style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: bold;">
                                    {{ strtoupper(substr($company->name, 0, 1)) }}
                                </div>
                            @else
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                     style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: bold;">
                                    {{ strtoupper(substr($company->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <!-- Company Info -->
                        <h5 class="card-title text-center mb-2">{{ $company->name }}</h5>
                        <p class="text-muted text-center small mb-3">
                            <i class="bi bi-envelope"></i> {{ $company->email }}
                        </p>

                        <!-- Stats -->
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="border-end">
                                    <h4 class="mb-0 text-primary">{{ $company->jobs_count }}</h4>
                                    <small class="text-muted">Jobs Posted</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h4 class="mb-0 text-success">
                                    @if($company->email_verified_at)
                                        <i class="bi bi-check-circle-fill"></i>
                                    @else
                                        <i class="bi bi-x-circle-fill text-danger"></i>
                                    @endif
                                </h4>
                                <small class="text-muted">Verified</small>
                            </div>
                        </div>

                        <!-- Company Details -->
                        @if($company->employerProfile)
                            <div class="mb-3">
                                @if($company->employerProfile->company_name)
                                    <p class="mb-1 small">
                                        <i class="bi bi-building"></i> 
                                        <strong>{{ $company->employerProfile->company_name }}</strong>
                                    </p>
                                @endif
                                @if($company->employerProfile->location)
                                    <p class="mb-1 small text-muted">
                                        <i class="bi bi-geo-alt"></i> {{ $company->employerProfile->location }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <!-- Joined Date -->
                        <p class="text-muted small mb-3">
                            <i class="bi bi-calendar"></i> Joined {{ $company->created_at->format('M d, Y') }}
                        </p>

                        <!-- Action Buttons -->
                        <a href="{{ route('admin.companies.show', $company->id) }}" 
                           class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-briefcase"></i> View All Jobs ({{ $company->jobs_count }})
                        </a>
                        @if($company->jobs_count > 0)
                            <a href="{{ route('admin.companies.show', $company->id) }}#jobs-section" 
                               class="btn btn-outline-success w-100">
                                <i class="bi bi-people"></i> View Applicants
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-building" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">No companies found</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($companies->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $companies->links() }}
        </div>
    @endif
</div>

<style>
.hover-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.card {
    border: none;
    border-radius: 0.5rem;
}
</style>
@endsection
