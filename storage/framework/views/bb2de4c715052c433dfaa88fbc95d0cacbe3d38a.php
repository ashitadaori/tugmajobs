

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Application Progress -->
            <div class="application-progress-card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Apply for <?php echo e($job->title); ?></h5>
                    
                    <!-- Progress Steps -->
                    <div class="application-steps">
                        <div class="step <?php echo e($currentStep >= 1 ? 'active' : ''); ?> <?php echo e($currentStep > 1 ? 'completed' : ''); ?>" data-step="1">
                            <div class="step-circle">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="step-label">Profile Update</div>
                        </div>
                        
                        <?php if($job->requires_screening && !empty($job->preliminary_questions)): ?>
                        <div class="step <?php echo e($currentStep >= 2 ? 'active' : ''); ?> <?php echo e($currentStep > 2 ? 'completed' : ''); ?>" data-step="2">
                            <div class="step-circle">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div class="step-label">Screening Questions</div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="step <?php echo e($currentStep >= ($job->requires_screening ? 3 : 2) ? 'active' : ''); ?> <?php echo e($currentStep > ($job->requires_screening ? 3 : 2) ? 'completed' : ''); ?>" data-step="<?php echo e($job->requires_screening ? 3 : 2); ?>">
                            <div class="step-circle">
                                <i class="fas fa-file-upload"></i>
                            </div>
                            <div class="step-label">Upload Documents</div>
                        </div>
                        
                        <div class="step <?php echo e($currentStep >= ($job->requires_screening ? 4 : 3) ? 'active' : ''); ?> <?php echo e($currentStep > ($job->requires_screening ? 4 : 3) ? 'completed' : ''); ?>" data-step="<?php echo e($job->requires_screening ? 4 : 3); ?>">
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
                    <form id="applicationWizardForm" method="POST" action="<?php echo e(route('job.application.process', $job->id)); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="current_step" value="<?php echo e($currentStep); ?>">
                        <input type="hidden" name="application_id" value="<?php echo e($application->id ?? ''); ?>">
                        
                        <!-- Step 1: Profile Update -->
                        <div class="form-step <?php echo e($currentStep == 1 ? 'active' : ''); ?>" id="step-1">
                            <div class="step-header">
                                <h4><i class="fas fa-user me-2"></i>Update Your Profile</h4>
                                <p class="text-muted">Make sure your profile information is complete and up-to-date</p>
                            </div>
                            
                            <div class="profile-completion">
                                <?php
                                    $user = Auth::user();
                                    $jobseeker = $user->jobSeekerProfile;
                                    $completionPercentage = $jobseeker ? $jobseeker->calculateProfileCompletion() : 0;
                                ?>
                                
                                <div class="completion-status mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold">Profile Completion</span>
                                        <span class="badge <?php echo e($completionPercentage >= 80 ? 'bg-success' : ($completionPercentage >= 50 ? 'bg-warning' : 'bg-danger')); ?>">
                                            <?php echo e($completionPercentage); ?>%
                                        </span>
                                    </div>
                                    <div class="progress mb-3">
                                        <div class="progress-bar <?php echo e($completionPercentage >= 80 ? 'bg-success' : ($completionPercentage >= 50 ? 'bg-warning' : 'bg-danger')); ?>" 
                                             style="width: <?php echo e($completionPercentage); ?>%"></div>
                                    </div>
                                    
                                    <?php if($completionPercentage < 80): ?>
                                        <div class="alert alert-warning">
                                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Profile Incomplete</h6>
                                            <p class="mb-2">To increase your chances of getting hired, please complete your profile:</p>
                                            <ul class="mb-2">
                                                <?php if(empty($jobseeker?->professional_summary)): ?>
                                                    <li>Add a professional summary</li>
                                                <?php endif; ?>
                                                <?php if(empty($jobseeker?->skills)): ?>
                                                    <li>Add your skills</li>
                                                <?php endif; ?>
                                                <?php if(empty($jobseeker?->work_experience)): ?>
                                                    <li>Add work experience</li>
                                                <?php endif; ?>
                                                <?php if(empty($jobseeker?->education)): ?>
                                                    <li>Add education details</li>
                                                <?php endif; ?>
                                                <?php if(empty($jobseeker?->resume_file)): ?>
                                                    <li>Upload your resume</li>
                                                <?php endif; ?>
                                            </ul>
                                            <a href="<?php echo e(route('account.myProfile')); ?>" class="btn btn-sm btn-warning" target="_blank">
                                                <i class="fas fa-edit me-1"></i>Update Profile
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle me-2"></i>Your profile is complete and ready for applications!
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Profile Summary -->
                                <div class="profile-summary">
                                    <h6>Profile Summary</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <strong>Name:</strong> <?php echo e($user->name); ?>

                                            </div>
                                            <div class="info-item">
                                                <strong>Email:</strong> <?php echo e($user->email); ?>

                                            </div>
                                            <div class="info-item">
                                                <strong>Phone:</strong> <?php echo e($jobseeker?->phone ?? 'Not provided'); ?>

                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <strong>Experience:</strong> <?php echo e($jobseeker?->total_experience ?? 'Not specified'); ?>

                                            </div>
                                            <div class="info-item">
                                                <strong>Skills:</strong> <?php echo e($jobseeker?->skills_string ?? 'Not specified'); ?>

                                            </div>
                                            <div class="info-item">
                                                <strong>Location:</strong> <?php echo e($jobseeker?->city ?? 'Not specified'); ?>

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
                        <?php if($job->requires_screening && !empty($job->preliminary_questions)): ?>
                        <div class="form-step <?php echo e($currentStep == 2 ? 'active' : ''); ?>" id="step-2">
                            <div class="step-header">
                                <h4><i class="fas fa-question-circle me-2"></i>Screening Questions</h4>
                                <p class="text-muted">Please answer these preliminary questions to help the employer assess your suitability</p>
                            </div>
                            
                            <div class="screening-questions">
                                <?php $__currentLoopData = $job->preliminary_questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="question-item mb-4">
                                    <div class="question-header">
                                        <span class="question-number"><?php echo e($index + 1); ?></span>
                                        <h6><?php echo e($question['question']); ?></h6>
                                        <?php if($question['required'] ?? false): ?>
                                            <span class="text-danger">*</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php switch($question['type']):
                                        case ('text'): ?>
                                            <textarea class="form-control" 
                                                    name="preliminary_answers[<?php echo e($index); ?>]" 
                                                    rows="3" 
                                                    placeholder="Type your answer here..."
                                                    <?php echo e(($question['required'] ?? false) ? 'required' : ''); ?>><?php echo e($application->preliminary_answers[$index] ?? ''); ?></textarea>
                                            <?php break; ?>
                                        
                                        <?php case ('multiple_choice'): ?>
                                            <?php $__currentLoopData = $question['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optionIndex => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="radio" 
                                                       name="preliminary_answers[<?php echo e($index); ?>]" 
                                                       id="q<?php echo e($index); ?>_<?php echo e($optionIndex); ?>"
                                                       value="<?php echo e($option); ?>"
                                                       <?php echo e(($application->preliminary_answers[$index] ?? '') == $option ? 'checked' : ''); ?>

                                                       <?php echo e(($question['required'] ?? false) ? 'required' : ''); ?>>
                                                <label class="form-check-label" for="q<?php echo e($index); ?>_<?php echo e($optionIndex); ?>">
                                                    <?php echo e($option); ?>

                                                </label>
                                            </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php break; ?>
                                        
                                        <?php case ('yes_no'): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="radio" 
                                                       name="preliminary_answers[<?php echo e($index); ?>]" 
                                                       id="q<?php echo e($index); ?>_yes"
                                                       value="Yes"
                                                       <?php echo e(($application->preliminary_answers[$index] ?? '') == 'Yes' ? 'checked' : ''); ?>

                                                       <?php echo e(($question['required'] ?? false) ? 'required' : ''); ?>>
                                                <label class="form-check-label" for="q<?php echo e($index); ?>_yes">Yes</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="radio" 
                                                       name="preliminary_answers[<?php echo e($index); ?>]" 
                                                       id="q<?php echo e($index); ?>_no"
                                                       value="No"
                                                       <?php echo e(($application->preliminary_answers[$index] ?? '') == 'No' ? 'checked' : ''); ?>

                                                       <?php echo e(($question['required'] ?? false) ? 'required' : ''); ?>>
                                                <label class="form-check-label" for="q<?php echo e($index); ?>_no">No</label>
                                            </div>
                                            <?php break; ?>
                                    <?php endswitch; ?>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Step 3/4: Document Upload -->
                        <div class="form-step <?php echo e($currentStep == ($job->requires_screening ? 3 : 2) ? 'active' : ''); ?>" id="step-<?php echo e($job->requires_screening ? 3 : 2); ?>">
                            <div class="step-header">
                                <h4><i class="fas fa-file-upload me-2"></i>Upload Documents</h4>
                                <p class="text-muted">Upload your resume and other relevant documents</p>
                            </div>
                            
                            <div class="document-upload">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Resume <span class="text-danger">*</span></label>
                                            
                                            <?php
                                                $existingResume = Auth::user()->jobSeekerProfile?->resume_file ?? Auth::user()->jobseeker?->resume_file;
                                            ?>
                                            
                                            <?php if($existingResume): ?>
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
                                                                <strong><?php echo e(basename($existingResume)); ?></strong>
                                                                <small class="text-muted d-block">From your profile</small>
                                                            </div>
                                                            <a href="<?php echo e(asset('storage/' . $existingResume)); ?>" target="_blank" class="btn btn-sm btn-outline-primary ms-auto">
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
                                                <input type="hidden" name="existing_resume" value="<?php echo e($existingResume); ?>">
                                            <?php else: ?>
                                                <!-- No existing resume - force upload -->
                                                <div class="alert alert-info mb-3">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    You don't have a resume in your profile. Please upload one to continue.
                                                </div>
                                                <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                                                <div class="form-text">Accepted formats: PDF, DOC, DOCX (Max: 5MB)</div>
                                            <?php endif; ?>
                                            
                                            <?php if($application && $application->resume): ?>
                                                <div class="current-application-file mt-2">
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    <span class="text-success">Resume uploaded for this application</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cover_letter" class="form-label">Cover Letter</label>
                                            <textarea class="form-control" id="cover_letter" name="cover_letter" rows="4" 
                                                    placeholder="Write a brief cover letter explaining why you're interested in this position..."><?php echo e($application->cover_letter ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4/5: Review & Submit -->
                        <div class="form-step <?php echo e($currentStep == ($job->requires_screening ? 4 : 3) ? 'active' : ''); ?>" id="step-<?php echo e($job->requires_screening ? 4 : 3); ?>">
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
                                                <?php echo e(substr($job->employer->name, 0, 1)); ?>

                                            </div>
                                            <div>
                                                <h5 class="mb-1"><?php echo e($job->title); ?></h5>
                                                <p class="text-muted mb-0"><?php echo e($job->employer->name); ?></p>
                                            </div>
                                        </div>
                                        <div class="job-details">
                                            <span class="badge bg-light text-dark me-2">
                                                <i class="fas fa-map-marker-alt me-1"></i><?php echo e($job->getFullAddress()); ?>

                                            </span>
                                            <span class="badge bg-light text-dark me-2">
                                                <i class="fas fa-clock me-1"></i><?php echo e($job->jobType->name); ?>

                                            </span>
                                            <?php if($job->salary): ?>
                                                <span class="badge bg-light text-dark">
                                                    <i class="fas fa-money-bill-wave me-1"></i><?php echo e($job->salary); ?>

                                                </span>
                                            <?php endif; ?>
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
    let currentStep = <?php echo e($currentStep); ?>;
    const totalSteps = <?php echo e($job->requires_screening ? 4 : 3); ?>;
    
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
                <p><strong>Name:</strong> <?php echo e(Auth::user()->name); ?></p>
                <p><strong>Email:</strong> <?php echo e(Auth::user()->email); ?></p>
            </div>
        `;
        
        // Screening Questions Summary (if applicable)
        <?php if($job->requires_screening && !empty($job->preliminary_questions)): ?>
        if (currentStep > 2) {
            summaryHTML += `
                <div class="review-section mb-3">
                    <h6>Screening Questions</h6>
            `;
            
            <?php $__currentLoopData = $job->preliminary_questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            const answer<?php echo e($index); ?> = document.querySelector('input[name="preliminary_answers[<?php echo e($index); ?>]"]:checked, textarea[name="preliminary_answers[<?php echo e($index); ?>]"]');
            if (answer<?php echo e($index); ?>) {
                summaryHTML += `
                    <div class="mb-2">
                        <strong><?php echo e($question['question']); ?></strong><br>
                        <span class="text-muted">${answer<?php echo e($index); ?>.value}</span>
                    </div>
                `;
            }
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            
            summaryHTML += `</div>`;
        }
        <?php endif; ?>
        
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('front.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/front/job-application-wizard.blade.php ENDPATH**/ ?>