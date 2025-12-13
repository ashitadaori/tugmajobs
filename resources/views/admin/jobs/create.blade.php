@extends('layouts.admin')

@section('page_title', 'Post New Job')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Post New Job</h4>
                    <p class="text-muted mb-0">Create a new job posting for the platform</p>
                </div>
                <a href="{{ route('admin.jobs.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Jobs
                </a>
            </div>

            <!-- Job Creation Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form id="jobForm" method="POST" action="{{ route('admin.jobs.store') }}">
                        @csrf

                        <!-- Basic Information -->
                        <div class="mb-4">
                            <h5 class="mb-3">Basic Information</h5>
                            
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="title" class="form-label">Job Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           placeholder="e.g. Senior Software Engineer" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="vacancy" class="form-label">Number of Positions <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="vacancy" name="vacancy" 
                                           min="1" value="1" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="company_id" class="form-label">Company (Optional)</label>
                                    <select class="form-select" id="company_id" name="company_id">
                                        <option value="">No Company (Standalone Job)</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Select a company to associate this job with, or leave blank for standalone job posting</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="jobType" class="form-label">Job Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="jobType" name="jobType" required>
                                        <option value="">Select Job Type</option>
                                        @foreach($job_types as $jobType)
                                            <option value="{{ $jobType->id }}">{{ $jobType->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Company Information -->
                        <div class="mb-4">
                            <h5 class="mb-3">Company Information <span class="text-muted small">(Optional)</span></h5>
                            
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" 
                                           placeholder="e.g. Tech Solutions Inc. (leave blank if not provided)">
                                    <div class="form-text">Leave blank if partner didn't provide company information</div>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="company_website" class="form-label">Company Website</label>
                                    <input type="url" class="form-control" id="company_website" name="company_website" 
                                           placeholder="https://example.com">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="mb-4">
                            <h5 class="mb-3">Location</h5>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                    <div class="location-input-wrapper position-relative">
                                        <input type="text" class="form-control" id="location" name="location"
                                               placeholder="Search location in Sta. Cruz, Davao del Sur"
                                               autocomplete="off" required>
                                        <input type="hidden" id="latitude" name="latitude">
                                        <input type="hidden" id="longitude" name="longitude">
                                        <input type="hidden" id="location_address" name="location_address">

                                        <!-- Location suggestions dropdown -->
                                        <div id="location-suggestions" class="location-suggestions" style="display: none;">
                                            <ul class="list-group position-absolute w-100" style="z-index: 1050; max-height: 250px; overflow-y: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                            </ul>
                                        </div>
                                    </div>
                                    <small class="text-muted">Type to search for barangays and places in Sta. Cruz</small>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Salary & Experience -->
                        <div class="mb-4">
                            <h5 class="mb-3">Salary & Experience</h5>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="salary_min" class="form-label">Minimum Salary (₱)</label>
                                    <input type="number" class="form-control" id="salary_min" name="salary_min"
                                           min="0" step="1000" placeholder="15000">
                                    <small class="text-muted">Optional</small>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="salary_max" class="form-label">Maximum Salary (₱)</label>
                                    <input type="number" class="form-control" id="salary_max" name="salary_max"
                                           min="0" step="1000" placeholder="25000">
                                    <small class="text-muted">Optional</small>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="experience_level" class="form-label">Experience Level <span class="text-danger">*</span></label>
                                    <select class="form-select" id="experience_level" name="experience_level" required>
                                        <option value="">Select Level</option>
                                        <option value="entry">Entry Level (0-2 years)</option>
                                        <option value="intermediate">Intermediate (2-5 years)</option>
                                        <option value="expert">Expert (5+ years)</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Job Details -->
                        <div class="mb-4">
                            <h5 class="mb-3">Job Details</h5>

                            <div class="mb-3">
                                <label for="description" class="form-label">Job Description</label>
                                <textarea class="form-control" id="description" name="description" rows="6"
                                          placeholder="Describe the role, responsibilities, and what makes this opportunity great..."></textarea>
                                <div class="form-text">Optional - Describe the job role and responsibilities</div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="mb-3">
                                <label for="qualifications" class="form-label">Qualifications <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="qualifications" name="qualifications" rows="8"
                                          placeholder="- Graduate of a 4-year BUSINESS-related course (preferably Accountancy)
- With experience as an advantage, or without experience as long as trainable
- Knowledge in accounting and business management
- Has keen attention to detail and paperwork
- Good communication skills
- Honest and trustworthy" required></textarea>
                                <div class="form-text">List the qualifications required for this position (one per line)</div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="mb-3">
                                <label for="requirements" class="form-label">Additional Requirements</label>
                                <textarea class="form-control" id="requirements" name="requirements" rows="4"
                                          placeholder="- Must have own motorcycle (with driver's license)
- Must be willing to be assigned for field work
- With valid professional driver's license"></textarea>
                                <div class="form-text">Any special requirements (licenses, certifications, physical requirements, etc.)</div>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="mb-3">
                                <label for="benefits" class="form-label">Benefits</label>
                                <textarea class="form-control" id="benefits" name="benefits" rows="4"
                                          placeholder="- Competitive salary
- Health insurance
- 13th month pay
- Paid leave"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Required Documents Section -->
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-file-alt me-2"></i>Required Documents
                                <span class="text-muted small">(for applicants to submit)</span>
                            </h5>
                            <p class="text-muted small mb-3">
                                Add documents that applicants must submit during the hiring process (e.g., 2x2 ID Photo, Resume, Certificates, etc.)
                            </p>

                            <div id="requirements-container">
                                <!-- Requirements will be added here dynamically -->
                            </div>

                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-requirement-btn">
                                <i class="fas fa-plus me-2"></i>Add Document Requirement
                            </button>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('admin.jobs.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <div>
                                <button type="submit" name="is_draft" value="1" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-save me-2"></i>Save as Draft
                                </button>
                                <button type="submit" name="is_draft" value="0" class="btn btn-primary">
                                    <i class="fas fa-check me-2"></i>Post Job
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.location-input-wrapper {
    position: relative;
}

.location-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 4px 4px;
    z-index: 1050;
}

.location-suggestions .list-group-item {
    cursor: pointer;
    border: none;
    border-bottom: 1px solid #eee;
    padding: 10px 15px;
}

.location-suggestions .list-group-item:hover {
    background-color: #f8f9fa;
}

.location-suggestions .list-group-item:last-child {
    border-bottom: none;
}

.location-suggestions .list-group-item .location-name {
    font-weight: 500;
    color: #333;
}

.location-suggestions .list-group-item .location-address {
    font-size: 0.85em;
    color: #666;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test if toast function is available
    console.log('Toast function available:', typeof showAdminToast);

    const form = document.getElementById('jobForm');

    // ========== Document Requirements Management ==========
    let requirementIndex = 0;
    const requirementsContainer = document.getElementById('requirements-container');
    const addRequirementBtn = document.getElementById('add-requirement-btn');

    addRequirementBtn.addEventListener('click', function() {
        addRequirement();
    });

    function addRequirement(name = '', description = '', isRequired = true) {
        const requirementHtml = `
            <div class="requirement-item card mb-3" data-index="${requirementIndex}">
                <div class="card-body py-3">
                    <div class="row align-items-start">
                        <div class="col-md-5">
                            <input type="text" class="form-control"
                                   name="job_requirements[${requirementIndex}][name]"
                                   placeholder="Document name (e.g., 2x2 ID Photo)"
                                   value="${name}" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control"
                                   name="job_requirements[${requirementIndex}][description]"
                                   placeholder="Description (optional)"
                                   value="${description}">
                        </div>
                        <div class="col-md-2">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox"
                                       name="job_requirements[${requirementIndex}][is_required]"
                                       id="req_required_${requirementIndex}"
                                       value="1" ${isRequired ? 'checked' : ''}>
                                <label class="form-check-label" for="req_required_${requirementIndex}">
                                    Required
                                </label>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-requirement" title="Remove">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        requirementsContainer.insertAdjacentHTML('beforeend', requirementHtml);
        requirementIndex++;

        // Add remove event listener to the new button
        const newItem = requirementsContainer.lastElementChild;
        newItem.querySelector('.remove-requirement').addEventListener('click', function() {
            newItem.remove();
        });
    }

    // Remove requirement functionality (for dynamically added items)
    requirementsContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-requirement')) {
            e.target.closest('.requirement-item').remove();
        }
    });
    // ========== End Document Requirements Management ==========
    const locationInput = document.getElementById('location');
    const suggestionsContainer = document.getElementById('location-suggestions');
    const suggestionsList = suggestionsContainer.querySelector('ul');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const addressInput = document.getElementById('location_address');

    let searchTimeout;
    let mapboxConfig = null;

    // Load Mapbox configuration
    fetch('/api/location/config')
        .then(response => response.json())
        .then(config => {
            mapboxConfig = config;
            console.log('Mapbox config loaded:', config);
        })
        .catch(error => console.error('Failed to load Mapbox config:', error));

    // Location search functionality
    locationInput.addEventListener('input', function() {
        const query = this.value.trim();

        if (query.length < 2) {
            hideSuggestions();
            return;
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchPlaces(query);
        }, 300);
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!locationInput.closest('.location-input-wrapper').contains(e.target)) {
            hideSuggestions();
        }
    });

    function searchPlaces(query) {
        fetch(`/api/location/search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.suggestions && data.suggestions.length > 0) {
                    showSuggestions(data.suggestions);
                } else {
                    hideSuggestions();
                }
            })
            .catch(error => {
                console.error('Location search error:', error);
                hideSuggestions();
            });
    }

    function showSuggestions(places) {
        suggestionsList.innerHTML = '';

        places.forEach(place => {
            const li = document.createElement('li');
            li.className = 'list-group-item';
            li.innerHTML = `
                <div class="location-name">${place.name || place.place_name}</div>
                <div class="location-address">${place.place_name || place.full_address || ''}</div>
            `;

            li.addEventListener('click', () => {
                selectPlace(place);
            });

            suggestionsList.appendChild(li);
        });

        suggestionsContainer.style.display = 'block';
    }

    function hideSuggestions() {
        suggestionsContainer.style.display = 'none';
    }

    function selectPlace(place) {
        const coordinates = place.geometry ? place.geometry.coordinates :
                          place.coordinates ? [place.coordinates.longitude, place.coordinates.latitude] : null;

        if (coordinates) {
            // Validate that the location is within Sta. Cruz bounds
            if (mapboxConfig && mapboxConfig.stacruz_bounds && !isWithinStaCruz(coordinates[0], coordinates[1])) {
                if (typeof showAdminToast === 'function') {
                    showAdminToast('Please select a location within Sta. Cruz, Davao del Sur only.', 'warning');
                } else {
                    alert('Please select a location within Sta. Cruz, Davao del Sur only.');
                }
                return;
            }

            locationInput.value = place.name || extractLocationName(place.place_name);
            lngInput.value = coordinates[0];
            latInput.value = coordinates[1];
            addressInput.value = place.place_name || place.full_address;

            hideSuggestions();

            // Trigger change event
            locationInput.dispatchEvent(new Event('change'));
        }
    }

    function isWithinStaCruz(longitude, latitude) {
        if (!mapboxConfig || !mapboxConfig.stacruz_bounds) return true;

        const bounds = mapboxConfig.stacruz_bounds;
        return longitude >= bounds.southwest[0] &&
               longitude <= bounds.northeast[0] &&
               latitude >= bounds.southwest[1] &&
               latitude <= bounds.northeast[1];
    }

    function extractLocationName(placeName) {
        if (!placeName) return '';

        // Split by comma and get the first meaningful part
        const parts = placeName.split(',');
        let name = parts[0].trim();

        // If it's just a number or very short, try the next part
        if (name.length < 3 || /^\d+$/.test(name)) {
            name = parts[1] ? parts[1].trim() : name;
        }

        return name;
    }
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        
        // Get the submit button that was clicked
        const submitButton = document.activeElement;
        const isDraft = submitButton.name === 'is_draft' && submitButton.value === '1';
        
        // Disable submit buttons
        const submitButtons = form.querySelectorAll('button[type="submit"]');
        submitButtons.forEach(btn => {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        });
        
        // Prepare form data
        const formData = new FormData(form);
        if (isDraft) {
            formData.set('is_draft', '1');
        } else {
            formData.set('is_draft', '0');
        }
        
        // Debug: Log form action
        console.log('Form action:', form.action);
        console.log('Is draft:', isDraft);
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        || document.querySelector('input[name="_token"]')?.value;

        // Submit form
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => {
            console.log('Response status:', response.status);

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // Try to get the text to see what the error is
                return response.text().then(text => {
                    console.error('Non-JSON response:', text.substring(0, 500));
                    throw new Error('Server error. Please check if all required fields are filled correctly.');
                });
            }

            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            
            if (data.success) {
                // Show success toast
                console.log('Success! Showing toast...');
                if (typeof showAdminToast === 'function') {
                    showAdminToast(data.message || 'Job posted successfully!', 'success', 2000);
                } else {
                    console.error('showAdminToast function not found!');
                    alert(data.message || 'Job posted successfully!');
                }
                
                // Redirect after showing toast
                console.log('Redirecting to:', data.redirect);
                setTimeout(() => {
                    window.location.href = data.redirect || '{{ route("admin.jobs.index") }}';
                }, 2000);
            } else {
                // Show validation errors
                let errorMessages = [];
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        const input = document.getElementById(key) || document.querySelector(`[name="${key}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            // Find the invalid-feedback element properly
                            let feedback = input.parentElement.querySelector('.invalid-feedback');
                            if (!feedback) {
                                feedback = input.nextElementSibling;
                            }
                            if (feedback && feedback.classList.contains('invalid-feedback')) {
                                feedback.textContent = data.errors[key][0];
                            }
                        }
                        errorMessages.push(data.errors[key][0]);
                    });
                }

                // Show error toast with first error message
                if (typeof showAdminToast === 'function' && errorMessages.length > 0) {
                    showAdminToast(errorMessages[0], 'error', 5000);
                } else if (data.message) {
                    if (typeof showAdminToast === 'function') {
                        showAdminToast(data.message, 'error', 5000);
                    } else {
                        alert(data.message);
                    }
                }

                // Re-enable buttons
                submitButtons.forEach((btn, index) => {
                    btn.disabled = false;
                    if (index === 0) {
                        btn.innerHTML = '<i class="fas fa-save me-2"></i>Save as Draft';
                    } else {
                        btn.innerHTML = '<i class="fas fa-check me-2"></i>Post Job';
                    }
                });

                // Scroll to first error
                const firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            console.error('Error details:', error.message);
            
            if (typeof showAdminToast === 'function') {
                showAdminToast('Error: ' + error.message, 'error', 5000);
            } else {
                alert('An error occurred: ' + error.message);
            }
            
            // Re-enable buttons
            submitButtons.forEach((btn, index) => {
                btn.disabled = false;
                if (index === 0) {
                    btn.innerHTML = '<i class="fas fa-save me-2"></i>Save as Draft';
                } else {
                    btn.innerHTML = '<i class="fas fa-check me-2"></i>Post Job';
                }
            });
        });
    });
});
</script>
@endpush
