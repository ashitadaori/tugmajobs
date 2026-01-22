@extends('layouts.jobseeker')

@section('page-title', 'My Profile')

@section('jobseeker-content')
<style>
/* Profile Page Modern Styles */
.profile-pro {
    padding: 0;
}

/* Modern Page Header - Gradient Style */
.profile-page-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
    border-radius: 20px;
    padding: 2rem 2.5rem;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(99, 102, 241, 0.2);
}

.profile-page-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 60%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
    pointer-events: none;
}

.profile-page-header .header-content h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: white;
    margin: 0 0 0.5rem 0;
    letter-spacing: -0.025em;
}

.profile-page-header .header-content p {
    font-size: 0.9375rem;
    color: rgba(255, 255, 255, 0.85);
    margin: 0;
}

.profile-page-header .completion-badge {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    color: white;
    padding: 0.75rem 1.25rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9375rem;
    border: 1px solid rgba(255, 255, 255, 0.25);
    position: relative;
    z-index: 1;
}

/* Modern Cards */
.profile-pro .card {
    border: 1px solid #e5e7eb !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    overflow: hidden;
}

.profile-pro .card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08) !important;
    border-color: #d1d5db !important;
}

.profile-pro .card-body {
    padding: 1.75rem !important;
}

.profile-pro .card-title {
    font-size: 1.125rem !important;
    font-weight: 700 !important;
    color: #111827 !important;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.profile-pro .card-title i {
    color: #6366f1 !important;
    font-size: 1.125rem;
}

/* Modern Form Controls */
.profile-pro .form-control,
.profile-pro .form-select {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.875rem 1rem;
    font-size: 0.9375rem;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    background-color: #fff;
}

.profile-pro .form-control:focus,
.profile-pro .form-select:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.profile-pro label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

/* Modern Gradient Buttons */
.profile-pro .btn-primary {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
    border: none !important;
    border-radius: 12px;
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.25);
}

.profile-pro .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.35);
}

.profile-pro .btn-outline-primary {
    color: #6366f1;
    border-color: #c7d2fe;
    border-radius: 12px;
    font-weight: 600;
    background: #eef2ff;
    transition: all 0.25s ease;
}

.profile-pro .btn-outline-primary:hover {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border-color: transparent;
    color: white;
    transform: translateY(-2px);
}

/* Modern Skills Tags */
.profile-pro #skillTags .badge {
    background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%) !important;
    color: #6366f1 !important;
    border: 1px solid #c7d2fe;
    border-radius: 50px;
    padding: 0.5rem 0.875rem;
    font-size: 0.8125rem;
    font-weight: 600;
    margin: 0.25rem;
    transition: all 0.2s ease;
}

.profile-pro #skillTags .badge:hover {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
    color: white !important;
    border-color: transparent;
}

/* Modern Profile Picture */
.profile-pro .profile-picture-preview img,
.profile-pro .profile-picture-preview > div {
    border: 4px solid white !important;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    transition: all 0.3s ease;
}

.profile-pro .profile-picture-preview:hover img,
.profile-pro .profile-picture-preview:hover > div {
    transform: scale(1.02);
    box-shadow: 0 12px 30px rgba(99, 102, 241, 0.2);
}
</style>

{{-- Success/Error messages are handled by the toast-notifications component in the layout --}}

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius: 0.75rem; border: none; background: #fee2e2; color: #b91c1c;">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="profile-pro">
    <!-- Page Header -->
    <div class="profile-page-header">
        <div class="header-content">
            <h1>My Profile</h1>
            <p>Manage your personal information and job preferences</p>
        </div>
        <div class="completion-badge">
            <i class="fas fa-chart-line"></i>
            <span>{{ $completionPercentage ?? 0 }}% Complete</span>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Main Profile Info -->
        <div class="col-lg-8">

            <!-- Profile Picture -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-1">
                        <i class="fas fa-camera text-primary me-2"></i>
                        Profile Picture
                    </h5>
                    <p class="mb-4 text-muted small">Upload a professional photo to help employers recognize you</p>

                        <div class="row align-items-center">
                            <div class="col-md-3 text-center mb-3 mb-md-0">
                                <div class="profile-picture-preview">
                                    @if($user->image)
                                        <img src="{{ asset('profile_img/thumb/'.$user->image) }}"
                                             alt="Profile Picture"
                                             class="rounded-circle img-thumbnail"
                                             style="width: 150px; height: 150px; object-fit: cover;"
                                             id="profileImagePreview">
                                    @else
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                             style="width: 150px; height: 150px; margin: 0 auto;"
                                             id="profileImagePreview">
                                            <i class="fas fa-user fa-4x text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-9">
                                <form id="profileImageForm" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="profileImage" class="form-label">Choose a new profile picture</label>
                                        <input type="file" class="form-control" id="profileImage" name="image" accept="image/jpeg,image/png,image/jpg,image/gif">
                                        <div class="form-text">Accepted formats: JPG, PNG, GIF (Max. 5MB)</div>
                                        <div id="imageError" class="text-danger mt-2" style="display: none;"></div>
                                    </div>
                                    <button type="submit" class="btn btn-primary" id="uploadImageBtn">
                                        <i class="fas fa-upload me-2"></i>Upload Picture
                                    </button>
                                    @if($user->image)
                                        <button type="button" class="btn btn-outline-danger ms-2" id="removeImageBtn">
                                            <i class="fas fa-trash me-2"></i>Remove Picture
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Personal Information -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-1">
                        <i class="fas fa-user text-primary me-2"></i>
                        Personal Information
                    </h5>
                    <p class="mb-4 text-muted small">Update your personal details and how employers will see you</p>
                        
                        <form action="{{ route('account.updateProfile') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <label for="name" class="mb-2">Full Name</label>
                                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" placeholder="Enter your full name">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="email" class="mb-2">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" placeholder="Enter your email">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="phone" class="mb-2">Phone</label>
                                    <input type="tel" name="phone" id="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="Enter your phone number">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <label for="designation" class="mb-2">Professional Title</label>
                                    <input type="text" name="designation" id="designation" class="form-control" value="{{ old('designation', $user->jobSeekerProfile->current_job_title ?? '') }}" placeholder="e.g., Senior Software Engineer">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <label for="bio" class="mb-2">Professional Summary</label>
                                    <textarea name="bio" id="bio" class="form-control" rows="4" placeholder="Tell employers about yourself, your experience and career goals">{{ old('bio', $user->jobSeekerProfile->professional_summary ?? '') }}</textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <label for="skills" class="mb-2">Skills</label>
                                    <div class="skills-input-container">
                                        <input type="text" id="skillInput" class="form-control" placeholder="Type a skill and press Enter">
                                        <div id="skillTags" class="mt-2"></div>
                                        @php
                                            $skillsValue = '';
                                            if ($user->jobSeekerProfile && $user->jobSeekerProfile->skills) {
                                                $skills = $user->jobSeekerProfile->skills;
                                                $skillsValue = is_array($skills) ? implode(',', $skills) : $skills;
                                            }
                                        @endphp
                                        <input type="hidden" name="skills" id="skillsHidden" value="{{ old('skills', $skillsValue) }}">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>

            <!-- Work Experience -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="card-title mb-1">
                                <i class="fas fa-briefcase text-primary me-2"></i>
                                Work Experience
                            </h5>
                            <p class="mb-0 text-muted small">Add your work history to showcase your expertise</p>
                        </div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExperienceModal">
                                <i class="fas fa-plus me-2"></i>Add Experience
                            </button>
                        </div>

                        <div id="experienceList">
                            @if($user->jobSeekerProfile && isset($user->jobSeekerProfile->work_experience) && is_array($user->jobSeekerProfile->work_experience))
                                @foreach($user->jobSeekerProfile->work_experience as $index => $experience)
                                    @php
                                        $experienceWithIndex = array_merge($experience, ['id' => $index]);
                                    @endphp
                                    <div class="experience-item mb-3 p-3 border rounded">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h5 class="mb-1">{{ $experience['title'] ?? '' }}</h5>
                                                <p class="mb-1">{{ $experience['company'] ?? '' }}</p>
                                                <p class="text-muted mb-2">{{ $experience['start_date'] ?? '' }} - {{ $experience['end_date'] ?? 'Present' }}</p>
                                                <p class="mb-0">{{ $experience['description'] ?? '' }}</p>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-outline-primary me-2 edit-experience" 
                                                    data-experience="{{ json_encode($experienceWithIndex) }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-experience" data-index="{{ $index }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

            <!-- Education -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="card-title mb-1">
                                <i class="fas fa-graduation-cap text-primary me-2"></i>
                                Education
                            </h5>
                            <p class="mb-0 text-muted small">Add your educational background</p>
                        </div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEducationModal">
                                <i class="fas fa-plus me-2"></i>Add Education
                            </button>
                        </div>

                        <div id="educationList">
                            @if($user->jobSeekerProfile && isset($user->jobSeekerProfile->education) && is_array($user->jobSeekerProfile->education))
                                @foreach($user->jobSeekerProfile->education as $index => $education)
                                    @php
                                        $educationWithIndex = array_merge($education, ['id' => $index]);
                                    @endphp
                                    <div class="education-item mb-3 p-3 border rounded">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h5 class="mb-1">{{ $education['degree'] ?? '' }}</h5>
                                                <p class="mb-1">{{ $education['school'] ?? '' }}</p>
                                                <p class="text-muted mb-0">{{ $education['start_date'] ?? '' }} - {{ $education['end_date'] ?? 'Present' }}</p>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-outline-primary me-2 edit-education" 
                                                    data-education="{{ json_encode($educationWithIndex) }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-education" data-index="{{ $index }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

            <!-- Resume Upload -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-1">
                        <i class="fas fa-file-pdf text-primary me-2"></i>
                        Resume/CV
                    </h5>
                    <p class="mb-4 text-muted small">Upload your latest resume or CV document</p>

                        <form action="{{ route('account.uploadResume') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                @if($user->jobSeekerProfile && $user->jobSeekerProfile->resume_file)
                                    <div class="current-resume mb-3">
                                        <p class="mb-2">Current Resume:</p>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-pdf me-2"></i>
                                            <span>{{ basename($user->jobSeekerProfile->resume_file) }}</span>
                                            <a href="{{ asset('storage/'.$user->jobSeekerProfile->resume_file) }}" 
                                               class="btn btn-sm btn-outline-primary ms-3" target="_blank">
                                                <i class="fas fa-download me-1"></i>Download
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                <label for="resume" class="form-label">Upload New Resume</label>
                                <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx">
                                <div class="form-text">Accepted formats: PDF, DOC, DOCX (Max. 5MB)</div>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload Resume</button>
                        </form>
                    </div>
                </div>

            <!-- Job Preferences -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-1">
                        <i class="fas fa-sliders-h text-primary me-2"></i>
                        Job Preferences
                    </h5>
                    <p class="mb-4 text-muted small">Set your job preferences to receive personalized job recommendations</p>
                        

                        
                        <form action="{{ route('account.updateProfile') }}" method="post" id="jobPreferencesForm">
                            @csrf
                            <!-- Hidden fields for required validation -->
                            <input type="hidden" name="name" value="{{ $user->name }}">
                            <input type="hidden" name="email" value="{{ $user->email }}">
                            <input type="hidden" name="phone" value="{{ $user->phone }}">
                            <input type="hidden" name="is_job_preferences_form" value="1">
                            @php
                                $currentJobTitle = $user->jobSeekerProfile->current_job_title ?? '';
                                $professionalSummary = $user->jobSeekerProfile->professional_summary ?? '';
                                $profileSkills = '';
                                if ($user->jobSeekerProfile && $user->jobSeekerProfile->skills) {
                                    $skillsArray = $user->jobSeekerProfile->skills;
                                    $profileSkills = is_array($skillsArray) ? implode(',', $skillsArray) : $skillsArray;
                                }
                            @endphp
                            <input type="hidden" name="designation" value="{{ $currentJobTitle }}">
                            <input type="hidden" name="bio" value="{{ $professionalSummary }}">
                            <input type="hidden" name="skills" value="{{ $profileSkills }}">
                            
                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <label for="preferred_categories" class="mb-2">Preferred Job Categories</label>
                                    <div class="custom-dropdown-container position-relative">
                                        <div class="form-control custom-dropdown-trigger" id="categoriesDropdown" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                                            <span class="selected-text" id="categoriesSelectedText">Select Job Categories</span>
                                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                                        </div>
                                        <div class="custom-dropdown-menu" id="categoriesDropdownMenu" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; background: white; border: 1px solid #ced4da; border-radius: 0.375rem; padding: 0.75rem; max-height: 300px; overflow-y: auto; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);">
                                            @php
                                                $categories = \App\Models\Category::where('status', 1)->get();
                                                $userCategories = $user->jobSeekerProfile->preferred_categories ?? [];
                                                $selectedCategories = old('preferred_categories', $userCategories);
                                            @endphp
                                            @foreach($categories as $category)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input category-checkbox" type="checkbox" 
                                                           name="preferred_categories[]" value="{{ $category->id }}"
                                                           id="category_{{ $category->id }}"
                                                           {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="category_{{ $category->id }}">
                                                        {{ $category->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-text">Select multiple categories by clicking the checkboxes</div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="preferred_job_types" class="mb-2">Preferred Job Types</label>
                                    <div class="custom-dropdown-container position-relative" id="jobTypesDropdownContainer">
                                        <div class="form-control custom-dropdown-trigger" id="jobTypesDropdown" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                                            <span class="selected-text" id="jobTypesSelectedText">Select Job Types</span>
                                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                                        </div>
                                        <div class="custom-dropdown-menu" id="jobTypesDropdownMenu" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; background: white; border: 1px solid #ced4da; border-radius: 0.375rem; padding: 0.75rem; max-height: 250px; overflow-y: auto; box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);">
                                            @php
                                                $jobTypesFromDB = \App\Models\JobType::where('status', 1)->get();
                                                $userJobTypes = $user->jobSeekerProfile->preferred_job_types ?? [];
                                                $selectedJobTypes = old('preferred_job_types', $userJobTypes);
                                            @endphp
                                            @foreach($jobTypesFromDB as $jobType)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" 
                                                           name="preferred_job_types[]" value="{{ $jobType->id }}"
                                                           id="job_type_{{ $jobType->id }}"
                                                           {{ in_array($jobType->id, $selectedJobTypes) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="job_type_{{ $jobType->id }}">
                                                        {{ $jobType->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-text">Select multiple job types by clicking the checkboxes</div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="experience_level" class="mb-2">Experience Level</label>
                                    <select name="experience_level" id="experience_level" class="form-select">
                                        <option value="">Select Experience Level</option>
                                        <option value="entry" {{ old('experience_level', $user->jobSeekerProfile->experience_level ?? '') == 'entry' ? 'selected' : '' }}>Entry Level (0-2 years)</option>
                                        <option value="mid" {{ old('experience_level', $user->jobSeekerProfile->experience_level ?? '') == 'mid' ? 'selected' : '' }}>Mid Level (2-5 years)</option>
                                        <option value="senior" {{ old('experience_level', $user->jobSeekerProfile->experience_level ?? '') == 'senior' ? 'selected' : '' }}>Senior Level (5-8 years)</option>
                                        <option value="lead" {{ old('experience_level', $user->jobSeekerProfile->experience_level ?? '') == 'lead' ? 'selected' : '' }}>Lead/Expert (8+ years)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="salary_expectation_min" class="mb-2">Minimum Salary Expectation</label>
                                    <input type="number" name="salary_expectation_min" id="salary_expectation_min"
                                           class="form-control" value="{{ old('salary_expectation_min', $user->jobSeekerProfile->expected_salary_min ?? '') }}"
                                           placeholder="e.g., 50000">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="salary_expectation_max" class="mb-2">Maximum Salary Expectation</label>
                                    <input type="number" name="salary_expectation_max" id="salary_expectation_max"
                                           class="form-control" value="{{ old('salary_expectation_max', $user->jobSeekerProfile->expected_salary_max ?? '') }}"
                                           placeholder="e.g., 80000">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Save Preferences</button>
                        </form>
                    </div>
                </div>

            <!-- Social Links -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-1">
                        <i class="fas fa-link text-primary me-2"></i>
                        Professional Links
                    </h5>
                    <p class="mb-4 text-muted small">Add your professional social media and portfolio links</p>

                        <form action="{{ route('account.updateSocialLinks') }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="linkedin" class="mb-2">
                                        <i class="fab fa-linkedin me-2"></i>LinkedIn
                                    </label>
                                    <input type="url" name="social_links[linkedin]" id="linkedin" class="form-control"
                                           value="{{ old('social_links.linkedin', ($user->jobSeekerProfile ? $user->jobSeekerProfile->linkedin_url : '')) }}"
                                           placeholder="https://linkedin.com/in/your-profile">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="github" class="mb-2">
                                        <i class="fab fa-github me-2"></i>GitHub
                                    </label>
                                    <input type="url" name="social_links[github]" id="github" class="form-control"
                                           value="{{ old('social_links.github', ($user->jobSeekerProfile ? $user->jobSeekerProfile->github_url : '')) }}"
                                           placeholder="https://github.com/your-username">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="portfolio" class="mb-2">
                                        <i class="fas fa-globe me-2"></i>Portfolio Website
                                    </label>
                                    <input type="url" name="social_links[portfolio]" id="portfolio" class="form-control"
                                           value="{{ old('social_links.portfolio', ($user->jobSeekerProfile ? $user->jobSeekerProfile->portfolio_url : '')) }}"
                                           placeholder="https://your-portfolio.com">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="facebook" class="mb-2">
                                        <i class="fab fa-facebook me-2"></i>Facebook
                                    </label>
                                    <input type="url" name="social_links[facebook]" id="facebook" class="form-control"
                                           value="{{ old('social_links.facebook', ($user->jobSeekerProfile ? $user->jobSeekerProfile->facebook_url : '')) }}"
                                           placeholder="https://facebook.com/your-profile">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="twitter" class="mb-2">
                                        <i class="fab fa-twitter me-2"></i>Twitter/X
                                    </label>
                                    <input type="url" name="social_links[twitter]" id="twitter" class="form-control"
                                           value="{{ old('social_links.twitter', ($user->jobSeekerProfile ? $user->jobSeekerProfile->twitter_url : '')) }}"
                                           placeholder="https://twitter.com/your-username">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="instagram" class="mb-2">
                                        <i class="fab fa-instagram me-2"></i>Instagram
                                    </label>
                                    <input type="url" name="social_links[instagram]" id="instagram" class="form-control"
                                           value="{{ old('social_links.instagram', ($user->jobSeekerProfile ? $user->jobSeekerProfile->instagram_url : '')) }}"
                                           placeholder="https://instagram.com/your-username">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Links</button>
                        </form>
                    </div>
                </div>

        </div>
        <!-- End Left Column -->

        <!-- Right Column - Quick Actions & Tips -->
        <div class="col-lg-4">

            <!-- Profile Completion Checklist -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-tasks text-primary me-2"></i>
                        Profile Checklist
                    </h5>
                    <x-profile-completion-checklist :user="Auth::user()" :completionPercentage="$completionPercentage ?? 0" />
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card border-0 shadow mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        Quick Actions
                    </h5>
                    <div class="d-grid gap-2">
                        <a href="{{ route('jobs') }}" class="btn btn-outline-primary">
                            <i class="fas fa-search me-2"></i>Search Jobs
                        </a>
                        <a href="{{ route('account.resume-builder.index') }}" class="btn btn-outline-success">
                            <i class="fas fa-file-pdf me-2"></i>Build Resume
                        </a>
                        <a href="{{ route('account.myJobApplications') }}" class="btn btn-outline-info">
                            <i class="fas fa-file-alt me-2"></i>My Applications
                        </a>
                        <a href="{{ route('account.settings') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </div>
                </div>
            </div>

            <!-- Profile Tips Card -->
            <div class="card border-0 shadow mb-4 bg-light">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-lightbulb text-success me-2"></i>
                        Profile Tips
                    </h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Use a professional profile picture</small>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Keep your skills updated and relevant</small>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Add your work experience with details</small>
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Upload an up-to-date resume</small>
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Complete all profile sections for better visibility</small>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card border-0 shadow bg-primary text-white">
                <div class="card-body p-4">
                    <h5 class="card-title mb-2">
                        <i class="fas fa-question-circle me-2"></i>
                        Need Help?
                    </h5>
                    <p class="mb-3 small">Having trouble with your profile? We're here to help!</p>
                    <a href="mailto:support@tugmajobs.com" class="btn btn-light btn-sm">
                        <i class="fas fa-envelope me-2"></i>Contact Support
                    </a>
                </div>
            </div>

        </div>
        <!-- End Right Column -->

    </div>
</div>

<!-- Experience Modal -->
<div class="modal fade" id="addExperienceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Work Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="experienceForm">
                    <div class="mb-3">
                        <label for="jobTitle" class="form-label">Job Title</label>
                        <input type="text" class="form-control" id="jobTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="company" class="form-label">Company</label>
                        <input type="text" class="form-control" id="company" required>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="month" class="form-control" id="startDate" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="month" class="form-control" id="endDate">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="currentlyWorking">
                                <label class="form-check-label" for="currentlyWorking">
                                    I currently work here
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveExperience">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Education Modal -->
<div class="modal fade" id="addEducationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Education</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="educationForm">
                    <div class="mb-3">
                        <label for="school" class="form-label">School/University</label>
                        <input type="text" class="form-control" id="school" required>
                    </div>
                    <div class="mb-3">
                        <label for="degree" class="form-label">Degree</label>
                        <input type="text" class="form-control" id="degree" required>
                    </div>
                    <div class="mb-3">
                        <label for="fieldOfStudy" class="form-label">Field of Study</label>
                        <input type="text" class="form-control" id="fieldOfStudy">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="eduStartDate" class="form-label">Start Date</label>
                            <input type="month" class="form-control" id="eduStartDate" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="eduEndDate" class="form-label">End Date</label>
                            <input type="month" class="form-control" id="eduEndDate">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="currentlyStudying">
                                <label class="form-check-label" for="currentlyStudying">
                                    I'm currently studying here
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEducation">Save</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Green Button Styling - Matching Settings Page */
.btn-primary {
    background-color: #82b440 !important;
    border-color: #82b440 !important;
    color: white !important;
}

.btn-primary:hover {
    background-color: #6f9a37 !important;
    border-color: #6f9a37 !important;
    color: white !important;
}

.btn-primary:focus,
.btn-primary:active {
    background-color: #6f9a37 !important;
    border-color: #6f9a37 !important;
    box-shadow: 0 0 0 0.2rem rgba(130, 180, 64, 0.25) !important;
}

/* Profile Picture Styling */
.profile-picture-preview {
    position: relative;
    display: inline-block;
}

.profile-picture-preview img {
    border: 4px solid #f0f0f0;
    transition: border-color 0.3s ease;
}

.profile-picture-preview img:hover {
    border-color: #82b440;
}

.profile-picture-preview .rounded-circle.bg-light {
    border: 4px solid #f0f0f0;
}

/* Custom Dropdown Styling */
.custom-dropdown-container .custom-dropdown-trigger {
    background-color: #fff;
    border: 1px solid #ced4da;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.custom-dropdown-container .custom-dropdown-trigger:hover,
.custom-dropdown-container .custom-dropdown-trigger:focus {
    border-color: #82b440;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(130, 180, 64, 0.15);
}

.custom-dropdown-container .custom-dropdown-menu {
    border: 1px solid #ced4da;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.custom-dropdown-container .form-check {
    padding-left: 1.25em;
}

.custom-dropdown-container .form-check-input:checked {
    background-color: #82b440;
    border-color: #82b440;
}

.custom-dropdown-container .form-check-label {
    cursor: pointer;
    font-size: 0.95em;
}

.custom-dropdown-container .dropdown-arrow {
    transition: transform 0.2s;
}

.custom-dropdown-container.open .dropdown-arrow {
    transform: rotate(180deg);
}

/* Card Hover Effect */
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
}

/* Form Control Focus */
.form-control:focus,
.form-select:focus {
    border-color: #82b440;
    box-shadow: 0 0 0 0.2rem rgba(130, 180, 64, 0.25);
}

/* Badge Styling */
.badge.bg-primary {
    background-color: #82b440 !important;
}
</style>
@endpush

@push('scripts')
<script>
// Skills Input Handling
document.addEventListener('DOMContentLoaded', function() {
    const skillInput = document.getElementById('skillInput');
    const skillTags = document.getElementById('skillTags');
    const skillsHidden = document.getElementById('skillsHidden');
    let skills = skillsHidden.value ? skillsHidden.value.split(',') : [];

    function updateSkillTags() {
        skillTags.innerHTML = '';
        skills.forEach(skill => {
            const tag = document.createElement('span');
            tag.className = 'badge bg-primary me-2 mb-2';
            tag.innerHTML = `${skill} <i class="fas fa-times ms-1" style="cursor: pointer;"></i>`;
            tag.querySelector('i').onclick = () => {
                skills = skills.filter(s => s !== skill);
                updateSkillTags();
                skillsHidden.value = skills.join(',');
            };
            skillTags.appendChild(tag);
        });
    }

    skillInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            const skill = this.value.trim();
            if (skill && !skills.includes(skill)) {
                skills.push(skill);
                updateSkillTags();
                skillsHidden.value = skills.join(',');
                this.value = '';
            }
        }
    });

    // Initialize tags
    updateSkillTags();

    // Experience Form Handling
    const currentlyWorkingCheckbox = document.getElementById('currentlyWorking');
    const endDateInput = document.getElementById('endDate');

    currentlyWorkingCheckbox.addEventListener('change', function() {
        endDateInput.disabled = this.checked;
        if (this.checked) {
            endDateInput.value = '';
        }
    });

    // Education Form Handling
    const currentlyStudyingCheckbox = document.getElementById('currentlyStudying');
    const eduEndDateInput = document.getElementById('eduEndDate');

    currentlyStudyingCheckbox.addEventListener('change', function() {
        eduEndDateInput.disabled = this.checked;
        if (this.checked) {
            eduEndDateInput.value = '';
        }
    });

    // Categories Custom Dropdown Handling
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
    const categoriesSelectedText = document.getElementById('categoriesSelectedText');
    const categoriesDropdown = document.getElementById('categoriesDropdown');
    const categoriesDropdownMenu = document.getElementById('categoriesDropdownMenu');
    const customDropdownContainer = document.querySelector('.custom-dropdown-container');
    let isDropdownOpen = false;

    function updateCategoriesText() {
        const checkedBoxes = document.querySelectorAll('.category-checkbox:checked');
        if (checkedBoxes.length === 0) {
            categoriesSelectedText.textContent = 'Select Job Categories';
        } else if (checkedBoxes.length === 1) {
            categoriesSelectedText.textContent = checkedBoxes[0].nextElementSibling.textContent;
        } else {
            categoriesSelectedText.textContent = `${checkedBoxes.length} categories selected`;
        }
    }

    function toggleDropdown() {
        isDropdownOpen = !isDropdownOpen;
        if (isDropdownOpen) {
            categoriesDropdownMenu.style.display = 'block';
            customDropdownContainer.classList.add('open');
        } else {
            categoriesDropdownMenu.style.display = 'none';
            customDropdownContainer.classList.remove('open');
        }
    }

    function closeDropdown() {
        isDropdownOpen = false;
        categoriesDropdownMenu.style.display = 'none';
        customDropdownContainer.classList.remove('open');
    }

    // Toggle dropdown when clicking trigger
    categoriesDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleDropdown();
    });

    // Add event listeners to category checkboxes
    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function(e) {
            e.stopPropagation();
            updateCategoriesText();
        });
    });

    // Prevent dropdown from closing when clicking inside
    categoriesDropdownMenu.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!customDropdownContainer.contains(e.target)) {
            closeDropdown();
        }
    });

    // Initialize categories text on page load
    updateCategoriesText();

    // Job Types Custom Dropdown Handling
    const jobTypeCheckboxes = document.querySelectorAll('.job-type-checkbox');
    const jobTypesSelectedText = document.getElementById('jobTypesSelectedText');
    const jobTypesDropdown = document.getElementById('jobTypesDropdown');
    const jobTypesDropdownMenu = document.getElementById('jobTypesDropdownMenu');
    const jobTypesDropdownContainer = document.getElementById('jobTypesDropdownContainer');
    let isJobTypesDropdownOpen = false;

    function updateJobTypesText() {
        const checkedBoxes = document.querySelectorAll('.job-type-checkbox:checked');
        if (checkedBoxes.length === 0) {
            jobTypesSelectedText.textContent = 'Select Job Types';
        } else if (checkedBoxes.length === 1) {
            jobTypesSelectedText.textContent = checkedBoxes[0].nextElementSibling.textContent;
        } else {
            jobTypesSelectedText.textContent = `${checkedBoxes.length} types selected`;
        }
    }

    function toggleJobTypesDropdown() {
        isJobTypesDropdownOpen = !isJobTypesDropdownOpen;
        if (isJobTypesDropdownOpen) {
            jobTypesDropdownMenu.style.display = 'block';
            jobTypesDropdownContainer.classList.add('open');
        } else {
            jobTypesDropdownMenu.style.display = 'none';
            jobTypesDropdownContainer.classList.remove('open');
        }
    }

    function closeJobTypesDropdown() {
        isJobTypesDropdownOpen = false;
        jobTypesDropdownMenu.style.display = 'none';
        jobTypesDropdownContainer.classList.remove('open');
    }

    // Toggle job types dropdown when clicking trigger
    jobTypesDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleJobTypesDropdown();
    });

    // Add event listeners to job type checkboxes
    jobTypeCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function(e) {
            e.stopPropagation();
            updateJobTypesText();
        });
    });

    // Prevent job types dropdown from closing when clicking inside
    jobTypesDropdownMenu.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Close job types dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!jobTypesDropdownContainer.contains(e.target)) {
            closeJobTypesDropdown();
        }
        // Also close categories dropdown if clicking outside
        if (!customDropdownContainer.contains(e.target)) {
            closeDropdown();
        }
    });

    // Initialize job types text on page load
    updateJobTypesText();
    
    // Add form submission debugging
    const jobPreferencesForm = document.getElementById('jobPreferencesForm');
    if (jobPreferencesForm) {
        jobPreferencesForm.addEventListener('submit', function(e) {
            console.log('Job preferences form submitting...');
            
            // Log selected categories
            const selectedCategories = [];
            document.querySelectorAll('.category-checkbox:checked').forEach(cb => {
                selectedCategories.push(cb.value);
            });
            console.log('Selected categories:', selectedCategories);
            
            // Log selected job types
            const selectedJobTypes = [];
            document.querySelectorAll('.job-type-checkbox:checked').forEach(cb => {
                selectedJobTypes.push(cb.value);
            });
            console.log('Selected job types:', selectedJobTypes);
            
            // Check if any preferences are selected
            if (selectedCategories.length === 0 && selectedJobTypes.length === 0) {
                console.warn('No preferences selected!');
            }
            
            console.log('Form action:', this.action);
            console.log('Form method:', this.method);
        });
    }

    // Experience Modal Handling
    const saveExperienceBtn = document.getElementById('saveExperience');
    const experienceModal = document.getElementById('addExperienceModal');
    let editingExperienceId = null;

    if (saveExperienceBtn) {
        saveExperienceBtn.addEventListener('click', function() {
            const form = document.getElementById('experienceForm');
            const formData = new FormData();
            
            // Get form data
            formData.append('title', document.getElementById('jobTitle').value);
            formData.append('company', document.getElementById('company').value);
            formData.append('location', document.getElementById('location').value);
            formData.append('start_date', document.getElementById('startDate').value);
            formData.append('end_date', document.getElementById('endDate').value);
            formData.append('currently_working', document.getElementById('currentlyWorking').checked ? 1 : 0);
            formData.append('description', document.getElementById('description').value);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            
            if (editingExperienceId !== null) {
                formData.append('index', editingExperienceId);
            }
            
            const url = editingExperienceId !== null ? '{{ route("account.updateExperience") }}' : '{{ route("account.addExperience") }}';
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.status) {
                    // Close modal and reset form
                    bootstrap.Modal.getInstance(experienceModal).hide();
                    form.reset();
                    editingExperienceId = null;
                    
                    // Show success message and reload page
                    if (typeof showToast === 'function') {
                        showToast('Work experience saved successfully', 'success');
                    }
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    const errorMsg = data.errors ? Object.values(data.errors)[0] : 'Something went wrong';
                    if (typeof showToast === 'function') {
                        showToast('Error: ' + errorMsg, 'error');
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                if (typeof showToast === 'function') {
                    showToast('An error occurred. Please try again.', 'error');
                } else {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    }

    // Education Modal Handling
    const saveEducationBtn = document.getElementById('saveEducation');
    const educationModal = document.getElementById('addEducationModal');
    let editingEducationId = null;

    if (saveEducationBtn) {
        saveEducationBtn.addEventListener('click', function() {
            const form = document.getElementById('educationForm');
            const formData = new FormData();
            
            // Get form data
            formData.append('school', document.getElementById('school').value);
            formData.append('degree', document.getElementById('degree').value);
            formData.append('field_of_study', document.getElementById('fieldOfStudy').value);
            formData.append('start_date', document.getElementById('eduStartDate').value);
            formData.append('end_date', document.getElementById('eduEndDate').value);
            formData.append('currently_studying', document.getElementById('currentlyStudying').checked ? 1 : 0);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            
            if (editingEducationId !== null) {
                formData.append('index', editingEducationId);
            }
            
            const url = editingEducationId !== null ? '{{ route("account.updateEducation") }}' : '{{ route("account.addEducation") }}';
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.status) {
                    // Close modal and reset form
                    bootstrap.Modal.getInstance(educationModal).hide();
                    form.reset();
                    editingEducationId = null;
                    
                    // Show success message and reload page
                    if (typeof showToast === 'function') {
                        showToast('Education saved successfully', 'success');
                    }
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    const errorMsg = data.errors ? Object.values(data.errors)[0] : 'Something went wrong';
                    if (typeof showToast === 'function') {
                        showToast('Error: ' + errorMsg, 'error');
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                if (typeof showToast === 'function') {
                    showToast('An error occurred. Please try again.', 'error');
                } else {
                    alert('An error occurred. Please try again.');
                }
            });
        });
    }

    // Delete Experience Handler
    window.deleteExperience = function(experienceIndex) {
        if (confirm('Are you sure you want to delete this experience?')) {
            const formData = new FormData();
            formData.append('index', experienceIndex);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            formData.append('_method', 'DELETE');
            
            fetch('{{ route("account.deleteExperience") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Delete response:', data);
                if (data.status) {
                    if (typeof showToast === 'function') {
                        showToast('Work experience deleted successfully', 'success');
                    }
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    const errorMsg = data.errors ? Object.values(data.errors)[0] : 'Something went wrong';
                    if (typeof showToast === 'function') {
                        showToast('Error: ' + errorMsg, 'error');
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                }
            })
            .catch(error => {
                console.error('Delete Error:', error);
                if (typeof showToast === 'function') {
                    showToast('An error occurred. Please try again.', 'error');
                } else {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    };

    // Delete Education Handler
    window.deleteEducation = function(educationIndex) {
        if (confirm('Are you sure you want to delete this education?')) {
            const formData = new FormData();
            formData.append('index', educationIndex);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            formData.append('_method', 'DELETE');
            
            fetch('{{ route("account.deleteEducation") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Delete response:', data);
                if (data.status) {
                    if (typeof showToast === 'function') {
                        showToast('Education deleted successfully', 'success');
                    }
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    const errorMsg = data.errors ? Object.values(data.errors)[0] : 'Something went wrong';
                    if (typeof showToast === 'function') {
                        showToast('Error: ' + errorMsg, 'error');
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                }
            })
            .catch(error => {
                console.error('Delete Error:', error);
                if (typeof showToast === 'function') {
                    showToast('An error occurred. Please try again.', 'error');
                } else {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    };
    
    // Edit handlers for experience and education items
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-experience') || e.target.closest('.edit-experience')) {
            const button = e.target.classList.contains('edit-experience') ? e.target : e.target.closest('.edit-experience');
            const experienceData = JSON.parse(button.getAttribute('data-experience'));
            
            // Populate form with existing data
            document.getElementById('jobTitle').value = experienceData.title || '';
            document.getElementById('company').value = experienceData.company || '';
            document.getElementById('location').value = experienceData.location || '';
            document.getElementById('startDate').value = experienceData.start_date || '';
            document.getElementById('endDate').value = experienceData.end_date || '';
            document.getElementById('currentlyWorking').checked = experienceData.currently_working || false;
            document.getElementById('description').value = experienceData.description || '';
            
            editingExperienceId = experienceData.id;
            
            // Show modal
            new bootstrap.Modal(experienceModal).show();
        }
        
        if (e.target.classList.contains('edit-education') || e.target.closest('.edit-education')) {
            const button = e.target.classList.contains('edit-education') ? e.target : e.target.closest('.edit-education');
            const educationData = JSON.parse(button.getAttribute('data-education'));
            
            // Populate form with existing data
            document.getElementById('school').value = educationData.school || '';
            document.getElementById('degree').value = educationData.degree || '';
            document.getElementById('fieldOfStudy').value = educationData.field_of_study || '';
            document.getElementById('eduStartDate').value = educationData.start_date || '';
            document.getElementById('eduEndDate').value = educationData.end_date || '';
            document.getElementById('currentlyStudying').checked = educationData.currently_studying || false;
            
            editingEducationId = educationData.id;
            
            // Show modal
            new bootstrap.Modal(educationModal).show();
        }
        
        if (e.target.classList.contains('delete-experience') || e.target.closest('.delete-experience')) {
            const button = e.target.classList.contains('delete-experience') ? e.target : e.target.closest('.delete-experience');
            const index = button.getAttribute('data-index');
            if (index !== null) {
                deleteExperience(index);
            }
        }
        
        if (e.target.classList.contains('delete-education') || e.target.closest('.delete-education')) {
            const button = e.target.classList.contains('delete-education') ? e.target : e.target.closest('.delete-education');
            const index = button.getAttribute('data-index');
            if (index !== null) {
                deleteEducation(index);
            }
        }
    });

    // Reset modal when closed
    if (experienceModal) {
        experienceModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('experienceForm').reset();
            editingExperienceId = null;
            document.querySelector('#addExperienceModal .modal-title').textContent = 'Add Work Experience';
        });
    }

    if (educationModal) {
        educationModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('educationForm').reset();
            editingEducationId = null;
            document.querySelector('#addEducationModal .modal-title').textContent = 'Add Education';
        });
    }

    // Profile Picture Upload Handler
    const profileImageForm = document.getElementById('profileImageForm');
    const profileImageInput = document.getElementById('profileImage');
    const uploadImageBtn = document.getElementById('uploadImageBtn');
    const imageError = document.getElementById('imageError');
    const profileImagePreview = document.getElementById('profileImagePreview');

    if (profileImageForm) {
        // Preview image before upload
        profileImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    imageError.textContent = 'File size must be less than 5MB';
                    imageError.style.display = 'block';
                    e.target.value = '';
                    return;
                }

                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    imageError.textContent = 'Please select a valid image file (JPG, PNG, GIF)';
                    imageError.style.display = 'block';
                    e.target.value = '';
                    return;
                }

                imageError.style.display = 'none';

                // Show preview
                const reader = new FileReader();
                reader.onload = function(event) {
                    // Update preview
                    if (profileImagePreview.tagName === 'IMG') {
                        profileImagePreview.src = event.target.result;
                    } else {
                        // Replace placeholder with image
                        const newImg = document.createElement('img');
                        newImg.src = event.target.result;
                        newImg.alt = 'Profile Picture';
                        newImg.className = 'rounded-circle img-thumbnail';
                        newImg.style.cssText = 'width: 150px; height: 150px; object-fit: cover;';
                        newImg.id = 'profileImagePreview';
                        profileImagePreview.parentNode.replaceChild(newImg, profileImagePreview);
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        // Handle form submission
        profileImageForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const file = profileImageInput.files[0];
            if (!file) {
                imageError.textContent = 'Please select an image file';
                imageError.style.display = 'block';
                return;
            }

            const formData = new FormData();
            formData.append('image', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            // Disable button and show loading
            uploadImageBtn.disabled = true;
            uploadImageBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
            imageError.style.display = 'none';

            fetch('{{ route("account.updateProfileimg") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    // Show success message
                    if (typeof showToast === 'function') {
                        showToast('Profile picture updated successfully!', 'success');
                    } else {
                        alert('Profile picture updated successfully!');
                    }

                    // Reload page to show new image and remove button if needed
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    // Show error
                    const errorMsg = data.errors ? Object.values(data.errors)[0] : 'Failed to upload image';
                    imageError.textContent = Array.isArray(errorMsg) ? errorMsg[0] : errorMsg;
                    imageError.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                imageError.textContent = 'An error occurred while uploading. Please try again.';
                imageError.style.display = 'block';
            })
            .finally(() => {
                // Re-enable button
                uploadImageBtn.disabled = false;
                uploadImageBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Upload Picture';
            });
        });
    }

    // Profile Picture Remove Handler
    const removeImageBtn = document.getElementById('removeImageBtn');
    if (removeImageBtn) {
        removeImageBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to remove your profile picture?')) {
                // Disable button and show loading
                removeImageBtn.disabled = true;
                removeImageBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Removing...';

                fetch('{{ route("account.removeProfileImage") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        // Show success message
                        if (typeof showToast === 'function') {
                            showToast('Profile picture removed successfully!', 'success');
                        } else {
                            alert('Profile picture removed successfully!');
                        }

                        // Reload page
                        setTimeout(() => window.location.reload(), 500);
                    } else {
                        alert('Failed to remove profile picture. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Remove error:', error);
                    alert('An error occurred. Please try again.');
                })
                .finally(() => {
                    // Re-enable button
                    removeImageBtn.disabled = false;
                    removeImageBtn.innerHTML = '<i class="fas fa-trash me-2"></i>Remove Picture';
                });
            }
        });
    }
});
</script>
@endpush 