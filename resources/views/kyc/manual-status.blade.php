@extends('front.layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('kyc.start.form') }}">KYC Verification</a></li>
                    <li class="breadcrumb-item active">Submission Status</li>
                </ol>
            </nav>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Manual Verification Status
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Current KYC Status -->
                    <div class="alert
                        @if($user->kyc_status === 'verified') alert-success
                        @elseif($user->kyc_status === 'pending_review') alert-info
                        @elseif($user->kyc_status === 'failed') alert-danger
                        @else alert-secondary
                        @endif mb-4">
                        <h5 class="alert-heading">
                            @if($user->kyc_status === 'verified')
                                <i class="fas fa-check-circle me-2"></i>Verified
                            @elseif($user->kyc_status === 'pending_review')
                                <i class="fas fa-hourglass-half me-2"></i>Pending Review
                            @elseif($user->kyc_status === 'failed')
                                <i class="fas fa-times-circle me-2"></i>Verification Failed
                            @else
                                <i class="fas fa-clock me-2"></i>{{ ucfirst(str_replace('_', ' ', $user->kyc_status)) }}
                            @endif
                        </h5>
                        <p class="mb-0">
                            @if($user->kyc_status === 'verified')
                                Your identity has been verified. You now have full access to all features.
                            @elseif($user->kyc_status === 'pending_review')
                                Your documents are being reviewed by our team. This typically takes 1-3 business days.
                            @elseif($user->kyc_status === 'failed')
                                Your verification was unsuccessful. Please review the rejection reason below and try again.
                            @else
                                Your verification status: {{ ucfirst(str_replace('_', ' ', $user->kyc_status)) }}
                            @endif
                        </p>
                    </div>

                    @if($submissions->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open text-muted" style="font-size: 4rem;"></i>
                            <h5 class="mt-3">No Submissions Found</h5>
                            <p class="text-muted">You haven't submitted any manual verification documents yet.</p>
                            <a href="{{ route('kyc.manual.form') }}" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>Submit Documents
                            </a>
                        </div>
                    @else
                        <h5 class="mb-3">Your Submissions</h5>

                        @foreach($submissions as $submission)
                            <div class="card mb-3
                                @if($submission->status === 'verified') border-success
                                @elseif($submission->status === 'pending') border-warning
                                @elseif($submission->status === 'rejected') border-danger
                                @endif">
                                <div class="card-header d-flex justify-content-between align-items-center
                                    @if($submission->status === 'verified') bg-success text-white
                                    @elseif($submission->status === 'pending') bg-warning
                                    @elseif($submission->status === 'rejected') bg-danger text-white
                                    @endif">
                                    <span>
                                        <strong>{{ $submission->document_type }}</strong>
                                    </span>
                                    <span class="badge
                                        @if($submission->status === 'verified') bg-light text-success
                                        @elseif($submission->status === 'pending') bg-dark text-white
                                        @elseif($submission->status === 'rejected') bg-light text-danger
                                        @endif">
                                        {{ ucfirst($submission->status) }}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Document Number:</strong></p>
                                            <p class="text-muted">{{ $submission->document_number }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Submitted:</strong></p>
                                            <p class="text-muted">{{ $submission->created_at->format('M d, Y \a\t g:i A') }}</p>
                                        </div>
                                    </div>

                                    @if($submission->status === 'rejected' && $submission->rejection_reason)
                                        <div class="alert alert-danger mt-3 mb-0">
                                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Rejection Reason:</h6>
                                            <p class="mb-0">{{ $submission->rejection_reason }}</p>
                                        </div>
                                    @endif

                                    <!-- Document Preview Links -->
                                    @php
                                        $files = json_decode($submission->document_file, true);
                                    @endphp

                                    <div class="mt-3">
                                        <p class="mb-2"><strong>Uploaded Documents:</strong></p>
                                        <div class="btn-group" role="group">
                                            @if(is_array($files))
                                                @if(!empty($files['front']))
                                                    <a href="{{ route('kyc.manual.view', [$submission, 'front']) }}"
                                                       class="btn btn-outline-secondary btn-sm" target="_blank">
                                                        <i class="fas fa-image me-1"></i>Front
                                                    </a>
                                                @endif
                                                @if(!empty($files['back']))
                                                    <a href="{{ route('kyc.manual.view', [$submission, 'back']) }}"
                                                       class="btn btn-outline-secondary btn-sm" target="_blank">
                                                        <i class="fas fa-image me-1"></i>Back
                                                    </a>
                                                @endif
                                                @if(!empty($files['selfie']))
                                                    <a href="{{ route('kyc.manual.view', [$submission, 'selfie']) }}"
                                                       class="btn btn-outline-secondary btn-sm" target="_blank">
                                                        <i class="fas fa-camera me-1"></i>Selfie
                                                    </a>
                                                @endif
                                            @else
                                                <a href="{{ route('kyc.manual.view', [$submission, 'front']) }}"
                                                   class="btn btn-outline-secondary btn-sm" target="_blank">
                                                    <i class="fas fa-file me-1"></i>View Document
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    @if($submission->status === 'pending')
                                        <div class="mt-3 pt-3 border-top">
                                            <form action="{{ route('kyc.manual.cancel', $submission) }}" method="POST"
                                                  class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this submission?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-times me-1"></i>Cancel Submission
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <!-- Submit New if last was rejected -->
                        @if($submissions->first()->status === 'rejected')
                            <div class="text-center mt-4">
                                <a href="{{ route('kyc.manual.form') }}" class="btn btn-primary">
                                    <i class="fas fa-redo me-2"></i>Submit New Documents
                                </a>
                            </div>
                        @endif
                    @endif

                    <!-- Back Link -->
                    <div class="text-center mt-4 pt-4 border-top">
                        <a href="{{ route('kyc.start.form') }}" class="btn btn-link">
                            <i class="fas fa-arrow-left me-2"></i>
                            Back to Verification Options
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
