@extends('layouts.jobseeker')

@section('page-title', 'Submit Required Documents')

@section('jobseeker-content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('account.myJobApplications') }}">My Applications</a></li>
                    <li class="breadcrumb-item active">Submit Documents</li>
                </ol>
            </nav>

            <!-- Header Card -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-file-upload fa-lg"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">Submit Required Documents</h4>
                            <p class="text-muted mb-0">{{ $application->job->title }}</p>
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Your application for <strong>{{ $application->job->title }}</strong> at
                        <strong>{{ $application->job->employer->employerProfile->company_name ?? 'Company' }}</strong>
                        has been approved! Please submit the required documents below to proceed to the next stage.
                    </div>
                </div>
            </div>

            <!-- Progress Indicator -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-3">Application Progress</h6>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 50%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 small">
                        <span class="text-success"><i class="fas fa-check-circle me-1"></i>Application</span>
                        <span class="text-primary fw-bold"><i class="fas fa-circle me-1"></i>Documents</span>
                        <span class="text-muted">Interview</span>
                        <span class="text-muted">Hired</span>
                    </div>
                </div>
            </div>

            <!-- Documents Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i>Required Documents</h5>
                </div>
                <div class="card-body">
                    @if($application->job->jobRequirements->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                            <h5>No Documents Required</h5>
                            <p class="text-muted">This job does not require any additional documents.</p>
                            <form action="{{ route('job.submitRequirements.process', $application->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-arrow-right me-2"></i>Continue to Next Stage
                                </button>
                            </form>
                        </div>
                    @else
                        <form action="{{ route('job.submitRequirements.process', $application->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="alert alert-warning mb-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important:</strong> Please ensure all documents are clear and legible. Accepted formats: PDF, DOC, DOCX, JPG, PNG (max 10MB each).
                            </div>

                            @foreach($application->job->jobRequirements as $requirement)
                                <div class="mb-4 p-3 border rounded {{ $requirement->is_required ? 'border-primary' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                {{ $requirement->name }}
                                                @if($requirement->is_required)
                                                    <span class="badge bg-danger ms-2">Required</span>
                                                @else
                                                    <span class="badge bg-secondary ms-2">Optional</span>
                                                @endif
                                            </h6>
                                            @if($requirement->description)
                                                <p class="text-muted small mb-0">{{ $requirement->description }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <input type="file"
                                               class="form-control @error('requirement_' . $requirement->id) is-invalid @enderror"
                                               name="requirement_{{ $requirement->id }}"
                                               id="requirement_{{ $requirement->id }}"
                                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                               {{ $requirement->is_required ? 'required' : '' }}>

                                        @error('requirement_' . $requirement->id)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <div class="form-text">
                                            Accepted: PDF, DOC, DOCX, JPG, PNG (max 10MB)
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <hr class="my-4">

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('account.myJobApplications') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Applications
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i>Submit Documents
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Tips Card -->
            <div class="card mt-4 border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6><i class="fas fa-lightbulb text-warning me-2"></i>Tips for Document Submission</h6>
                    <ul class="mb-0 small text-muted">
                        <li>Ensure all documents are recent and valid</li>
                        <li>Scan or photograph documents clearly without blur</li>
                        <li>Make sure all text is readable</li>
                        <li>File names should not contain special characters</li>
                        <li>If a document has multiple pages, combine them into a single PDF</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
