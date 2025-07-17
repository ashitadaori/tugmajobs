@extends('front.layouts.employer-layout')


@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card bg-white rounded-3 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 mb-2">Edit Job</h1>
                        <p class="text-muted mb-0">Update job details and requirements</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Display success/error messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Display warnings for inactive categories/job types -->
    @if(isset($warnings) && !empty($warnings))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Warning:</strong>
            <ul class="mb-0 mt-2">
                @foreach($warnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Display validation errors -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form id="editJobForm" action="{{ route('employer.jobs.update', $job->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-4">
                    <!-- Job Title -->
                    <div class="col-md-6">
                        <label class="form-label">Job Title <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="title" 
                               class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title', $job->title) }}" 
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Category -->
                  <!-- Category -->
<div class="col-md-6">
    <label class="form-label">Category</label>
    @if(isset($categories) && $categories->count())
        <select name="category_id" class="form-select" required>
            <option value="">Select Category</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id', $job->category_id) == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    @else
        <div class="alert alert-warning p-2">
            No categories available. Please make sure categories are active in the admin panel.
        </div>
    @endif
</div>


                    <!-- Job Type -->
                    <div class="col-md-6">
                        <label class="form-label">Job Type <span class="text-danger">*</span></label>
                        <select name="job_type_id" 
                                class="form-select @error('job_type_id') is-invalid @enderror" 
                                required>
                            <option value="">Select Job Type</option>
                            @foreach($jobTypes as $type)
                                <option value="{{ $type->id }}" 
                                        {{ old('job_type_id', $job->job_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('job_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Location -->
                    <div class="col-md-6">
                        <label class="form-label">Location <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="location" 
                               class="form-control @error('location') is-invalid @enderror" 
                               value="{{ old('location', $job->location) }}" 
                               required>
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Salary Range -->
                    <div class="col-md-6">
                        <label class="form-label">Salary Range (Optional)</label>
                        <div class="input-group">
                            <input type="number" 
                                   name="salary_min" 
                                   class="form-control @error('salary_min') is-invalid @enderror" 
                                   placeholder="Min" 
                                   value="{{ old('salary_min', $job->salary_min) }}"
                                   min="0">
                            <span class="input-group-text">to</span>
                            <input type="number" 
                                   name="salary_max" 
                                   class="form-control @error('salary_max') is-invalid @enderror" 
                                   placeholder="Max" 
                                   value="{{ old('salary_max', $job->salary_max) }}"
                                   min="0">
                        </div>
                        @error('salary_min')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('salary_max')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Experience -->
                    <div class="col-md-6">
                        <label class="form-label">Experience Required <span class="text-danger">*</span></label>
                        <select name="experience_level" 
                                class="form-select @error('experience_level') is-invalid @enderror" 
                                required>
                            <option value="">Select Experience Level</option>
                            <option value="entry" 
                                    {{ old('experience_level', $job->experience_level) == 'entry' ? 'selected' : '' }}>
                                Entry Level
                            </option>
                            <option value="intermediate" 
                                    {{ old('experience_level', $job->experience_level) == 'intermediate' ? 'selected' : '' }}>
                                Intermediate
                            </option>
                            <option value="expert" 
                                    {{ old('experience_level', $job->experience_level) == 'expert' ? 'selected' : '' }}>
                                Expert
                            </option>
                        </select>
                        @error('experience_level')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label">Job Description <span class="text-danger">*</span></label>
                        <textarea name="description" 
                                  class="form-control @error('description') is-invalid @enderror" 
                                  rows="5" 
                                  required 
                                  placeholder="Describe the job role, responsibilities, and what you're looking for...">{{ old('description', $job->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Requirements -->
                    <div class="col-12">
                        <label class="form-label">Requirements <span class="text-danger">*</span></label>
                        <textarea name="requirements" 
                                  class="form-control @error('requirements') is-invalid @enderror" 
                                  rows="5" 
                                  required 
                                  placeholder="List the required skills, qualifications, and experience...">{{ old('requirements', $job->requirements) }}</textarea>
                        @error('requirements')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Benefits -->
                    <div class="col-12">
                        <label class="form-label">Benefits (Optional)</label>
                        <textarea name="benefits" 
                                  class="form-control @error('benefits') is-invalid @enderror" 
                                  rows="3" 
                                  placeholder="List the benefits and perks offered...">{{ old('benefits', $job->benefits) }}</textarea>
                        @error('benefits')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-md-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" 
                                class="form-select @error('status') is-invalid @enderror" 
                                required>
                            <option value="draft" 
                                    {{ old('status', $job->status) == 'draft' ? 'selected' : '' }}>
                                Draft
                            </option>
                            <option value="published" 
                                    {{ old('status', $job->status) == 'published' ? 'selected' : '' }}>
                                Published
                            </option>
                            <option value="closed" 
                                    {{ old('status', $job->status) == 'closed' ? 'selected' : '' }}>
                                Closed
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <a href="{{ route('employer.jobs.index') }}" 
                               class="btn btn-light">
                                <i class="bi bi-arrow-left me-1"></i> Cancel
                            </a>
                            <button type="submit" 
                                    class="btn btn-primary" 
                                    id="submitBtn">
                                <i class="bi bi-check-circle me-1"></i> Update Job
                            </button>
                        </div>
                    </div>
                </div>
            </form>
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
    color: var(--bs-gray-800);
}

.text-danger {
    color: var(--bs-danger) !important;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.btn-primary {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.btn-primary:hover {
    background-color: var(--bs-primary-dark);
    border-color: var(--bs-primary-dark);
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

.is-invalid {
    border-color: var(--bs-danger);
}

.invalid-feedback {
    display: block;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Loading state for submit button */
.btn-loading {
    position: relative;
    pointer-events: none;
}

.btn-loading::after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    margin: auto;
    border: 2px solid transparent;
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editJobForm');
    const submitBtn = document.getElementById('submitBtn');
    const originalBtnText = submitBtn.innerHTML;
    
    // Salary validation
    const salaryMin = document.querySelector('input[name="salary_min"]');
    const salaryMax = document.querySelector('input[name="salary_max"]');
    
    if (salaryMin && salaryMax) {
        function validateSalary() {
            const minVal = parseFloat(salaryMin.value) || 0;
            const maxVal = parseFloat(salaryMax.value) || 0;
            
            if (minVal > 0 && maxVal > 0 && minVal > maxVal) {
                salaryMax.setCustomValidity('Maximum salary must be greater than or equal to minimum salary');
            } else {
                salaryMax.setCustomValidity('');
            }
        }
        
        salaryMin.addEventListener('input', validateSalary);
        salaryMax.addEventListener('input', validateSalary);
    }
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        submitBtn.classList.add('btn-loading');
        submitBtn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Updating...';
        submitBtn.disabled = true;
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.status) {
                // Show success message briefly before redirect
                submitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Updated Successfully!';
                submitBtn.classList.remove('btn-loading');
                submitBtn.classList.add('btn-success');
                
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            } else {
                throw new Error(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Reset button state
            submitBtn.classList.remove('btn-loading');
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
            
            // Show error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
            errorDiv.innerHTML = `
                <strong>Error:</strong> ${error.message || 'An error occurred while updating the job. Please try again.'}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            form.insertBefore(errorDiv, form.firstChild);
            
            // Auto-dismiss error after 5 seconds
            setTimeout(() => {
                if (errorDiv.parentNode) {
                    errorDiv.remove();
                }
            }, 5000);
        });
    });
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    });
});
</script>
@endpush