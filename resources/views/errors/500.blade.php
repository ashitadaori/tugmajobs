@extends('front.layouts.app')

@section('content')
    <section class="section-5 py-5">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 text-center">
                    <div class="error-illustration mb-4">
                        <i class="fas fa-server fa-5x text-danger mb-3"></i>
                    </div>
                    <h1 class="display-4 fw-bold mb-3">500</h1>
                    <h2 class="h3 mb-4">Server Error</h2>
                    <p class="lead text-muted mb-5">
                        Oops! Something went wrong on our end. We're working to fix it. Please try again later.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i> Go Home
                        </a>
                        <a href="{{ url()->current() }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-redo me-2"></i> Try Again
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection