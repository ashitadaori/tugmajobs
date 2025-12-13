/**
 * Job Form Wizard
 * Enhances the job creation form with step-by-step wizard functionality
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
    
    // Add rich text editor for description
    initRichTextEditor();
    
    // Add form autosave
    initFormAutosave();
});

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
    
    // Show the current step
    function showStep(stepIndex) {
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
                btn.textContent = 'Submit Job';
                btn.classList.add('btn-submit');
            } else {
                btn.textContent = 'Next Step';
                btn.classList.remove('btn-submit');
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
                    document.getElementById('jobForm').submit();
                    return;
                }
                
                // Otherwise, go to next step
                currentStep++;
                showStep(currentStep);
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
            // Only allow navigating to previous steps or the next step
            if (index <= currentStep + 1) {
                // Validate current section if moving forward
                if (index > currentStep && !validateSection(currentStep)) {
                    return;
                }
                
                currentStep = index;
                showStep(currentStep);
            }
        });
    });
    
    // Initialize the first step
    showStep(currentStep);
}

/**
 * Validate the current section
 */
function validateSection(stepIndex) {
    const formSections = document.querySelectorAll('.wizard-section');
    const currentSection = formSections[stepIndex];
    const requiredFields = currentSection.querySelectorAll('[required]');
    let isValid = true;
    
    // Reset validation messages
    const errorMessages = currentSection.querySelectorAll('.invalid-feedback');
    errorMessages.forEach(msg => msg.remove());
    
    // Check each required field
    requiredFields.forEach(field => {
        field.classList.remove('is-invalid');
        
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
            
            // Add error message
            const errorMessage = document.createElement('div');
            errorMessage.className = 'invalid-feedback';
            errorMessage.textContent = 'This field is required';
            field.parentNode.appendChild(errorMessage);
        }
    });
    
    return isValid;
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const form = document.getElementById('jobForm');
    
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        // Prevent form submission if validation fails
        if (!validateForm()) {
            e.preventDefault();
        }
    });
    
    // Validate the entire form
    function validateForm() {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        // Reset validation messages
        const errorMessages = form.querySelectorAll('.invalid-feedback');
        errorMessages.forEach(msg => msg.remove());
        
        // Check each required field
        requiredFields.forEach(field => {
            field.classList.remove('is-invalid');
            
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
                
                // Add error message
                const errorMessage = document.createElement('div');
                errorMessage.className = 'invalid-feedback';
                errorMessage.textContent = 'This field is required';
                field.parentNode.appendChild(errorMessage);
            }
        });
        
        return isValid;
    }
}

/**
 * Initialize character counters for textareas
 */
function initCharacterCounters() {
    const textareas = document.querySelectorAll('textarea[maxlength]');
    
    textareas.forEach(textarea => {
        const maxLength = parseInt(textarea.getAttribute('maxlength'));
        const counterEl = document.createElement('div');
        counterEl.className = 'character-counter';
        counterEl.textContent = `${textarea.value.length}/${maxLength}`;
        
        textarea.parentNode.appendChild(counterEl);
        
        textarea.addEventListener('input', function() {
            counterEl.textContent = `${textarea.value.length}/${maxLength}`;
            
            if (textarea.value.length > maxLength * 0.9) {
                counterEl.classList.add('near-limit');
            } else {
                counterEl.classList.remove('near-limit');
            }
        });
    });
}

/**
 * Initialize salary range slider
 */
function initSalaryRangeSlider() {
    const salaryMinInput = document.getElementById('salary_min');
    const salaryMaxInput = document.getElementById('salary_max');
    const salaryRangeSlider = document.getElementById('salary_range');
    
    if (!salaryMinInput || !salaryMaxInput || !salaryRangeSlider) return;
    
    // Initialize noUiSlider if available
    if (window.noUiSlider) {
        noUiSlider.create(salaryRangeSlider, {
            start: [
                parseInt(salaryMinInput.value) || 15000,
                parseInt(salaryMaxInput.value) || 50000
            ],
            connect: true,
            step: 1000,
            range: {
                'min': 0,
                'max': 200000
            },
            format: {
                to: function(value) {
                    return Math.round(value);
                },
                from: function(value) {
                    return Math.round(value);
                }
            }
        });
        
        // Update inputs when slider changes
        salaryRangeSlider.noUiSlider.on('update', function(values, handle) {
            const value = values[handle];
            
            if (handle === 0) {
                salaryMinInput.value = value;
            } else {
                salaryMaxInput.value = value;
            }
            
            // Update the displayed values
            document.getElementById('salary_min_display').textContent = formatCurrency(values[0]);
            document.getElementById('salary_max_display').textContent = formatCurrency(values[1]);
        });
        
        // Update slider when inputs change
        salaryMinInput.addEventListener('change', function() {
            salaryRangeSlider.noUiSlider.set([this.value, null]);
        });
        
        salaryMaxInput.addEventListener('change', function() {
            salaryRangeSlider.noUiSlider.set([null, this.value]);
        });
    } else {
        // Fallback for when noUiSlider is not available
        salaryMinInput.addEventListener('input', function() {
            document.getElementById('salary_min_display').textContent = formatCurrency(this.value);
        });
        
        salaryMaxInput.addEventListener('input', function() {
            document.getElementById('salary_max_display').textContent = formatCurrency(this.value);
        });
    }
    
    // Format currency
    function formatCurrency(value) {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }
}

/**
 * Initialize tag input for skills
 */
function initTagInput() {
    const skillsInput = document.getElementById('skills_input');
    const skillsContainer = document.getElementById('skills_container');
    const skillsHiddenInput = document.getElementById('skills');
    
    if (!skillsInput || !skillsContainer || !skillsHiddenInput) return;
    
    // Initialize with existing skills if any
    const existingSkills = skillsHiddenInput.value ? JSON.parse(skillsHiddenInput.value) : [];
    existingSkills.forEach(skill => {
        addSkillTag(skill);
    });
    
    // Add skill when pressing Enter
    skillsInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            
            const skill = this.value.trim();
            if (skill) {
                addSkillTag(skill);
                this.value = '';
                updateHiddenInput();
            }
        }
    });
    
    // Add skill when clicking outside
    skillsInput.addEventListener('blur', function() {
        const skill = this.value.trim();
        if (skill) {
            addSkillTag(skill);
            this.value = '';
            updateHiddenInput();
        }
    });
    
    // Add a skill tag
    function addSkillTag(skill) {
        const tag = document.createElement('div');
        tag.className = 'skill-tag';
        tag.innerHTML = `
            <span>${skill}</span>
            <button type="button" class="remove-skill">×</button>
        `;
        
        // Remove skill when clicking the remove button
        tag.querySelector('.remove-skill').addEventListener('click', function() {
            tag.remove();
            updateHiddenInput();
        });
        
        skillsContainer.appendChild(tag);
    }
    
    // Update the hidden input with all skills
    function updateHiddenInput() {
        const skills = [];
        skillsContainer.querySelectorAll('.skill-tag span').forEach(span => {
            skills.push(span.textContent);
        });
        
        skillsHiddenInput.value = JSON.stringify(skills);
    }
}

/**
 * Initialize rich text editor for description
 */
function initRichTextEditor() {
    const descriptionTextarea = document.getElementById('description');
    const requirementsTextarea = document.getElementById('requirements');
    const benefitsTextarea = document.getElementById('benefits');
    
    // Initialize TinyMCE if available
    if (window.tinymce) {
        const editorConfig = {
            height: 300,
            menubar: false,
            plugins: 'lists link autolink',
            toolbar: 'undo redo | formatselect | bold italic | bullist numlist | link',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 16px; }',
            branding: false
        };
        
        if (descriptionTextarea) {
            tinymce.init({
                ...editorConfig,
                selector: '#description'
            });
        }
        
        if (requirementsTextarea) {
            tinymce.init({
                ...editorConfig,
                selector: '#requirements'
            });
        }
        
        if (benefitsTextarea) {
            tinymce.init({
                ...editorConfig,
                selector: '#benefits'
            });
        }
    }
}

/**
 * Initialize form autosave
 */
function initFormAutosave() {
    const form = document.getElementById('jobForm');
    const autosaveKey = 'job_form_autosave';
    let autosaveTimer;
    
    if (!form) return;
    
    // Load autosaved data
    const autosavedData = localStorage.getItem(autosaveKey);
    if (autosavedData) {
        const data = JSON.parse(autosavedData);
        
        // Ask user if they want to restore
        const restorePrompt = document.createElement('div');
        restorePrompt.className = 'autosave-prompt';
        restorePrompt.innerHTML = `
            <div class="autosave-message">
                <i class="fas fa-history"></i>
                <span>We found a previously unsaved job draft. Would you like to restore it?</span>
            </div>
            <div class="autosave-actions">
                <button type="button" class="btn btn-sm btn-primary" id="restore-autosave">Restore</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="discard-autosave">Discard</button>
            </div>
        `;
        
        form.prepend(restorePrompt);
        
        // Restore autosaved data
        document.getElementById('restore-autosave').addEventListener('click', function() {
            Object.keys(data).forEach(key => {
                const field = form.elements[key];
                if (field) {
                    field.value = data[key];
                }
            });
            
            restorePrompt.remove();
        });
        
        // Discard autosaved data
        document.getElementById('discard-autosave').addEventListener('click', function() {
            localStorage.removeItem(autosaveKey);
            restorePrompt.remove();
        });
    }
    
    // Autosave form data
    form.addEventListener('input', function() {
        clearTimeout(autosaveTimer);
        
        autosaveTimer = setTimeout(function() {
            const formData = {};
            
            // Collect form data
            const formElements = form.elements;
            for (let i = 0; i < formElements.length; i++) {
                const field = formElements[i];
                
                if (field.name && field.type !== 'submit' && field.type !== 'button') {
                    formData[field.name] = field.value;
                }
            }
            
            // Save to localStorage
            localStorage.setItem(autosaveKey, JSON.stringify(formData));
            
            // Show autosave indicator
            showAutosaveIndicator();
        }, 1000);
    });
    
    // Show autosave indicator
    function showAutosaveIndicator() {
        let indicator = document.getElementById('autosave-indicator');
        
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'autosave-indicator';
            indicator.className = 'autosave-indicator';
            indicator.innerHTML = '<i class="fas fa-save"></i> Saving...';
            
            document.body.appendChild(indicator);
        }
        
        indicator.classList.add('show');
        
        setTimeout(function() {
            indicator.innerHTML = '<i class="fas fa-check"></i> Saved';
            
            setTimeout(function() {
                indicator.classList.remove('show');
            }, 1500);
        }, 500);
    }
    
    // Clear autosave data on successful submission
    form.addEventListener('submit', function() {
        localStorage.removeItem(autosaveKey);
    });
}

/**
 * Initialize job preview updates
 */
function initJobPreview() {
    const form = document.getElementById('jobForm');
    if (!form) return;
    
    // Update preview when form fields change
    form.addEventListener('input', updateJobPreview);
    form.addEventListener('change', updateJobPreview);
    
    // Initial preview update
    updateJobPreview();
}

/**
 * Update job preview based on form data
 */
function updateJobPreview() {
    // Update job title
    const titleInput = document.getElementById('title');
    const previewTitle = document.getElementById('preview-title');
    if (titleInput && previewTitle) {
        previewTitle.textContent = titleInput.value || 'Job Title';
    }
    
    // Update location
    const locationInput = document.getElementById('location');
    const previewLocation = document.getElementById('preview-location');
    if (locationInput && previewLocation) {
        previewLocation.textContent = locationInput.value || 'Location';
    }
    
    // Update job type
    const jobTypeSelect = document.getElementById('job_type_id');
    const previewType = document.getElementById('preview-type');
    if (jobTypeSelect && previewType) {
        const selectedOption = jobTypeSelect.options[jobTypeSelect.selectedIndex];
        previewType.textContent = selectedOption ? selectedOption.text : 'Job Type';
    }
    
    // Update vacancy
    const vacancyInput = document.getElementById('vacancy');
    const previewVacancy = document.getElementById('preview-vacancy');
    if (vacancyInput && previewVacancy) {
        previewVacancy.textContent = vacancyInput.value || '1';
    }
    
    // Update description
    const descriptionInput = document.getElementById('description');
    const previewDescription = document.getElementById('preview-description');
    if (descriptionInput && previewDescription) {
        previewDescription.textContent = descriptionInput.value || 'Job description will appear here...';
    }
    
    // Update requirements
    const requirementsInput = document.getElementById('requirements');
    const previewRequirements = document.getElementById('preview-requirements');
    if (requirementsInput && previewRequirements) {
        previewRequirements.textContent = requirementsInput.value || 'Job requirements will appear here...';
    }
    
    // Update benefits
    const benefitsInput = document.getElementById('benefits');
    const previewBenefits = document.getElementById('preview-benefits');
    const previewBenefitsSection = document.getElementById('preview-benefits-section');
    if (benefitsInput && previewBenefits && previewBenefitsSection) {
        if (benefitsInput.value.trim()) {
            previewBenefits.textContent = benefitsInput.value;
            previewBenefitsSection.style.display = 'block';
        } else {
            previewBenefitsSection.style.display = 'none';
        }
    }
    
    // Update salary range
    const salaryMinInput = document.getElementById('salary_min');
    const salaryMaxInput = document.getElementById('salary_max');
    const previewSalary = document.getElementById('preview-salary');
    const previewSalarySection = document.getElementById('preview-salary-section');
    
    if (salaryMinInput && salaryMaxInput && previewSalary && previewSalarySection) {
        const minSalary = salaryMinInput.value;
        const maxSalary = salaryMaxInput.value;
        
        if (minSalary || maxSalary) {
            let salaryText = '';
            if (minSalary && maxSalary) {
                salaryText = `₱${parseInt(minSalary).toLocaleString()} - ₱${parseInt(maxSalary).toLocaleString()}`;
            } else if (minSalary) {
                salaryText = `₱${parseInt(minSalary).toLocaleString()}+`;
            } else if (maxSalary) {
                salaryText = `Up to ₱${parseInt(maxSalary).toLocaleString()}`;
            }
            
            previewSalary.textContent = salaryText;
            previewSalarySection.style.display = 'block';
        } else {
            previewSalarySection.style.display = 'none';
        }
    }
}

// Initialize job preview when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add this to the existing initialization
    initJobPreview();
});
