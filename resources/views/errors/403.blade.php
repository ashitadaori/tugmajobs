@extends('front.layouts.app')

@section('content')
    <section class="section-5 py-5">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 text-center">
                    <div class="error-illustration mb-4">
                        <i class="fas fa-lock fa-5x text-warning mb-3"></i>
                    </div>
                    <h1 class="display-4 fw-bold mb-3">403</h1>
                    <h2 class="h3 mb-4">Access Denied</h2>
                    <p class="lead text-muted mb-5">
                        Sorry, you don't have permission to access this page. It might be restricted to specific user roles.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i> Go Home
                        </a>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i> Go Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection