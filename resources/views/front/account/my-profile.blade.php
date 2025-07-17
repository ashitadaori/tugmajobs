@extends('front.layouts.app')

@section('content')
<section class="section-5 bg-2">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="rounded-3 p-3 mb-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">My Profile</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                @include('front.account.sidebar')
            </div>
            <div class="col-lg-9">
                @include('front.message')

                <!-- Profile Completion Card -->
                <div class="card border-0 shadow mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h3 class="fs-4 mb-1">Profile Completion</h3>
                                <p class="mb-0 text-muted">Complete your profile to increase visibility</p>
                            </div>
                            <div class="completion-percentage">
                                <h4 class="mb-0">{{ $completionPercentage ?? 0 }}%</h4>
                            </div>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                style="width: {{ $completionPercentage ?? 0 }}%" 
                                aria-valuenow="{{ $completionPercentage ?? 0 }}" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="card border-0 shadow mb-4">
                    <div class="card-body p-4">
                        <h3 class="fs-4 mb-1">Personal Information</h3>
                        <p class="mb-4 text-muted">Update your personal details and how employers will see you</p>
                        
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
                                    <input type="text" name="designation" id="designation" class="form-control" value="{{ old('designation', $user->designation) }}" placeholder="e.g., Senior Software Engineer">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <label for="bio" class="mb-2">Professional Summary</label>
                                    <textarea name="bio" id="bio" class="form-control" rows="4" placeholder="Tell employers about yourself, your experience and career goals">{{ old('bio', $user->bio) }}</textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <label for="skills" class="mb-2">Skills</label>
                                    <div class="skills-input-container">
                                        <input type="text" id="skillInput" class="form-control" placeholder="Type a skill and press Enter">
                                        <div id="skillTags" class="mt-2"></div>
                                        <input type="hidden" name="skills" id="skillsHidden" value="{{ old('skills', is_array($user->skills) ? implode(',', $user->skills) : '') }}">
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
                                <h3 class="fs-4 mb-1">Work Experience</h3>
                                <p class="mb-0 text-muted">Add your work history to showcase your expertise</p>
                            </div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExperienceModal">
                                <i class="fas fa-plus me-2"></i>Add Experience
                            </button>
                        </div>

                        <div id="experienceList">
                            @if($user->jobSeekerProfile && isset($user->jobSeekerProfile->work_experience))
                                @foreach(json_decode($user->jobSeekerProfile->work_experience) as $experience)
                                    <div class="experience-item mb-3 p-3 border rounded">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h5 class="mb-1">{{ $experience->title }}</h5>
                                                <p class="mb-1">{{ $experience->company }}</p>
                                                <p class="text-muted mb-2">{{ $experience->start_date }} - {{ $experience->end_date ?? 'Present' }}</p>
                                                <p class="mb-0">{{ $experience->description }}</p>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-outline-primary me-2 edit-experience" 
                                                    data-experience="{{ json_encode($experience) }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-experience">
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
                                <h3 class="fs-4 mb-1">Education</h3>
                                <p class="mb-0 text-muted">Add your educational background</p>
                            </div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEducationModal">
                                <i class="fas fa-plus me-2"></i>Add Education
                            </button>
                        </div>

                        <div id="educationList">
                            @if($user->jobSeekerProfile && isset($user->jobSeekerProfile->education))
                                @foreach(json_decode($user->jobSeekerProfile->education) as $education)
                                    <div class="education-item mb-3 p-3 border rounded">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h5 class="mb-1">{{ $education->degree }}</h5>
                                                <p class="mb-1">{{ $education->school }}</p>
                                                <p class="text-muted mb-0">{{ $education->start_date }} - {{ $education->end_date ?? 'Present' }}</p>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-outline-primary me-2 edit-education" 
                                                    data-education="{{ json_encode($education) }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-education">
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
                        <h3 class="fs-4 mb-1">Resume</h3>
                        <p class="mb-4 text-muted">Upload your latest resume</p>

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

                <!-- Social Links -->
                <div class="card border-0 shadow mb-4">
                    <div class="card-body p-4">
                        <h3 class="fs-4 mb-1">Professional Links</h3>
                        <p class="mb-4 text-muted">Add your professional social media and portfolio links</p>

                        <form action="{{ route('account.updateSocialLinks') }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="linkedin" class="mb-2">
                                        <i class="fab fa-linkedin me-2"></i>LinkedIn
                                    </label>
                                    <input type="url" name="social_links[linkedin]" id="linkedin" class="form-control" 
                                           value="{{ old('social_links.linkedin', ($user->jobSeekerProfile ? ($user->jobSeekerProfile->social_links['linkedin'] ?? '') : '')) }}" 
                                           placeholder="https://linkedin.com/in/your-profile">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="github" class="mb-2">
                                        <i class="fab fa-github me-2"></i>GitHub
                                    </label>
                                    <input type="url" name="social_links[github]" id="github" class="form-control" 
                                           value="{{ old('social_links.github', ($user->jobSeekerProfile ? ($user->jobSeekerProfile->social_links['github'] ?? '') : '')) }}" 
                                           placeholder="https://github.com/your-username">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="portfolio" class="mb-2">
                                        <i class="fas fa-globe me-2"></i>Portfolio Website
                                    </label>
                                    <input type="url" name="social_links[portfolio]" id="portfolio" class="form-control" 
                                           value="{{ old('social_links.portfolio', ($user->jobSeekerProfile ? ($user->jobSeekerProfile->social_links['portfolio'] ?? '') : '')) }}" 
                                           placeholder="https://your-portfolio.com">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="other" class="mb-2">
                                        <i class="fas fa-link me-2"></i>Other Link
                                    </label>
                                    <input type="url" name="social_links[other]" id="other" class="form-control" 
                                           value="{{ old('social_links.other', ($user->jobSeekerProfile ? ($user->jobSeekerProfile->social_links['other'] ?? '') : '')) }}" 
                                           placeholder="https://other-professional-link.com">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Links</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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
});
</script>
@endpush 