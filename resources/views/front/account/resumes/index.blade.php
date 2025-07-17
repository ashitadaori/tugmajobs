@extends('front.layouts.app')

@section('content')
<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Resumes</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                @include('components.sidebar')
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="card border-0 shadow mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="fs-4 mb-0">My Resumes</h3>
                            <button class="btn btn-primary">Upload Resume</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection 