@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- General Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">General Settings</h5>
                    <div class="small text-muted">Configure basic site information and branding</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.updateGeneral') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="site_name" class="form-label">Site Name</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" 
                                value="{{ $settings['general']['site_name'] }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="site_description" class="form-label">Site Description</label>
                            <textarea class="form-control" id="site_description" name="site_description" rows="3" 
                                required>{{ $settings['general']['site_description'] }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="contact_email" class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" 
                                value="{{ $settings['general']['contact_email'] }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save General Settings</button>
                    </form>
                </div>
            </div>

            <!-- Platform Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Platform Settings</h5>
                    <div class="small text-muted">Configure platform behavior and user permissions</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.updatePlatform') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                    {{ $settings['platform']['maintenance_mode'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="maintenance_mode">Maintenance Mode</label>
                                <div class="small text-muted">Temporarily disable the platform for maintenance</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="user_registration" name="user_registration" 
                                    {{ $settings['platform']['user_registration'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="user_registration">User Registration</label>
                                <div class="small text-muted">Allow new users to register on the platform</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="kyc_required" name="kyc_required" 
                                    {{ $settings['platform']['kyc_required'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="kyc_required">KYC Required</label>
                                <div class="small text-muted">Require identity verification for all users</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" 
                                    {{ $settings['platform']['email_notifications'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_notifications">Email Notifications</label>
                                <div class="small text-muted">Send email notifications to users</div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Platform Settings</button>
                    </form>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Limits & Security</h5>
                    <div class="small text-muted">Configure platform limits and security settings</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.updateSecurity') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="max_job_postings" class="form-label">Max Job Postings per Employer</label>
                            <input type="number" class="form-control" id="max_job_postings" name="max_job_postings" 
                                value="{{ $settings['security']['max_job_postings'] }}" min="1" max="1000" required>
                        </div>
                        <div class="mb-3">
                            <label for="session_timeout" class="form-label">Session Timeout (minutes)</label>
                            <input type="number" class="form-control" id="session_timeout" name="session_timeout" 
                                value="{{ $settings['security']['session_timeout'] }}" min="1" max="1440" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Security Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 