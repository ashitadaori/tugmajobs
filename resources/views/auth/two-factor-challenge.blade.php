@extends('layouts.app')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Two-Factor Authentication</h4>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div id="code-form">
                        <p class="text-muted mb-4">
                            Please enter the 6-digit verification code sent to your email address.
                        </p>

                        <form method="POST" action="{{ route('two-factor.verify') }}">
                            @csrf
                            <div class="mb-4">
                                <label for="code" class="form-label">Verification Code</label>
                                <input type="text"
                                       class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                                       id="code"
                                       name="code"
                                       maxlength="6"
                                       pattern="\d{6}"
                                       placeholder="000000"
                                       autofocus
                                       autocomplete="one-time-code">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-check me-2"></i>Verify Code
                            </button>
                        </form>

                        <div class="text-center">
                            <form method="POST" action="{{ route('two-factor.resend') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link text-decoration-none">
                                    <i class="fas fa-redo me-1"></i>Resend Code
                                </button>
                            </form>
                        </div>

                        <hr class="my-4">

                        <div class="text-center">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleRecoveryForm()">
                                <i class="fas fa-key me-1"></i>Use Recovery Code Instead
                            </button>
                        </div>
                    </div>

                    <div id="recovery-form" style="display: none;">
                        <p class="text-muted mb-4">
                            Enter one of your recovery codes to access your account.
                        </p>

                        <form method="POST" action="{{ route('two-factor.verify-recovery') }}">
                            @csrf
                            <div class="mb-4">
                                <label for="recovery_code" class="form-label">Recovery Code</label>
                                <input type="text"
                                       class="form-control @error('recovery_code') is-invalid @enderror"
                                       id="recovery_code"
                                       name="recovery_code"
                                       placeholder="XXXXX-XXXXX">
                                @error('recovery_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-check me-2"></i>Verify Recovery Code
                            </button>
                        </form>

                        <div class="text-center">
                            <button type="button" class="btn btn-link text-decoration-none" onclick="toggleRecoveryForm()">
                                <i class="fas fa-arrow-left me-1"></i>Back to Verification Code
                            </button>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <a href="{{ route('login') }}" class="text-muted">
                            <i class="fas fa-sign-out-alt me-1"></i>Cancel and Return to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleRecoveryForm() {
    const codeForm = document.getElementById('code-form');
    const recoveryForm = document.getElementById('recovery-form');

    if (codeForm.style.display === 'none') {
        codeForm.style.display = 'block';
        recoveryForm.style.display = 'none';
    } else {
        codeForm.style.display = 'none';
        recoveryForm.style.display = 'block';
    }
}
</script>
@endsection
