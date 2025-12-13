@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Maintenance Mode</h1>
            <p class="text-muted">Control maintenance notifications for different user types</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.maintenance.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Jobseeker Maintenance -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Job Seeker Maintenance</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="jobseeker_active" 
                                   name="jobseeker_active" value="1"
                                   {{ $jobseekerMaintenance && $jobseekerMaintenance->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="jobseeker_active">
                                <strong>Enable Maintenance Mode</strong>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label for="jobseeker_message" class="form-label">Maintenance Message</label>
                            <textarea class="form-control @error('jobseeker_message') is-invalid @enderror" 
                                      id="jobseeker_message" name="jobseeker_message" rows="4" 
                                      required>{{ old('jobseeker_message', $jobseekerMaintenance->message ?? '') }}</textarea>
                            @error('jobseeker_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">This message will be shown to job seekers when maintenance is active</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Status:</strong> 
                            @if($jobseekerMaintenance && $jobseekerMaintenance->is_active)
                                <span class="badge bg-warning text-dark">Maintenance Active</span>
                            @else
                                <span class="badge bg-success">Normal Operation</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employer Maintenance -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-building me-2"></i>Employer Maintenance</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="employer_active" 
                                   name="employer_active" value="1"
                                   {{ $employerMaintenance && $employerMaintenance->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="employer_active">
                                <strong>Enable Maintenance Mode</strong>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label for="employer_message" class="form-label">Maintenance Message</label>
                            <textarea class="form-control @error('employer_message') is-invalid @enderror" 
                                      id="employer_message" name="employer_message" rows="4" 
                                      required>{{ old('employer_message', $employerMaintenance->message ?? '') }}</textarea>
                            @error('employer_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">This message will be shown to employers when maintenance is active</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Status:</strong> 
                            @if($employerMaintenance && $employerMaintenance->is_active)
                                <span class="badge bg-warning text-dark">Maintenance Active</span>
                            @else
                                <span class="badge bg-success">Normal Operation</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-save me-2"></i>Save Maintenance Settings
            </button>
        </div>
    </form>

    <!-- Information Card -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-lightbulb me-2"></i>How It Works</h5>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li><strong>Admins are never affected</strong> - You can always access the system</li>
                <li><strong>Users see a banner notification</strong> - They can still use the system but will see your maintenance message</li>
                <li><strong>Independent control</strong> - Enable maintenance for job seekers and employers separately</li>
                <li><strong>Customizable messages</strong> - Tailor the message for each user type</li>
                <li><strong>Instant updates</strong> - Changes take effect immediately</li>
            </ul>
        </div>
    </div>
</div>
@endsection
