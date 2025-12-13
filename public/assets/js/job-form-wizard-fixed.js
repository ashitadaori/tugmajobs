/**
 * Job Form Wizard - Fixed Version
 * Handles step-by-step wizard functionality with proper error handling
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the form wizard
    initFormWizard();
    
    // Add form validation
    initFormValidation();
    
    // Add character counters for textareas
    initCharacterCounters();
    
    // Add salary range slider
    initSalaryRangeSlider();
    
    // Add tag input for skills
    initTagInput();
    
    // Add form autosave
    initFormAutosave();
    
    // Initialize location feature
    initLocationFeature();
    
    // Handle server-side validation errors
    handleServerErrors();
});

/**
 * Handle server-side validation errors by showing the appropriate step
 */
function handleServerErrors() {
    const errorElements = document.querySelectorAll('.invalid-feedback.d-block');
    
    if (errorElements.length > 0) {
        // Find which step has errors and navigate to it
        const formSections = document.querySelectorAll('.wizard-section');
        
        for (let i = 0; i < formSections.length; i++) {
            const sectionErrors = formSections[i].querySelectorAll('.invalid-feedback.d-block');
            if (sectionErrors.length > 0) {
                // Navigate to the step with errors
                showStep(i);
                
                // Highlight the first error field
                const firstErrorField = formSections[i].querySelector('.form-control.is-invalid, .form-select.is-invalid');
                if (firstErrorField) {
                    firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstErrorField.focus();
                }
                break;
            }
        }
    }
}

/**
 * Initialize location feature
 * Note: The main Mapbox location autocomplete is handled in the blade template.
 * This function is kept as a fallback but the enhanced version in the template takes precedence.
 */
function initLocationFeature() {
    // Location feature is now handled by the Mapbox autocomplete in the blade template
    // This function is kept for compatibility but does nothing to avoid duplicate handlers
    return;
}

/**
 * Initialize the form wizard
 */
function initFormWizard() {
    const progressSteps = document.querySelectorAll('.progress-step');
    const formSections = document.querySelectorAll('.wizard-section');
    const progressBar = document.querySelector('.progress-bar');
    const nextButtons = document.querySelectorAll('.btn-next-step');
    const prevButtons = document.querySelectorAll('.btn-prev-step');
    
    if (!progressSteps.length || !formSections.length) return;
    
    let currentStep = 0;
    
    // Make showStep available globally for error handling
    window.showStep = showStep;
    
    // Show the current step
    function showStep(stepIndex) {
        currentStep = stepIndex;
        
        // Hide all sections
        formSections.forEach(section => {
            section.style.display = 'none';
        });
        
        // Show the current section
        formSections[stepIndex].style.display = 'block';
        
        // Update progress steps
        progressSteps.forEach((step, index) => {
            if (index < stepIndex) {
                step.classList.add('completed');
                step.classList.remove('active');
            } else if (index === stepIndex) {
                step.classList.add('active');
                step.classList.remove('completed');
            } else {
                step.classList.remove('active', 'completed');
            }
        });
        
        // Update progress bar
        const progress = ((stepIndex + 1) / progressSteps.length) * 100;
        progressBar.style.width = `${progress}%`;
        progressBar.setAttribute('aria-valuenow', progress);
        
        // Update button states
        updateButtonStates();
        
        // Update preview if on last step
        if (stepIndex === formSections.length - 1) {
            updatePreview();
        }
        
        // Scroll to top of form
        const formTop = document.querySelector('.job-form-card').offsetTop;
        window.scrollTo({ top: formTop - 100, behavior: 'smooth' });
    }
    
    // Update button states based on current step
    function updateButtonStates() {
        // Enable/disable previous buttons
        prevButtons.forEach(btn => {
            btn.disabled = currentStep === 0;
        });
        
        // Update next button text on last step
        nextButtons.forEach(btn => {
            if (currentStep === formSections.length - 1) {
                btn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Job Posting';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-success');
            } else {
                btn.innerHTML = 'Next Step <i class="fas fa-arrow-right ms-2"></i>';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-primary');
            }
        });
    }
    
    // Handle next button click
    nextButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Validate current section
            if (validateSection(currentStep)) {
                // If on last step, submit the form
                if (currentStep === formSections.length - 1) {
                    submitForm();
                    return;
                }
                
                // Otherwise, go to next step
                currentStep++;
                showStep(currentStep);
            } else {
                // Show error message
                showErrorMessage('Please fill in all required fields before proceeding.');
            }
        });
    });
    
    // Handle previous button click
    prevButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        });
    });
    
    // Allow clicking on progress steps to navigate
    progressSteps.forEach((step, index) => {
        step.addEventListener('click', function() {
            // Allow navigating to any previous step or next step if current is valid
            if (index <= currentStep || (index === currentStep + 1 && validateSection(currentStep))) {
                currentStep = index;
                showStep(currentStep);
            }
        });
    });
    
    // Initialize the first step
    showStep(currentStep);
    
    // Add location functionality
    initLocationFeature();
}

/**
 * Submit the form with proper error handling
 */
function submitForm() {
    const form = document.getElementById('jobForm');
    const submitBtn = document.querySelector('.btn-next-step');
    
    console.log('submitForm called');
    console.log('Form found:', !!form);
    console.log('Submit button found:', !!submitBtn);
    
    if (!form) {
        console.error('Form not found!');
        return;
    }
    
    // Validate entire form before submission
    const isValid = validateEntireForm();
    console.log('Form validation result:', isValid);
    
    if (!isValid) {
        showErrorMessage('Please fill in all required fields.');
        return;
    }
    
    // Show loading state
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
    }
    
    // Log form data before submission
    const formData = new FormData(form);
    console.log('Form data to be submitted:');
    for (let [key, value] of formData.entries()) {
        console.log(key + ':', value);
    }
    
    // Submit form
    console.log('Submitting form now...');
    form.submit();
}

/**
 * Validate entire form
 */
function validateEntireForm() {
    const requiredFields = document.querySelectorAll('[required]');
    let isValid = true;
    let firstErrorStep = -1;
    
    // Clear previous validation
    requiredFields.forEach(field => {
        field.classList.remove('is-invalid');
    });
    document.querySelectorAll('.invalid-feedback:not(.d-block)').forEach(el => el.remove());
    
    // Check each required field
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
            
            // Find which step this field belongs to
            const fieldStep = findFieldStep(field);
            if (firstErrorStep === -1 || fieldStep < firstErrorStep) {
                firstErrorStep = fieldStep;
            }
            
            // Add error message if doesn't exist
            if (!field.parentNode.querySelector('.invalid-feedback')) {
                const errorMessage = document.createElement('div');
                errorMessage.className = 'invalid-feedback';
                errorMessage.textContent = 'This field is required';
                field.parentNode.appendChild(errorMessage);
            }
        }
    });
    
    // If there are errors, go to first error step
    if (!isValid && firstErrorStep !== -1) {
        showStep(firstErrorStep);
    }
    
    return isValid;
}

/**
 * Find which step a field belongs to
 */
function findFieldStep(field) {
    const sections = document.querySelectorAll('.wizard-section');
    for (let i = 0; i < sections.length; i++) {
        if (sections[i].contains(field)) {
            return i;
        }
    }
    return 0;
}

/**
 * Validate the current section
 */
function validateSection(stepIndex) {
    const formSections = document.querySelectorAll('.wizard-section');
    const currentSection = formSections[stepIndex];
    const requiredFields = currentSection.querySelectorAll('[required]');
    let isValid = true;
    
    // Reset validation messages in current section
    const errorMessages = currentSection.querySelectorAll('.invalid-feedback:not(.d-block)');
    errorMessages.forEach(msg => msg.remove());
    
    // Check each required field in current section
    requiredFields.forEach(field => {
        field.classList.remove('is-invalid');
        
        let fieldValue = field.value.trim();
        
        // Special handling for select fields
        if (field.tagName === 'SELECT' && fieldValue === '') {
            isValid = false;
            field.classList.add('is-invalid');
            addErrorMessage(field, 'Please select an option');
        }
        // Regular text/textarea fields
        else if (!fieldValue) {
            isValid = false;
            field.classList.add('is-invalid');
            addErrorMessage(field, 'This field is required');
        }
    });
    
    return isValid;
}

/**
 * Add error message to field
 */
function addErrorMessage(field, message) {
    if (!field.parentNode.querySelector('.invalid-feedback')) {
        const errorMessage = document.createElement('div');
        errorMessage.className = 'invalid-feedback';
        errorMessage.textContent = message;
        field.parentNode.appendChild(errorMessage);
    }
}

/**
 * Show error message
 */
function showErrorMessage(message) {
    // Remove existing error alerts
    const existingAlerts = document.querySelectorAll('.alert-danger.auto-generated');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new error alert
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger auto-generated';
    alert.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>${message}`;
    
    // Insert at top of current step
    const currentSection = document.querySelector('.wizard-section[style*="display: block"]');
    if (currentSection) {
        currentSection.insertBefore(alert, currentSection.firstChild);
    }
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

/**
 * Update preview on last step
 */
function updatePreview() {
    const formData = new FormData(document.getElementById('jobForm'));
    
    // Update preview fields
    document.getElementById('preview-title').textContent = formData.get('title') || 'Job Title';
    document.getElementById('preview-location').textContent = formData.get('location') || 'Location';
    document.getElementById('preview-vacancy').textContent = formData.get('vacancy') || '1';
    
    // Update job type
    const jobTypeSelect = document.getElementById('job_type_id');
    const selectedJobType = jobTypeSelect.options[jobTypeSelect.selectedIndex];
    document.getElementById('preview-type').textContent = selectedJobType ? selectedJobType.text : 'Job Type';
    
    // Update description
    document.getElementById('preview-description').innerHTML = formData.get('description') ?
        formData.get('description').replace(/\n/g, '<br>') : 'Job description will appear here...';

    // Update qualifications
    const qualifications = formData.get('qualifications');
    const qualificationsEl = document.getElementById('preview-qualifications');
    if (qualificationsEl) {
        qualificationsEl.innerHTML = qualifications ?
            qualifications.replace(/\n/g, '<br>') : 'Job qualifications will appear here...';
    }

    // Update requirements
    const requirements = formData.get('requirements');
    const requirementsSection = document.getElementById('preview-requirements-section');
    if (requirements && requirements.trim()) {
        if (requirementsSection) requirementsSection.style.display = 'block';
        document.getElementById('preview-requirements').innerHTML = requirements.replace(/\n/g, '<br>');
    } else {
        if (requirementsSection) requirementsSection.style.display = 'none';
    }
    
    // Update benefits
    const benefits = formData.get('benefits');
    const benefitsSection = document.getElementById('preview-benefits-section');
    if (benefits && benefits.trim()) {
        benefitsSection.style.display = 'block';
        document.getElementById('preview-benefits').innerHTML = benefits.replace(/\n/g, '<br>');
    } else {
        benefitsSection.style.display = 'none';
    }
    
    // Update salary
    const salaryMin = formData.get('salary_min');
    const salaryMax = formData.get('salary_max');
    const salarySection = document.getElementById('preview-salary-section');
    
    if (salaryMin || salaryMax) {
        salarySection.style.display = 'block';
        let salaryText = '';
        if (salaryMin && salaryMax) {
            salaryText = `₱${Number(salaryMin).toLocaleString()} - ₱${Number(salaryMax).toLocaleString()}`;
        } else if (salaryMin) {
            salaryText = `₱${Number(salaryMin).toLocaleString()}+`;
        } else if (salaryMax) {
            salaryText = `Up to ₱${Number(salaryMax).toLocaleString()}`;
        }
        document.getElementById('preview-salary').textContent = salaryText;
    } else {
        salarySection.style.display = 'none';
    }
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const form = document.getElementById('jobForm');
    
    if (!form) return;
    
    // Prevent default form submission to handle via our custom logic
    form.addEventListener('submit', function(e) {
        // Let it submit naturally, our wizard handles validation
    });
}

/**
 * Initialize character counters for textareas
 */
function initCharacterCounters() {
    const textareas = document.querySelectorAll('textarea[maxlength]');
    
    textareas.forEach(textarea => {
        const maxLength = parseInt(textarea.getAttribute('maxlength'));
        let counterEl = textarea.parentNode.querySelector('.character-counter');
        
        if (!counterEl) {
            counterEl = document.createElement('div');
            counterEl.className = 'character-counter text-muted small mt-1';
            textarea.parentNode.appendChild(counterEl);
        }
        
        function updateCounter() {
            const remaining = maxLength - textarea.value.length;
            counterEl.textContent = `${textarea.value.length}/${maxLength} characters`;
            
            if (remaining < maxLength * 0.1) {
                counterEl.classList.add('text-danger');
                counterEl.classList.remove('text-muted');
            } else {
                counterEl.classList.add('text-muted');
                counterEl.classList.remove('text-danger');
            }
        }
        
        updateCounter();
        textarea.addEventListener('input', updateCounter);
    });
}

/**
 * Initialize salary range slider
 */
function initSalaryRangeSlider() {
    const salaryMinInput = document.getElementById('salary_min');
    const salaryMaxInput = document.getElementById('salary_max');
    
    if (!salaryMinInput || !salaryMaxInput) return;
    
    // Simple input synchronization without external slider library
    function formatCurrency(value) {
        return value ? `₱${Number(value).toLocaleString()}` : '₱0';
    }
    
    function updateDisplays() {
        const minDisplay = document.getElementById('salary_min_display');
        const maxDisplay = document.getElementById('salary_max_display');
        
        if (minDisplay) minDisplay.textContent = formatCurrency(salaryMinInput.value);
        if (maxDisplay) maxDisplay.textContent = formatCurrency(salaryMaxInput.value);
    }
    
    salaryMinInput.addEventListener('input', updateDisplays);
    salaryMaxInput.addEventListener('input', updateDisplays);
    
    // Initial update
    updateDisplays();
}

/**
 * Initialize tag input for skills
 */
function initTagInput() {
    const skillsInput = document.getElementById('skills_input');
    const skillsContainer = document.getElementById('skills_container');
    const hiddenSkillsInput = document.getElementById('skills');
    
    if (!skillsInput || !skillsContainer || !hiddenSkillsInput) return;
    
    let skills = [];
    
    // Load existing skills from hidden input
    if (hiddenSkillsInput.value) {
        try {
            skills = JSON.parse(hiddenSkillsInput.value);
            renderSkills();
        } catch (e) {
            skills = [];
        }
    }
    
    function addSkill(skill) {
        skill = skill.trim();
        if (skill && !skills.includes(skill)) {
            skills.push(skill);
            updateHiddenInput();
            renderSkills();
        }
    }
    
    function removeSkill(skill) {
        skills = skills.filter(s => s !== skill);
        updateHiddenInput();
        renderSkills();
    }
    
    function updateHiddenInput() {
        hiddenSkillsInput.value = JSON.stringify(skills);
    }
    
    function renderSkills() {
        skillsContainer.innerHTML = '';
        skills.forEach(skill => {
            const tag = document.createElement('span');
            tag.className = 'badge bg-primary me-2 mb-2 skill-tag';
            tag.innerHTML = `
                ${skill}
                <button type="button" class="btn-close btn-close-white ms-1" data-skill="${skill}"></button>
            `;
            skillsContainer.appendChild(tag);
        });
        
        // Add event listeners to remove buttons
        skillsContainer.querySelectorAll('.btn-close').forEach(btn => {
            btn.addEventListener('click', function() {
                removeSkill(this.dataset.skill);
            });
        });
    }
    
    // Handle Enter and comma for adding skills
    skillsInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addSkill(this.value);
            this.value = '';
        }
    });
    
    // Handle blur event
    skillsInput.addEventListener('blur', function() {
        if (this.value.trim()) {
            addSkill(this.value);
            this.value = '';
        }
    });
}

/**
 * Initialize form autosave
 */
function initFormAutosave() {
    const form = document.getElementById('jobForm');
    if (!form) return;
    
    // Simple autosave to localStorage every 30 seconds
    setInterval(() => {
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        localStorage.setItem('job_form_autosave', JSON.stringify(data));
    }, 30000);
    
    // Load autosaved data on page load
    const autosaved = localStorage.getItem('job_form_autosave');
    if (autosaved) {
        try {
            const data = JSON.parse(autosaved);
            
            // Only restore if form is empty (no old() data)
            if (!document.getElementById('title').value) {
                Object.keys(data).forEach(key => {
                    const field = form.querySelector(`[name="${key}"]`);
                    if (field && field.type !== 'hidden') {
                        if (field.type === 'checkbox') {
                            field.checked = data[key] === '1';
                        } else {
                            field.value = data[key];
                        }
                    }
                });
            }
        } catch (e) {
            // Ignore parsing errors
        }
    }
}
