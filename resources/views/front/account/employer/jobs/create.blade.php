@extends('layouts.employer')

@section('page_title', 'Post New Job')

@push('styles')
<style>
/* Job Creation Form Styling */
.job-form-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.job-create-header {
    margin-bottom: 30px;
}

.page-title {
    font-size: 2rem;
    font-weight: 600;
    color: #333;
}

.page-subtitle {
    color: #666;
    font-size: 1.1rem;
}

.icon-circle {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-item {
    display: flex;
    align-items: center;
}

/* Progress Steps */
.progress-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    padding: 20px;
}

.progress-step {
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.progress-step .step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin: 0 auto 8px;
    transition: all 0.3s ease;
}

.progress-step.active .step-circle {
    background: #007bff;
    color: white;
}

.progress-step.completed .step-circle {
    background: #28a745;
    color: white;
}

.progress-step small {
    color: #6c757d;
    font-weight: 500;
}

.progress-step.active small,
.progress-step.completed small {
    color: #333;
    font-weight: 600;
}

/* Form Card */
.job-form-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border: none;
}

.card-body {
    padding: 40px;
}

/* Wizard Sections */
.wizard-section {
    display: none;
    animation: fadeIn 0.3s ease;
}

.wizard-section:first-child {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Form Elements */
.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.form-control.is-invalid, .form-select.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 5px;
}

/* Buttons */
.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102,126,234,0.4);
}

.btn-outline-secondary {
    border: 2px solid #6c757d;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(40,167,69,0.4);
}

/* Skills Tags */
.skill-tag {
    background: #e3f2fd;
    color: #1976d2;
    border: 1px solid #bbdefb;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    margin: 4px;
}

.skill-tag .btn-close {
    font-size: 0.7rem;
    margin-left: 8px;
}

/* Character Counter */
.character-counter {
    text-align: right;
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 5px;
}

.character-counter.text-danger {
    color: #dc3545 !important;
}

/* Location Button */
#use-current-location {
    border-left: none;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

/* Mapbox Location Autocomplete Styles */
.location-autocomplete-wrapper {
    position: relative;
}

.location-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1050;
    background: white;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 10px 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    max-height: 300px;
    overflow-y: auto;
    display: none;
}

.location-suggestions.show {
    display: block;
}

.location-suggestion-item {
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s ease;
}

.location-suggestion-item:last-child {
    border-bottom: none;
}

.location-suggestion-item:hover,
.location-suggestion-item.active {
    background-color: #f8f9fa;
}

.location-suggestion-item .suggestion-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 2px;
}

.location-suggestion-item .suggestion-address {
    font-size: 0.85rem;
    color: #666;
}

.location-suggestion-item .suggestion-icon {
    color: #007bff;
    margin-right: 10px;
}

.location-loading {
    padding: 12px 16px;
    text-align: center;
    color: #666;
}

.location-loading i {
    margin-right: 8px;
}

.location-no-results {
    padding: 12px 16px;
    text-align: center;
    color: #666;
}

/* Highlight matched text in suggestions */
.location-suggestion-item .highlight {
    background-color: #fff3cd;
    font-weight: 600;
}

/* Preview Section */
.job-preview {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 24px;
    border-left: 4px solid #007bff;
}

.job-preview h5 {
    color: #333;
    font-weight: 600;
    margin-bottom: 16px;
}

.job-preview h6 {
    color: #555;
    font-weight: 600;
    margin-top: 20px;
    margin-bottom: 8px;
}

/* Alert Styling */
.alert {
    border-radius: 10px;
    border: none;
    padding: 16px 20px;
}

.alert-info {
    background: #e3f2fd;
    color: #1565c0;
}

.alert-danger {
    background: #ffebee;
    color: #c62828;
}

/* Responsive Design */
@media (max-width: 768px) {
    .job-form-container {
        padding: 15px;
    }
    
    .card-body {
        padding: 25px;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .icon-circle {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
}

@media (max-width: 576px) {
    .card-body {
        padding: 20px;
    }
    
    .progress-container {
        padding: 15px;
    }
    
    .quick-stats {
        flex-direction: column;
        gap: 10px !important;
    }
}

/* Loading State */
.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.fa-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Preliminary Questions Styling */
.question-item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    position: relative;
}

.question-item .question-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 10px;
}

.question-item .question-number {
    background: #007bff;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: bold;
    margin-right: 10px;
}

.question-item .remove-question {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.question-item .remove-question:hover {
    background: #c82333;
    transform: scale(1.1);
}

.question-type-selector {
    margin-bottom: 10px;
}

.question-type-selector select {
    border: 1px solid #ced4da;
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 0.9rem;
}

.question-input textarea,
.question-input input {
    border: 1px solid #ced4da;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 0.9rem;
    width: 100%;
    margin-bottom: 8px;
}

.question-options {
    margin-top: 10px;
}

.option-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.option-item input {
    flex: 1;
    margin-right: 10px;
    margin-bottom: 0;
}

.option-item .remove-option {
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 0.7rem;
    cursor: pointer;
}

.add-option-btn {
    background: #28a745;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 6px 12px;
    font-size: 0.8rem;
    cursor: pointer;
    margin-top: 5px;
}

.add-option-btn:hover {
    background: #218838;
}

#preliminary_questions_container {
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 20px;
    margin-top: 15px;
}

#preliminary_questions_container.active {
    border-color: #007bff;
    background-color: #f8f9ff;
}

/* Job Requirements Styling */
.requirement-item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    position: relative;
}

.requirement-item .remove-requirement {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.requirement-item .remove-requirement:hover {
    background: #c82333;
    transform: scale(1.1);
}

#job_requirements_container {
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    min-height: 60px;
}

#job_requirements_container:empty::before {
    content: "No document requirements added yet. Click the button below to add requirements.";
    color: #6c757d;
    font-style: italic;
    display: block;
    text-align: center;
    padding: 10px;
}
</style>
@endpush

@section('content')
<div class="job-form-container">
    <!-- Maintenance Notice -->
    @if(\App\Models\MaintenanceSetting::isMaintenanceActive('employer'))
        <div class="alert alert-warning text-center mb-4">
            <i class="bi bi-exclamation-triangle-fill fs-1 d-block mb-3"></i>
            <h4>Feature Under Maintenance</h4>
            <p class="mb-3">{{ \App\Models\MaintenanceSetting::getMaintenanceMessage('employer') }}</p>
            <a href="{{ route('employer.dashboard') }}" class="btn btn-primary">
                <i class="bi bi-house-door me-2"></i>Return to Dashboard
            </a>
        </div>
    @else
    <!-- Enhanced Page Header -->
    <div class="job-create-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-circle me-3">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div>
                        <h1 class="page-title mb-1">Create New Job Posting</h1>
                        <p class="page-subtitle mb-0">Find the perfect candidates for your company</p>
                    </div>
                </div>
                <div class="quick-stats d-flex gap-4">
                    <div class="stat-item">
                        <i class="fas fa-clock text-primary me-2"></i>
                        <span class="text-muted">~5 minutes to complete</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-users text-success me-2"></i>
                        <span class="text-muted">Reach 1000+ candidates</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('employer.jobs.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Back to Jobs
                </a>
            </div>
        </div>
    </div>

    <!-- Progress Indicator -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="progress-container">
                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="d-flex justify-content-between">
                    <div class="progress-step active" data-step="0">
                        <div class="step-circle">1</div>
                        <small>Basic Info</small>
                    </div>
                    <div class="progress-step" data-step="1">
                        <div class="step-circle">2</div>
                        <small>Details</small>
                    </div>
                    <div class="progress-step" data-step="2">
                        <div class="step-circle">3</div>
                        <small>Qualifications</small>
                    </div>
                    <div class="progress-step" data-step="3">
                        <div class="step-circle">4</div>
                        <small>Review</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Form -->
    <div class="card job-form-card">
        <div class="card-body">
            <form id="jobForm" action="{{ route('employer.jobs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Global Error Display -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                

                
                <!-- Step 1: Basic Information -->
                <div class="wizard-section" id="step-1">
                    <h4 class="mb-4">Basic Job Information</h4>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Job Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" required 
                                       placeholder="e.g. Senior Software Developer" value="{{ old('title') }}">
                                @error('title')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="job_type_id" class="form-label">Job Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="job_type_id" name="job_type_id" required>
                                    <option value="">Select Job Type</option>
                                    @if(isset($jobTypes) && $jobTypes->count() > 0)
                                        @foreach($jobTypes as $jobType)
                                            <option value="{{ $jobType->id }}" {{ old('job_type_id') == $jobType->id ? 'selected' : '' }}>
                                                {{ $jobType->name }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>No job types available</option>
                                    @endif
                                </select>
                                @error('job_type_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @if(isset($categories) && $categories->count() > 0)
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>No categories available</option>
                                    @endif
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vacancy" class="form-label">Number of Positions <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="vacancy" name="vacancy" required 
                                       min="1" max="100" value="{{ old('vacancy', 1) }}">
                                @error('vacancy')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Job Location <span class="text-danger">*</span></label>
                        <div class="location-autocomplete-wrapper">
                            <div class="input-group">
                                <input type="text" class="form-control" id="location" name="location" required
                                       placeholder="Start typing to search locations in Sta. Cruz..."
                                       value="{{ old('location') }}"
                                       autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="use-current-location">
                                    <i class="fas fa-map-marker-alt"></i> Use Current Location
                                </button>
                            </div>
                            <div class="location-suggestions" id="location-suggestions">
                                <!-- Suggestions will be populated here -->
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle me-1"></i>Search for barangays, streets, or landmarks in Sta. Cruz, Davao del Sur
                        </small>
                        <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
                        <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">
                        @error('location')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Remote Work Options</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_remote" name="is_remote" value="1" 
                                           {{ old('is_remote') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_remote">
                                        This is a remote position
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Featured Job</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" 
                                           {{ old('is_featured') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">
                                        Make this job featured (additional cost applies)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary btn-next-step">
                            Next Step <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Job Details -->
                <div class="wizard-section" id="step-2" style="display: none;">
                    <h4 class="mb-4">Job Details</h4>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Job Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="6" required 
                                  maxlength="5000" placeholder="Describe the role, responsibilities, and what makes this position exciting...">{{ old('description') }}</textarea>
                        <div class="character-counter"></div>
                        @error('description')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="experience_level" class="form-label">Experience Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="experience_level" name="experience_level" required>
                                    <option value="">Select Experience Level</option>
                                    <option value="entry" {{ old('experience_level') == 'entry' ? 'selected' : '' }}>Entry Level (0-2 years)</option>
                                    <option value="intermediate" {{ old('experience_level') == 'intermediate' ? 'selected' : '' }}>Intermediate Level (3-5 years)</option>
                                    <option value="expert" {{ old('experience_level') == 'expert' ? 'selected' : '' }}>Expert Level (6+ years)</option>
                                </select>
                                @error('experience_level')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="education_level" class="form-label">Education Level</label>
                                <select class="form-select" id="education_level" name="education_level">
                                    <option value="">Select Education Level</option>
                                    <option value="high_school" {{ old('education_level') == 'high_school' ? 'selected' : '' }}>High School</option>
                                    <option value="vocational" {{ old('education_level') == 'vocational' ? 'selected' : '' }}>Vocational/Technical</option>
                                    <option value="associate" {{ old('education_level') == 'associate' ? 'selected' : '' }}>Associate Degree</option>
                                    <option value="bachelor" {{ old('education_level') == 'bachelor' ? 'selected' : '' }}>Bachelor's Degree</option>
                                    <option value="master" {{ old('education_level') == 'master' ? 'selected' : '' }}>Master's Degree</option>
                                    <option value="doctorate" {{ old('education_level') == 'doctorate' ? 'selected' : '' }}>Doctorate</option>
                                </select>
                                @error('education_level')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Salary Range (PHP)</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="salary_min" name="salary_min" 
                                           placeholder="15,000" value="{{ old('salary_min') }}">
                                </div>
                                <small class="text-muted">Minimum salary</small>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="salary_max" name="salary_max" 
                                           placeholder="50,000" value="{{ old('salary_max') }}">
                                </div>
                                <small class="text-muted">Maximum salary</small>
                            </div>
                        </div>
                        <div id="salary_range" class="mt-3"></div>
                        <div class="d-flex justify-content-between mt-2">
                            <span id="salary_min_display">₱15,000</span>
                            <span id="salary_max_display">₱50,000</span>
                        </div>
                        @error('salary_min')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('salary_max')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="deadline" class="form-label">Application Deadline</label>
                        <input type="date" class="form-control" id="deadline" name="deadline" 
                               min="{{ date('Y-m-d') }}" value="{{ old('deadline') }}">
                        <small class="text-muted">Leave blank for no deadline</small>
                        @error('deadline')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-prev-step">
                            <i class="fas fa-arrow-left me-2"></i>Previous
                        </button>
                        <button type="button" class="btn btn-primary btn-next-step">
                            Next Step <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 3: Qualifications & Benefits -->
                <div class="wizard-section" id="step-3" style="display: none;">
                    <h4 class="mb-4">Qualifications & Benefits</h4>

                    <div class="mb-4">
                        <label for="qualifications" class="form-label">Qualifications <span class="text-danger">*</span></label>
                        <small class="text-muted d-block mb-2">List the qualifications required for this position (one per line). Example: Graduate of 4-year course, With experience, etc.</small>
                        <textarea class="form-control" id="qualifications" name="qualifications" rows="8" required
                                  maxlength="3000" placeholder="- Graduate of a 4-year BUSINESS-related course (preferably Accountancy)
- With experience as an advantage, or without experience as long as trainable
- Knowledge in accounting and business management
- Has keen attention to detail and paperwork
- Good communication skills
- Honest and trustworthy">{{ old('qualifications') }}</textarea>
                        <div class="character-counter"></div>
                        @error('qualifications')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="requirements" class="form-label">Additional Requirements</label>
                        <small class="text-muted d-block mb-2">Any special requirements (licenses, certifications, physical requirements, etc.)</small>
                        <textarea class="form-control" id="requirements" name="requirements" rows="4"
                                  maxlength="2000" placeholder="- Must have own motorcycle (with driver's license)
- Must be willing to be assigned for field work
- With valid professional driver's license">{{ old('requirements') }}</textarea>
                        <div class="character-counter"></div>
                        @error('requirements')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="benefits" class="form-label">Benefits & Perks</label>
                        <textarea class="form-control" id="benefits" name="benefits" rows="4"
                                  maxlength="2000" placeholder="- Competitive salary
- Health insurance
- 13th month pay
- Paid leave">{{ old('benefits') }}</textarea>
                        <div class="character-counter"></div>
                        @error('benefits')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="skills_input" class="form-label">Required Skills</label>
                        <input type="text" class="form-control" id="skills_input" 
                               placeholder="Type a skill and press Enter or comma to add">
                        <div id="skills_container" class="mt-2"></div>
                        <input type="hidden" id="skills" name="skills" value="{{ old('skills') }}">
                        <small class="text-muted">Add relevant skills that candidates should have</small>
                    </div>

                    <!-- Preliminary Questions Section -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>Preliminary Interview Questions</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="requires_screening" name="requires_screening"
                                       {{ old('requires_screening') ? 'checked' : '' }}>
                                <label class="form-check-label" for="requires_screening">
                                    Enable screening questions
                                </label>
                            </div>
                        </div>

                        <div id="preliminary_questions_container" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Add questions that job seekers must answer before submitting their application.
                            </div>

                            <div id="questions_list">
                                <!-- Questions will be added here dynamically -->
                            </div>

                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="add_question_btn">
                                    <i class="fas fa-plus me-2"></i>Add Question
                                </button>
                            </div>

                            <input type="hidden" id="preliminary_questions" name="preliminary_questions" value="{{ old('preliminary_questions') }}">
                        </div>
                    </div>

                    <!-- Required Documents Section -->
                    <div class="mb-4">
                        <h6 class="mb-3"><i class="fas fa-file-alt me-2"></i>Required Documents</h6>
                        <p class="text-muted mb-3">Specify documents that applicants must submit if their application is approved (e.g., 2x2 ID Photo, certificates, valid IDs).</p>

                        <div id="job_requirements_container">
                            <!-- Existing requirements will be added here -->
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm" id="add_requirement_btn">
                            <i class="fas fa-plus me-2"></i>Add Document Requirement
                        </button>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-prev-step">
                            <i class="fas fa-arrow-left me-2"></i>Previous
                        </button>
                        <button type="button" class="btn btn-primary btn-next-step">
                            Next Step <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 4: Review & Submit -->
                <div class="wizard-section" id="step-4" style="display: none;">
                    <h4 class="mb-4">Review Your Job Posting</h4>
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="job-preview">
                                <h5 id="preview-title">Job Title</h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-building me-2"></i><span id="preview-company">{{ Auth::user()->employerProfile->company_name ?? Auth::user()->name }}</span>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-map-marker-alt me-2"></i><span id="preview-location">Location</span>
                                </p>
                                <p class="text-muted mb-3">
                                    <i class="fas fa-briefcase me-2"></i><span id="preview-type">Job Type</span>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-users me-2"></i><span id="preview-vacancy">1</span> position(s)
                                </p>
                                
                                <div class="mb-3">
                                    <h6>Description</h6>
                                    <div id="preview-description">Job description will appear here...</div>
                                </div>
                                
                                <div class="mb-3">
                                    <h6>Qualifications</h6>
                                    <div id="preview-qualifications">Job qualifications will appear here...</div>
                                </div>

                                <div class="mb-3" id="preview-requirements-section" style="display: none;">
                                    <h6>Additional Requirements</h6>
                                    <div id="preview-requirements">Additional requirements will appear here...</div>
                                </div>
                                
                                <div class="mb-3" id="preview-benefits-section" style="display: none;">
                                    <h6>Benefits</h6>
                                    <div id="preview-benefits">Benefits will appear here...</div>
                                </div>
                                
                                <div class="mb-3" id="preview-salary-section" style="display: none;">
                                    <h6>Salary Range</h6>
                                    <div id="preview-salary">Salary range will appear here...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Your job posting will be reviewed by our team before being published. 
                        You will receive a notification once it's approved.
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-prev-step">
                            <i class="fas fa-arrow-left me-2"></i>Previous
                        </button>
                        <button type="submit" class="btn btn-success btn-lg" id="submitJobBtn">
                            <i class="fas fa-paper-plane me-2"></i>Submit Job Posting
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Autosave Indicator -->
    <div id="autosave-indicator" class="autosave-indicator">
        <i class="fas fa-save"></i> Saving...
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/nouislider@14.6.0/distribute/nouislider.min.js"></script>
<script src="{{ asset('assets/js/job-form-wizard-fixed.js') }}"></script>
<script src="{{ asset('assets/js/preliminary-questions.js') }}"></script>

<script>
/**
 * Mapbox Location Autocomplete
 * Uses the backend API to search for locations in Sta. Cruz, Davao del Sur
 */
(function() {
    'use strict';

    let searchTimeout = null;
    let currentSuggestions = [];
    let selectedIndex = -1;

    document.addEventListener('DOMContentLoaded', function() {
        initMapboxLocationAutocomplete();
    });

    function initMapboxLocationAutocomplete() {
        const locationInput = document.getElementById('location');
        const suggestionsContainer = document.getElementById('location-suggestions');
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');
        const locationBtn = document.getElementById('use-current-location');

        if (!locationInput || !suggestionsContainer) return;

        // Handle input changes with debounce
        locationInput.addEventListener('input', function(e) {
            const query = e.target.value.trim();

            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            // Clear coordinates when user types (they haven't selected a suggestion yet)
            if (latInput) latInput.value = '';
            if (lngInput) lngInput.value = '';

            // Hide suggestions if query is too short
            if (query.length < 2) {
                hideSuggestions();
                return;
            }

            // Debounce the search (wait 300ms after user stops typing)
            searchTimeout = setTimeout(function() {
                searchLocations(query);
            }, 300);
        });

        // Handle keyboard navigation
        locationInput.addEventListener('keydown', function(e) {
            if (!suggestionsContainer.classList.contains('show')) return;

            const items = suggestionsContainer.querySelectorAll('.location-suggestion-item');

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                    updateSelectedItem(items);
                    break;

                case 'ArrowUp':
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, 0);
                    updateSelectedItem(items);
                    break;

                case 'Enter':
                    e.preventDefault();
                    if (selectedIndex >= 0 && currentSuggestions[selectedIndex]) {
                        selectSuggestion(currentSuggestions[selectedIndex]);
                    }
                    break;

                case 'Escape':
                    hideSuggestions();
                    break;
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.location-autocomplete-wrapper')) {
                hideSuggestions();
            }
        });

        // Handle "Use Current Location" button with reverse geocoding
        if (locationBtn) {
            locationBtn.addEventListener('click', function() {
                if (!navigator.geolocation) {
                    showNotification('Geolocation is not supported by this browser.', 'error');
                    return;
                }

                // Show loading state
                locationBtn.disabled = true;
                locationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Getting Location...';

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        // Set coordinates
                        if (latInput) latInput.value = lat;
                        if (lngInput) lngInput.value = lng;

                        // Use reverse geocoding to get address
                        reverseGeocode(lat, lng, function(address) {
                            locationInput.value = address || 'Sta. Cruz, Davao del Sur';
                            resetLocationButton();
                            showNotification('Location detected successfully!', 'success');
                        });
                    },
                    function(error) {
                        let errorMessage = 'Unable to get your location.';
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = 'Location permission denied. Please enable location access.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = 'Location information is unavailable.';
                                break;
                            case error.TIMEOUT:
                                errorMessage = 'Location request timed out.';
                                break;
                        }
                        showNotification(errorMessage, 'error');
                        resetLocationButton();
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        }

        function resetLocationButton() {
            locationBtn.disabled = false;
            locationBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Use Current Location';
        }
    }

    // Search for locations using the backend API
    function searchLocations(query) {
        const suggestionsContainer = document.getElementById('location-suggestions');

        // Show loading state
        suggestionsContainer.innerHTML = '<div class="location-loading"><i class="fas fa-spinner fa-spin"></i>Searching locations...</div>';
        suggestionsContainer.classList.add('show');

        // Call the backend API
        fetch('/api/location/search?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                if (data.suggestions && data.suggestions.length > 0) {
                    currentSuggestions = data.suggestions;
                    renderSuggestions(data.suggestions, query);
                } else {
                    currentSuggestions = [];
                    suggestionsContainer.innerHTML = '<div class="location-no-results"><i class="fas fa-map-marker-alt me-2"></i>No locations found. Try a different search term.</div>';
                }
            })
            .catch(error => {
                console.error('Location search error:', error);
                currentSuggestions = [];
                suggestionsContainer.innerHTML = '<div class="location-no-results"><i class="fas fa-exclamation-circle me-2"></i>Error searching locations. Please try again.</div>';
            });
    }

    // Render location suggestions
    function renderSuggestions(suggestions, query) {
        const suggestionsContainer = document.getElementById('location-suggestions');
        selectedIndex = -1;

        let html = '';
        suggestions.forEach((suggestion, index) => {
            const name = highlightMatch(suggestion.name || '', query);
            const address = suggestion.full_address || suggestion.place_name || '';

            html += `
                <div class="location-suggestion-item" data-index="${index}">
                    <i class="fas fa-map-marker-alt suggestion-icon"></i>
                    <div class="d-inline-block">
                        <div class="suggestion-name">${name}</div>
                        <div class="suggestion-address">${address}</div>
                    </div>
                </div>
            `;
        });

        suggestionsContainer.innerHTML = html;
        suggestionsContainer.classList.add('show');

        // Add click handlers to suggestions
        suggestionsContainer.querySelectorAll('.location-suggestion-item').forEach(item => {
            item.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                if (currentSuggestions[index]) {
                    selectSuggestion(currentSuggestions[index]);
                }
            });
        });
    }

    // Select a suggestion
    function selectSuggestion(suggestion) {
        const locationInput = document.getElementById('location');
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        // Set the location name
        const displayName = suggestion.full_address || suggestion.place_name || suggestion.name;
        locationInput.value = displayName;

        // Set coordinates
        if (suggestion.coordinates) {
            if (latInput) latInput.value = suggestion.coordinates.latitude;
            if (lngInput) lngInput.value = suggestion.coordinates.longitude;
        } else if (suggestion.geometry && suggestion.geometry.coordinates) {
            if (lngInput) lngInput.value = suggestion.geometry.coordinates[0];
            if (latInput) latInput.value = suggestion.geometry.coordinates[1];
        }

        // Hide suggestions
        hideSuggestions();

        // Show success notification
        showNotification('Location selected: ' + suggestion.name, 'success');
    }

    // Reverse geocode coordinates to get address
    function reverseGeocode(lat, lng, callback) {
        fetch('/api/location/reverse-geocode?lat=' + lat + '&lng=' + lng)
            .then(response => response.json())
            .then(data => {
                if (data.features && data.features.length > 0) {
                    callback(data.features[0].place_name);
                } else {
                    callback('Sta. Cruz, Davao del Sur');
                }
            })
            .catch(error => {
                console.error('Reverse geocoding error:', error);
                callback('Sta. Cruz, Davao del Sur');
            });
    }

    // Highlight matching text in suggestions
    function highlightMatch(text, query) {
        if (!query) return text;
        const regex = new RegExp('(' + escapeRegex(query) + ')', 'gi');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }

    // Escape regex special characters
    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Update selected item highlighting
    function updateSelectedItem(items) {
        items.forEach((item, index) => {
            if (index === selectedIndex) {
                item.classList.add('active');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('active');
            }
        });
    }

    // Hide suggestions dropdown
    function hideSuggestions() {
        const suggestionsContainer = document.getElementById('location-suggestions');
        if (suggestionsContainer) {
            suggestionsContainer.classList.remove('show');
        }
        selectedIndex = -1;
    }

    // Show notification
    function showNotification(message, type) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.location-notification');
        existingNotifications.forEach(n => n.remove());

        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'location-notification alert alert-' + (type === 'success' ? 'success' : 'danger') + ' mt-2';
        notification.style.cssText = 'position: absolute; z-index: 1060; left: 0; right: 0; animation: fadeIn 0.3s ease;';
        notification.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' me-2"></i>' + message;

        // Insert after location wrapper
        const wrapper = document.querySelector('.location-autocomplete-wrapper');
        if (wrapper) {
            wrapper.appendChild(notification);
        }

        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, 3000);
    }
})();
</script>

<script>
// Add form submission debugging
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('jobForm');
    const submitBtn = document.getElementById('submitJobBtn');
    
    // Check if there are validation errors on page load
    const errorAlert = document.querySelector('.alert-danger');
    if (errorAlert) {
        // Scroll to the error message
        errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Show step 1 if there are errors (most errors are in step 1)
        const step1 = document.getElementById('step-1');
        if (step1) {
            // Hide all steps
            document.querySelectorAll('.wizard-section').forEach(section => {
                section.style.display = 'none';
            });
            // Show step 1
            step1.style.display = 'block';
            
            // Update progress indicator
            document.querySelectorAll('.progress-step').forEach(step => {
                step.classList.remove('active');
            });
            document.querySelector('[data-step="0"]').classList.add('active');
            
            // Update progress bar
            const progressBar = document.querySelector('.progress-bar');
            if (progressBar) {
                progressBar.style.width = '25%';
            }
        }
    }
    
    if (form && submitBtn) {
        // Track form submission attempts
        form.addEventListener('submit', function(e) {
            console.log('🚀 Form submission started');
            console.log('Form action:', form.action);
            console.log('Form method:', form.method);
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Job...';
            
            // Add a temporary success message
            const tempAlert = document.createElement('div');
            tempAlert.className = 'alert alert-info mt-3';
            tempAlert.innerHTML = '<i class="fas fa-info-circle me-2"></i>Submitting your job posting...';
            form.insertBefore(tempAlert, form.firstChild);
            
            // Log form data
            const formData = new FormData(form);
            console.log('Form data:');
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}: ${value}`);
            }
        });
        
        // Track any form errors
        window.addEventListener('error', function(e) {
            console.error('JavaScript error:', e.error);
        });
    }
});

// Job Requirements Management
(function() {
    'use strict';

    let requirementIndex = 0;

    document.addEventListener('DOMContentLoaded', function() {
        initJobRequirements();
    });

    function initJobRequirements() {
        const container = document.getElementById('job_requirements_container');
        const addBtn = document.getElementById('add_requirement_btn');

        if (!container || !addBtn) return;

        // Add button click handler
        addBtn.addEventListener('click', function() {
            addRequirement();
        });

        // Event delegation for remove buttons
        container.addEventListener('click', function(e) {
            if (e.target.closest('.remove-requirement')) {
                const item = e.target.closest('.requirement-item');
                if (item) {
                    item.remove();
                }
            }
        });
    }

    function addRequirement(name = '', description = '', isRequired = true) {
        const container = document.getElementById('job_requirements_container');
        const index = requirementIndex++;

        const html = `
            <div class="requirement-item">
                <button type="button" class="remove-requirement" title="Remove requirement">
                    <i class="fas fa-times"></i>
                </button>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Document Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="job_requirements[${index}][name]"
                               value="${escapeHtml(name)}" placeholder="e.g., 2x2 ID Photo" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="job_requirements[${index}][description]"
                               value="${escapeHtml(description)}" placeholder="e.g., Recent photo with white background">
                    </div>
                </div>
                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input" name="job_requirements[${index}][is_required]"
                           id="req_required_${index}" ${isRequired ? 'checked' : ''}>
                    <label class="form-check-label" for="req_required_${index}">
                        This document is mandatory
                    </label>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', html);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Make addRequirement globally accessible
    window.addRequirement = addRequirement;
})();
</script>
@endpush
