@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">User Profile</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                        <li class="breadcrumb-item active">{{ $user->name }}</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Users
                </a>
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Edit User
                </a>
            </div>
        </div>

        <div class="row">
            <!-- User Info Card -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @php
                                $mobile = $user->phone ?? 'N/A';
                                $designation = 'N/A';
                                $profilePhoto = $user->profile_image; // Accessor defaults

                                if ($user->hasRole('jobseeker')) {
                                    $profile = $user->jobSeekerProfile;
                                    if ($profile) {
                                        // Fallback to user phone if profile phone is missing
                                        $mobile = $profile->phone ?? $user->phone ?? 'N/A';
                                        $designation = $profile->current_job_title ?? 'N/A';
                                        if ($profile->profile_photo) {
                                            $profilePhoto = asset('storage/' . $profile->profile_photo);
                                        }
                                    }
                                } elseif ($user->hasRole('employer')) {
                                    $profile = $user->employerProfile;
                                    if ($profile) {
                                        $mobile = $profile->business_phone ?? $user->phone ?? 'N/A';
                                        $designation = $profile->contact_person_designation ?? 'N/A';
                                        if ($profile->company_logo) {
                                            $profilePhoto = asset('storage/' . $profile->company_logo);
                                        }
                                    }
                                }
                            @endphp

                            @if($profilePhoto)
                                <img src="{{ $profilePhoto }}" alt="{{ $user->name }}" class="rounded-circle"
                                    style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #f0f0f0;">
                            @else
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                    style="width: 120px; height: 120px; font-size: 3rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <h4>{{ $user->name }}</h4>
                        <p class="text-muted">{{ $user->email }}</p>
                        <span
                            class="badge bg-{{ $user->role === 'employer' ? 'primary' : ($user->role === 'jobseeker' ? 'success' : 'danger') }}">
                            {{ ucfirst($user->role) }}
                        </span>
                        @if($user->email_verified_at)
                            <span class="badge bg-success">Verified</span>
                        @else
                            <span class="badge bg-warning">Unverified</span>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">User Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th>User ID:</th>
                                <td>{{ $user->id }}</td>
                            </tr>
                            <tr>
                                <th>Mobile:</th>
                                <td>{{ $mobile }}</td>
                            </tr>
                            <tr>
                                <th>Designation:</th>
                                <td>{{ $designation }}</td>
                            </tr>
                            <tr>
                                <th>KYC Status:</th>
                                <td>
                                    <span
                                        class="badge bg-{{ $user->kyc_status === 'verified' ? 'success' : ($user->kyc_status === 'in_progress' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst(str_replace('_', ' ', $user->kyc_status)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Registered:</th>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                            </tr>
                            <tr>
                                <th>Last Login:</th>
                                <td>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Activity Section -->
            <div class="col-md-8">
                @if($user->role === 'employer' && count($jobs) > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Posted Jobs ({{ count($jobs) }})</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Job Title</th>
                                            <th>Category</th>
                                            <th>Type</th>
                                            <th>Applications</th>
                                            <th>Status</th>
                                            <th>Posted</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($jobs as $job)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.jobs.show', $job->id) }}">{{ $job->title }}</a>
                                                </td>
                                                <td>{{ $job->category->name ?? 'N/A' }}</td>
                                                <td>{{ $job->jobType->name ?? 'N/A' }}</td>
                                                <td>{{ $job->applications_count }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $job->status == 1 ? 'success' : 'secondary' }}">
                                                        {{ $job->status == 1 ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>{{ $job->created_at->format('M d, Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                @if($user->role === 'jobseeker' && count($applications) > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Job Applications ({{ count($applications) }})</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Job Title</th>
                                            <th>Company</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Applied</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($applications as $application)
                                            <tr>
                                                <td>
                                                    <a
                                                        href="{{ route('admin.jobs.show', $application->job_id) }}">{{ $application->job->title }}</a>
                                                </td>
                                                <td>{{ $application->job->employer->name ?? 'N/A' }}</td>
                                                <td>{{ $application->job->jobType->name ?? 'N/A' }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $application->status === 'pending' ? 'warning' : ($application->status === 'accepted' ? 'success' : 'danger') }}">
                                                        {{ ucfirst($application->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $application->created_at->format('M d, Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                @if(($user->role === 'employer' && count($jobs) === 0) || ($user->role === 'jobseeker' && count($applications) === 0))
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5>No Activity Yet</h5>
                            <p class="text-muted">This user hasn't
                                {{ $user->role === 'employer' ? 'posted any jobs' : 'applied to any jobs' }} yet.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection