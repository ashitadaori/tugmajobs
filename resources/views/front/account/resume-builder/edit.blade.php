@extends('layouts.jobseeker')

@section('jobseeker-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Edit Resume - {{ $resume->title }}</h4>
                <div class="page-title-right">
                    <a href="{{ route('account.resume-builder.index') }}" class="btn btn-secondary">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('account.resume-builder.update', $resume->id) }}" method="POST" id="resumeForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Resume Title -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Resume Title</h5>
                        <input type="text" name="title" class="form-control" value="{{ $resume->title }}" required>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Personal Information</h5>
                        <div class="alert alert-info">
                            <i class="mdi mdi-information"></i> You can edit these fields
                        </div>
                        
                        <!-- Photo Upload -->
                        <div class="mb-3">
                            <label>Profile Photo (Optional)</label>
                            <input type="file" name="photo" class="form-control" accept="image/*" id="photoInput">
                            <small class="text-muted">Upload a professional photo for your resume</small>
                            @if(!empty($resume->data->personal_info['photo']))
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $resume->data->personal_info['photo']) }}" 
                                         alt="Current photo" 
                                         style="max-width: 150px; max-height: 150px; border-radius: 50%;">
                                    <p class="small text-muted">Current photo (upload new to replace)</p>
                                </div>
                            @endif
                            <div id="photoPreview" class="mt-2" style="display: none;">
                                <img id="photoPreviewImg" src="" alt="Preview" style="max-width: 150px; max-height: 150px; border-radius: 50%;">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $resume->data->personal_info['name'] ?? '' }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="{{ $resume->data->personal_info['email'] ?? '' }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ $resume->data->personal_info['phone'] ?? '' }}" placeholder="e.g., +1234567890">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Address</label>
                                <input type="text" name="address" class="form-control" value="{{ $resume->data->personal_info['address'] ?? '' }}" placeholder="e.g., City, State">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Website (Optional)</label>
                                <input type="url" name="website" class="form-control" value="{{ $resume->data->personal_info['website'] ?? '' }}" placeholder="e.g., www.yoursite.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Job Title</label>
                                <input type="text" name="job_title" class="form-control" value="{{ $resume->data->personal_info['job_title'] ?? '' }}" placeholder="e.g., Graphics Designer" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Summary -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Professional Summary</h5>
                        <textarea name="professional_summary" class="form-control" rows="4">{{ $resume->data->professional_summary ?? '' }}</textarea>
                    </div>
                </div>

                <!-- Work Experience -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Work Experience</h5>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addWorkExperience()">
                                <i class="mdi mdi-plus"></i> Add Experience
                            </button>
                        </div>
                        <div id="workExperienceContainer"></div>
                    </div>
                </div>

                <!-- Education -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Education</h5>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addEducation()">
                                <i class="mdi mdi-plus"></i> Add Education
                            </button>
                        </div>
                        <div id="educationContainer"></div>
                    </div>
                </div>

                <!-- Skills -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Skills</h5>
                        <div class="mb-3">
                            <input type="text" id="skillInput" class="form-control" placeholder="Type a skill and press Enter">
                        </div>
                        <div id="skillsContainer" class="d-flex flex-wrap gap-2"></div>
                    </div>
                </div>

                <!-- Certifications (Optional) -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Certifications <span class="badge bg-secondary">Optional</span></h5>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addCertification()">
                                <i class="mdi mdi-plus"></i> Add
                            </button>
                        </div>
                        <div id="certificationsContainer"></div>
                    </div>
                </div>

                <!-- Languages (Optional) -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Languages <span class="badge bg-secondary">Optional</span></h5>
                        <div class="mb-3">
                            <input type="text" id="languageInput" class="form-control" placeholder="Type a language and press Enter (e.g., English - Fluent)">
                        </div>
                        <div id="languagesContainer" class="d-flex flex-wrap gap-2"></div>
                    </div>
                </div>

                <!-- Projects (Optional) -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Projects <span class="badge bg-secondary">Optional</span></h5>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addProject()">
                                <i class="mdi mdi-plus"></i> Add
                            </button>
                        </div>
                        <div id="projectsContainer"></div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Actions</h5>
                        
                        <button type="submit" class="btn btn-success btn-block w-100 mb-2">
                            <i class="mdi mdi-content-save"></i> Save Changes
                        </button>
                        
                        <a href="{{ route('account.resume-builder.preview', $resume->id) }}" 
                           class="btn btn-info btn-block w-100 mb-2" target="_blank">
                            <i class="mdi mdi-eye"></i> Preview
                        </a>
                        
                        <a href="{{ route('account.resume-builder.download', $resume->id) }}" 
                           class="btn btn-primary btn-block w-100">
                            <i class="mdi mdi-download"></i> Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hidden inputs for JSON data - MUST be inside the form -->
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
// Initialize with existing data
let workExperiences = @json($resume->data->work_experience ?? []);
let educations = @json($resume->data->education ?? []);
let skills = @json($resume->data->skills ?? []);
let certifications = @json($resume->data->certifications ?? []);
let languages = @json($resume->data->languages ?? []);
let projects = @json($resume->data->projects ?? []);

// Ensure IDs exist
workExperiences = workExperiences.map((exp, index) => ({...exp, id: exp.id || Date.now() + index}));
educations = educations.map((edu, index) => ({...edu, id: edu.id || Date.now() + index}));
certifications = certifications.map((cert, index) => ({...cert, id: cert.id || Date.now() + index + 1000}));
projects = projects.map((proj, index) => ({...proj, id: proj.id || Date.now() + index + 2000}));

// Work Experience Functions
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
    workExperiences = workExperiences.filter(exp => exp.id !== id);
    renderWorkExperiences();
}

function renderWorkExperiences() {
    const container = document.getElementById('workExperienceContainer');
    container.innerHTML = workExperiences.map((exp, index) => `
        <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between mb-2">
                <h6>Experience ${index + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeWorkExperience(${exp.id})">
                    <i class="mdi mdi-delete"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Job Title" 
                           value="${exp.title || ''}" onchange="updateWorkExperience(${exp.id}, 'title', this.value)">
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Company" 
                           value="${exp.company || ''}" onchange="updateWorkExperience(${exp.id}, 'company', this.value)">
                </div>
                <div class="col-md-12 mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Location" 
                           value="${exp.location || ''}" onchange="updateWorkExperience(${exp.id}, 'location', this.value)">
                </div>
                <div class="col-md-5 mb-2">
                    <input type="month" class="form-control form-control-sm" placeholder="Start Date" 
                           value="${exp.start_date || ''}" onchange="updateWorkExperience(${exp.id}, 'start_date', this.value)">
                </div>
                <div class="col-md-5 mb-2">
                    <input type="month" class="form-control form-control-sm" placeholder="End Date" 
                           value="${exp.end_date || ''}" ${exp.current ? 'disabled' : ''} 
                           onchange="updateWorkExperience(${exp.id}, 'end_date', this.value)">
                </div>
                <div class="col-md-2 mb-2">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" ${exp.current ? 'checked' : ''} 
                               onchange="updateWorkExperience(${exp.id}, 'current', this.checked)">
                        <label class="form-check-label" style="font-size: 11px;">Current</label>
                    </div>
                </div>
                <div class="col-md-12 mb-2">
                    <textarea class="form-control form-control-sm" rows="2" placeholder="Description" 
                              onchange="updateWorkExperience(${exp.id}, 'description', this.value)">${exp.description || ''}</textarea>
                </div>
            </div>
        </div>
    `).join('');
    document.getElementById('workExperienceData').value = JSON.stringify(workExperiences);
}

function updateWorkExperience(id, field, value) {
    const exp = workExperiences.find(e => e.id === id);
    if (exp) {
        exp[field] = value;
        if (field === 'current' && value) {
            exp.end_date = '';
        }
        renderWorkExperiences();
    }
}

// Education Functions
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
    educations = educations.filter(edu => edu.id !== id);
    renderEducations();
}

function renderEducations() {
    const container = document.getElementById('educationContainer');
    container.innerHTML = educations.map((edu, index) => `
        <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between mb-2">
                <h6>Education ${index + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeEducation(${edu.id})">
                    <i class="mdi mdi-delete"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Degree" 
                           value="${edu.degree || ''}" onchange="updateEducation(${edu.id}, 'degree', this.value)">
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Institution" 
                           value="${edu.institution || ''}" onchange="updateEducation(${edu.id}, 'institution', this.value)">
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Location" 
                           value="${edu.location || ''}" onchange="updateEducation(${edu.id}, 'location', this.value)">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="month" class="form-control form-control-sm" placeholder="Graduation" 
                           value="${edu.graduation_date || ''}" onchange="updateEducation(${edu.id}, 'graduation_date', this.value)">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="GPA" 
                           value="${edu.gpa || ''}" onchange="updateEducation(${edu.id}, 'gpa', this.value)">
                </div>
            </div>
        </div>
    `).join('');
    document.getElementById('educationData').value = JSON.stringify(educations);
}

function updateEducation(id, field, value) {
    const edu = educations.find(e => e.id === id);
    if (edu) {
        edu[field] = value;
        renderEducations();
    }
}

// Skills Functions
document.getElementById('skillInput').addEventListener('keypress', function(e) {
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

function removeSkill(skill) {
    skills = skills.filter(s => s !== skill);
    renderSkills();
}

function renderSkills() {
    const container = document.getElementById('skillsContainer');
    container.innerHTML = skills.map(skill => `
        <span class="badge bg-primary" style="font-size: 14px; padding: 8px 12px;">
            ${skill}
            <i class="mdi mdi-close" style="cursor: pointer;" onclick="removeSkill('${skill}')"></i>
        </span>
    `).join('');
    document.getElementById('skillsData').value = JSON.stringify(skills);
}

// Certifications Functions
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
        <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between mb-2">
                <h6>Certification ${index + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeCertification(${cert.id})">
                    <i class="mdi mdi-delete"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Certification Name" 
                           value="${cert.name || ''}" onchange="updateCertification(${cert.id}, 'name', this.value)">
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Issuing Organization" 
                           value="${cert.issuer || ''}" onchange="updateCertification(${cert.id}, 'issuer', this.value)">
                </div>
                <div class="col-md-6 mb-2">
                    <input type="month" class="form-control form-control-sm" placeholder="Date Obtained" 
                           value="${cert.date || ''}" onchange="updateCertification(${cert.id}, 'date', this.value)">
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Credential ID (Optional)" 
                           value="${cert.credential_id || ''}" onchange="updateCertification(${cert.id}, 'credential_id', this.value)">
                </div>
            </div>
        </div>
    `).join('');
    document.getElementById('certificationsData').value = JSON.stringify(certifications);
}

function updateCertification(id, field, value) {
    const cert = certifications.find(c => c.id === id);
    if (cert) {
        cert[field] = value;
        renderCertifications();
    }
}

// Languages Functions
document.getElementById('languageInput').addEventListener('keypress', function(e) {
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

function removeLanguage(language) {
    languages = languages.filter(l => l !== language);
    renderLanguages();
}

function renderLanguages() {
    const container = document.getElementById('languagesContainer');
    container.innerHTML = languages.map(language => `
        <span class="badge bg-success" style="font-size: 14px; padding: 8px 12px;">
            ${language}
            <i class="mdi mdi-close" style="cursor: pointer;" onclick="removeLanguage('${language}')"></i>
        </span>
    `).join('');
    document.getElementById('languagesData').value = JSON.stringify(languages);
}

// Projects Functions
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
        <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between mb-2">
                <h6>Project ${index + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeProject(${proj.id})">
                    <i class="mdi mdi-delete"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-12 mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Project Name" 
                           value="${proj.name || ''}" onchange="updateProject(${proj.id}, 'name', this.value)">
                </div>
                <div class="col-md-12 mb-2">
                    <textarea class="form-control form-control-sm" rows="2" placeholder="Description" 
                              onchange="updateProject(${proj.id}, 'description', this.value)">${proj.description || ''}</textarea>
                </div>
                <div class="col-md-6 mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Technologies Used" 
                           value="${proj.technologies || ''}" onchange="updateProject(${proj.id}, 'technologies', this.value)">
                </div>
                <div class="col-md-6 mb-2">
                    <input type="url" class="form-control form-control-sm" placeholder="Project Link (Optional)" 
                           value="${proj.link || ''}" onchange="updateProject(${proj.id}, 'link', this.value)">
                </div>
            </div>
        </div>
    `).join('');
    document.getElementById('projectsData').value = JSON.stringify(projects);
}

function updateProject(id, field, value) {
    const proj = projects.find(p => p.id === id);
    if (proj) {
        proj[field] = value;
        renderProjects();
    }
}

// Photo preview functionality
document.getElementById('photoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPreviewImg').src = e.target.result;
            document.getElementById('photoPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Initialize
renderWorkExperiences();
renderEducations();
renderSkills();
renderCertifications();
renderLanguages();
renderProjects();

// CRITICAL: Update hidden fields before form submission
document.getElementById('resumeForm').addEventListener('submit', function(e) {
    // Update all hidden fields with current data
    document.getElementById('workExperienceData').value = JSON.stringify(workExperiences);
    document.getElementById('educationData').value = JSON.stringify(educations);
    document.getElementById('skillsData').value = JSON.stringify(skills);
    document.getElementById('certificationsData').value = JSON.stringify(certifications);
    document.getElementById('languagesData').value = JSON.stringify(languages);
    document.getElementById('projectsData').value = JSON.stringify(projects);
    
    console.log('=== FORM SUBMISSION DEBUG ===');
    console.log('Work Experiences:', workExperiences);
    console.log('Educations:', educations);
    console.log('Skills:', skills);
    console.log('Certifications:', certifications);
    console.log('Languages:', languages);
    console.log('Projects:', projects);
    console.log('Hidden field values:');
    console.log('workExperienceData:', document.getElementById('workExperienceData').value);
    console.log('educationData:', document.getElementById('educationData').value);
    console.log('skillsData:', document.getElementById('skillsData').value);
    console.log('============================');
    
    // Show alert to confirm data is being captured
    if (workExperiences.length > 0 || educations.length > 0 || skills.length > 0) {
        console.log('✅ DATA FOUND - Form will submit with data!');
    } else {
        console.warn('⚠️ NO DATA - Form will submit empty!');
    }
});
</script>
@endpush
@endsection
