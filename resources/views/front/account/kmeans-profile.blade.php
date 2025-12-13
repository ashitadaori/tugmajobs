@extends('front.layouts.app')

@section('main')
<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">K-means Enhanced Profile</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                @include('components.jobseeker-sidebar')
            </div>
            <div class="col-lg-9">
                <!-- Profile Completion & Cluster Insights -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h2 class="h4 text-dark mb-0">Profile Completion</h2>
                                    <span class="badge bg-primary">{{ round($completionPercentage) }}%</span>
                                </div>
                                <div class="progress mb-3" style="height: 10px;">
                                    <div class="progress-bar bg-gradient" role="progressbar" 
                                         style="width: {{ $completionPercentage }}%"></div>
                                </div>
                                <p class="text-muted small mb-0">
                                    Complete your profile for better job matching with our K-means clustering algorithm
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow mb-4">
                            <div class="card-body text-center">
                                <h5 class="card-title">Your Cluster</h5>
                                <p class="text-muted small">{{ $clusterInsights['cluster_name'] }}</p>
                                <span class="badge bg-success">{{ $clusterInsights['similar_users_count'] }} similar professionals</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- K-means Profile Form -->
                <div class="card border-0 shadow mb-4">
                    <div class="card-header">
                        <h3 class="h5 mb-0">K-means Enhanced Profile</h3>
                        <small class="text-muted">Fields marked with * are critical for job matching algorithm</small>
                    </div>
                    <div class="card-body">
                        <form id="kmeansProfileForm" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Basic Information Section -->
                            <div class="section mb-5">
                                <h4 class="section-title">Basic Information</h4>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">First Name *</label>
                                        <input type="text" class="form-control" name="first_name" 
                                               value="{{ $profile->first_name }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" name="last_name" 
                                               value="{{ $profile->last_name }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" name="middle_name" 
                                               value="{{ $profile->middle_name }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone *</label>
                                        <input type="tel" class="form-control" name="phone" 
                                               value="{{ $profile->phone }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" name="date_of_birth" 
                                               value="{{ $profile->date_of_birth ? $profile->date_of_birth->format('Y-m-d') : '' }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Location Section (K-means Critical) -->
                            <div class="section mb-5">
                                <h4 class="section-title">Location Information <span class="text-danger">*</span></h4>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">City *</label>
                                        <input type="text" class="form-control" name="city" 
                                               value="{{ $profile->city }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">State/Province *</label>
                                        <input type="text" class="form-control" name="state" 
                                               value="{{ $profile->state }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Country *</label>
                                        <input type="text" class="form-control" name="country" 
                                               value="{{ $profile->country ?? 'Philippines' }}" required>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Current Address</label>
                                        <textarea class="form-control" name="current_address" rows="2">{{ $profile->current_address }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Professional Information -->
                            <div class="section mb-5">
                                <h4 class="section-title">Professional Information</h4>
                                <div class="mb-3">
                                    <label class="form-label">Professional Summary * <span class="text-muted">(Min 50 characters)</span></label>
                                    <textarea class="form-control" name="professional_summary" rows="4" 
                                              placeholder="Describe your professional experience, key achievements, and career goals..." required>{{ $profile->professional_summary }}</textarea>
                                    <div class="form-text">This helps our algorithm understand your professional profile better.</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Current Job Title</label>
                                        <input type="text" class="form-control" name="current_job_title" 
                                               value="{{ $profile->current_job_title }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Current Company</label>
                                        <input type="text" class="form-control" name="current_company" 
                                               value="{{ $profile->current_company }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Total Experience (Years) *</label>
                                        <input type="number" class="form-control" name="total_experience_years" 
                                               value="{{ $profile->total_experience_years }}" min="0" max="50" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Additional Months</label>
                                        <input type="number" class="form-control" name="total_experience_months" 
                                               value="{{ $profile->total_experience_months }}" min="0" max="11">
                                    </div>
                                </div>
                            </div>

                            <!-- Skills Section (K-means Critical) -->
                            <div class="section mb-5">
                                <h4 class="section-title">Skills <span class="text-danger">*</span></h4>
                                <p class="text-muted mb-3">Skills are crucial for our matching algorithm. Add at least 3 relevant skills.</p>
                                <div class="mb-3">
                                    <label class="form-label">Technical Skills * <span class="text-muted">(Add at least 3)</span></label>
                                    <div class="skills-input-container">
                                        <input type="text" class="form-control" id="skillsInput" 
                                               placeholder="Type a skill and press Enter">
                                        <div class="skills-container mt-2" id="skillsContainer">
                                            @if($profile->skills)
                                                @foreach($profile->skills as $skill)
                                                    <span class="skill-tag">{{ $skill }} <span class="remove-skill">&times;</span></span>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Soft Skills</label>
                                    <div class="soft-skills-input-container">
                                        <input type="text" class="form-control" id="softSkillsInput" 
                                               placeholder="Type a soft skill and press Enter">
                                        <div class="soft-skills-container mt-2" id="softSkillsContainer">
                                            @if($profile->soft_skills)
                                                @foreach($profile->soft_skills as $skill)
                                                    <span class="skill-tag soft">{{ $skill }} <span class="remove-skill">&times;</span></span>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Job Preferences Section (K-means Critical) -->
                            <div class="section mb-5">
                                <h4 class="section-title">Job Preferences <span class="text-danger">*</span></h4>
                                <p class="text-muted mb-3">These preferences are critical for our K-means job matching algorithm.</p>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Preferred Categories * <span class="text-muted">(Max 5)</span></label>
                                        <select class="form-select" name="preferred_categories[]" multiple required>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" 
                                                    {{ $profile->preferred_categories && in_array($category->id, $profile->preferred_categories) ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text">Hold Ctrl/Cmd to select multiple categories</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Preferred Job Types *</label>
                                        <select class="form-select" name="preferred_job_types[]" multiple required>
                                            @foreach($jobTypes as $jobType)
                                                <option value="{{ $jobType->id }}" 
                                                    {{ $profile->preferred_job_types && in_array($jobType->id, $profile->preferred_job_types) ? 'selected' : '' }}>
                                                    {{ $jobType->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="open_to_remote" 
                                                   {{ $profile->open_to_remote ? 'checked' : '' }}>
                                            <label class="form-check-label">Open to Remote Work</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="open_to_relocation" 
                                                   {{ $profile->open_to_relocation ? 'checked' : '' }}>
                                            <label class="form-check-label">Open to Relocation</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="currently_employed" 
                                                   {{ $profile->currently_employed ? 'checked' : '' }}>
                                            <label class="form-check-label">Currently Employed</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Salary Expectations (K-means Important) -->
                            <div class="section mb-5">
                                <h4 class="section-title">Salary Expectations <span class="text-danger">*</span></h4>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Minimum Salary *</label>
                                        <input type="number" class="form-control" name="expected_salary_min" 
                                               value="{{ $profile->expected_salary_min }}" step="1000" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Maximum Salary *</label>
                                        <input type="number" class="form-control" name="expected_salary_max" 
                                               value="{{ $profile->expected_salary_max }}" step="1000" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Currency *</label>
                                        <select class="form-select" name="salary_currency" required>
                                            <option value="PHP" {{ $profile->salary_currency == 'PHP' ? 'selected' : '' }}>PHP</option>
                                            <option value="USD" {{ $profile->salary_currency == 'USD' ? 'selected' : '' }}>USD</option>
                                            <option value="EUR" {{ $profile->salary_currency == 'EUR' ? 'selected' : '' }}>EUR</option>
                                            <option value="GBP" {{ $profile->salary_currency == 'GBP' ? 'selected' : '' }}>GBP</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Period *</label>
                                        <select class="form-select" name="salary_period" required>
                                            <option value="monthly" {{ $profile->salary_period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                            <option value="yearly" {{ $profile->salary_period == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Availability -->
                            <div class="section mb-5">
                                <h4 class="section-title">Availability</h4>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Availability *</label>
                                        <select class="form-select" name="availability" required>
                                            <option value="immediate" {{ $profile->availability == 'immediate' ? 'selected' : '' }}>Immediately</option>
                                            <option value="1_week" {{ $profile->availability == '1_week' ? 'selected' : '' }}>Within 1 week</option>
                                            <option value="2_weeks" {{ $profile->availability == '2_weeks' ? 'selected' : '' }}>Within 2 weeks</option>
                                            <option value="1_month" {{ $profile->availability == '1_month' ? 'selected' : '' }}>Within 1 month</option>
                                            <option value="2_months" {{ $profile->availability == '2_months' ? 'selected' : '' }}>Within 2 months</option>
                                            <option value="3_months" {{ $profile->availability == '3_months' ? 'selected' : '' }}>Within 3 months</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Education Section -->
                            <div class="section mb-5">
                                <h4 class="section-title">Education *</h4>
                                <div id="educationContainer">
                                    @if($profile->education && count($profile->education) > 0)
                                        @foreach($profile->education as $index => $edu)
                                            <div class="education-entry mb-3">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="education[{{ $index }}][degree]" 
                                                               value="{{ $edu['degree'] ?? '' }}" placeholder="Degree/Qualification">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="education[{{ $index }}][institution]" 
                                                               value="{{ $edu['institution'] ?? '' }}" placeholder="Institution">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="number" class="form-control" name="education[{{ $index }}][year]" 
                                                               value="{{ $edu['year'] ?? '' }}" placeholder="Year" min="1970" max="{{ date('Y') + 10 }}">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <button type="button" class="btn btn-outline-danger btn-sm remove-education">×</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="education-entry mb-3">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" name="education[0][degree]" placeholder="Degree/Qualification">
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" name="education[0][institution]" placeholder="Institution">
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="number" class="form-control" name="education[0][year]" placeholder="Year" min="1970" max="{{ date('Y') + 10 }}">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-education">×</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addEducation">+ Add Education</button>
                            </div>

                            <!-- Work Experience Section -->
                            <div class="section mb-5">
                                <h4 class="section-title">Work Experience</h4>
                                <div id="workExperienceContainer">
                                    @if($profile->work_experience && count($profile->work_experience) > 0)
                                        @foreach($profile->work_experience as $index => $exp)
                                            <div class="work-experience-entry mb-4 border p-3 rounded">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <input type="text" class="form-control" name="work_experience[{{ $index }}][job_title]" 
                                                               value="{{ $exp['job_title'] ?? '' }}" placeholder="Job Title">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <input type="text" class="form-control" name="work_experience[{{ $index }}][company]" 
                                                               value="{{ $exp['company'] ?? '' }}" placeholder="Company">
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <input type="date" class="form-control" name="work_experience[{{ $index }}][start_date]" 
                                                               value="{{ $exp['start_date'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <input type="date" class="form-control" name="work_experience[{{ $index }}][end_date]" 
                                                               value="{{ $exp['end_date'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <button type="button" class="btn btn-outline-danger btn-sm remove-work-experience">Remove</button>
                                                    </div>
                                                    <div class="col-12">
                                                        <textarea class="form-control" name="work_experience[{{ $index }}][description]" 
                                                                  rows="2" placeholder="Brief description of responsibilities">{{ $exp['description'] ?? '' }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addWorkExperience">+ Add Work Experience</button>
                            </div>

                            <!-- File Uploads -->
                            <div class="section mb-5">
                                <h4 class="section-title">Documents</h4>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Resume/CV</label>
                                        <input type="file" class="form-control" name="resume_file" accept=".pdf,.doc,.docx">
                                        @if($profile->resume_file)
                                            <small class="text-muted">Current: {{ basename($profile->resume_file) }}</small>
                                        @endif
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Profile Photo</label>
                                        <input type="file" class="form-control" name="profile_photo" accept="image/*">
                                        @if($profile->profile_photo)
                                            <small class="text-muted">Current photo uploaded</small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg position-relative" id="saveProfileBtn">
                                    <span class="button-text">
                                        <i class="fas fa-save me-2"></i>
                                        Save K-means Enhanced Profile
                                    </span>
                                    <span class="button-loader d-none" id="saveSpinner">
                                        <span class="spinner-dots">
                                            <span class="dot"></span>
                                            <span class="dot"></span>
                                            <span class="dot"></span>
                                        </span>
                                        <span class="ms-2">Saving your profile...</span>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Job Recommendations -->
                @if($recommendedJobs && $recommendedJobs->count() > 0)
                <div class="card border-0 shadow mb-4">
                    <div class="card-header">
                        <h3 class="h5 mb-0">Recommended Jobs (K-means Algorithm)</h3>
                        <small class="text-muted">Jobs matched based on your profile and clustering analysis</small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($recommendedJobs->take(3) as $job)
                            <div class="col-md-4 mb-3">
                                <div class="job-card">
                                    <h6><a href="{{ route('jobDetail', $job->id) }}">{{ $job->title }}</a></h6>
                                    <p class="text-muted small">{{ Str::limit($job->description, 80) }}</p>
                                    <small class="text-primary">{{ $job->location }}</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center">
                            <a href="{{ route('jobs') }}" class="btn btn-outline-primary">View All Recommendations</a>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</section>

<style>
.section-title {
    color: #2c3e50;
    border-bottom: 2px solid #e3f2fd;
    padding-bottom: 8px;
    margin-bottom: 20px;
}

.skill-tag {
    display: inline-block;
    background: #007bff;
    color: white;
    padding: 4px 8px;
    border-radius: 15px;
    margin: 2px;
    font-size: 12px;
    cursor: default;
}

.skill-tag.soft {
    background: #28a745;
}

.remove-skill {
    margin-left: 5px;
    cursor: pointer;
    font-weight: bold;
}

.remove-skill:hover {
    color: #ff6b6b;
}

.job-card {
    border: 1px solid #e9ecef;
    padding: 15px;
    border-radius: 8px;
    height: 100%;
}

.job-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-text {
    font-size: 0.8em;
    color: #6c757d;
}

.progress-bar {
    background: linear-gradient(90deg, #007bff 0%, #28a745 100%);
}

.badge.bg-primary {
    font-size: 0.9em;
}

.education-entry, .work-experience-entry {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

/* Modern Loading Animation */
.button-loader {
    display: inline-flex;
    align-items: center;
}

.spinner-dots {
    display: inline-flex;
    gap: 6px;
}

.spinner-dots .dot {
    width: 8px;
    height: 8px;
    background-color: white;
    border-radius: 50%;
    display: inline-block;
    animation: dotPulse 1.4s infinite ease-in-out both;
}

.spinner-dots .dot:nth-child(1) {
    animation-delay: -0.32s;
}

.spinner-dots .dot:nth-child(2) {
    animation-delay: -0.16s;
}

@keyframes dotPulse {
    0%, 80%, 100% {
        transform: scale(0.6);
        opacity: 0.5;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}

#saveProfileBtn {
    min-width: 280px;
    transition: all 0.3s ease;
}

#saveProfileBtn:disabled {
    opacity: 0.8;
    cursor: not-allowed;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Skills management
    setupSkillsInput('skillsInput', 'skillsContainer', 'skills');
    setupSkillsInput('softSkillsInput', 'softSkillsContainer', 'soft_skills');

    // Education management
    let educationIndex = {{ $profile->education ? count($profile->education) : 1 }};
    document.getElementById('addEducation').addEventListener('click', function() {
        const container = document.getElementById('educationContainer');
        const newEntry = createEducationEntry(educationIndex++);
        container.appendChild(newEntry);
    });

    // Work Experience management
    let workIndex = {{ $profile->work_experience ? count($profile->work_experience) : 0 }};
    document.getElementById('addWorkExperience').addEventListener('click', function() {
        const container = document.getElementById('workExperienceContainer');
        const newEntry = createWorkExperienceEntry(workIndex++);
        container.appendChild(newEntry);
    });

    // Form submission
    document.getElementById('kmeansProfileForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const saveBtn = document.getElementById('saveProfileBtn');
        const spinner = document.getElementById('saveSpinner');
        const buttonText = saveBtn.querySelector('.button-text');

        saveBtn.disabled = true;
        buttonText.classList.add('d-none');
        spinner.classList.remove('d-none');

        const formData = new FormData(this);
        
        // Collect skills data
        const skills = Array.from(document.querySelectorAll('#skillsContainer .skill-tag')).map(tag => 
            tag.textContent.trim().replace(' ×', ''));
        const softSkills = Array.from(document.querySelectorAll('#softSkillsContainer .skill-tag')).map(tag => 
            tag.textContent.trim().replace(' ×', ''));
        
        skills.forEach((skill, index) => formData.append(`skills[${index}]`, skill));
        softSkills.forEach((skill, index) => formData.append(`soft_skills[${index}]`, skill));

        fetch('{{ route("kmeans.profile.update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Profile updated successfully!', 'success');
                // Update completion percentage if available
                if (data.completion_percentage) {
                    updateProfileCompletion(data.completion_percentage);
                }
            } else {
                showAlert(data.message || 'Failed to update profile', 'error');
                if (data.errors) {
                    console.log('Validation errors:', data.errors);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred. Please try again.', 'error');
        })
        .finally(() => {
            saveBtn.disabled = false;
            buttonText.classList.remove('d-none');
            spinner.classList.add('d-none');
        });
    });

    // Remove handlers for existing entries
    setupRemoveHandlers();
});

function setupSkillsInput(inputId, containerId, fieldName) {
    const input = document.getElementById(inputId);
    const container = document.getElementById(containerId);

    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const skill = input.value.trim();
            if (skill && !isSkillExists(container, skill)) {
                addSkillTag(container, skill);
                input.value = '';
            }
        }
    });
}

function isSkillExists(container, skill) {
    const existing = Array.from(container.querySelectorAll('.skill-tag'));
    return existing.some(tag => tag.textContent.trim().replace(' ×', '').toLowerCase() === skill.toLowerCase());
}

function addSkillTag(container, skill) {
    const tag = document.createElement('span');
    tag.className = container.id === 'softSkillsContainer' ? 'skill-tag soft' : 'skill-tag';
    tag.innerHTML = `${skill} <span class="remove-skill">&times;</span>`;
    
    tag.querySelector('.remove-skill').addEventListener('click', function() {
        tag.remove();
    });
    
    container.appendChild(tag);
}

function createEducationEntry(index) {
    const div = document.createElement('div');
    div.className = 'education-entry mb-3';
    div.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <input type="text" class="form-control" name="education[${index}][degree]" placeholder="Degree/Qualification">
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" name="education[${index}][institution]" placeholder="Institution">
            </div>
            <div class="col-md-3">
                <input type="number" class="form-control" name="education[${index}][year]" placeholder="Year" min="1970" max="${new Date().getFullYear() + 10}">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger btn-sm remove-education">×</button>
            </div>
        </div>
    `;
    
    div.querySelector('.remove-education').addEventListener('click', function() {
        div.remove();
    });
    
    return div;
}

function createWorkExperienceEntry(index) {
    const div = document.createElement('div');
    div.className = 'work-experience-entry mb-4 border p-3 rounded';
    div.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-3">
                <input type="text" class="form-control" name="work_experience[${index}][job_title]" placeholder="Job Title">
            </div>
            <div class="col-md-6 mb-3">
                <input type="text" class="form-control" name="work_experience[${index}][company]" placeholder="Company">
            </div>
            <div class="col-md-4 mb-3">
                <input type="date" class="form-control" name="work_experience[${index}][start_date]">
            </div>
            <div class="col-md-4 mb-3">
                <input type="date" class="form-control" name="work_experience[${index}][end_date]">
            </div>
            <div class="col-md-4 mb-3">
                <button type="button" class="btn btn-outline-danger btn-sm remove-work-experience">Remove</button>
            </div>
            <div class="col-12">
                <textarea class="form-control" name="work_experience[${index}][description]" rows="2" placeholder="Brief description of responsibilities"></textarea>
            </div>
        </div>
    `;
    
    div.querySelector('.remove-work-experience').addEventListener('click', function() {
        div.remove();
    });
    
    return div;
}

function setupRemoveHandlers() {
    // Skills removal
    document.querySelectorAll('.remove-skill').forEach(btn => {
        btn.addEventListener('click', function() {
            btn.parentElement.remove();
        });
    });

    // Education removal
    document.querySelectorAll('.remove-education').forEach(btn => {
        btn.addEventListener('click', function() {
            btn.closest('.education-entry').remove();
        });
    });

    // Work experience removal
    document.querySelectorAll('.remove-work-experience').forEach(btn => {
        btn.addEventListener('click', function() {
            btn.closest('.work-experience-entry').remove();
        });
    });
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.col-lg-9');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

function updateProfileCompletion(percentage) {
    const progressBar = document.querySelector('.progress-bar');
    const badge = document.querySelector('.badge.bg-primary');
    
    if (progressBar) {
        progressBar.style.width = percentage + '%';
    }
    if (badge) {
        badge.textContent = Math.round(percentage) + '%';
    }
}
</script>

@endsection
