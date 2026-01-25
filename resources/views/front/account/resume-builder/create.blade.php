@extends('layouts.jobseeker')

@section('jobseeker-content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1">Create Resume</h4>
                        <p class="text-muted mb-0">Template: <span class="fw-bold text-primary">{{ $template->name }}</span></p>
                    </div>
                    <a href="{{ route('account.resume-builder.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Templates
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('account.resume-builder.store') }}" method="POST" id="resumeForm"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="template_id" value="{{ $template->id }}">

            <div class="row g-4">
                <!-- Main Content Area -->
                <div class="col-lg-8">
                    
                    <!-- 1. Resume Title -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0"><i class="fas fa-heading me-2 text-primary"></i>Resume Title</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label text-muted">Give your resume a name (e.g., "Senior Developer 2024")</label>
                                <input type="text" name="title" class="form-control form-control-lg"
                                    placeholder="Enter resume title..." required>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Personal Information -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0"><i class="fas fa-user-edit me-2 text-primary"></i>Personal Information</h5>
                        </div>
                        <div class="card-body">
                            
                            <!-- Check user type to determine which profile photo to show -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-auto">
                                    <div class="position-relative">
                                        <div id="photoPreview" style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; background-color: #f0f2f5; border: 2px solid #e1e4e8; display: flex; align-items: center; justify-content: center;">
                                            @if(!empty($personalInfo['photo']) && Storage::disk('public')->exists($personalInfo['photo']))
                                                 <img id="photoPreviewImg" src="{{ asset('storage/'.$personalInfo['photo']) }}" alt="Profile Photo" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                 <img id="photoPreviewImg" src="{{ asset('assets/images/avatar7.png') }}" alt="Default Photo" style="width: 100%; height: 100%; object-fit: cover; {{ !empty($personalInfo['photo']) ? '' : 'display:none;' }}">
                                                 @if(empty($personalInfo['photo']))
                                                    <i class="fas fa-camera text-muted" style="font-size: 32px;" id="defaultPhotoIcon"></i>
                                                 @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label fw-bold">Profile Photo</label>
                                    <input type="file" name="photo" class="form-control" accept="image/*" id="photoInput">
                                    <small class="text-muted d-block mt-1">Recommended: Square JPG/PNG, max 2MB</small>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ $personalInfo['name'] }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ $personalInfo['email'] }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $personalInfo['phone'] }}" placeholder="+1 234 567 8900">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="address" class="form-control" value="{{ $personalInfo['address'] }}" placeholder="City, Country">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Website / Portfolio</label>
                                    <input type="url" name="website" class="form-control" value="{{ $personalInfo['website'] ?? '' }}" placeholder="https://...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Current Job Title <span class="text-danger">*</span></label>
                                    <input type="text" name="job_title" class="form-control" value="{{ $personalInfo['job_title'] }}" placeholder="e.g. Software Engineer" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Professional Summary -->
                    <div class="card shadow-sm mb-4">
                         <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Professional Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light border-0 bg-soft-primary text-primary mb-3">
                                <i class="fas fa-info-circle me-1"></i> Auto-filled from your profile bio. Feel free to customize it for this resume.
                            </div>
                            <textarea name="professional_summary" class="form-control" rows="5"
                                placeholder="Write a brief overview of your professional background, key achievements, and career goals...">{{ $professionalSummary ?? '' }}</textarea>
                        </div>
                    </div>

                    <!-- 4. Work Experience -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="fas fa-briefcase me-2 text-primary"></i>Work Experience</h5>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill" onclick="addWorkExperience()">
                                <i class="fas fa-plus me-1"></i> Add Experience
                            </button>
                        </div>
                        <div class="card-body bg-light">
                            <div id="workExperienceContainer"></div>
                            <div id="workExperienceEmptyState" class="text-center py-4 text-muted" style="display: none;">
                                <i class="fas fa-briefcase mb-2" style="font-size: 32px; opacity: 0.5;"></i>
                                <p>No work experience added yet.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Education -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="fas fa-graduation-cap me-2 text-primary"></i>Education</h5>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill" onclick="addEducation()">
                                <i class="fas fa-plus me-1"></i> Add Education
                            </button>
                        </div>
                        <div class="card-body bg-light">
                            <div id="educationContainer"></div>
                             <div id="educationEmptyState" class="text-center py-4 text-muted" style="display: none;">
                                <i class="fas fa-graduation-cap text-muted mb-2" style="font-size: 32px; opacity: 0.5;"></i>
                                <p>No education details added yet.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Skills -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0"><i class="fas fa-star me-2 text-primary"></i>Skills</h5>
                        </div>
                        <div class="card-body">
                             <div class="input-group mb-3">
                                <span class="input-group-text"><i class="fas fa-plus"></i></span>
                                <input type="text" id="skillInput" class="form-control"
                                    placeholder="Type a skill and press Enter to add (e.g. PHP, Project Management)">
                            </div>
                            <div id="skillsContainer" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>
                    </div>

                    <!-- 7. Certifications -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Certifications <small class="text-muted ms-1">(Optional)</small></h5>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" onclick="addCertification()">
                                <i class="fas fa-plus me-1"></i> Add
                            </button>
                        </div>
                        <div class="card-body bg-light">
                            <div id="certificationsContainer"></div>
                        </div>
                    </div>

                    <!-- 8. Languages -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">Languages <small class="text-muted ms-1">(Optional)</small></h5>
                        </div>
                        <div class="card-body">
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="fas fa-language"></i></span>
                                <input type="text" id="languageInput" class="form-control"
                                    placeholder="Type a language and press Enter (e.g., English - Native)">
                            </div>
                            <div id="languagesContainer" class="d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>

                     <!-- 9. Projects -->
                     <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Projects <small class="text-muted ms-1">(Optional)</small></h5>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" onclick="addProject()">
                                <i class="fas fa-plus me-1"></i> Add
                            </button>
                        </div>
                        <div class="card-body bg-light">
                            <div id="projectsContainer"></div>
                        </div>
                    </div>

                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="sticky-top" style="top: 100px; z-index: 10;">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-3">Selected Template</h5>
                                
                                <div class="bg-light rounded mb-3 overflow-hidden position-relative template-preview-container"
                                    style="height: 300px; border: 1px solid #eee;">
                                     @php
                                        $imageName = strtolower($template->name);
                                    @endphp
                                    @if(file_exists(public_path('assets/images/resume-templates/' . $imageName . '.png')))
                                        <img src="{{ asset('assets/images/resume-templates/' . $imageName . '.png') }}" 
                                             alt="{{ $template->name }}" 
                                             class="img-fluid w-100 h-100"
                                             style="object-fit: contain;">
                                    @else
                                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                                            <i class="fas fa-file-alt fa-3x mb-2 text-primary opacity-50"></i>
                                            <span class="fw-medium">{{ $template->name }} Preview</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="mb-4">
                                    <h6 class="mb-1 fw-bold">{{ $template->name }}</h6>
                                    <p class="text-muted small mb-0">{{ $template->description }}</p>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="button" onclick="submitForm()" class="btn btn-primary btn-lg shadow-sm">
                                        <i class="fas fa-save me-1"></i> Save Resume
                                    </button>
                                    <a href="{{ route('account.resume-builder.index') }}" class="btn btn-light text-muted">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden inputs for JSON data -->
            <input type="hidden" id="workExperienceData" name="work_experience">
            <input type="hidden" id="educationData" name="education">
            <input type="hidden" id="skillsData" name="skills">
            <input type="hidden" id="certificationsData" name="certifications">
            <input type="hidden" id="languagesData" name="languages">
            <input type="hidden" id="projectsData" name="projects">
        </form>
    </div>

    @push('scripts')
        <script>
            // Initialize with autofilled data from profile
            let workExperiences = []; // Initially empty, will populate from import or add manually
            let educations = @json($education ?? []);
            let skills = @json($skills ?? []);
            let certifications = [];
            let languages = [];
            let projects = [];

            // Ensure IDs exist for autofilled data
            educations = educations.map((edu, index) => ({ ...edu, id: edu.id || Date.now() + index }));

            // --- UI Helper Functions ---
            
            function updateEmptyStates() {
                const weEmpty = document.getElementById('workExperienceEmptyState');
                if(weEmpty) weEmpty.style.display = workExperiences.length === 0 ? 'block' : 'none';

                const edEmpty = document.getElementById('educationEmptyState');
                if(edEmpty) edEmpty.style.display = educations.length === 0 ? 'block' : 'none';
            }

            // --- Work Experience Functions ---
            function addWorkExperience() {
                const id = Date.now();
                workExperiences.push({
                    id: id,
                    title: '',
                    company: '',
                    location: '',
                    start_date: '',
                    end_date: '',
                    current: false,
                    description: ''
                });
                renderWorkExperiences();
            }

            function removeWorkExperience(id) {
                if(confirm('Are you sure you want to remove this experience?')) {
                    workExperiences = workExperiences.filter(exp => exp.id !== id);
                    renderWorkExperiences();
                }
            }

            function renderWorkExperiences() {
                const container = document.getElementById('workExperienceContainer');
                container.innerHTML = workExperiences.map((exp, index) => `
                <div class="card mb-3 border">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="card-title mb-0 fw-bold text-primary">#${index + 1} Experience</h6>
                            <button type="button" class="btn btn-sm btn-icon btn-soft-danger" onclick="removeWorkExperience(${exp.id})" title="Remove">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Job Title</label>
                                <input type="text" class="form-control" placeholder="e.g. Product Manager" 
                                       value="${exp.title || ''}" oninput="updateWorkExperience(${exp.id}, 'title', this.value)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Company Name</label>
                                <input type="text" class="form-control" placeholder="e.g. Microsoft" 
                                       value="${exp.company || ''}" oninput="updateWorkExperience(${exp.id}, 'company', this.value)">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small text-muted">Location</label>
                                <input type="text" class="form-control" placeholder="e.g. Redmond, WA" 
                                       value="${exp.location || ''}" oninput="updateWorkExperience(${exp.id}, 'location', this.value)">
                            </div>
                             <div class="col-md-5">
                                <label class="form-label small text-muted">Start Date</label>
                                <input type="month" class="form-control" 
                                       value="${exp.start_date || ''}" oninput="updateWorkExperience(${exp.id}, 'start_date', this.value)">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small text-muted">End Date</label>
                                <input type="month" class="form-control" 
                                       value="${exp.end_date || ''}" ${exp.current ? 'disabled' : ''} 
                                       oninput="updateWorkExperience(${exp.id}, 'end_date', this.value)">
                            </div>
                            <div class="col-md-2 d-flex align-items-end mb-2">
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" id="currentCheck_${exp.id}" ${exp.current ? 'checked' : ''} 
                                           onchange="updateWorkExperience(${exp.id}, 'current', this.checked)">
                                    <label class="form-check-label small" for="currentCheck_${exp.id}">I currently work here</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small text-muted">Description</label>
                                <textarea class="form-control" rows="3" placeholder="Describe your responsibilities and achievements..." 
                                          oninput="updateWorkExperience(${exp.id}, 'description', this.value)">${exp.description || ''}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
                updateEmptyStates();
            }

            function updateWorkExperience(id, field, value) {
                const exp = workExperiences.find(e => e.id === id);
                if (exp) {
                    exp[field] = value;
                    if (field === 'current') {
                         if(value === true) {
                             exp.end_date = '';
                             // Re-render to disable the end date input
                             renderWorkExperiences();
                             // Refocus logic could be added here if needed, but simple re-render is safe
                         } else {
                             renderWorkExperiences();
                         }
                    }
                }
            }

            // --- Education Functions ---
            function addEducation() {
                const id = Date.now();
                educations.push({
                    id: id,
                    degree: '',
                    institution: '',
                    location: '',
                    graduation_date: '',
                    gpa: ''
                });
                renderEducations();
            }

            function removeEducation(id) {
                 if(confirm('Are you sure you want to remove this education?')) {
                    educations = educations.filter(edu => edu.id !== id);
                    renderEducations();
                 }
            }

            function renderEducations() {
                const container = document.getElementById('educationContainer');
                container.innerHTML = educations.map((edu, index) => `
                <div class="card mb-3 border">
                     <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                             <h6 class="card-title mb-0 fw-bold text-primary">#${index + 1} Education</h6>
                             <button type="button" class="btn btn-sm btn-icon btn-soft-danger" onclick="removeEducation(${edu.id})">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Degree / Major</label>
                                <input type="text" class="form-control" placeholder="e.g. Bachelor of Science in CS" 
                                       value="${edu.degree || ''}" oninput="updateEducation(${edu.id}, 'degree', this.value)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Institution / University</label>
                                <input type="text" class="form-control" placeholder="e.g. Stanford University" 
                                       value="${edu.institution || ''}" oninput="updateEducation(${edu.id}, 'institution', this.value)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Location</label>
                                <input type="text" class="form-control" placeholder="City, Country" 
                                       value="${edu.location || ''}" oninput="updateEducation(${edu.id}, 'location', this.value)">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Graduation Date</label>
                                <input type="month" class="form-control" 
                                       value="${edu.graduation_date || ''}" oninput="updateEducation(${edu.id}, 'graduation_date', this.value)">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">GPA (Optional)</label>
                                <input type="text" class="form-control" placeholder="e.g. 3.8/4.0" 
                                       value="${edu.gpa || ''}" oninput="updateEducation(${edu.id}, 'gpa', this.value)">
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
                 updateEmptyStates();
            }

            function updateEducation(id, field, value) {
                const edu = educations.find(e => e.id === id);
                if (edu) {
                    edu[field] = value;
                }
            }

            // --- Skills Functions ---
            document.getElementById('skillInput').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const skill = this.value.trim();
                    if (skill && !skills.includes(skill)) {
                        skills.push(skill);
                        renderSkills();
                        this.value = '';
                    }
                }
            });

            function removeSkill(index) {
                skills.splice(index, 1);
                renderSkills();
            }

            function renderSkills() {
                const container = document.getElementById('skillsContainer');
                container.innerHTML = skills.map((skill, index) => `
                <span class="badge bg-soft-primary text-primary border border-primary-subtle d-flex align-items-center gap-2 p-2" style="font-size: 0.9rem;">
                    ${skill}
                    <i class="fas fa-times-circle" style="cursor: pointer; font-size: 1.1em;" onclick="removeSkill(${index})"></i>
                </span>
            `).join('');
            }

            // --- Certifications Functions ---
            function addCertification() {
                const id = Date.now();
                certifications.push({
                    id: id,
                    name: '',
                    issuer: '',
                    date: '',
                    credential_id: ''
                });
                renderCertifications();
            }

            function removeCertification(id) {
                certifications = certifications.filter(cert => cert.id !== id);
                renderCertifications();
            }

            function renderCertifications() {
                const container = document.getElementById('certificationsContainer');
                container.innerHTML = certifications.map((cert, index) => `
                <div class="card mb-3 border bg-white">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between mb-2">
                             <h6 class="card-title fw-medium">Certification #${index + 1}</h6>
                             <button type="button" class="btn btn-sm btn-icon btn-ghost-danger" onclick="removeCertification(${cert.id})">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        <div class="row g-3">
                             <div class="col-md-6">
                                <input type="text" class="form-control form-control-sm" placeholder="Certification Name" 
                                       value="${cert.name || ''}" oninput="updateCertification(${cert.id}, 'name', this.value)">
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control form-control-sm" placeholder="Issuing Org" 
                                       value="${cert.issuer || ''}" oninput="updateCertification(${cert.id}, 'issuer', this.value)">
                            </div>
                             <div class="col-md-6">
                                <input type="month" class="form-control form-control-sm" 
                                       value="${cert.date || ''}" oninput="updateCertification(${cert.id}, 'date', this.value)">
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control form-control-sm" placeholder="Credential ID (Optional)" 
                                       value="${cert.credential_id || ''}" oninput="updateCertification(${cert.id}, 'credential_id', this.value)">
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            }

            function updateCertification(id, field, value) {
                const cert = certifications.find(c => c.id === id);
                if (cert) cert[field] = value;
            }

            // --- Languages Functions ---
             document.getElementById('languageInput').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const language = this.value.trim();
                    if (language && !languages.includes(language)) {
                        languages.push(language);
                        renderLanguages();
                        this.value = '';
                    }
                }
            });

            function removeLanguage(index) {
                languages.splice(index, 1);
                renderLanguages();
            }

            function renderLanguages() {
                const container = document.getElementById('languagesContainer');
                container.innerHTML = languages.map((language, index) => `
                <span class="badge bg-soft-success text-success border border-success-subtle d-flex align-items-center gap-2 p-2" style="font-size: 0.9rem;">
                    ${language}
                    <i class="fas fa-times-circle" style="cursor: pointer; font-size: 1.1em;" onclick="removeLanguage(${index})"></i>
                </span>
            `).join('');
            }

            // --- Projects Functions ---
            function addProject() {
                const id = Date.now();
                projects.push({
                    id: id,
                    name: '',
                    description: '',
                    technologies: '',
                    link: ''
                });
                renderProjects();
            }

            function removeProject(id) {
                projects = projects.filter(proj => proj.id !== id);
                renderProjects();
            }

            function renderProjects() {
                const container = document.getElementById('projectsContainer');
                container.innerHTML = projects.map((proj, index) => `
                <div class="card mb-3 border bg-white">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between mb-2">
                             <h6 class="card-title fw-medium">Project #${index + 1}</h6>
                             <button type="button" class="btn btn-sm btn-icon btn-ghost-danger" onclick="removeProject(${proj.id})">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        <div class="row g-3">
                             <div class="col-md-12">
                                <input type="text" class="form-control form-control-sm" placeholder="Project Name" 
                                       value="${proj.name || ''}" oninput="updateProject(${proj.id}, 'name', this.value)">
                            </div>
                            <div class="col-md-12">
                                <textarea class="form-control form-control-sm" rows="2" placeholder="Project Description" 
                                          oninput="updateProject(${proj.id}, 'description', this.value)">${proj.description || ''}</textarea>
                            </div>
                             <div class="col-md-6">
                                <input type="text" class="form-control form-control-sm" placeholder="Technologies (e.g. React, Node.js)" 
                                       value="${proj.technologies || ''}" oninput="updateProject(${proj.id}, 'technologies', this.value)">
                            </div>
                             <div class="col-md-6">
                                <input type="url" class="form-control form-control-sm" placeholder="Project Link (http://...)" 
                                       value="${proj.link || ''}" oninput="updateProject(${proj.id}, 'link', this.value)">
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            }

            function updateProject(id, field, value) {
                const proj = projects.find(p => p.id === id);
                if (proj) proj[field] = value;
            }

            // --- Photo Preview ---
            document.getElementById('photoInput').addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                         const img = document.getElementById('photoPreviewImg');
                         img.src = e.target.result;
                         img.style.display = 'block';
                         const icon = document.getElementById('defaultPhotoIcon');
                         if(icon) icon.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                }
            });

            // --- Form Submission ---
            function submitForm() {
                const form = document.getElementById('resumeForm');
                
                // 1. Validate Required Fields
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // 2. Serialize Data to Hidden Fields
                document.getElementById('workExperienceData').value = JSON.stringify(workExperiences);
                document.getElementById('educationData').value = JSON.stringify(educations);
                document.getElementById('skillsData').value = JSON.stringify(skills);
                document.getElementById('certificationsData').value = JSON.stringify(certifications);
                document.getElementById('languagesData').value = JSON.stringify(languages);
                document.getElementById('projectsData').value = JSON.stringify(projects);

                // 3. Debug Output (Optional)
                console.log('Submitting Resume Data:', {
                    workExperiences, educations, skills, certifications, languages, projects
                });

                // 4. Submit
                form.submit();
            }

            // Initial Render
            renderWorkExperiences();
            renderEducations();
            renderSkills();
            renderCertifications();
            renderLanguages();
            renderProjects();

        </script>
    @endpush
@endsection