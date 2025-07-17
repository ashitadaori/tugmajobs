@extends('front.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- Company Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="company-logo me-4">
                            @if($company->logo_url)
                                <img src="{{ asset($company->logo_url) }}" alt="{{ $company->company_name }}" 
                                    class="rounded-3" style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                                <div class="rounded-3 bg-light d-flex align-items-center justify-content-center" 
                                    style="width: 100px; height: 100px;">
                                    <i class="fas fa-building text-muted fa-3x"></i>
                                </div>
                            @endif
                        </div>
                        <div>
                            <h1 class="h3 mb-2">{{ $company->company_name }}</h1>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                {{ $company->location ?? 'Location not specified' }}
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-briefcase me-2"></i>
                                {{ $activeJobs->total() }} {{ Str::plural('job', $activeJobs->total()) }} open
                            </p>
                        </div>
                    </div>

                    @if($company->description)
                        <div class="mb-4">
                            <h5 class="mb-3">About {{ $company->company_name }}</h5>
                            <p class="text-muted">{{ $company->description }}</p>
                        </div>
                    @endif

                    @if($company->website)
                        <a href="{{ $company->website }}" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-external-link-alt me-2"></i>
                            Visit Website
                        </a>
                    @endif
                </div>
            </div>

            <!-- Open Positions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Open Positions</h5>
                </div>
                <div class="card-body p-0">
                    @if($activeJobs->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($activeJobs as $job)
                                <div class="list-group-item p-4">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="mb-1">
                                                <a href="{{ route('jobDetail', $job->id) }}" class="text-dark text-decoration-none">
                                                    {{ $job->title }}
                                                </a>
                                            </h5>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-map-marker-alt me-2"></i>{{ $job->location }}
                                                <span class="mx-2">&bull;</span>
                                                <i class="fas fa-clock me-2"></i>{{ $job->job_type }}
                                            </p>
                                            <p class="mb-0">{{ Str::limit($job->description, 150) }}</p>
                                        </div>
                                        <span class="badge bg-light text-dark">
                                            {{ $job->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-briefcase text-muted fa-3x mb-3"></i>
                            <h5>No Open Positions</h5>
                            <p class="text-muted">This company currently has no open positions.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pagination -->
            @if($activeJobs->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $activeJobs->links() }}
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Company Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Company Information</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        @if($company->industry)
                            <li class="mb-3">
                                <i class="fas fa-industry me-2 text-muted"></i>
                                <span class="text-muted">Industry:</span>
                                <br>
                                <strong>{{ $company->industry }}</strong>
                            </li>
                        @endif
                        
                        @if($company->company_size)
                            <li class="mb-3">
                                <i class="fas fa-users me-2 text-muted"></i>
                                <span class="text-muted">Company Size:</span>
                                <br>
                                <strong>{{ $company->company_size }}</strong>
                            </li>
                        @endif

                        @if($company->founded_year)
                            <li class="mb-3">
                                <i class="fas fa-calendar-alt me-2 text-muted"></i>
                                <span class="text-muted">Founded:</span>
                                <br>
                                <strong>{{ $company->founded_year }}</strong>
                            </li>
                        @endif

                        @if($company->email)
                            <li>
                                <i class="fas fa-envelope me-2 text-muted"></i>
                                <span class="text-muted">Email:</span>
                                <br>
                                <strong>{{ $company->email }}</strong>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Social Links -->
            @if($company->linkedin_url || $company->twitter_url || $company->facebook_url)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Connect With Us</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            @if($company->linkedin_url)
                                <a href="{{ $company->linkedin_url }}" target="_blank" 
                                    class="btn btn-outline-primary">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                            @endif
                            
                            @if($company->twitter_url)
                                <a href="{{ $company->twitter_url }}" target="_blank" 
                                    class="btn btn-outline-primary">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            @endif
                            
                            @if($company->facebook_url)
                                <a href="{{ $company->facebook_url }}" target="_blank" 
                                    class="btn btn-outline-primary">
                                    <i class="fab fa-facebook"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 