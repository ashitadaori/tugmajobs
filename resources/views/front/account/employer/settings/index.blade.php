@extends('front.layouts.employer-layout')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card bg-white rounded-3 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 mb-2">Account Settings</h1>
                        <p class="text-muted mb-0">Manage your account preferences and settings</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" form="settingsForm" class="btn btn-primary d-flex align-items-center">
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
                    <a href="{{ route('employer.settings.index') }}" class="nav-link active">
                        <i class="bi bi-gear me-2"></i>General
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('employer.settings.notifications') }}" class="nav-link">
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
                            <h5 class="card-title mb-1">Settings Overview</h5>
                            <div class="small text-muted">Configure your account preferences</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="settingsForm" action="{{ route('employer.settings.update') }}" method="POST">
                        @csrf
                        <!-- Email Preferences -->
                        <div class="settings-section mb-4">
                            <h6 class="settings-title mb-3">Email Preferences</h6>
                            <div class="settings-content">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="emailNotifications" 
                                           name="email_notifications" value="1" 
                                           {{ ($employer->settings['email_notifications'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="emailNotifications">
                                        Receive email notifications
                                    </label>
                                    <div class="small text-muted">Get notified about new applications and messages</div>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="marketingEmails" 
                                           name="marketing_emails" value="1" 
                                           {{ ($employer->settings['marketing_emails'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="marketingEmails">
                                        Receive marketing emails
                                    </label>
                                    <div class="small text-muted">Stay updated with our latest features and news</div>
                                </div>
                            </div>
                        </div>

                        <!-- Localization Settings -->
                        <div class="settings-section mb-4">
                            <h6 class="settings-title mb-3">Localization</h6>
                            <div class="settings-content">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Language</label>
                                            <select name="language" class="form-select">
                                                <option value="en" {{ ($employer->settings['language'] ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                                                <option value="es" {{ ($employer->settings['language'] ?? 'en') == 'es' ? 'selected' : '' }}>Spanish</option>
                                                <option value="fr" {{ ($employer->settings['language'] ?? 'en') == 'fr' ? 'selected' : '' }}>French</option>
                                            </select>
                                            <div class="small text-muted mt-1">Choose your preferred language</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Timezone</label>
                                            <select name="timezone" class="form-select">
                                                @foreach(timezone_identifiers_list() as $timezone)
                                                    <option value="{{ $timezone }}" {{ ($employer->settings['timezone'] ?? config('app.timezone')) == $timezone ? 'selected' : '' }}>
                                                        {{ $timezone }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="small text-muted mt-1">Set your local timezone</div>
                                        </div>
                                    </div>
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
                           class="list-group-item list-group-item-action d-flex align-items-center active">
                            <i class="bi bi-gear me-3"></i>
                            General Settings
                        </a>
                        <a href="{{ route('employer.settings.notifications') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center">
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