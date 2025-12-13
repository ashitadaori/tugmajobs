@extends('layouts.jobseeker')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Category Selection Required Alert -->
            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div>
                        <strong>Job Category Selection Required!</strong><br>
                        Please select your preferred job categories to view available jobs and get personalized recommendations using our advanced matching system.
                    </div>
                </div>
            </div>

            <div class="card modern-card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-tags me-2"></i>Select Your Job Preferences
                    </h4>
                    <small class="opacity-75">Choose at least one category to continue browsing jobs</small>
                </div>

                <div class="card-body">
                    <form id="jobPreferencesForm" method="POST" action="{{ route('jobs.save-preferences') }}">
                        @csrf
                        
                        <!-- Job Categories Section -->
                        <div class="mb-5">
                            <h5 class="section-title">
                                <i class="fas fa-list-alt text-primary me-2"></i>
                                Job Categories 
                                <span class="badge bg-danger">Required</span>
                            </h5>
                            <p class="text-muted mb-4">Select the job categories that interest you. You can select multiple categories.</p>
                            
                            <div class="row">
                                @foreach($categories as $category)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="preference-card">
                                            <input type="checkbox" 
                                                   id="category_{{ $category->id }}" 
                                                   name="categories[]" 
                                                   value="{{ $category->id }}"
                                                   class="category-checkbox">
                                            <label for="category_{{ $category->id }}" class="preference-label">
                                                <div class="preference-icon">
                                                    @if($category->icon)
                                                        <i class="{{ $category->icon }}"></i>
                                                    @else
                                                        <i class="fas fa-briefcase"></i>
                                                    @endif
                                                </div>
                                                <div class="preference-content">
                                                    <h6 class="preference-title">{{ $category->name }}</h6>
                                                    <small class="preference-description">{{ $category->description ?? 'Job opportunities in ' . $category->name }}</small>
                                                </div>
                                                <div class="preference-check">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div id="categoryError" class="invalid-feedback d-none">
                                Please select at least one job category.
                            </div>
                        </div>

                        <!-- Job Types Section (Optional) -->
                        <div class="mb-5">
                            <h5 class="section-title">
                                <i class="fas fa-clock text-info me-2"></i>
                                Job Types 
                                <span class="badge bg-secondary">Optional</span>
                            </h5>
                            <p class="text-muted mb-4">Select your preferred job types (full-time, part-time, etc.). This helps us provide better recommendations.</p>
                            
                            <div class="row">
                                @foreach($jobTypes as $jobType)
                                    <div class="col-md-6 col-lg-3 mb-3">
                                        <div class="preference-card secondary">
                                            <input type="checkbox" 
                                                   id="jobtype_{{ $jobType->id }}" 
                                                   name="job_types[]" 
                                                   value="{{ $jobType->id }}"
                                                   class="jobtype-checkbox">
                                            <label for="jobtype_{{ $jobType->id }}" class="preference-label">
                                                <div class="preference-icon">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                                <div class="preference-content">
                                                    <h6 class="preference-title">{{ $jobType->name }}</h6>
                                                </div>
                                                <div class="preference-check">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- K-Means Info Section -->
                        <div class="alert alert-info mb-4">
                            <h6 class="alert-heading">
                                <i class="fas fa-robot me-2"></i>Smart Job Matching
                            </h6>
                            <p class="mb-0">
                                Our advanced K-Means clustering algorithm analyzes your preferences along with other jobseekers' patterns 
                                to provide you with personalized job recommendations that match your interests and career goals.
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <div class="selected-count">
                                <small class="text-muted">
                                    <span id="categoryCount">0</span> categories selected
                                </small>
                            </div>
                            
                            <div class="action-buttons">
                                <button type="button" class="btn btn-outline-secondary me-2" id="skipBtn">
                                    <i class="fas fa-forward me-1"></i>Skip for Now
                                </button>
                                <button type="submit" class="btn btn-primary" id="savePreferencesBtn" disabled>
                                    <i class="fas fa-save me-1"></i>Save Preferences & Continue
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
.modern-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.section-title {
    color: #333;
    font-weight: 600;
    margin-bottom: 1rem;
}

.preference-card {
    position: relative;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    transition: all 0.3s ease;
    cursor: pointer;
    height: 100%;
}

.preference-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.preference-card.secondary {
    border-color: #e9ecef;
}

.preference-card.secondary:hover {
    border-color: #17a2b8;
}

.preference-checkbox,
.jobtype-checkbox {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.preference-label {
    display: flex;
    align-items: center;
    padding: 1rem;
    margin: 0;
    cursor: pointer;
    transition: all 0.3s ease;
}

.preference-icon {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.2rem;
    color: #6c757d;
    transition: all 0.3s ease;
}

.preference-content {
    flex: 1;
}

.preference-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #333;
}

.preference-description {
    color: #6c757d;
    line-height: 1.4;
}

.preference-check {
    width: 24px;
    height: 24px;
    border: 2px solid #dee2e6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.preference-check i {
    opacity: 0;
    transform: scale(0.5);
    transition: all 0.3s ease;
    color: white;
}

/* Checked state */
.category-checkbox:checked + .preference-label .preference-icon {
    background: var(--primary-color);
    color: white;
}

.jobtype-checkbox:checked + .preference-label .preference-icon {
    background: #17a2b8;
    color: white;
}

.category-checkbox:checked + .preference-label .preference-check {
    background: var(--primary-color);
    border-color: var(--primary-color);
}

.jobtype-checkbox:checked + .preference-label .preference-check {
    background: #17a2b8;
    border-color: #17a2b8;
}

.category-checkbox:checked + .preference-label .preference-check i,
.jobtype-checkbox:checked + .preference-label .preference-check i {
    opacity: 1;
    transform: scale(1);
}

.category-checkbox:checked + .preference-label {
    background: rgba(82, 73, 255, 0.05);
}

.jobtype-checkbox:checked + .preference-label {
    background: rgba(23, 162, 184, 0.05);
}

.selected-count {
    display: flex;
    align-items: center;
}

.action-buttons .btn {
    min-width: 120px;
}

@media (max-width: 768px) {
    .preference-label {
        padding: 0.75rem;
    }
    
    .preference-icon {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }
    
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .action-buttons .btn {
        width: 100%;
    }
}
</style>
@endsection

@section('customJs')
<script>
$(document).ready(function() {
    let selectedCategories = 0;
    
    // Update counter and button state
    function updateCounter() {
        selectedCategories = $('.category-checkbox:checked').length;
        $('#categoryCount').text(selectedCategories);
        
        if (selectedCategories > 0) {
            $('#savePreferencesBtn').prop('disabled', false);
            $('#categoryError').addClass('d-none');
        } else {
            $('#savePreferencesBtn').prop('disabled', true);
        }
    }
    
    // Handle category selection
    $('.category-checkbox').change(function() {
        updateCounter();
    });
    
    // Handle form submission
    $('#jobPreferencesForm').submit(function(e) {
        e.preventDefault();
        
        if (selectedCategories === 0) {
            $('#categoryError').removeClass('d-none');
            $('html, body').animate({
                scrollTop: $('#categoryError').offset().top - 100
            }, 500);
            return false;
        }
        
        const formData = new FormData(this);
        const btn = $('#savePreferencesBtn');
        const originalText = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...').prop('disabled', true);
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status) {
                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Continue to Jobs',
                        confirmButtonColor: '#5249FF'
                    }).then((result) => {
                        window.location.href = response.redirect;
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                        confirmButtonColor: '#5249FF'
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while saving your preferences.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    title: 'Error!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#5249FF'
                });
            },
            complete: function() {
                btn.html(originalText).prop('disabled', selectedCategories === 0);
            }
        });
    });
    
    // Handle skip button
    $('#skipBtn').click(function() {
        Swal.fire({
            title: 'Skip Category Selection?',
            text: 'You will need to select categories later to get personalized job recommendations.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Skip Anyway',
            cancelButtonText: 'Stay Here',
            confirmButtonColor: '#6c757d',
            cancelButtonColor: '#5249FF'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route("jobs") }}';
            }
        });
    });
    
    // Initialize counter
    updateCounter();
});
</script>
@endsection
