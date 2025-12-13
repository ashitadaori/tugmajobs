@extends('front.layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Application Progress -->
            <div class="application-progress-card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Apply for {{ $job->title }}</h5>
                    
                    <!-- Progress Steps -->
                    <div class="application-steps">
                        <div class="step {{ $currentStep >= 1 ? 'active' : '' }} {{ $currentStep > 1 ? 'completed' : '' }}" data-step="1">
                            <div class="step-circle">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="step-label">Profile Update</div>
                        </div>
                        
                        @if($job->requires_screening && !empty($job->preliminary_questions))
                        <div class="step {{ $currentStep >= 2 ? 'active' : '' }} {{ $currentStep > 2 ? 'completed' : '' }}" data-step="2">
                            <div class="step-circle">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div class="step-label">Screening Questions</div>
                        </div>
                        @endif
                        
                        <div class="step {{ $currentStep >= ($job->requires_screening ? 3 : 2) ? 'active' : '' }} {{ $currentStep > ($job->requires_screening ? 3 : 2) ? 'completed' : '' }}" data-step="{{ $job->requires_screening ? 3 : 2 }}">
                            <div class="step-circle">
                                <i class="fas fa-file-upload"></i>
                            </div>
                            <div class="step-label">Upload Documents</div>
                        </div>
                        
                        <div class="step {{ $currentStep >= ($job->requires_screening ? 4 : 3) ? 'active' : '' }} {{ $currentStep > ($job->requires_screening ? 4 : 3) ? 'completed' : '' }}" data-step="{{ $job->requires_screening ? 4 : 3 }}">
                            <div class="step-circle">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="step-label">Review & Submit</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Form -->
            <div class="application-form-card">
                <div class="card-body">
                    <form id="applicationWizardForm" method="POST" action="{{ route('job.application.process', $job->id) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="current_step" value="{{ $currentStep }}">
                        <input type="hidden" name="application_id" value="{{ $application->id ?? '' }}">
                        
                        <!-- Step 1: Profile Update -->
                        <div class="form-step {{ $currentStep == 1 ? 'active' : '' }}" id="step-1">
                            <div class="step-header">
                                <h4><i class="fas fa-user me-2"></i>Update Your Profile</h4>
                                <p class="text-muted">Make sure your profile information is complete and up-to-date</p>
                            </div>
                            
                            <div class="profile-completion">
                                @php
                                    $user = Auth::user();
                                    $jobseeker = $user->jobSeekerProfile;
                                    $completionPercentage = $jobseeker ? $jobseeker->calculateProfileCompletion() : 0;
                                @endphp
                                
                                <div class="completion-status mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold">Profile Completion</span>
                                        <span class="badge {{ $completionPercentage >= 80 ? 'bg-success' : ($completionPercentage >= 50 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $completionPercentage }}%
                                        </span>
                                    </div>
                                    <div class="progress mb-3">
                                        <div class="progress-bar {{ $completionPercentage >= 80 ? 'bg-success' : ($completionPercentage >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                             style="width: {{ $completionPercentage }}%"></div>
                                    </div>
                                    
                                    @if($completionPercentage < 80)
                                        <div class="alert alert-warning">
                                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Profile Incomplete</h6>
                                            <p class="mb-2">To increase your chances of getting hired, please complete your profile:</p>
                                            <ul class="mb-2">
                                                @if(empty($jobseeker?->professional_summary))
                                                    <li>Add a professional summary</li>
                                                @endif
                                                @if(empty($jobseeker?->skills))
                                                    <li>Add your skills</li>
                                                @endif
                                                @if(empty($jobseeker?->work_experience))
                                                    <li>Add work experience</li>
                                                @endif
                                                @if(empty($jobseeker?->education))
                                                    <li>Add education details</li>
                                                @endif
                                                @if(empty($jobseeker?->resume_file))
                                                    <li>Upload your resume</li>
                                                @endif
                                            </ul>
                                            <a href="{{ route('account.myProfile') }}" class="btn btn-sm btn-warning" target="_blank">
                                                <i class="fas fa-edit me-1"></i>Update Profile
                                            </a>
                                        </div>
                                    @else
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle me-2"></i>Your profile is complete and ready for applications!
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Profile Summary -->
                                <div class="profile-summary">
                                    <h6>Profile Summary</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <strong>Name:</strong> {{ $user->name }}
                                            </div>
                                            <div class="info-item">
                                                <strong>Email:</strong> {{ $user->email }}
                                            </div>
                                            <div class="info-item">
                                                <strong>Phone:</strong> {{ $jobseeker?->phone ?? 'Not provided' }}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <strong>Experience:</strong> {{ $jobseeker?->total_experience ?? 'Not specified' }}
                                            </div>
                                            <div class="info-item">
                                                <strong>Skills:</strong> {{ $jobseeker?->skills_string ?? 'Not specified' }}
                                            </div>
                                            <div class="info-item">
                                                <strong>Location:</strong> {{ $jobseeker?->city ?? 'Not specified' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" id="profileConfirm" name="profile_confirmed" required>
                                    <label class="form-check-label" for="profileConfirm">
                                        I confirm that my profile information is accurate and up-to-date
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Screening Questions (if required) -->
                        @if($job->requires_screening && !empty($job->preliminary_questions))
                        <div class="form-step {{ $currentStep == 2 ? 'active' : '' }}" id="step-2">
                            <div class="step-header">
                                <h4><i class="fas fa-question-circle me-2"></i>Screening Questions</h4>
                                <p class="text-muted">Please answer these preliminary questions to help the employer assess your suitability</p>
                            </div>
                            
                            <div class="screening-questions">
                                @foreach($job->preliminary_questions as $index => $question)
                                <div class="question-item mb-4">
                                    <div class="question-header">
                                        <span class="question-number">{{ $index + 1 }}</span>
                                        <h6>{{ $question['question'] }}</h6>
                                        @if($question['required'] ?? false)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </div>
                                    
                                    @switch($question['type'])
                                        @case('text')
                                            <textarea class="form-control" 
                                                    name="preliminary_answers[{{ $index }}]" 
                                                    rows="3" 
                                                    placeholder="Type your answer here..."
                                                    {{ ($question['required'] ?? false) ? 'required' : '' }}>{{ $application->preliminary_answers[$index] ?? '' }}</textarea>
                                            @break
                                        
                                        @case('multiple_choice')
                                            @foreach($question['options'] as $optionIndex => $option)
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="radio" 
                                                       name="preliminary_answers[{{ $index }}]" 
                                                       id="q{{ $index }}_{{ $optionIndex }}"
                                                       value="{{ $option }}"
                                                       {{ ($application->preliminary_answers[$index] ?? '') == $option ? 'checked' : '' }}
                                                       {{ ($question['required'] ?? false) ? 'required' : '' }}>
                                                <label class="form-check-label" for="q{{ $index }}_{{ $optionIndex }}">
                                                    {{ $option }}
                                                </label>
                                            </div>
                                            @endforeach
                                            @break
                                        
                                        @case('yes_no')
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="radio" 
                                                       name="preliminary_answers[{{ $index }}]" 
                                                       id="q{{ $index }}_yes"
                                                       value="Yes"
                                                       {{ ($application->preliminary_answers[$index] ?? '') == 'Yes' ? 'checked' : '' }}
                                                       {{ ($question['required'] ?? false) ? 'required' : '' }}>
                                                <label class="form-check-label" for="q{{ $index }}_yes">Yes</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="radio" 
                                                       name="preliminary_answers[{{ $index }}]" 
                                                       id="q{{ $index }}_no"
                                                       value="No"
                                                       {{ ($application->preliminary_answers[$index] ?? '') == 'No' ? 'checked' : '' }}
                                                       {{ ($question['required'] ?? false) ? 'required' : '' }}>
                                                <label class="form-check-label" for="q{{ $index }}_no">No</label>
                                            </div>
                                            @break
                                    @endswitch
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Step 3/4: Document Upload -->
                        <div class="form-step {{ $currentStep == ($job->requires_screening ? 3 : 2) ? 'active' : '' }}" id="step-{{ $job->requires_screening ? 3 : 2 }}">
                            <div class="step-header">
                                <h4><i class="fas fa-file-upload me-2"></i>Upload Documents</h4>
                                <p class="text-muted">Upload your resume and other relevant documents</p>
                            </div>
                            
                            <div class="document-upload">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Resume <span class="text-danger">*</span></label>
                                            
                                            @php
                                                $existingResume = Auth::user()->jobSeekerProfile?->resume_file ?? Auth::user()->jobseeker?->resume_file;
                                            @endphp
                                            
                                            @if($existingResume)
                                                <!-- Resume Selection Options -->
                                                <div class="resume-options mb-3">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="radio" name="resume_option" id="use_existing" value="existing" checked onchange="toggleResumeUpload()">
                                                        <label class="form-check-label" for="use_existing">
                                                            <strong>Use my profile resume</strong>
                                                        </label>
                                                    </div>
                                                    
                                                    <div class="existing-resume-preview p-3 border rounded bg-light">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-file-pdf text-danger me-2 fa-lg"></i>
                                                            <div>
                                                                <strong>{{ basename($existingResume) }}</strong>
                                                                <small class="text-muted d-block">From your profile</small>
                                                            </div>
                                                            <a href="{{ asset('storage/' . $existingResume) }}" target="_blank" class="btn btn-sm btn-outline-primary ms-auto">
                                                                <i class="fas fa-eye me-1"></i>View
                                                            </a>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="radio" name="resume_option" id="upload_new" value="new" onchange="toggleResumeUpload()">
                                                        <label class="form-check-label" for="upload_new">
                                                            <strong>Upload a different resume</strong>
                                                        </label>
                                                    </div>
                                                </div>
                                                
                                                <!-- File Upload Input (hidden by default) -->
                                                <div id="resume-upload-section" style="display: none;">
                                                    <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx">
                                                    <div class="form-text">Accepted formats: PDF, DOC, DOCX (Max: 5MB)</div>
                                                </div>
                                                
                                                <!-- Hidden input to store existing resume choice -->
                                                <input type="hidden" name="existing_resume" value="{{ $existingResume }}">
                                            @else
                                                <!-- No existing resume - force upload -->
                                                <div class="alert alert-info mb-3">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    You don't have a resume in your profile. Please upload one to continue.
                                                </div>
                                                <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                                                <div class="form-text">Accepted formats: PDF, DOC, DOCX (Max: 5MB)</div>
                                            @endif
                                            
                                            @if($application && $application->resume)
                                                <div class="current-application-file mt-2">
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    <span class="text-success">Resume uploaded for this application</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cover_letter" class="form-label">Cover Letter</label>
                                            <textarea class="form-control" id="cover_letter" name="cover_letter" rows="4" 
                                                    placeholder="Write a brief cover letter explaining why you're interested in this position...">{{ $application->cover_letter ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4/5: Review & Submit -->
                        <div class="form-step {{ $currentStep == ($job->requires_screening ? 4 : 3) ? 'active' : '' }}" id="step-{{ $job->requires_screening ? 4 : 3 }}">
                            <div class="step-header">
                                <h4><i class="fas fa-eye me-2"></i>Review & Submit</h4>
                                <p class="text-muted">Review your application before submitting</p>
                            </div>
                            
                            <div class="application-review">
                                <!-- Job Summary -->
                                <div class="review-section mb-4">
                                    <h6>Job Information</h6>
                                    <div class="job-summary">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="company-logo me-3">
                                                {{ substr($job->employer->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <h5 class="mb-1">{{ $job->title }}</h5>
                                                <p class="text-muted mb-0">{{ $job->employer->name }}</p>
                                            </div>
                                        </div>
                                        <div class="job-details">
                                            <span class="badge bg-light text-dark me-2">
                                                <i class="fas fa-map-marker-alt me-1"></i>{{ $job->getFullAddress() }}
                                            </span>
                                            <span class="badge bg-light text-dark me-2">
                                                <i class="fas fa-clock me-1"></i>{{ $job->jobType->name }}
                                            </span>
                                            @if($job->salary)
                                                <span class="badge bg-light text-dark">
                                                    <i class="fas fa-money-bill-wave me-1"></i>{{ $job->salary }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Application Summary will be populated by JavaScript -->
                                <div id="applicationSummary">
                                    <!-- Content will be dynamically generated -->
                                </div>
                                
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="finalConfirm" name="final_confirmation" required>
                                    <label class="form-check-label" for="finalConfirm">
                                        I confirm that all the information provided is accurate and I want to submit this application
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="form-navigation mt-4">
                            <div class="d-flex justify-content-between">
                                <button type="button" id="prevBtn" class="btn btn-secondary" style="display: none;">
                                    <i class="fas fa-arrow-left me-2"></i>Previous
                                </button>
                                <button type="button" id="nextBtn" class="btn btn-primary">
                                    Next<i class="fas fa-arrow-right ms-2"></i>
                                </button>
                                <button type="submit" id="submitBtn" class="btn btn-success" style="display: none;">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Application Progress Styles */
.application-progress-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    color: white;
    border: none;
}

.application-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    margin: 2rem 0;
}

.application-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 50px;
    right: 50px;
    height: 2px;
    background: rgba(255, 255, 255, 0.3);
    z-index: 1;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.step.active .step-circle {
    background: rgba(255, 255, 255, 0.9);
    color: #667eea;
}

.step.completed .step-circle {
    background: #28a745;
    color: white;
}

.step-label {
    font-size: 0.875rem;
    text-align: center;
    opacity: 0.8;
}

.step.active .step-label {
    opacity: 1;
    font-weight: 600;
}

/* Form Steps */
.form-step {
    display: none;
}

.form-step.active {
    display: block;
}

.step-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.step-header h4 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

/* Profile Completion */
.completion-status .progress {
    height: 8px;
    border-radius: 4px;
}

.info-item {
    margin-bottom: 0.75rem;
}

/* Screening Questions */
.question-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    background: #f8f9fa;
}

.question-header {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.question-number {
    background: #667eea;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.question-header h6 {
    margin: 0;
    flex-grow: 1;
}

/* Document Upload */
.document-upload .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.current-file {
    padding: 0.5rem;
    background: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 4px;
    color: #155724;
}

/* Resume Selection Styles */
.resume-options .form-check-label {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.existing-resume-preview {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef !important;
}

.existing-resume-preview:hover {
    border-color: #667eea !important;
    background-color: #f8f9ff !important;
}

.existing-resume-preview .btn-outline-primary {
    border-color: #667eea;
    color: #667eea;
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

.existing-resume-preview .btn-outline-primary:hover {
    background-color: #667eea;
    border-color: #667eea;
}

.current-application-file {
    padding: 0.5rem;
    background: #d1f2eb;
    border: 1px solid #badbcc;
    border-radius: 4px;
    color: #0c5460;
}

/* Review Section */
.company-logo {
    width: 50px;
    height: 50px;
    background: #667eea;
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 600;
}

.job-summary {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.review-section {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.5rem;
    background: white;
}

/* Navigation Buttons */
.form-navigation {
    border-top: 1px solid #e9ecef;
    padding-top: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .application-steps {
        flex-direction: column;
        gap: 1rem;
    }
    
    .application-steps::before {
        display: none;
    }
    
    .step {
        flex-direction: row;
        width: 100%;
        text-align: left;
    }
    
    .step-circle {
        margin-right: 1rem;
        margin-bottom: 0;
    }
    
    .step-label {
        text-align: left;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = {{ $currentStep }};
    const totalSteps = {{ $job->requires_screening ? 4 : 3 }};
    
    // Initialize wizard
    showStep(currentStep);
    
    // Next button click
    document.getElementById('nextBtn').addEventListener('click', function() {
        if (validateStep(currentStep)) {
            if (currentStep < totalSteps) {
                currentStep++;
                document.querySelector('input[name="current_step"]').value = currentStep;
                showStep(currentStep);
            }
        }
    });
    
    // Previous button click
    document.getElementById('prevBtn').addEventListener('click', function() {
        if (currentStep > 1) {
            currentStep--;
            document.querySelector('input[name="current_step"]').value = currentStep;
            showStep(currentStep);
        }
    });
    
    function showStep(step) {
        // Hide all steps
        document.querySelectorAll('.form-step').forEach(function(element) {
            element.classList.remove('active');
        });
        
        // Show current step
        const currentStepElement = document.getElementById(`step-${step}`);
        if (currentStepElement) {
            currentStepElement.classList.add('active');
        }
        
        // Update progress
        updateProgress(step);
        
        // Update navigation buttons
        updateNavigationButtons(step);
        
        // Generate application summary for review step
        if (step === totalSteps) {
            generateApplicationSummary();
        }
    }
    
    function updateProgress(step) {
        document.querySelectorAll('.step').forEach(function(element, index) {
            element.classList.remove('active', 'completed');
            if (index + 1 < step) {
                element.classList.add('completed');
            } else if (index + 1 === step) {
                element.classList.add('active');
            }
        });
    }
    
    function updateNavigationButtons(step) {
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        
        prevBtn.style.display = step > 1 ? 'block' : 'none';
        nextBtn.style.display = step < totalSteps ? 'block' : 'none';
        submitBtn.style.display = step === totalSteps ? 'block' : 'none';
    }
    
    function validateStep(step) {
        const currentStepElement = document.getElementById(`step-${step}`);
        if (!currentStepElement) return true;
        
        const requiredFields = currentStepElement.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(function(field) {
            if (!field.checkValidity()) {
                field.reportValidity();
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    function generateApplicationSummary() {
        const summaryContainer = document.getElementById('applicationSummary');
        let summaryHTML = '';
        
        // Profile Summary
        summaryHTML += `
            <div class="review-section mb-3">
                <h6>Your Profile</h6>
                <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
                <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
            </div>
        `;
        
        // Screening Questions Summary (if applicable)
        @if($job->requires_screening && !empty($job->preliminary_questions))
        if (currentStep > 2) {
            summaryHTML += `
                <div class="review-section mb-3">
                    <h6>Screening Questions</h6>
            `;
            
            @foreach($job->preliminary_questions as $index => $question)
            const answer{{ $index }} = document.querySelector('input[name="preliminary_answers[{{ $index }}]"]:checked, textarea[name="preliminary_answers[{{ $index }}]"]');
            if (answer{{ $index }}) {
                summaryHTML += `
                    <div class="mb-2">
                        <strong>{{ $question['question'] }}</strong><br>
                        <span class="text-muted">${answer{{ $index }}.value}</span>
                    </div>
                `;
            }
            @endforeach
            
            summaryHTML += `</div>`;
        }
        @endif
        
        // Documents Summary
        const resumeFile = document.getElementById('resume').files[0];
        const coverLetter = document.getElementById('cover_letter').value;
        
        summaryHTML += `
            <div class="review-section mb-3">
                <h6>Documents</h6>
                <p><strong>Resume:</strong> ${resumeFile ? resumeFile.name : 'Previously uploaded'}</p>
                <p><strong>Cover Letter:</strong> ${coverLetter ? 'Provided' : 'Not provided'}</p>
            </div>
        `;
        
        summaryContainer.innerHTML = summaryHTML;
    }
    
    // Form submission
    document.getElementById('applicationWizardForm').addEventListener('submit', function(e) {
        if (currentStep !== totalSteps) {
            e.preventDefault();
            return;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
        submitBtn.disabled = true;
    });
});

// Function to toggle resume upload section
function toggleResumeUpload() {
    const uploadNewRadio = document.getElementById('upload_new');
    const uploadSection = document.getElementById('resume-upload-section');
    const resumeInput = document.getElementById('resume');
    
    if (uploadNewRadio && uploadNewRadio.checked) {
        uploadSection.style.display = 'block';
        resumeInput.required = true;
    } else {
        uploadSection.style.display = 'none';
        resumeInput.required = false;
        resumeInput.value = ''; // Clear the file input
    }
}
</script>
@endsection
