@extends('front.layouts.app')

@section('content')
<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item">Profile</li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                @include('components.sidebar')
            </div>
            <div class="col-lg-9">
                <div class="row">
                    <!-- Settings Navigation Sidebar -->
                    <div class="col-md-3">
                <div class="card border-0 shadow mb-4">
                            <div class="card-body p-0">
                                <div class="settings-nav">
                                    <a href="#personal-info" class="nav-link active" data-bs-toggle="pill">
                                        <i class="fas fa-user me-2"></i> Personal Info
                                    </a>
                                    <a href="#experience" class="nav-link" data-bs-toggle="pill">
                                        <i class="fas fa-briefcase me-2"></i> Experience
                                    </a>
                                    <a href="#education" class="nav-link" data-bs-toggle="pill">
                                        <i class="fas fa-graduation-cap me-2"></i> Education
                                    </a>
                                    <a href="#skills" class="nav-link" data-bs-toggle="pill">
                                        <i class="fas fa-tools me-2"></i> Skills
                                    </a>
                                    <a href="#social-links" class="nav-link" data-bs-toggle="pill">
                                        <i class="fas fa-link me-2"></i> Social Links
                                    </a>
                                    <a href="#preferences" class="nav-link" data-bs-toggle="pill">
                                        <i class="fas fa-cog me-2"></i> Preferences
                                    </a>
                            </div>
                            </div>
                            </div>
                        </div>

                    <!-- Settings Content -->
                    <div class="col-md-9">
                        <div class="tab-content">
                            <!-- Personal Information -->
                            <div class="tab-pane fade show active" id="personal-info">
                                <div class="card border-0 shadow mb-4">
                                    <div class="card-body p-4">
                                        <h3 class="h4 mb-4">Personal Information</h3>
                                        <div class="mb-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="avatar-upload me-4">
                                                    <img src="{{ Auth::user()->image ? asset('profile_img/'.Auth::user()->image) : asset('assets/images/avatar7.png') }}" 
                                                         alt="Profile" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                                </div>
                                                <div>
                                                    <h5 class="mb-0">Upload a new avatar</h5>
                                                    <p class="text-muted mb-2">JPG, GIF or PNG. Max size 2MB</p>
                                                    <form action="{{ route('account.updateProfileimg') }}" method="post" enctype="multipart/form-data" id="profileImgForm">
                                                        @csrf
                                                        <input type="file" name="image" id="image" class="d-none" onchange="document.getElementById('profileImgForm').submit()">
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('image').click()">
                                                            Choose File
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                        <form action="{{ route('account.updateProfile') }}" method="post" id="profileForm">
                            @csrf
                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                           id="name" name="name" value="{{ old('name', Auth::user()->name) }}">
                                                    @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="email" class="form-label">Email</label>
                                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                           id="email" name="email" value="{{ old('email', Auth::user()->email) }}">
                                                    @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="mobile" class="form-label">Phone Number</label>
                                                    <input type="text" class="form-control @error('mobile') is-invalid @enderror" 
                                                           id="mobile" name="mobile" value="{{ old('mobile', Auth::user()->mobile) }}">
                                                    @error('mobile')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="designation" class="form-label">Job Title</label>
                                                    <input type="text" class="form-control @error('designation') is-invalid @enderror" 
                                                           id="designation" name="designation" value="{{ old('designation', Auth::user()->designation) }}">
                                                    @error('designation')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label for="bio" class="form-label">Bio</label>
                                                    <textarea class="form-control @error('bio') is-invalid @enderror" 
                                                              id="bio" name="bio" rows="4">{{ old('bio', Auth::user()->bio) }}</textarea>
                                                    @error('bio')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Experience Section -->
                            <div class="tab-pane fade" id="experience">
                                <div class="card border-0 shadow mb-4">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h3 class="h4 mb-0">Experience</h3>
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addExperienceModal">
                                                <i class="fas fa-plus"></i> Add Experience
                                            </button>
                                        </div>
                                        <div id="experienceList">
                                            <!-- Experience items will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Education Section -->
                            <div class="tab-pane fade" id="education">
                                <div class="card border-0 shadow mb-4">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h3 class="h4 mb-0">Education</h3>
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEducationModal">
                                                <i class="fas fa-plus"></i> Add Education
                                            </button>
                                        </div>
                                        <div id="educationList">
                                            <!-- Education items will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Skills Section -->
                            <div class="tab-pane fade" id="skills">
                                <div class="card border-0 shadow mb-4">
                                    <div class="card-body p-4">
                                        <h3 class="h4 mb-4">Skills</h3>
                                        <form action="{{ route('account.updateProfile') }}" method="post">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="skills" class="form-label">Skills (comma separated)</label>
                                                <input type="text" class="form-control @error('skills') is-invalid @enderror" 
                                                       id="skills" name="skills" value="{{ old('skills', Auth::user()->skills) }}"
                                                       placeholder="e.g. PHP, Laravel, MySQL, JavaScript">
                                                @error('skills')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <button type="submit" class="btn btn-primary">Save Skills</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Social Links Section -->
                            <div class="tab-pane fade" id="social-links">
                                <div class="card border-0 shadow mb-4">
                                    <div class="card-body p-4">
                                        <h3 class="h4 mb-4">Social Links</h3>
                                        <form action="{{ route('account.updateSocialLinks') }}" method="post">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="linkedin" class="form-label">LinkedIn Profile</label>
                                                <input type="url" class="form-control" id="linkedin" name="linkedin" 
                                                       value="{{ old('linkedin', Auth::user()->linkedin) }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="github" class="form-label">GitHub Profile</label>
                                                <input type="url" class="form-control" id="github" name="github" 
                                                       value="{{ old('github', Auth::user()->github) }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="website" class="form-label">Personal Website</label>
                                                <input type="url" class="form-control" id="website" name="website" 
                                                       value="{{ old('website', Auth::user()->website) }}">
                                            </div>
                                            <button type="submit" class="btn btn-primary">Save Social Links</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Preferences Section -->
                            <div class="tab-pane fade" id="preferences">
                                <div class="card border-0 shadow mb-4">
                                    <div class="card-body p-4">
                                        <h3 class="h4 mb-4">Preferences</h3>
                                        <form action="{{ route('account.updatePrivacy') }}" method="post">
                                            @csrf
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="profileVisibility" 
                                                           name="profile_visibility" {{ Auth::user()->profile_visibility ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="profileVisibility">
                                                        Make my profile visible to employers
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="emailNotifications" 
                                                           name="email_notifications" {{ Auth::user()->email_notifications ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="emailNotifications">
                                                        Receive email notifications for new job matches
                                                    </label>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Save Preferences</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Experience Modal -->
<div class="modal fade" id="addExperienceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="experienceForm" action="{{ route('account.addExperience') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="job_title" class="form-label">Job Title</label>
                        <input type="text" class="form-control" id="job_title" name="job_title" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="col">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="current_job" name="current_job">
                            <label class="form-check-label" for="current_job">
                                I currently work here
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="experienceForm" class="btn btn-primary">Add Experience</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Education Modal -->
<div class="modal fade" id="addEducationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Education</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="educationForm" action="{{ route('account.addEducation') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label for="school_name" class="form-label">School/University Name</label>
                        <input type="text" class="form-control" id="school_name" name="school_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="degree" class="form-label">Degree</label>
                        <input type="text" class="form-control" id="degree" name="degree" required>
                    </div>
                    <div class="mb-3">
                        <label for="field_of_study" class="form-label">Field of Study</label>
                        <input type="text" class="form-control" id="field_of_study" name="field_of_study" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="edu_start_date" name="start_date" required>
                        </div>
                        <div class="col">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="edu_end_date" name="end_date">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="current_education" name="current_education">
                            <label class="form-check-label" for="current_education">
                                I'm currently studying here
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="edu_description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="educationForm" class="btn btn-primary">Add Education</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.settings-nav {
    display: flex;
    flex-direction: column;
}

.settings-nav .nav-link {
    padding: 12px 15px;
    color: #666;
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
}

.settings-nav .nav-link:hover {
    background-color: #f8f9fa;
    color: #0d6efd;
    border-left-color: #0d6efd;
}

.settings-nav .nav-link.active {
    background-color: #f8f9fa;
    color: #0d6efd;
    border-left-color: #0d6efd;
    font-weight: 500;
}

.avatar-upload img {
    border: 2px solid #eee;
    transition: all 0.3s ease;
}

.avatar-upload img:hover {
    border-color: #0d6efd;
}

.tab-content {
    min-height: 400px;
}

.form-switch .form-check-input {
    width: 3em;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle current job checkbox
    document.getElementById('current_job')?.addEventListener('change', function() {
        const endDateInput = document.getElementById('end_date');
        endDateInput.disabled = this.checked;
        if (this.checked) endDateInput.value = '';
    });

    // Handle current education checkbox
    document.getElementById('current_education')?.addEventListener('change', function() {
        const endDateInput = document.getElementById('edu_end_date');
        endDateInput.disabled = this.checked;
        if (this.checked) endDateInput.value = '';
    });

    // Load experience and education lists
    loadExperienceList();
    loadEducationList();
});

function loadExperienceList() {
    // Add AJAX call to load experience items
}

function loadEducationList() {
    // Add AJAX call to load education items
}
</script>
@endpush 