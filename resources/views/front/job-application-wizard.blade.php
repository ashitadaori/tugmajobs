@extends('front.layouts.app')

@section('content')
<div class="application-wizard-wrapper">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">

                <!-- Job Header Card -->
                <div class="job-header-card">
                    <div class="job-header-content">
                        <div class="job-company-logo">
                            @if($job->employer->logo)
                                <img src="{{ asset('storage/' . $job->employer->logo) }}" alt="{{ $job->employer->name }}">
                            @else
                                <span class="logo-placeholder">{{ strtoupper(substr($job->employer->name, 0, 2)) }}</span>
                            @endif
                        </div>
                        <div class="job-header-info">
                            <h1 class="job-title">{{ $job->title }}</h1>
                            <p class="company-name">{{ $job->employer->name }}</p>
                            <div class="job-meta-tags">
                                <span class="meta-tag"><i class="fas fa-map-marker-alt"></i> {{ $job->getFullAddress() }}</span>
                                <span class="meta-tag"><i class="fas fa-briefcase"></i> {{ $job->jobType->name }}</span>
                                @if($job->salary)
                                    <span class="meta-tag salary"><i class="fas fa-peso-sign"></i> {{ $job->salary }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Steps -->
                <div class="wizard-progress-container">
                    <div class="wizard-progress">
                        <div class="progress-track">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <div class="wizard-steps">
                            <div class="wizard-step {{ $currentStep >= 1 ? 'active' : '' }} {{ $currentStep > 1 ? 'completed' : '' }}" data-step="1">
                                <div class="step-indicator">
                                    <span class="step-icon"><i class="fas fa-user"></i></span>
                                    <span class="step-check"><i class="fas fa-check"></i></span>
                                </div>
                                <span class="step-title">Profile</span>
                            </div>

                            @if($job->requires_screening && !empty($job->preliminary_questions))
                            <div class="wizard-step {{ $currentStep >= 2 ? 'active' : '' }} {{ $currentStep > 2 ? 'completed' : '' }}" data-step="2">
                                <div class="step-indicator">
                                    <span class="step-icon"><i class="fas fa-clipboard-list"></i></span>
                                    <span class="step-check"><i class="fas fa-check"></i></span>
                                </div>
                                <span class="step-title">Screening</span>
                            </div>
                            @endif

                            <div class="wizard-step {{ $currentStep >= ($job->requires_screening ? 3 : 2) ? 'active' : '' }} {{ $currentStep > ($job->requires_screening ? 3 : 2) ? 'completed' : '' }}" data-step="{{ $job->requires_screening ? 3 : 2 }}">
                                <div class="step-indicator">
                                    <span class="step-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                                    <span class="step-check"><i class="fas fa-check"></i></span>
                                </div>
                                <span class="step-title">Documents</span>
                            </div>

                            <div class="wizard-step {{ $currentStep >= ($job->requires_screening ? 4 : 3) ? 'active' : '' }} {{ $currentStep > ($job->requires_screening ? 4 : 3) ? 'completed' : '' }}" data-step="{{ $job->requires_screening ? 4 : 3 }}">
                                <div class="step-indicator">
                                    <span class="step-icon"><i class="fas fa-paper-plane"></i></span>
                                    <span class="step-check"><i class="fas fa-check"></i></span>
                                </div>
                                <span class="step-title">Submit</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Application Form Card -->
                <div class="wizard-form-card">
                    <form id="applicationWizardForm" method="POST" action="{{ route('job.application.process', $job->id) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="current_step" value="{{ $currentStep }}">
                        <input type="hidden" name="application_id" value="{{ $application->id ?? '' }}">

                        <!-- Step 1: Profile Update -->
                        <div class="wizard-step-content {{ $currentStep == 1 ? 'active' : '' }}" id="step-1">
                            <div class="step-content-header">
                                <div class="step-icon-circle">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="step-header-text">
                                    <h3>Review Your Profile</h3>
                                    <p>Ensure your information is complete and up-to-date before applying</p>
                                </div>
                            </div>

                            <div class="step-content-body">
                                @php
                                    $user = Auth::user();
                                    $jobseeker = $user->jobSeekerProfile;
                                    $completionPercentage = $jobseeker ? $jobseeker->calculateProfileCompletion() : 0;
                                @endphp

                                <!-- Profile Completion Ring -->
                                <div class="profile-completion-section">
                                    <div class="completion-ring-wrapper">
                                        <svg class="completion-ring" viewBox="0 0 120 120">
                                            <circle class="ring-bg" cx="60" cy="60" r="54" />
                                            <circle class="ring-progress {{ $completionPercentage >= 80 ? 'complete' : ($completionPercentage >= 50 ? 'warning' : 'incomplete') }}"
                                                    cx="60" cy="60" r="54"
                                                    stroke-dasharray="339.292"
                                                    stroke-dashoffset="{{ 339.292 - (339.292 * $completionPercentage / 100) }}" />
                                        </svg>
                                        <div class="completion-percentage">
                                            <span class="percentage-value">{{ $completionPercentage }}</span>
                                            <span class="percentage-symbol">%</span>
                                        </div>
                                    </div>
                                    <div class="completion-status-text">
                                        @if($completionPercentage >= 80)
                                            <span class="status-badge success"><i class="fas fa-check-circle"></i> Profile Ready</span>
                                            <p>Your profile is complete and ready for applications</p>
                                        @elseif($completionPercentage >= 50)
                                            <span class="status-badge warning"><i class="fas fa-exclamation-circle"></i> Almost There</span>
                                            <p>Complete your profile to increase your chances</p>
                                        @else
                                            <span class="status-badge danger"><i class="fas fa-times-circle"></i> Needs Attention</span>
                                            <p>Please complete your profile before applying</p>
                                        @endif
                                    </div>
                                </div>

                                @if($completionPercentage < 80)
                                <div class="missing-items-card">
                                    <h5><i class="fas fa-list-check"></i> Missing Information</h5>
                                    <div class="missing-items-list">
                                        @if(empty($jobseeker?->professional_summary))
                                            <div class="missing-item"><i class="fas fa-circle"></i> Professional summary</div>
                                        @endif
                                        @if(empty($jobseeker?->skills))
                                            <div class="missing-item"><i class="fas fa-circle"></i> Skills</div>
                                        @endif
                                        @if(empty($jobseeker?->work_experience))
                                            <div class="missing-item"><i class="fas fa-circle"></i> Work experience</div>
                                        @endif
                                        @if(empty($jobseeker?->education))
                                            <div class="missing-item"><i class="fas fa-circle"></i> Education details</div>
                                        @endif
                                        @if(empty($jobseeker?->resume_file))
                                            <div class="missing-item"><i class="fas fa-circle"></i> Resume file</div>
                                        @endif
                                    </div>
                                    <a href="{{ route('account.myProfile') }}" class="btn-update-profile" target="_blank">
                                        <i class="fas fa-external-link-alt"></i> Update Profile
                                    </a>
                                </div>
                                @endif

                                <!-- Profile Summary Cards -->
                                <div class="profile-info-grid">
                                    <div class="profile-info-card">
                                        <div class="info-card-icon"><i class="fas fa-id-card"></i></div>
                                        <div class="info-card-content">
                                            <label>Full Name</label>
                                            <span>{{ $user->name }}</span>
                                        </div>
                                    </div>
                                    <div class="profile-info-card">
                                        <div class="info-card-icon"><i class="fas fa-envelope"></i></div>
                                        <div class="info-card-content">
                                            <label>Email Address</label>
                                            <span>{{ $user->email }}</span>
                                        </div>
                                    </div>
                                    <div class="profile-info-card">
                                        <div class="info-card-icon"><i class="fas fa-phone"></i></div>
                                        <div class="info-card-content">
                                            <label>Phone Number</label>
                                            <span>{{ $jobseeker?->phone ?? 'Not provided' }}</span>
                                        </div>
                                    </div>
                                    <div class="profile-info-card">
                                        <div class="info-card-icon"><i class="fas fa-briefcase"></i></div>
                                        <div class="info-card-content">
                                            <label>Experience</label>
                                            <span>{{ $jobseeker?->total_experience ?? 'Not specified' }}</span>
                                        </div>
                                    </div>
                                    <div class="profile-info-card">
                                        <div class="info-card-icon"><i class="fas fa-tools"></i></div>
                                        <div class="info-card-content">
                                            <label>Skills</label>
                                            <span>{{ Str::limit($jobseeker?->skills_string ?? 'Not specified', 40) }}</span>
                                        </div>
                                    </div>
                                    <div class="profile-info-card">
                                        <div class="info-card-icon"><i class="fas fa-map-marker-alt"></i></div>
                                        <div class="info-card-content">
                                            <label>Location</label>
                                            <span>{{ $jobseeker?->city ?? 'Not specified' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Confirmation Checkbox -->
                                <div class="confirmation-box">
                                    <label class="custom-checkbox">
                                        <input type="checkbox" id="profileConfirm" name="profile_confirmed" required>
                                        <span class="checkmark"></span>
                                        <span class="checkbox-text">I confirm that my profile information is accurate and up-to-date</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Screening Questions (if required) -->
                        @if($job->requires_screening && !empty($job->preliminary_questions))
                        <div class="wizard-step-content {{ $currentStep == 2 ? 'active' : '' }}" id="step-2">
                            <div class="step-content-header">
                                <div class="step-icon-circle">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <div class="step-header-text">
                                    <h3>Screening Questions</h3>
                                    <p>Answer these questions to help the employer evaluate your application</p>
                                </div>
                            </div>

                            <div class="step-content-body">
                                <div class="questions-container">
                                    @foreach($job->preliminary_questions as $index => $question)
                                    <div class="question-card">
                                        <div class="question-header">
                                            <span class="question-badge">Q{{ $index + 1 }}</span>
                                            <h5 class="question-text">
                                                {{ $question['question'] }}
                                                @if($question['required'] ?? false)
                                                    <span class="required-mark">*</span>
                                                @endif
                                            </h5>
                                        </div>
                                        <div class="question-body">
                                            @switch($question['type'])
                                                @case('text')
                                                    <textarea class="modern-textarea"
                                                            name="preliminary_answers[{{ $index }}]"
                                                            rows="4"
                                                            placeholder="Type your answer here..."
                                                            {{ ($question['required'] ?? false) ? 'required' : '' }}>{{ $application->preliminary_answers[$index] ?? '' }}</textarea>
                                                    @break

                                                @case('multiple_choice')
                                                    <div class="options-grid">
                                                        @foreach($question['options'] as $optionIndex => $option)
                                                        <label class="option-card">
                                                            <input type="radio"
                                                                   name="preliminary_answers[{{ $index }}]"
                                                                   value="{{ $option }}"
                                                                   {{ ($application->preliminary_answers[$index] ?? '') == $option ? 'checked' : '' }}
                                                                   {{ ($question['required'] ?? false) ? 'required' : '' }}>
                                                            <span class="option-content">
                                                                <span class="option-radio"></span>
                                                                <span class="option-text">{{ $option }}</span>
                                                            </span>
                                                        </label>
                                                        @endforeach
                                                    </div>
                                                    @break

                                                @case('yes_no')
                                                    <div class="yesno-options">
                                                        <label class="yesno-option">
                                                            <input type="radio"
                                                                   name="preliminary_answers[{{ $index }}]"
                                                                   value="Yes"
                                                                   {{ ($application->preliminary_answers[$index] ?? '') == 'Yes' ? 'checked' : '' }}
                                                                   {{ ($question['required'] ?? false) ? 'required' : '' }}>
                                                            <span class="yesno-btn yes">
                                                                <i class="fas fa-check"></i> Yes
                                                            </span>
                                                        </label>
                                                        <label class="yesno-option">
                                                            <input type="radio"
                                                                   name="preliminary_answers[{{ $index }}]"
                                                                   value="No"
                                                                   {{ ($application->preliminary_answers[$index] ?? '') == 'No' ? 'checked' : '' }}
                                                                   {{ ($question['required'] ?? false) ? 'required' : '' }}>
                                                            <span class="yesno-btn no">
                                                                <i class="fas fa-times"></i> No
                                                            </span>
                                                        </label>
                                                    </div>
                                                    @break
                                            @endswitch
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Step 3/4: Document Upload -->
                        <div class="wizard-step-content {{ $currentStep == ($job->requires_screening ? 3 : 2) ? 'active' : '' }}" id="step-{{ $job->requires_screening ? 3 : 2 }}">
                            <div class="step-content-header">
                                <div class="step-icon-circle">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="step-header-text">
                                    <h3>Upload Documents</h3>
                                    <p>Attach your resume and cover letter to complete your application</p>
                                </div>
                            </div>

                            <div class="step-content-body">
                                <div class="documents-grid">
                                    <!-- Resume Section -->
                                    <div class="document-section">
                                        <div class="document-section-header">
                                            <i class="fas fa-file-alt"></i>
                                            <h5>Resume <span class="required-mark">*</span></h5>
                                        </div>

                                        @php
                                            $existingResume = Auth::user()->jobSeekerProfile?->resume_file ?? Auth::user()->jobseeker?->resume_file;
                                        @endphp

                                        @if($existingResume)
                                            <div class="resume-choice-container">
                                                <label class="resume-choice active" id="choiceExisting">
                                                    <input type="radio" name="resume_option" value="existing" checked onchange="toggleResumeUpload()">
                                                    <div class="choice-content">
                                                        <div class="choice-icon existing">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </div>
                                                        <div class="choice-info">
                                                            <span class="choice-title">Use Profile Resume</span>
                                                            <span class="choice-filename">{{ basename($existingResume) }}</span>
                                                        </div>
                                                        <a href="{{ asset('storage/' . $existingResume) }}" target="_blank" class="preview-btn" onclick="event.stopPropagation()">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </label>

                                                <label class="resume-choice" id="choiceNew">
                                                    <input type="radio" name="resume_option" value="new" onchange="toggleResumeUpload()">
                                                    <div class="choice-content">
                                                        <div class="choice-icon new">
                                                            <i class="fas fa-upload"></i>
                                                        </div>
                                                        <div class="choice-info">
                                                            <span class="choice-title">Upload New Resume</span>
                                                            <span class="choice-filename">Select a different file</span>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>

                                            <div id="resume-upload-section" class="file-upload-zone" style="display: none;">
                                                <input type="file" class="file-input" id="resume" name="resume" accept=".pdf,.doc,.docx">
                                                <label for="resume" class="file-upload-label">
                                                    <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                                    <span class="upload-text">Drag & drop or click to upload</span>
                                                    <span class="upload-hint">PDF, DOC, DOCX (Max 5MB)</span>
                                                </label>
                                                <div class="file-preview" id="resumePreview"></div>
                                            </div>

                                            <input type="hidden" name="existing_resume" value="{{ $existingResume }}">
                                        @else
                                            <div class="no-resume-alert">
                                                <i class="fas fa-info-circle"></i>
                                                <p>You don't have a resume in your profile. Please upload one to continue.</p>
                                            </div>
                                            <div class="file-upload-zone active">
                                                <input type="file" class="file-input" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                                                <label for="resume" class="file-upload-label">
                                                    <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                                    <span class="upload-text">Drag & drop or click to upload</span>
                                                    <span class="upload-hint">PDF, DOC, DOCX (Max 5MB)</span>
                                                </label>
                                                <div class="file-preview" id="resumePreview"></div>
                                            </div>
                                        @endif

                                        @if($application && $application->resume)
                                            <div class="upload-success-badge">
                                                <i class="fas fa-check-circle"></i> Resume already uploaded
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Cover Letter Section -->
                                    <div class="document-section">
                                        <div class="document-section-header">
                                            <i class="fas fa-envelope-open-text"></i>
                                            <h5>Cover Letter <span class="optional-tag">Optional</span></h5>
                                        </div>
                                        <div class="cover-letter-container">
                                            <textarea class="modern-textarea cover-letter"
                                                    id="cover_letter"
                                                    name="cover_letter"
                                                    rows="8"
                                                    placeholder="Write a compelling cover letter explaining why you're the perfect fit for this position...">{{ $application->cover_letter ?? '' }}</textarea>
                                            <div class="textarea-footer">
                                                <span class="char-count"><span id="charCount">0</span> characters</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4/5: Review & Submit -->
                        <div class="wizard-step-content {{ $currentStep == ($job->requires_screening ? 4 : 3) ? 'active' : '' }}" id="step-{{ $job->requires_screening ? 4 : 3 }}">
                            <div class="step-content-header">
                                <div class="step-icon-circle success">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <div class="step-header-text">
                                    <h3>Review & Submit</h3>
                                    <p>Double-check your application details before submitting</p>
                                </div>
                            </div>

                            <div class="step-content-body">
                                <div class="review-container">
                                    <!-- Job Summary Card -->
                                    <div class="review-card job-summary-card">
                                        <div class="review-card-header">
                                            <i class="fas fa-briefcase"></i>
                                            <h5>Job Details</h5>
                                        </div>
                                        <div class="review-card-body">
                                            <div class="job-summary-content">
                                                <div class="job-summary-logo">
                                                    @if($job->employer->logo)
                                                        <img src="{{ asset('storage/' . $job->employer->logo) }}" alt="{{ $job->employer->name }}">
                                                    @else
                                                        <span>{{ strtoupper(substr($job->employer->name, 0, 2)) }}</span>
                                                    @endif
                                                </div>
                                                <div class="job-summary-info">
                                                    <h4>{{ $job->title }}</h4>
                                                    <p class="company">{{ $job->employer->name }}</p>
                                                    <div class="job-tags">
                                                        <span><i class="fas fa-map-marker-alt"></i> {{ $job->getFullAddress() }}</span>
                                                        <span><i class="fas fa-clock"></i> {{ $job->jobType->name }}</span>
                                                        @if($job->salary)
                                                            <span><i class="fas fa-peso-sign"></i> {{ $job->salary }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Application Summary -->
                                    <div id="applicationSummary">
                                        <!-- Content will be dynamically generated -->
                                    </div>

                                    <!-- Final Confirmation -->
                                    <div class="final-confirmation-box">
                                        <label class="custom-checkbox">
                                            <input type="checkbox" id="finalConfirm" name="final_confirmation" required>
                                            <span class="checkmark"></span>
                                            <span class="checkbox-text">I confirm that all information provided is accurate and I want to submit this application</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="wizard-navigation">
                            <button type="button" id="prevBtn" class="nav-btn prev-btn" style="display: none;">
                                <i class="fas fa-arrow-left"></i>
                                <span>Previous</span>
                            </button>
                            <div class="nav-spacer"></div>
                            <button type="button" id="nextBtn" class="nav-btn next-btn">
                                <span>Continue</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                            <button type="submit" id="submitBtn" class="nav-btn submit-btn" style="display: none;">
                                <i class="fas fa-paper-plane"></i>
                                <span>Submit Application</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
/* ===== Application Wizard Base Styles ===== */
.application-wizard-wrapper {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: 100vh;
    padding-bottom: 3rem;
}

/* ===== Job Header Card ===== */
.job-header-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
}

.job-header-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.job-company-logo {
    width: 80px;
    height: 80px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
    backdrop-filter: blur(10px);
}

.job-company-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.job-company-logo .logo-placeholder {
    color: white;
    font-size: 1.75rem;
    font-weight: 700;
}

.job-header-info {
    flex: 1;
}

.job-title {
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.25rem 0;
}

.company-name {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
    margin: 0 0 0.75rem 0;
}

.job-meta-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.meta-tag {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    backdrop-filter: blur(5px);
}

.meta-tag.salary {
    background: rgba(255, 255, 255, 0.3);
    font-weight: 600;
}

/* ===== Progress Steps ===== */
.wizard-progress-container {
    background: white;
    border-radius: 16px;
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.wizard-progress {
    position: relative;
}

.progress-track {
    position: absolute;
    top: 24px;
    left: 10%;
    right: 10%;
    height: 4px;
    background: #e2e8f0;
    border-radius: 2px;
    z-index: 0;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 2px;
    transition: width 0.5s ease;
    width: 0%;
}

.wizard-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    z-index: 1;
}

.wizard-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    cursor: default;
}

.step-indicator {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #f1f5f9;
    border: 3px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    position: relative;
}

.step-icon {
    color: #94a3b8;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}

.step-check {
    display: none;
    color: white;
    font-size: 1rem;
}

.step-title {
    font-size: 0.8rem;
    color: #94a3b8;
    font-weight: 500;
    transition: all 0.3s ease;
}

.wizard-step.active .step-indicator {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-color: transparent;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.wizard-step.active .step-icon {
    color: white;
}

.wizard-step.active .step-title {
    color: #667eea;
    font-weight: 600;
}

.wizard-step.completed .step-indicator {
    background: #10b981;
    border-color: transparent;
}

.wizard-step.completed .step-icon {
    display: none;
}

.wizard-step.completed .step-check {
    display: block;
}

.wizard-step.completed .step-title {
    color: #10b981;
}

/* ===== Form Card ===== */
.wizard-form-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

/* ===== Step Content ===== */
.wizard-step-content {
    display: none;
}

.wizard-step-content.active {
    display: block;
    animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.step-content-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #f1f5f9;
}

.step-icon-circle {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.4rem;
    flex-shrink: 0;
}

.step-icon-circle.success {
    background: linear-gradient(135deg, #10b981, #059669);
}

.step-header-text h3 {
    margin: 0 0 0.25rem 0;
    color: #1e293b;
    font-size: 1.35rem;
    font-weight: 700;
}

.step-header-text p {
    margin: 0;
    color: #64748b;
    font-size: 0.95rem;
}

/* ===== Profile Completion Section ===== */
.profile-completion-section {
    display: flex;
    align-items: center;
    gap: 2rem;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.completion-ring-wrapper {
    position: relative;
    width: 120px;
    height: 120px;
    flex-shrink: 0;
}

.completion-ring {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.ring-bg {
    fill: none;
    stroke: #e2e8f0;
    stroke-width: 8;
}

.ring-progress {
    fill: none;
    stroke-width: 8;
    stroke-linecap: round;
    transition: stroke-dashoffset 1s ease;
}

.ring-progress.complete { stroke: #10b981; }
.ring-progress.warning { stroke: #f59e0b; }
.ring-progress.incomplete { stroke: #ef4444; }

.completion-percentage {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.percentage-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}

.percentage-symbol {
    font-size: 1rem;
    color: #64748b;
}

.completion-status-text {
    flex: 1;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.status-badge.success {
    background: #d1fae5;
    color: #059669;
}

.status-badge.warning {
    background: #fef3c7;
    color: #d97706;
}

.status-badge.danger {
    background: #fee2e2;
    color: #dc2626;
}

.completion-status-text p {
    margin: 0;
    color: #64748b;
    font-size: 0.95rem;
}

/* ===== Missing Items Card ===== */
.missing-items-card {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.missing-items-card h5 {
    color: #92400e;
    font-size: 1rem;
    margin: 0 0 1rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.missing-items-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.missing-item {
    background: white;
    color: #92400e;
    padding: 0.4rem 0.75rem;
    border-radius: 6px;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.missing-item i {
    font-size: 0.4rem;
}

.btn-update-profile {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #f59e0b;
    color: white;
    padding: 0.6rem 1.25rem;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-update-profile:hover {
    background: #d97706;
    color: white;
    transform: translateY(-2px);
}

/* ===== Profile Info Grid ===== */
.profile-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.profile-info-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    transition: all 0.2s ease;
}

.profile-info-card:hover {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.info-card-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.info-card-content {
    flex: 1;
    min-width: 0;
}

.info-card-content label {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.15rem;
}

.info-card-content span {
    display: block;
    font-size: 0.95rem;
    color: #1e293b;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ===== Custom Checkbox ===== */
.confirmation-box,
.final-confirmation-box {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
    margin-top: 1.5rem;
}

.custom-checkbox {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    cursor: pointer;
    position: relative;
}

.custom-checkbox input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.custom-checkbox .checkmark {
    width: 24px;
    height: 24px;
    border: 2px solid #cbd5e1;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s ease;
    background: white;
}

.custom-checkbox .checkmark::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    color: white;
    font-size: 0.75rem;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.custom-checkbox input:checked + .checkmark {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-color: transparent;
}

.custom-checkbox input:checked + .checkmark::after {
    opacity: 1;
}

.checkbox-text {
    color: #475569;
    font-size: 0.95rem;
    line-height: 1.5;
}

/* ===== Questions Section ===== */
.questions-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.question-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
}

.question-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    background: white;
    border-bottom: 1px solid #e2e8f0;
}

.question-badge {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 700;
    flex-shrink: 0;
}

.question-text {
    margin: 0;
    color: #1e293b;
    font-size: 1rem;
    font-weight: 600;
    flex: 1;
    padding-top: 0.35rem;
}

.required-mark {
    color: #ef4444;
    margin-left: 0.25rem;
}

.question-body {
    padding: 1.5rem;
}

.modern-textarea {
    width: 100%;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    font-size: 0.95rem;
    color: #1e293b;
    resize: vertical;
    transition: all 0.2s ease;
    font-family: inherit;
}

.modern-textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.modern-textarea::placeholder {
    color: #94a3b8;
}

/* Options Grid */
.options-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
}

.option-card {
    cursor: pointer;
}

.option-card input {
    display: none;
}

.option-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    transition: all 0.2s ease;
}

.option-card input:checked + .option-content {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
}

.option-radio {
    width: 20px;
    height: 20px;
    border: 2px solid #cbd5e1;
    border-radius: 50%;
    position: relative;
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.option-card input:checked + .option-content .option-radio {
    border-color: #667eea;
}

.option-card input:checked + .option-content .option-radio::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 10px;
    height: 10px;
    background: #667eea;
    border-radius: 50%;
}

.option-text {
    color: #475569;
    font-size: 0.95rem;
}

/* Yes/No Options */
.yesno-options {
    display: flex;
    gap: 1rem;
}

.yesno-option {
    flex: 1;
    cursor: pointer;
}

.yesno-option input {
    display: none;
}

.yesno-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1rem;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.yesno-btn.yes {
    background: #f0fdf4;
    border: 2px solid #86efac;
    color: #16a34a;
}

.yesno-btn.no {
    background: #fef2f2;
    border: 2px solid #fca5a5;
    color: #dc2626;
}

.yesno-option input:checked + .yesno-btn.yes {
    background: #16a34a;
    border-color: #16a34a;
    color: white;
}

.yesno-option input:checked + .yesno-btn.no {
    background: #dc2626;
    border-color: #dc2626;
    color: white;
}

/* ===== Documents Section ===== */
.documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
}

.document-section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.5rem;
}

.document-section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.document-section-header i {
    color: #667eea;
    font-size: 1.25rem;
}

.document-section-header h5 {
    margin: 0;
    color: #1e293b;
    font-size: 1.1rem;
    font-weight: 600;
}

.optional-tag {
    background: #e2e8f0;
    color: #64748b;
    font-size: 0.7rem;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-weight: 500;
    margin-left: 0.5rem;
}

/* Resume Choice Container */
.resume-choice-container {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.resume-choice {
    cursor: pointer;
}

.resume-choice input {
    display: none;
}

.choice-content {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    transition: all 0.2s ease;
}

.resume-choice.active .choice-content,
.resume-choice input:checked + .choice-content {
    border-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
}

.choice-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.choice-icon.existing {
    background: #fee2e2;
    color: #dc2626;
}

.choice-icon.new {
    background: #dbeafe;
    color: #2563eb;
}

.choice-info {
    flex: 1;
    min-width: 0;
}

.choice-title {
    display: block;
    font-weight: 600;
    color: #1e293b;
    font-size: 0.95rem;
}

.choice-filename {
    display: block;
    color: #64748b;
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.preview-btn {
    width: 40px;
    height: 40px;
    background: #667eea;
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    text-decoration: none;
}

.preview-btn:hover {
    background: #5a67d8;
    color: white;
    transform: scale(1.05);
}

/* File Upload Zone */
.file-upload-zone {
    margin-top: 1rem;
}

.file-input {
    display: none;
}

.file-upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: white;
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.file-upload-label:hover {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.02);
}

.upload-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.upload-text {
    color: #475569;
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.upload-hint {
    color: #94a3b8;
    font-size: 0.8rem;
}

.no-resume-alert {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background: #dbeafe;
    border: 1px solid #93c5fd;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.no-resume-alert i {
    color: #2563eb;
    font-size: 1.1rem;
    margin-top: 0.1rem;
}

.no-resume-alert p {
    margin: 0;
    color: #1e40af;
    font-size: 0.9rem;
}

.upload-success-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #d1fae5;
    color: #059669;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
    margin-top: 1rem;
}

/* Cover Letter */
.cover-letter-container {
    background: white;
    border-radius: 12px;
    overflow: hidden;
}

.modern-textarea.cover-letter {
    border: none;
    border-radius: 12px 12px 0 0;
    min-height: 200px;
}

.textarea-footer {
    background: #f8fafc;
    padding: 0.75rem 1.25rem;
    border-top: 1px solid #e2e8f0;
}

.char-count {
    color: #64748b;
    font-size: 0.8rem;
}

/* ===== Review Section ===== */
.review-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.review-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
}

.review-card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    background: white;
    border-bottom: 1px solid #e2e8f0;
}

.review-card-header i {
    color: #667eea;
    font-size: 1.1rem;
}

.review-card-header h5 {
    margin: 0;
    color: #1e293b;
    font-size: 1rem;
    font-weight: 600;
}

.review-card-body {
    padding: 1.5rem;
}

.job-summary-content {
    display: flex;
    align-items: center;
    gap: 1.25rem;
}

.job-summary-logo {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}

.job-summary-logo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.job-summary-logo span {
    color: white;
    font-size: 1.25rem;
    font-weight: 700;
}

.job-summary-info h4 {
    margin: 0 0 0.25rem 0;
    color: #1e293b;
    font-size: 1.15rem;
    font-weight: 600;
}

.job-summary-info .company {
    margin: 0 0 0.75rem 0;
    color: #64748b;
    font-size: 0.9rem;
}

.job-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.job-tags span {
    background: white;
    border: 1px solid #e2e8f0;
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    font-size: 0.8rem;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.job-tags span i {
    color: #667eea;
}

/* ===== Navigation Buttons ===== */
.wizard-navigation {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid #f1f5f9;
}

.nav-spacer {
    flex: 1;
}

.nav-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.9rem 1.75rem;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.prev-btn {
    background: #f1f5f9;
    color: #475569;
}

.prev-btn:hover {
    background: #e2e8f0;
}

.next-btn {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.next-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.submit-btn {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
}

.submit-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

/* ===== Responsive Design ===== */
@media (max-width: 768px) {
    .job-header-content {
        flex-direction: column;
        text-align: center;
    }

    .job-meta-tags {
        justify-content: center;
    }

    .wizard-steps {
        gap: 0.5rem;
    }

    .step-title {
        font-size: 0.7rem;
    }

    .step-indicator {
        width: 40px;
        height: 40px;
    }

    .step-icon {
        font-size: 0.9rem;
    }

    .profile-completion-section {
        flex-direction: column;
        text-align: center;
    }

    .documents-grid {
        grid-template-columns: 1fr;
    }

    .yesno-options {
        flex-direction: column;
    }

    .wizard-navigation {
        flex-wrap: wrap;
    }

    .nav-btn {
        flex: 1;
        justify-content: center;
    }

    .nav-spacer {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = {{ $currentStep }};
    const totalSteps = {{ $job->requires_screening ? 4 : 3 }};

    // Initialize wizard
    showStep(currentStep);
    updateProgressBar(currentStep);

    // Cover letter character count
    const coverLetterEl = document.getElementById('cover_letter');
    const charCountEl = document.getElementById('charCount');
    if (coverLetterEl && charCountEl) {
        charCountEl.textContent = coverLetterEl.value.length;
        coverLetterEl.addEventListener('input', function() {
            charCountEl.textContent = this.value.length;
        });
    }

    // File upload preview
    const resumeInput = document.getElementById('resume');
    if (resumeInput) {
        resumeInput.addEventListener('change', function() {
            const preview = document.getElementById('resumePreview');
            if (this.files && this.files[0] && preview) {
                const file = this.files[0];
                preview.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: #d1fae5; border-radius: 8px; margin-top: 1rem;">
                        <i class="fas fa-file-pdf" style="color: #059669; font-size: 1.25rem;"></i>
                        <div style="flex: 1;">
                            <strong style="color: #065f46;">${file.name}</strong>
                            <span style="display: block; color: #047857; font-size: 0.8rem;">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                        </div>
                        <i class="fas fa-check-circle" style="color: #059669;"></i>
                    </div>
                `;
            }
        });
    }

    // Next button click
    document.getElementById('nextBtn').addEventListener('click', function() {
        if (validateStep(currentStep)) {
            if (currentStep < totalSteps) {
                currentStep++;
                document.querySelector('input[name="current_step"]').value = currentStep;
                showStep(currentStep);
                updateProgressBar(currentStep);
            }
        }
    });

    // Previous button click
    document.getElementById('prevBtn').addEventListener('click', function() {
        if (currentStep > 1) {
            currentStep--;
            document.querySelector('input[name="current_step"]').value = currentStep;
            showStep(currentStep);
            updateProgressBar(currentStep);
        }
    });

    function showStep(step) {
        // Hide all steps
        document.querySelectorAll('.wizard-step-content').forEach(function(element) {
            element.classList.remove('active');
        });

        // Show current step
        const currentStepElement = document.getElementById(`step-${step}`);
        if (currentStepElement) {
            currentStepElement.classList.add('active');
        }

        // Update progress indicators
        updateProgress(step);

        // Update navigation buttons
        updateNavigationButtons(step);

        // Generate application summary for review step
        if (step === totalSteps) {
            generateApplicationSummary();
        }

        // Scroll to top of form
        document.querySelector('.wizard-form-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function updateProgress(step) {
        document.querySelectorAll('.wizard-step').forEach(function(element, index) {
            element.classList.remove('active', 'completed');
            if (index + 1 < step) {
                element.classList.add('completed');
            } else if (index + 1 === step) {
                element.classList.add('active');
            }
        });
    }

    function updateProgressBar(step) {
        const progressFill = document.getElementById('progressFill');
        if (progressFill) {
            const percentage = ((step - 1) / (totalSteps - 1)) * 100;
            progressFill.style.width = percentage + '%';
        }
    }

    function updateNavigationButtons(step) {
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');

        prevBtn.style.display = step > 1 ? 'inline-flex' : 'none';
        nextBtn.style.display = step < totalSteps ? 'inline-flex' : 'none';
        submitBtn.style.display = step === totalSteps ? 'inline-flex' : 'none';
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

        // Profile Summary Card
        summaryHTML += `
            <div class="review-card">
                <div class="review-card-header">
                    <i class="fas fa-user"></i>
                    <h5>Your Profile</h5>
                </div>
                <div class="review-card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div>
                            <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase;">Name</span>
                            <p style="margin: 0.25rem 0 0 0; font-weight: 500; color: #1e293b;">{{ Auth::user()->name }}</p>
                        </div>
                        <div>
                            <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase;">Email</span>
                            <p style="margin: 0.25rem 0 0 0; font-weight: 500; color: #1e293b;">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Screening Questions Summary (if applicable)
        @if($job->requires_screening && !empty($job->preliminary_questions))
        summaryHTML += `
            <div class="review-card">
                <div class="review-card-header">
                    <i class="fas fa-clipboard-list"></i>
                    <h5>Screening Questions</h5>
                </div>
                <div class="review-card-body">
        `;

        @foreach($job->preliminary_questions as $index => $question)
        const answer{{ $index }} = document.querySelector('input[name="preliminary_answers[{{ $index }}]"]:checked, textarea[name="preliminary_answers[{{ $index }}]"]');
        if (answer{{ $index }}) {
            summaryHTML += `
                <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
                    <span style="font-size: 0.85rem; color: #64748b;">{{ $question['question'] }}</span>
                    <p style="margin: 0.5rem 0 0 0; font-weight: 500; color: #1e293b;">${answer{{ $index }}.value}</p>
                </div>
            `;
        }
        @endforeach

        summaryHTML += `</div></div>`;
        @endif

        // Documents Summary Card
        const resumeInput = document.getElementById('resume');
        const resumeFile = resumeInput ? resumeInput.files[0] : null;
        const coverLetter = document.getElementById('cover_letter').value;
        const useExistingResume = document.querySelector('input[name="resume_option"][value="existing"]:checked');

        let resumeText = 'Using profile resume';
        if (resumeFile) {
            resumeText = resumeFile.name;
        } else if (!useExistingResume && !resumeFile) {
            resumeText = 'No resume selected';
        }

        summaryHTML += `
            <div class="review-card">
                <div class="review-card-header">
                    <i class="fas fa-file-alt"></i>
                    <h5>Documents</h5>
                </div>
                <div class="review-card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div>
                            <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase;">Resume</span>
                            <p style="margin: 0.25rem 0 0 0; font-weight: 500; color: #1e293b; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-file-pdf" style="color: #dc2626;"></i> ${resumeText}
                            </p>
                        </div>
                        <div>
                            <span style="font-size: 0.75rem; color: #64748b; text-transform: uppercase;">Cover Letter</span>
                            <p style="margin: 0.25rem 0 0 0; font-weight: 500; color: #1e293b;">
                                ${coverLetter ? '<span style="color: #059669;"><i class="fas fa-check-circle"></i> Provided</span>' : '<span style="color: #94a3b8;">Not provided</span>'}
                            </p>
                        </div>
                    </div>
                </div>
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
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Submitting...</span>';
        submitBtn.disabled = true;
    });
});

// Function to toggle resume upload section
function toggleResumeUpload() {
    const existingChoice = document.getElementById('choiceExisting');
    const newChoice = document.getElementById('choiceNew');
    const uploadSection = document.getElementById('resume-upload-section');
    const resumeInput = document.getElementById('resume');
    const useNewResume = document.querySelector('input[name="resume_option"][value="new"]:checked');

    if (existingChoice && newChoice) {
        if (useNewResume) {
            existingChoice.classList.remove('active');
            newChoice.classList.add('active');
            if (uploadSection) {
                uploadSection.style.display = 'block';
            }
            if (resumeInput) {
                resumeInput.required = true;
            }
        } else {
            existingChoice.classList.add('active');
            newChoice.classList.remove('active');
            if (uploadSection) {
                uploadSection.style.display = 'none';
            }
            if (resumeInput) {
                resumeInput.required = false;
                resumeInput.value = '';
            }
            const preview = document.getElementById('resumePreview');
            if (preview) {
                preview.innerHTML = '';
            }
        }
    }
}
</script>
@endsection
