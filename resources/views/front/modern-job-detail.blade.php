@extends('front.layouts.app')

@section('content')
<div class="job-detail-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <a href="{{ route('jobs') }}" class="back-link">
                    <i class="fas fa-arrow-left me-2"></i>Back to Jobs
                </a>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-lg-8">
                <div class="d-flex align-items-center">
                    <div class="company-badge me-4" style="background: var(--primary-color);">
                        {{ substr($job->employer->employerProfile->company_name ?? 'C', 0, 1) }}
                    </div>
                    <div>
                        <h1 class="job-title mb-2">{{ $job->title }}</h1>
                        <p class="company-name mb-0">
                            {{ $job->employer->employerProfile->company_name }}
                            <x-verified-badge :user="$job->employer" size="sm" />
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                @auth
                    @if(Auth::user()->role === 'jobseeker')
                        <button type="button" 
                                id="saveJobBtn-{{ $job->id }}"
                                onclick="toggleSaveJob({{ $job->id }})"
                                class="btn btn-outline-primary save-job-btn me-2 {{ $count > 0 ? 'saved' : '' }}">
                            <i class="fa-heart {{ $count > 0 ? 'fas text-danger' : 'far text-muted' }}" id="saveJobIcon-{{ $job->id }}"></i>
                            <span>{{ $count > 0 ? 'Saved' : 'Save Job' }}</span>
                        </button>
                        <button type="button" class="btn btn-primary apply-job-btn" data-bs-toggle="modal" data-bs-target="#applicationModal">
                            <i class="fas fa-paper-plane me-2"></i>Apply Now
                        </button>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">
                        <i class="far fa-heart text-muted"></i>
                        <span>Save Job</span>
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Apply Now
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 mb-4">
            <!-- Job Details -->
            <div class="content-card mb-4">
                @include('front.message')

                <div class="job-tags mb-4">
                    <span class="tag">
                        <i class="fas fa-map-marker-alt"></i> {{ $job->getFullAddress() ?: $job->location }}
                    </span>
                    <span class="tag">
                        <i class="fas fa-clock"></i> {{ $job->jobType->name }}
                    </span>
                    @if (!is_null($job->salary))
                        <span class="tag">
                            <i class="fas fa-money-bill-wave"></i> {{ $job->salary }}
                        </span>
                    @endif
                    <span class="tag">
                        <i class="fas fa-calendar"></i> Posted {{ \Carbon\Carbon::parse($job->created_at)->diffForHumans() }}
                    </span>
                </div>

                <div class="content-section">
                    <h3>Job Description</h3>
                    <div class="content-text">
                        {!! nl2br($job->description) !!}
                    </div>
                </div>

                @if (!empty($job->responsibility))
                    <div class="content-section">
                        <h3>Responsibilities</h3>
                        <div class="content-text">
                            {!! nl2br($job->responsibility) !!}
                        </div>
                    </div>
                @endif

                @if (!empty($job->qualifications))
                    <div class="content-section">
                        <h3>Qualifications</h3>
                        <div class="content-text">
                            {!! nl2br($job->qualifications) !!}
                        </div>
                    </div>
                @endif

                @if (!empty($job->benefits))
                    <div class="content-section">
                        <h3>Benefits</h3>
                        <div class="content-text">
                            {!! nl2br($job->benefits) !!}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Applicants Section -->
            @if (Auth::user() && Auth::user()->id == $job->user_id)
                <div class="content-card">
                    <h3 class="mb-4">Applicants</h3>
                    @if ($applications->isNotEmpty())
                        <div class="applicants-list">
                            @foreach ($applications as $application)
                                <div class="applicant-item">
                                    <div class="applicant-info">
                                        <h4>{{ $application->user->name }}</h4>
                                        <p class="mb-0">{{ $application->user->email }}</p>
                                        <p class="mb-0">{{ $application->user->mobile }}</p>
                                    </div>
                                    <div class="applicant-date">
                                        {{ \Carbon\Carbon::parse($application->applied_date)->format('d M, Y') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="no-applicants">
                            <i class="fas fa-users mb-3"></i>
                            <p>No applicants yet</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Job Summary -->
            <div class="content-card mb-4">
                <h3>Job Summary</h3>
                <div class="summary-list">
                    <div class="summary-item">
                        <span class="label">Published on</span>
                        <span class="value">{{ \Carbon\Carbon::parse($job->created_at)->format('d M, Y') }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Vacancy</span>
                        <span class="value">{{ $job->vacancy }} Position(s)</span>
                    </div>
                    @if (!empty($job->salary))
                        <div class="summary-item">
                            <span class="label">Salary</span>
                            <span class="value">{{ $job->salary }}</span>
                        </div>
                    @endif
                    <div class="summary-item">
                        <span class="label">Location</span>
                        <span class="value">{{ $job->getFullAddress() ?: $job->location }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Job Type</span>
                        <span class="value">{{ $job->jobType->name }}</span>
                    </div>
                </div>
            </div>

            <!-- Company Details -->
            <div class="content-card">
                <h3>Company Details</h3>
                <div class="summary-list">
                    <div class="summary-item">
                        <span class="label">Company Name</span>
                        <span class="value">{{ $job->employer->employerProfile->company_name }}</span>
                    </div>
                    @if (!empty($job->employer->employerProfile->industry))
                        <div class="summary-item">
                            <span class="label">Industry</span>
                            <span class="value">{{ $job->employer->employerProfile->industry }}</span>
                        </div>
                    @endif
                    @if (!empty($job->employer->employerProfile->company_size))
                        <div class="summary-item">
                            <span class="label">Company Size</span>
                            <span class="value">{{ $job->employer->employerProfile->company_size }}</span>
                        </div>
                    @endif
                    @if (!empty($job->employer->employerProfile->location))
                        <div class="summary-item">
                            <span class="label">Location</span>
                            <span class="value">{{ $job->employer->employerProfile->location }}</span>
                        </div>
                    @endif
                    @if (!empty($job->employer->employerProfile->founded_year))
                        <div class="summary-item">
                            <span class="label">Founded</span>
                            <span class="value">{{ $job->employer->employerProfile->founded_year }}</span>
                        </div>
                    @endif
                    @if (!empty($job->employer->employerProfile->website))
                        <div class="summary-item">
                            <span class="label">Website</span>
                            <a href="{{ $job->employer->employerProfile->website }}" target="_blank" class="website-link">
                                {{ $job->employer->employerProfile->website }}
                                <i class="fas fa-external-link-alt ms-2"></i>
                            </a>
                        </div>
                    @endif
                </div>

                @if (!empty($job->employer->employerProfile->company_description))
                    <div class="company-description mt-4">
                        <h4>About the Company</h4>
                        <div class="content-text">
                            {!! nl2br($job->employer->employerProfile->company_description) !!}
                        </div>
                    </div>
                @endif

                @if (!empty($job->employer->employerProfile->benefits_offered))
                    <div class="company-benefits mt-4">
                        <h4>Benefits Offered</h4>
                        <div class="benefits-list">
                            @foreach($job->employer->employerProfile->benefits_offered as $benefit)
                                <span class="benefit-tag">
                                    <i class="fas fa-check-circle me-2"></i>{{ $benefit }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (!empty($job->employer->employerProfile->company_culture))
                    <div class="company-culture mt-4">
                        <h4>Company Culture</h4>
                        <div class="culture-list">
                            @foreach($job->employer->employerProfile->company_culture as $culture)
                                <span class="culture-tag">
                                    <i class="fas fa-star me-2"></i>{{ $culture }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (!empty($job->employer->employerProfile->specialties))
                    <div class="company-specialties mt-4">
                        <h4>Specialties</h4>
                        <div class="specialties-list">
                            @foreach($job->employer->employerProfile->specialties as $specialty)
                                <span class="specialty-tag">{{ $specialty }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Application Modal -->
<div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="applicationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applicationModalLabel">Apply for {{ $job->title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="applicationForm" action="{{ route('jobs.apply', $job->id) }}" method="POST" enctype="multipart/form-data" onsubmit="submitApplication(event)">
                    @csrf
                    <input type="hidden" name="job_id" value="{{ $job->id }}">
                    <div class="mb-3">
                        <label for="coverLetter" class="form-label">Cover Letter (Optional)</label>
                        <textarea class="form-control" id="coverLetter" name="cover_letter" rows="5" placeholder="Tell us why you're a great fit for this position..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="resume" class="form-label">Resume (PDF, DOC, DOCX)</label>
                        <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
                        <div class="form-text">Maximum file size: 5MB</div>
                        <div id="resumeError" class="invalid-feedback" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitApplication">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary-color: #6366f1;
    --secondary-color: #4f46e5;
    --text-dark: #1f2937;
    --text-light: #6b7280;
    --bg-light: #f9fafb;
    --bg-dark: #111827;
}

.job-detail-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    padding: 3rem 0;
    margin-top: -2rem;
    color: #fff;
}

.back-link {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: color 0.3s ease;
    font-size: 0.95rem;
}

.back-link:hover {
    color: #fff;
}

.company-badge {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.5rem;
    font-weight: 600;
    color: #fff;
}

.job-title {
    font-size: 2rem;
    font-weight: 600;
    color: #fff;
    margin: 0;
}

.company-name {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.9);
}

.save-job-btn, .apply-job-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
}

.save-job-btn {
    background-color: rgba(255, 255, 255, 0.1);
    color: #fff;
}

.save-job-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.save-job-btn.saved {
    background-color: rgba(255, 255, 255, 0.2);
}

.apply-job-btn {
    background-color: #fff;
    color: var(--primary-color);
}

.apply-job-btn:hover {
    background-color: rgba(255, 255, 255, 0.9);
}

.content-card {
    background: #fff;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.job-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background-color: var(--bg-light);
    border-radius: 6px;
    color: var(--text-dark);
    font-size: 0.9rem;
}

.tag i {
    color: var(--primary-color);
}

.content-section {
    margin-bottom: 2rem;
}

.content-section:last-child {
    margin-bottom: 0;
}

.content-section h3 {
    color: var(--text-dark);
    font-size: 1.25rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.content-text {
    color: var(--text-light);
    line-height: 1.6;
    white-space: pre-line;
}

.summary-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--bg-light);
}

.summary-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.summary-item .label {
    color: var(--text-light);
    font-size: 0.95rem;
}

.summary-item .value {
    color: var(--text-dark);
    font-weight: 500;
}

.website-link {
    color: var(--primary-color);
    text-decoration: none;
    transition: color 0.3s ease;
}

.website-link:hover {
    color: var(--secondary-color);
}

.applicants-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.applicant-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: var(--bg-light);
    border-radius: 8px;
}

.applicant-info h4 {
    margin-bottom: 0.5rem;
    color: var(--text-dark);
    font-size: 1.1rem;
}

.applicant-info p {
    color: var(--text-light);
    font-size: 0.9rem;
}

.applicant-date {
    color: var(--primary-color);
    font-weight: 500;
    font-size: 0.9rem;
}

.no-applicants {
    text-align: center;
    padding: 2rem;
}

.no-applicants i {
    font-size: 3rem;
    color: var(--primary-color);
    display: block;
    margin-bottom: 1rem;
}

.no-applicants p {
    color: var(--text-light);
    margin: 0;
}

/* Modal Styles */
.modal-content {
    border-radius: 12px;
    border: none;
}

.modal-header {
    border-bottom: 1px solid var(--bg-light);
    padding: 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid var(--bg-light);
    padding: 1.5rem;
}

.form-label {
    color: var(--text-dark);
    font-weight: 500;
}

.form-control {
    border: 1px solid var(--bg-light);
    border-radius: 8px;
    padding: 0.75rem;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
}

.form-text {
    color: var(--text-light);
    font-size: 0.85rem;
}

.btn-secondary {
    background-color: var(--bg-light);
    border: none;
    color: var(--text-dark);
}

.btn-secondary:hover {
    background-color: #e5e7eb;
}

.btn-primary {
    background-color: var(--primary-color);
    border: none;
}

.btn-primary:hover {
    background-color: var(--secondary-color);
}

.company-description h4,
.company-benefits h4,
.company-culture h4,
.company-specialties h4 {
    color: var(--text-dark);
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.benefits-list,
.culture-list,
.specialties-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.benefit-tag,
.culture-tag,
.specialty-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    background-color: var(--bg-light);
    border-radius: 6px;
    color: var(--text-dark);
    font-size: 0.9rem;
}

.benefit-tag i {
    color: #10b981;
}

.culture-tag i {
    color: #f59e0b;
}

.specialty-tag {
    background-color: rgba(99, 102, 241, 0.1);
    color: var(--primary-color);
}

.company-description {
    padding-top: 1.5rem;
    border-top: 1px solid var(--bg-light);
}

.company-benefits,
.company-culture,
.company-specialties {
    padding-top: 1.5rem;
    border-top: 1px solid var(--bg-light);
}

.website-link {
    color: var(--primary-color);
    text-decoration: none;
    transition: color 0.3s ease;
    display: inline-flex;
    align-items: center;
}

.website-link:hover {
    color: var(--secondary-color);
}

.website-link i {
    font-size: 0.85rem;
}
</style>

@endsection

@section('customJs')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    // Set up AJAX CSRF token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Add success alert div at the top of the page
    function showAlert(message, type = 'success') {
        const alertDiv = $(`
            <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-4" style="z-index: 9999;" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
        
        $('body').append(alertDiv);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.alert('close');
        }, 5000);
    }

    // File size validation function (5MB in bytes = 5 * 1024 * 1024)
    function validateFileSize(file, maxSize = 5 * 1024 * 1024) {
        if (file && file.size > maxSize) {
            return false;
        }
        return true;
    }

    // Handle resume file selection
    $('#resume').on('change', function() {
        const file = this.files[0];
        const resumeError = $('#resumeError');
        
        if (!file) {
            resumeError.text('Please select a file').show();
            return;
        }
        
        if (!validateFileSize(file)) {
            resumeError.text('File size must be less than 5MB').show();
            this.value = ''; // Clear the file input
        } else {
            resumeError.hide();
        }
    });

    // Handle form submission
    window.submitApplication = function(e) {
        e.preventDefault();
        
        const form = $('#applicationForm');
        const submitBtn = $('#submitApplication');
        const resumeFile = $('#resume')[0].files[0];
        const resumeError = $('#resumeError');

        // Validate required fields
        if (!resumeFile) {
            resumeError.text('Please select a resume file').show();
            return false;
        }

        // Check file size before submission
        if (!validateFileSize(resumeFile)) {
            resumeError.text('File size must be less than 5MB').show();
            return false;
        }

        const formData = new FormData(form[0]);
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting...');
        
        // Make the AJAX request
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Response:', response); // Debug log
                
                if (response.status === true) {
                    // Show success message first
                    showAlert('Application submitted successfully! We will notify you of any updates.', 'success');
                    
                    // Reset form and errors
                    form[0].reset();
                    resumeError.hide();
                    
                    // Hide modal
                    $('#applicationModal').modal('hide');
                    
                    // Reload the page after a short delay to update the application status
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    if (response.errors) {
                        let errorMessage = '';
                        for (let key in response.errors) {
                            errorMessage += response.errors[key] + '\n';
                        }
                        showAlert(errorMessage, 'danger');
                    } else {
                        showAlert(response.message || 'Error submitting application. Please try again.', 'danger');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.log('Error:', xhr.responseText); // Debug log
                const response = xhr.responseJSON;
                
                let errorMessage = 'Error submitting application. ';
                if (response && response.errors) {
                    for (let key in response.errors) {
                        errorMessage += response.errors[key] + ' ';
                    }
                } else if (response && response.message) {
                    errorMessage += response.message;
                } else {
                    errorMessage += 'Please try again.';
                }
                
                showAlert(errorMessage, 'danger');
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false).html('Submit Application');
            }
        });

        return false;
    };

    // Save job functionality
    function toggleSaveJob(jobId) {
        @if(!Auth::check())
            window.location.href = '{{ route("login") }}';
            return;
        @endif

        const btn = document.getElementById(`saveJobBtn-${jobId}`);
        const icon = document.getElementById(`saveJobIcon-${jobId}`);
        
        if (!btn.disabled) {
            btn.disabled = true;
            
            // Check if job is already saved
            const isSaved = icon.classList.contains('text-danger');
            const route = isSaved ? '{{ route("jobs.unsave", ":id") }}' : '{{ route("jobs.save", ":id") }}';
            const url = route.replace(':id', jobId);
            
            $.ajax({
                url: url,
                type: 'POST',
                success: function(response) {
                    if (response.status) {
                        if (isSaved) {
                            icon.classList.remove('text-danger');
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                            icon.classList.add('text-muted');
                            btn.querySelector('span').textContent = 'Save Job';
                            btn.classList.remove('saved');
                        } else {
                            icon.classList.add('text-danger');
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                            icon.classList.remove('text-muted');
                            btn.querySelector('span').textContent = 'Saved';
                            btn.classList.add('saved');
                        }
                    } else {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            alert(response.message || 'Error saving job');
                        }
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    if (response && response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        alert('Error saving job. Please try again.');
                    }
                },
                complete: function() {
                    btn.disabled = false;
                }
            });
        }
    }

    // Make functions globally accessible
    window.toggleSaveJob = toggleSaveJob;
});
</script>
@endsection
