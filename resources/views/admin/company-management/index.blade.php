@extends('layouts.admin')

@section('page_title', 'Company Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Company Management</h2>
                    <p class="text-muted mb-0">Manage all companies and their job postings</p>
                </div>
                <a href="{{ route('admin.company-management.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Add New Company
                </a>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.company-management.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-7">
                        <input type="text" name="search" class="form-control"
                               placeholder="Search by company name, email, location..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="filter" class="form-select">
                            <option value="all" {{ ($filter ?? 'all') == 'all' ? 'selected' : '' }}>All Companies</option>
                            <option value="admin" {{ ($filter ?? 'all') == 'admin' ? 'selected' : '' }}>Admin Created</option>
                            <option value="registered" {{ ($filter ?? 'all') == 'registered' ? 'selected' : '' }}>Registered Employers</option>
                        </select>
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

    <div class="row">
        @forelse($companies as $company)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm hover-card">
                    <div class="card-body">
                        {{-- Source badge --}}
                        <div class="text-end mb-2">
                            @if($company->source === 'admin')
                                <span class="badge bg-info"><i class="bi bi-shield-check"></i> Admin Created</span>
                            @else
                                <span class="badge bg-success"><i class="bi bi-person-badge"></i> Registered</span>
                            @endif
                        </div>

                        <div class="text-center mb-3">
                            @php
                                $logoUrl = null;
                                if ($company->source === 'admin') {
                                    $logoUrl = $company->logo ? asset('storage/' . $company->logo) : null;
                                } else {
                                    if ($company->logo) {
                                        if (filter_var($company->logo, FILTER_VALIDATE_URL)) {
                                            $logoUrl = $company->logo;
                                        } elseif (strpos($company->logo, 'storage/') === 0) {
                                            $logoUrl = asset($company->logo);
                                        } else {
                                            $logoUrl = asset('storage/' . $company->logo);
                                        }
                                    }
                                }
                                $initials = strtoupper(substr($company->name ?? 'C', 0, 1));
                            @endphp

                            @if($logoUrl)
                                <img src="{{ $logoUrl }}"
                                     alt="{{ $company->name }}"
                                     class="rounded-circle border border-2"
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                     style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: bold;">
                                    {{ $initials }}
                                </div>
                            @endif
                        </div>

                        <h5 class="card-title text-center mb-2">{{ $company->name }}</h5>

                        @if($company->email)
                            <p class="text-muted text-center small mb-2">
                                <i class="bi bi-envelope"></i> {{ $company->email }}
                            </p>
                        @endif

                        @if($company->location)
                            <p class="text-muted text-center small mb-3">
                                <i class="bi bi-geo-alt"></i> {{ $company->location }}
                            </p>
                        @endif

                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="border-end">
                                    <h4 class="mb-0 text-primary">{{ $company->jobs_count ?? 0 }}</h4>
                                    <small class="text-muted">Jobs</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h4 class="mb-0">
                                    @if($company->is_active)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-x-circle-fill text-danger"></i>
                                    @endif
                                </h4>
                                <small class="text-muted">Status</small>
                            </div>
                        </div>

                        @if($company->source === 'admin')
                            {{-- Admin-created company actions --}}
                            <div class="btn-group w-100 mb-2">
                                <a href="{{ route('admin.company-management.show', $company->id) }}"
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="{{ route('admin.company-management.edit', $company->id) }}"
                                   class="btn btn-outline-warning">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </div>

                            <form action="{{ route('admin.company-management.destroy', $company->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this company?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        @else
                            {{-- Registered employer company actions --}}
                            <a href="{{ route('admin.companies.show', $company->user_id) }}"
                               class="btn btn-outline-primary w-100">
                                <i class="bi bi-eye"></i> View Details
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
                        <a href="{{ route('admin.company-management.create') }}" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Add First Company
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

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
</style>
@endsection
