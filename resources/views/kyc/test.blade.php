@extends('front.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">KYC Test Page</h4>
                </div>
                <div class="card-body">
                    <h5>User Information</h5>
                    <ul>
                        <li><strong>Name:</strong> {{ Auth::user()->name }}</li>
                        <li><strong>Email:</strong> {{ Auth::user()->email }}</li>
                        <li><strong>Role:</strong> {{ Auth::user()->role }}</li>
                        <li><strong>KYC Status:</strong> {{ Auth::user()->kyc_status }}</li>
                    </ul>

                    <h5 class="mt-4">Didit Configuration</h5>
                    <ul>
                        <li><strong>Auth URL:</strong> {{ config('services.didit.auth_url') }}</li>
                        <li><strong>Base URL:</strong> {{ config('services.didit.base_url') }}</li>
                        <li><strong>API Key Set:</strong> {{ !empty(config('services.didit.api_key')) ? 'Yes' : 'No' }}</li>
                        <li><strong>Client ID Set:</strong> {{ !empty(config('services.didit.client_id')) ? 'Yes' : 'No' }}</li>
                        <li><strong>Client Secret Set:</strong> {{ !empty(config('services.didit.client_secret')) ? 'Yes' : 'No' }}</li>
                        <li><strong>Workflow ID Set:</strong> {{ !empty(config('services.didit.workflow_id')) ? 'Yes' : 'No' }}</li>
                        <li><strong>Callback URL:</strong> {{ config('services.didit.callback_url') }}</li>
                        <li><strong>Redirect URL:</strong> {{ config('services.didit.redirect_url') }}</li>
                    </ul>

                    <div class="mt-4">
                        <h5>Test Actions</h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('kyc.start.form') }}" class="btn btn-primary">
                                Go to Start Page
                            </a>
                            <form action="{{ route('kyc.start') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    Start Verification Directly
                                </button>
                            </form>
                            <a href="{{ route('kyc.mock-verify') }}" class="btn btn-warning">
                                Use Mock Verification (No Internet Required)
                            </a>
                        </div>
                    </div>
                    
                    <div class="mt-4 alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>Network Connectivity</h5>
                        <p>Your system is having trouble connecting to external services. This could be due to:</p>
                        <ul>
                            <li>Internet connection issues</li>
                            <li>DNS resolution problems</li>
                            <li>Firewall or proxy settings</li>
                        </ul>
                        <p>For development purposes, you can use the "Mock Verification" option which doesn't require internet connectivity.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection