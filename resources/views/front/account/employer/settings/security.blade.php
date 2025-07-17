@extends('front.layouts.employer-layout')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card bg-white rounded-3 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 mb-2">Security Settings</h1>
                        <p class="text-muted mb-0">Manage your account security and privacy</p>
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
                    <a href="{{ route('employer.settings.notifications') }}" class="nav-link">
                        <i class="bi bi-bell me-2"></i>Notifications
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('employer.settings.security') }}" class="nav-link active">
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
                            <h5 class="card-title mb-1">Security Settings</h5>
                            <div class="small text-muted">Manage your password and account security</div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Password Change Form -->
                    <form action="{{ route('employer.settings.password.update') }}" method="POST" class="mb-4">
                        @csrf
                        <div class="settings-section mb-4">
                            <h6 class="settings-title mb-3">Change Password</h6>
                            <div class="settings-content">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">Current Password</label>
                                            <input type="password" name="current_password" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">New Password</label>
                                            <input type="password" name="new_password" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Confirm New Password</label>
                                            <input type="password" name="new_password_confirmation" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">Update Password</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Two-Factor Authentication -->
                    <div class="settings-section">
                        <h6 class="settings-title mb-3">Two-Factor Authentication</h6>
                        <div class="settings-content">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <p class="mb-1">Two-factor authentication is {{ $employer->two_factor_enabled ? 'enabled' : 'disabled' }}</p>
                                    <small class="text-muted">Add an extra layer of security to your account</small>
                                </div>
                                @if($employer->two_factor_enabled)
                                    <form action="{{ route('employer.settings.2fa.disable') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Disable 2FA</button>
                                    </form>
                                @else
                                    <form action="{{ route('employer.settings.2fa.enable') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Enable 2FA</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
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
                           class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-bell me-3"></i>
                            Notification Preferences
                        </a>
                        <a href="{{ route('employer.settings.security') }}" 
                           class="list-group-item list-group-item-action d-flex align-items-center active">
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

@push('styles')
<style>
.welcome-card {
    background: linear-gradient(to right, var(--bs-primary-bg-subtle), var(--bs-white));
    border-left: 4px solid var(--bs-primary);
}

.nav-pills .nav-link {
    color: var(--bs-gray-700);
}

.nav-pills .nav-link.active {
    background-color: var(--bs-primary);
}
</style>
@endpush 