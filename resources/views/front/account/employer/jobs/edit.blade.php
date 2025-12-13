@extends('layouts.employer')

@section('page_title', 'Edit Job - ' . $job->title)

@push('styles')
<style>
.rejection-alert {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(238, 90, 111, 0.3);
}

.rejection-alert h4 {
    color: white;
    font-weight: 600;
    margin-bottom: 15px;
}

.rejection-alert .rejection-reason {
    background: rgba(255, 255, 255, 0.2);
    padding: 15px;
    border-radius: 10px;
    margin-top: 15px;
}

.job-form-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border: none;
}

.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 16px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

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

.location-no-results {
    padding: 12px 16px;
    text-align: center;
    color: #666;
}

.location-suggestion-item .highlight {
    background-color: #fff3cd;
    font-weight: 600;
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
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-pencil-square text-primary me-2"></i>
                        Edit Job Posting
                    </h2>
                    <p class="text-muted mb-0">Update your job details</p>
                </div>
                <a href="{{ route('employer.jobs.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Jobs
                </a>
            </div>
        </div>
    </div>

    @if($job->isRejected())
    <div class="rejection-alert">
        <div class="d-flex align-items-start">
            <i class="bi bi-exclamation-triangle-fill fs-1 me-3"></i>
            <div class="flex-grow-1">
                <h4>
                    <i class="bi bi-exclamation-circle me-2"></i>
                    Job Posting Needs Revision
                </h4>
                <p class="mb-2">Your job posting was reviewed by our admin team and requires some changes before it can be published.</p>
                
                @if($job->rejection_reason)
                <div class="rejection-reason">
                    <strong><i class="bi bi-chat-left-text me-2"></i>Admin Feedback:</strong>
                    <p class="mb-0 mt-2">{{ $job->rejection_reason }}</p>
                </div>
                @endif

                <div class="mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <small>Please address the feedback above and resubmit your job posting for approval.</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card job-form-card">
        <div class="card-body p-4">
            <form id="editJobForm" method="POST" action="{{ route('employer.jobs.update', $job->id) }}">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3">
                            <i class="bi bi-info-circle text-primary me-2"></i>
                            Basic Information
                        </h5>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label for="title" class="form-label">Job Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $job->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="job_type_id" class="form-label">Job Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('job_type_id') is-invalid @enderror" 
                                id="job_type_id" name="job_type_id" required>
                            <option value="">Select Type</option>
                            @foreach($jobTypes as $type)
                                <option value="{{ $type->id }}" {{ old('job_type_id', $job->job_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('job_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" 
                                id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $job->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                        <div class="location-autocomplete-wrapper">
                            <div class="input-group">
                                <input type="text" class="form-control @error('location') is-invalid @enderror"
                                       id="location" name="location" value="{{ old('location', $job->location) }}" required
                                       placeholder="Start typing to search locations..."
                                       autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="use-current-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                </button>
                            </div>
                            <div class="location-suggestions" id="location-suggestions"></div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            <i class="fas fa-info-circle me-1"></i>Search for locations in Sta. Cruz, Davao del Sur
                        </small>
                        <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $job->latitude) }}">
                        <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $job->longitude) }}">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="salary_range" class="form-label">Salary Range</label>
                        <input type="text" class="form-control @error('salary_range') is-invalid @enderror" 
                               id="salary_range" name="salary_range" value="{{ old('salary_range', $job->salary_range) }}" 
                               placeholder="e.g., $50,000 - $70,000">
                        @error('salary_range')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="deadline" class="form-label">Application Deadline</label>
                        <input type="date" class="form-control @error('deadline') is-invalid @enderror" 
                               id="deadline" name="deadline" value="{{ old('deadline', $job->deadline ? $job->deadline->format('Y-m-d') : '') }}">
                        @error('deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="vacancy" class="form-label">Number of Positions <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('vacancy') is-invalid @enderror" 
                               id="vacancy" name="vacancy" value="{{ old('vacancy', $job->vacancy ?? 1) }}" 
                               min="1" max="100" required>
                        @php
                            $acceptedCount = $job->applications()->where('status', 'approved')->count();
                        @endphp
                        @if($job->status === \App\Models\Job::STATUS_CLOSED && $acceptedCount > 0)
                            <div class="alert alert-info mt-2 mb-0 py-2 px-3" style="font-size: 0.875rem;">
                                <i class="bi bi-info-circle me-1"></i>
                                <strong>Job is closed:</strong> {{ $acceptedCount }} position(s) filled. 
                                Increase vacancy above {{ $acceptedCount }} to reopen this job.
                            </div>
                        @elseif($acceptedCount > 0)
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-check-circle text-success me-1"></i>
                                {{ $acceptedCount }} position(s) already filled
                            </small>
                        @else
                            <small class="text-muted">How many people do you want to hire for this position?</small>
                        @endif
                        @error('vacancy')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Job Description -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3">
                            <i class="bi bi-file-text text-primary me-2"></i>
                            Job Description
                        </h5>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="6" required>{{ old('description', $job->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label for="qualifications" class="form-label">Qualifications <span class="text-danger">*</span></label>
                        <small class="text-muted d-block mb-2">List the qualifications required for this position (one per line)</small>
                        <textarea class="form-control @error('qualifications') is-invalid @enderror"
                                  id="qualifications" name="qualifications" rows="6" required
                                  placeholder="- Graduate of a 4-year course
- With experience as an advantage
- Good communication skills">{{ old('qualifications', $job->qualifications) }}</textarea>
                        @error('qualifications')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label for="requirements" class="form-label">Additional Requirements</label>
                        <small class="text-muted d-block mb-2">Any special requirements (licenses, certifications, physical requirements, etc.)</small>
                        <textarea class="form-control @error('requirements') is-invalid @enderror"
                                  id="requirements" name="requirements" rows="4"
                                  placeholder="- Must have own motorcycle
- With valid driver's license">{{ old('requirements', $job->requirements) }}</textarea>
                        @error('requirements')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label for="benefits" class="form-label">Benefits</label>
                        <textarea class="form-control @error('benefits') is-invalid @enderror"
                                  id="benefits" name="benefits" rows="4"
                                  placeholder="- Competitive salary
- Health insurance
- 13th month pay">{{ old('benefits', $job->benefits) }}</textarea>
                        @error('benefits')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Required Documents Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3">
                            <i class="bi bi-file-earmark-text text-primary me-2"></i>
                            Required Documents
                        </h5>
                        <p class="text-muted mb-3">Specify documents that applicants must submit if their application is approved (e.g., 2x2 ID Photo, certificates, valid IDs).</p>
                    </div>

                    <div class="col-12">
                        <div id="job_requirements_container">
                            @if($job->jobRequirements && $job->jobRequirements->count() > 0)
                                @foreach($job->jobRequirements as $index => $requirement)
                                    <div class="requirement-item">
                                        <button type="button" class="remove-requirement" title="Remove requirement">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <input type="hidden" name="job_requirements[{{ $index }}][id]" value="{{ $requirement->id }}">
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Document Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="job_requirements[{{ $index }}][name]"
                                                       value="{{ old("job_requirements.$index.name", $requirement->name) }}"
                                                       placeholder="e.g., 2x2 ID Photo" required>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label">Description</label>
                                                <input type="text" class="form-control" name="job_requirements[{{ $index }}][description]"
                                                       value="{{ old("job_requirements.$index.description", $requirement->description) }}"
                                                       placeholder="e.g., Recent photo with white background">
                                            </div>
                                        </div>
                                        <div class="form-check mt-2">
                                            <input type="checkbox" class="form-check-input" name="job_requirements[{{ $index }}][is_required]"
                                                   id="req_required_{{ $index }}" {{ old("job_requirements.$index.is_required", $requirement->is_required) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="req_required_{{ $index }}">
                                                This document is mandatory
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm" id="add_requirement_btn">
                            <i class="fas fa-plus me-2"></i>Add Document Requirement
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('employer.jobs.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="bi bi-save me-2"></i>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
/**
 * Mapbox Location Autocomplete for Edit Form
 */
(function() {
    'use strict';

    let searchTimeout = null;
    let currentSuggestions = [];
    let selectedIndex = -1;

    document.addEventListener('DOMContentLoaded', function() {
        initMapboxLocationAutocomplete();
        initFormSubmission();
    });

    function initFormSubmission() {
        const form = document.getElementById('editJobForm');
        const submitBtn = document.getElementById('submitBtn');

        if (form && submitBtn) {
            form.addEventListener('submit', function(e) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Updating...';
                return true;
            });
        }
    }

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

            if (searchTimeout) clearTimeout(searchTimeout);

            // Clear coordinates when user types
            if (latInput) latInput.value = '';
            if (lngInput) lngInput.value = '';

            if (query.length < 2) {
                hideSuggestions();
                return;
            }

            searchTimeout = setTimeout(function() {
                searchLocations(query);
            }, 300);
        });

        // Keyboard navigation
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

        // Use Current Location button
        if (locationBtn) {
            locationBtn.addEventListener('click', function() {
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by this browser.');
                    return;
                }

                locationBtn.disabled = true;
                locationBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        if (latInput) latInput.value = lat;
                        if (lngInput) lngInput.value = lng;

                        // Reverse geocode
                        fetch('/api/location/reverse-geocode?lat=' + lat + '&lng=' + lng)
                            .then(response => response.json())
                            .then(data => {
                                if (data.features && data.features.length > 0) {
                                    locationInput.value = data.features[0].place_name;
                                } else {
                                    locationInput.value = 'Sta. Cruz, Davao del Sur';
                                }
                                resetLocationButton();
                            })
                            .catch(() => {
                                locationInput.value = 'Sta. Cruz, Davao del Sur';
                                resetLocationButton();
                            });
                    },
                    function(error) {
                        alert('Unable to get your location. Please enter it manually.');
                        resetLocationButton();
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            });
        }

        function resetLocationButton() {
            locationBtn.disabled = false;
            locationBtn.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
        }
    }

    function searchLocations(query) {
        const suggestionsContainer = document.getElementById('location-suggestions');

        suggestionsContainer.innerHTML = '<div class="location-loading"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
        suggestionsContainer.classList.add('show');

        fetch('/api/location/search?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                if (data.suggestions && data.suggestions.length > 0) {
                    currentSuggestions = data.suggestions;
                    renderSuggestions(data.suggestions, query);
                } else {
                    currentSuggestions = [];
                    suggestionsContainer.innerHTML = '<div class="location-no-results">No locations found</div>';
                }
            })
            .catch(error => {
                console.error('Location search error:', error);
                suggestionsContainer.innerHTML = '<div class="location-no-results">Error searching locations</div>';
            });
    }

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

        suggestionsContainer.querySelectorAll('.location-suggestion-item').forEach(item => {
            item.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                if (currentSuggestions[index]) {
                    selectSuggestion(currentSuggestions[index]);
                }
            });
        });
    }

    function selectSuggestion(suggestion) {
        const locationInput = document.getElementById('location');
        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        locationInput.value = suggestion.full_address || suggestion.place_name || suggestion.name;

        if (suggestion.coordinates) {
            if (latInput) latInput.value = suggestion.coordinates.latitude;
            if (lngInput) lngInput.value = suggestion.coordinates.longitude;
        } else if (suggestion.geometry && suggestion.geometry.coordinates) {
            if (lngInput) lngInput.value = suggestion.geometry.coordinates[0];
            if (latInput) latInput.value = suggestion.geometry.coordinates[1];
        }

        hideSuggestions();
    }

    function highlightMatch(text, query) {
        if (!query) return text;
        const regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }

    function updateSelectedItem(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === selectedIndex);
            if (index === selectedIndex) item.scrollIntoView({ block: 'nearest' });
        });
    }

    function hideSuggestions() {
        const suggestionsContainer = document.getElementById('location-suggestions');
        if (suggestionsContainer) suggestionsContainer.classList.remove('show');
        selectedIndex = -1;
    }
})();

// Job Requirements Management
(function() {
    'use strict';

    let requirementIndex = {{ $job->jobRequirements ? $job->jobRequirements->count() : 0 }};

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
