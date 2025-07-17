@extends('front.layouts.app')

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="auth-wrapper">
                    <div class="auth-form-side w-100">
                        <div class="auth-form-content">
                            <div class="logo-section mb-4">
                                <i class="fas fa-lock"></i>
                            </div>
                            <h1>Reset Password</h1>
                            <p class="subtitle">Enter your email to receive reset instructions</p>

                            @if(Session::has('success'))
                            <div class="alert alert-success">
                                {{ Session::get('success') }}
                            </div>
                            @endif

                            @if(Session::has('error'))
                            <div class="alert alert-danger">
                                {{ Session::get('error') }}
                            </div>
                            @endif

                            <form action="{{ route('account.processForgotPassword') }}" method="post">
                                @csrf
                                <div class="mb-4">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <input type="email" name="email" id="email" 
                                            class="form-control @error('email') is-invalid @enderror" 
                                            placeholder="name@example.com" value="{{ old('email') }}">
                                        <span class="input-icon">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="auth-btn">
                                    Send Reset Link
                                </button>

                                <div class="auth-footer mt-4">
                                    Remember your password? <a href="{{ route('login') }}">Login here</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Reuse the same styles from registration.blade.php */
.auth-section {
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 2rem 0;
    background: #f5f7fa;
}

.auth-wrapper {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.auth-form-side {
    padding: 3rem;
}

.auth-form-content {
    max-width: 400px;
    margin: 0 auto;
}

.logo-section {
    text-align: center;
    font-size: 2rem;
    color: #1e2a78;
}

.auth-form-content h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-align: center;
}

.subtitle {
    text-align: center;
    color: #6c757d;
    margin-bottom: 2rem;
}

.auth-btn {
    width: 100%;
    padding: 0.8rem;
    background: #1e2a78;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    margin-top: 1rem;
    transition: all 0.3s;
}

.auth-btn:hover {
    background: #161f5c;
}

.auth-footer {
    text-align: center;
    margin-top: 2rem;
}

.auth-footer a {
    color: #1e2a78;
    text-decoration: none;
    font-weight: 600;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.input-group {
    position: relative;
}

.input-icon {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    z-index: 4;
}

.form-control {
    padding-right: 3rem;
}

.form-control:focus {
    border-color: #1e2a78;
    box-shadow: none;
}
</style>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title pb-0" id="exampleModalLabel">Change Profile Picture</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form>
            <div class="mb-3">
                <label for="exampleInputEmail1" class="form-label">Profile Image</label>
                <input type="file" class="form-control" id="image"  name="image">
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary mx-3">Update</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </form>
      </div>
    </div>
  </div>
</div>

@endsection

{{-- Custom Js start --}}
@section('customJs')
    <script>

    </script>
@endsection
