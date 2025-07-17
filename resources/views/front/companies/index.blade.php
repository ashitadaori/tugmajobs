@extends('front.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2 mb-2">Featured Companies</h1>
            <p class="text-muted">Discover great places to work</p>
        </div>
    </div>

    <div class="row g-4">
        @foreach($companies as $company)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="company-logo me-3">
                                @if($company->logo_url)
                                    <img src="{{ asset($company->logo_url) }}" alt="{{ $company->company_name }}" 
                                        class="rounded-3" style="width: 64px; height: 64px; object-fit: cover;">
                                @else
                                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center" 
                                        style="width: 64px; height: 64px;">
                                        <i class="fas fa-building text-muted fa-2x"></i>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h5 class="card-title mb-1">
                                    <a href="{{ route('companies.show', $company->id) }}" class="text-dark text-decoration-none">
                                        {{ $company->company_name }}
                                    </a>
                                </h5>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $company->location ?? 'Location not specified' }}
                                </p>
                            </div>
                        </div>

                        <p class="card-text mb-3">
                            {{ Str::limit($company->description ?? 'Company description not available', 100) }}
                        </p>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark">
                                {{ $company->jobs->count() }} {{ Str::plural('job', $company->jobs->count()) }} open
                            </span>
                            <a href="{{ route('companies.show', $company->id) }}" class="btn btn-sm btn-outline-primary">
                                View Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mt-4">
        <div class="col-12 d-flex justify-content-center">
            {{ $companies->links() }}
        </div>
    </div>
</div>
@endsection 