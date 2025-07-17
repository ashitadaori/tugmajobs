@extends('front.layouts.app')

@section('content')
<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Delete Profile</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                @include('front.account.sidebar')
            </div>
            <div class="col-lg-9">
                <div class="card border-0 shadow mb-4">
                    <div class="card-body p-4">
                        <h3 class="h4 mb-4">Delete Profile</h3>
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle"></i> Warning!</h5>
                            <p>This action cannot be undone. Once you delete your profile, all of your data will be permanently removed, including:</p>
                            <ul>
                                <li>Your personal information</li>
                                <li>Your job applications</li>
                                <li>Your saved jobs</li>
                                <li>Your job alerts</li>
                                <li>Your uploaded resumes</li>
                            </ul>
                        </div>
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
                        <form action="{{ route('account.delete-account') }}" method="post" onsubmit="return confirm('Are you absolutely sure you want to delete your profile? This action cannot be undone.');">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Enter Your Password to Confirm</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                                        @error('password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="confirm_delete" name="confirm_delete" required>
                                        <label class="form-check-label" for="confirm_delete">
                                            I understand that this action cannot be undone and all my data will be permanently deleted.
                                        </label>
                                    </div>
                                    <button type="submit" class="btn btn-danger">Delete My Profile</button>
                                    <a href="{{ route('account.dashboard') }}" class="btn btn-secondary ms-2">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection 