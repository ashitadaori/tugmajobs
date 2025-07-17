@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card bg-white rounded-3 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 mb-2">Create New Job</h1>
                        <p class="text-muted mb-0">Fill in the details to post a new job opportunity</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.jobs') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Back to Jobs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Form -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <!-- Alert Container -->
                    <div id="alertContainer" class="mb-4" style="display: none;">
                        <div class="alert" role="alert"></div>
                    </div>
                    
                    <form id="createJobForm" action="{{ route('admin.job.save') }}" method="POST">
                        @csrf
                        <!-- Job Details Section -->
                        <div class="mb-4">
                            <h5 class="card-title mb-3">Job Details</h5>
                            <div class="row g-3">
                                <!-- Job Title -->
                                <div class="col-md-6">
                                    <label for="title" class="form-label">Job Title <span class="text-danger">*</span></label>
                                    <input type="text" id="title" name="title" class="form-control" placeholder="Enter job title" value="{{ old('title') }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Category -->
                                <div class="col-md-6">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select id="category" name="category" class="form-select" required>
                                        <option value="">Select Category</option>
                                        @if ($categories->isNotEmpty())
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                        @endif
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Job Type -->
                                <div class="col-md-6">
                                    <label for="jobType" class="form-label">Job Type <span class="text-danger">*</span></label>
                                    <select id="jobType" name="jobType" class="form-select" required>
                                        <option value="">Select Job Type</option>
                                        @if($job_types->isNotEmpty())
                                        @foreach ($job_types as $job_type)
                                            <option value="{{ $job_type->id }}" {{ old('jobType') == $job_type->id ? 'selected' : '' }}>
                                                {{ $job_type->name }}
                                            </option>
                                        @endforeach
                                        @endif
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Vacancy -->
                                <div class="col-md-6">
                                    <label for="vacancy" class="form-label">Number of Vacancies <span class="text-danger">*</span></label>
                                    <input type="number" id="vacancy" name="vacancy" class="form-control" min="1" value="{{ old('vacancy', 1) }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Location -->
                                <div class="col-md-12">
                                    <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                    <select id="location" name="location" class="form-select" required>
                                        <option value="">Select Location in Digos City</option>
                                        @foreach($locations ?? [] as $location)
                                            <option value="{{ $location['name'] }}" 
                                                data-lat="{{ $location['lat'] }}"
                                                data-lng="{{ $location['lng'] }}"
                                                data-address="{{ $location['name'] }}, Digos City, Davao del Sur"
                                                {{ old('location') == $location['name'] ? 'selected' : '' }}>
                                                {{ $location['name'] }}, Digos City
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                                    <input type="hidden" name="location_address" id="location_address" value="{{ old('location_address') }}">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Salary Range -->
                                <div class="col-md-12">
                                    <label class="form-label">Salary Range (Monthly) <span class="text-danger">*</span></label>
                                    <div class="row g-2">
                                        <div class="col-sm-6">
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" name="salary_min" class="form-control" placeholder="Minimum" value="{{ old('salary_min') }}" required>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="input-group">
                                                <span class="input-group-text">₱</span>
                                                <input type="number" name="salary_max" class="form-control" placeholder="Maximum" value="{{ old('salary_max') }}" required>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-text">Enter the salary range in Philippine Peso (₱)</div>
                                </div>

                                <!-- Experience Level -->
                                <div class="col-md-6">
                                    <label for="experience_level" class="form-label">Experience Required <span class="text-danger">*</span></label>
                                    <select id="experience_level" name="experience_level" class="form-select" required>
                                        <option value="">Select Experience Level</option>
                                        <option value="entry" {{ old('experience_level') == 'entry' ? 'selected' : '' }}>Entry Level (0-2 years)</option>
                                        <option value="intermediate" {{ old('experience_level') == 'intermediate' ? 'selected' : '' }}>Intermediate (3-5 years)</option>
                                        <option value="expert" {{ old('experience_level') == 'expert' ? 'selected' : '' }}>Expert (6+ years)</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Job Description Section -->
                        <div class="mb-4">
                            <h5 class="card-title mb-3">Job Description</h5>
                            <div class="row g-3">
                                <!-- Description -->
                                <div class="col-12">
                                    <label for="description" class="form-label">Job Description <span class="text-danger">*</span></label>
                                    <textarea id="description" name="description" class="form-control" rows="5" required>{{ old('description') }}</textarea>
                                    <div class="form-text">Describe the role, responsibilities, and ideal candidate</div>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Requirements -->
                                <div class="col-12">
                                    <label for="requirements" class="form-label">Requirements <span class="text-danger">*</span></label>
                                    <textarea id="requirements" name="requirements" class="form-control" rows="5" required>{{ old('requirements') }}</textarea>
                                    <div class="form-text">List the skills, qualifications, and experience needed</div>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Benefits -->
                                <div class="col-12">
                                    <label for="benefits" class="form-label">Benefits</label>
                                    <textarea id="benefits" name="benefits" class="form-control" rows="3">{{ old('benefits') }}</textarea>
                                    <div class="form-text">Highlight perks, benefits, and reasons to join your company</div>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Company Information Section -->
                        <div class="mb-4">
                            <h5 class="card-title mb-3">Company Information</h5>
                            <div class="row g-3">
                                <!-- Company Name -->
                                <div class="col-md-6">
                                    <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                    <input type="text" id="company_name" name="company_name" class="form-control" placeholder="Enter company name" value="{{ old('company_name') }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Company Website -->
                                <div class="col-md-6">
                                    <label for="company_website" class="form-label">Company Website</label>
                                    <input type="url" id="company_website" name="company_website" class="form-control" placeholder="https://example.com" value="{{ old('company_website') }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="pt-3 border-top">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('admin.jobs') }}" class="btn btn-light px-4">Cancel</a>
                                <button type="submit" name="is_draft" value="1" class="btn btn-outline-primary px-4">Save as Draft</button>
                                <button type="submit" class="btn btn-primary px-4">Post Job</button>
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
.welcome-card {
    background: linear-gradient(to right, var(--bs-primary-bg-subtle), var(--bs-white));
    border-left: 4px solid var(--bs-primary);
}
.form-label { 
    font-weight: 500;
    margin-bottom: 0.5rem; 
}
textarea { 
    resize: vertical; 
}
.invalid-feedback { 
    display: none; 
}
.form-control.is-invalid,
.form-select.is-invalid { 
    border-color: var(--bs-danger); 
}
.form-control.is-invalid + .invalid-feedback,
.form-select.is-invalid + .invalid-feedback { 
    display: block; 
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle location selection
    $('#location').change(function() {
        var selectedOption = $(this).find(':selected');
        $('#latitude').val(selectedOption.data('lat'));
        $('#longitude').val(selectedOption.data('lng'));
        $('#location_address').val(selectedOption.data('address'));
    });

    // Form validation and submission
    $('#createJobForm').on('submit', function(e) {
        e.preventDefault();
        
        // Reset previous validation states
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').hide();
        $('#alertContainer').hide();
        
        // Disable submit buttons
        const submitButtons = $(this).find('button[type="submit"]');
        submitButtons.prop('disabled', true);
        
        // Get form data
        const formData = new FormData(this);
        
        // Submit form
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('#alertContainer').html(
                        '<div class="alert alert-success">' +
                        '<i class="bi bi-check-circle me-2"></i>' +
                        response.message +
                        '</div>'
                    ).show();
                    
                    // Redirect after success
                    setTimeout(function() {
                        window.location.href = response.redirect || "{{ route('admin.jobs') }}";
                    }, 1500);
                } else {
                    // Show error message
                    $('#alertContainer').html(
                        '<div class="alert alert-danger">' +
                        '<i class="bi bi-exclamation-circle me-2"></i>' +
                        (response.message || 'An error occurred. Please try again.') +
                        '</div>'
                    ).show();
                }
            },
            error: function(xhr) {
                // Handle validation errors
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function(field) {
                        const input = $(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        input.siblings('.invalid-feedback').text(errors[field][0]).show();
                    });
                }
                
                // Show general error message
                $('#alertContainer').html(
                    '<div class="alert alert-danger">' +
                    '<i class="bi bi-exclamation-circle me-2"></i>' +
                    'Please correct the errors in the form.' +
                    '</div>'
                ).show();
            },
            complete: function() {
                // Re-enable submit buttons
                submitButtons.prop('disabled', false);
            }
        });
    });
});
</script>
@endpush 