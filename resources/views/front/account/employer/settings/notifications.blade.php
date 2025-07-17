@extends('front.layouts.employer-layout')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card bg-white rounded-3 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 mb-2">Notification Settings</h1>
                        <p class="text-muted mb-0">Manage your notification preferences</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" form="notificationForm" class="btn btn-primary d-flex align-items-center">
                            <i class="bi bi-save me-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Settings Navigation -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a href="{{ route('employer.settings.index') }}" class="nav-link">
                        <i class="bi bi-gear me-2"></i>General
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('employer.settings.notifications') }}" class="nav-link active">
                        <i class="bi bi-bell me-2"></i>Notifications
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('employer.settings.security') }}" class="nav-link">
                        <i class="bi bi-shield-lock me-2"></i>Security
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="row g-4">
        <!-- Main Settings Form -->
        <div class="col-12 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Notification Preferences</h5>
                            <div class="small text-muted">Choose how you want to be notified</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="notificationForm" action="{{ route('employer.settings.notifications.update') }}" method="POST">
                        @csrf
                        <!-- Application Notifications -->
                        <div class="settings-section mb-4">
                            <h6 class="settings-title mb-3">Application Notifications</h6>
                            <div class="settings-content">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="newApplications" 
                                           name="notification_preferences[new_applications]" value="1" 
                                           {{ isset($employer->notification_preferences['new_applications']) && $employer->notification_preferences['new_applications'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="newApplications">
                                        New job applications
                                    </label>
                                    <div class="small text-muted">Get notified when candidates apply to your jobs</div>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="applicationUpdates" 
                                           name="notification_preferences[application_updates]" value="1" 
                                           {{ isset($employer->notification_preferences['application_updates']) && $employer->notification_preferences['application_updates'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="applicationUpdates">
                                        Application status updates
                                    </label>
                                    <div class="small text-muted">Receive notifications about application status changes</div>
                                </div>
                            </div>
                        </div>

                        <!-- Message Notifications -->
                        <div class="settings-section mb-4">
                            <h6 class="settings-title mb-3">Message Notifications</h6>
                            <div class="settings-content">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="newMessages" 
                                           name="notification_preferences[new_messages]" value="1" 
                                           {{ isset($employer->notification_preferences['new_messages']) && $employer->notification_preferences['new_messages'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="newMessages">
                                        New messages
                                    </label>
                                    <div class="small text-muted">Get notified when you receive new messages</div>
                                </div>
                            </div>
                        </div>

                        <!-- Job Notifications -->
                        <div class="settings-section">
                            <h6 class="settings-title mb-3">Job Notifications</h6>
                            <div class="settings-content">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="jobExpiry" 
                                           name="notification_preferences[job_expiry]" value="1" 
                                           {{ isset($employer->notification_preferences['job_expiry']) && $employer->notification_preferences['job_expiry'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="jobExpiry">
                                        Job expiry reminders
                                    </label>
                                    <div class="small text-muted">Get notified before your job posts expire</div>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="jobPerformance" 
                                           name="notification_preferences[job_performance]" value="1" 
                                           {{ isset($employer->notification_preferences['job_performance']) && $employer->notification_preferences['job_performance'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="jobPerformance">
                                        Job performance updates
                                    </label>
                                    <div class="small text-muted">Receive weekly performance reports for your active jobs</div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="col-12 col-xl-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4">
                    <h5 class="card-title mb-1">Quick Links</h5>
                    <div class="small text-muted">Frequently accessed settings</div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('employer.settings.index') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-gear me-3"></i>
                            General Settings
                        </a>
                        <a href="{{ route('employer.settings.notifications') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center active">
                            <i class="bi bi-bell me-3"></i>
                            Notification Preferences
                        </a>
                        <a href="{{ route('employer.settings.security') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-shield-lock me-3"></i>
                            Security Settings
                        </a>
                        <a href="{{ route('employer.profile.edit') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-building me-3"></i>
                            Company Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 