@extends('layouts.jobseeker')

@section('page-title', 'Resume Builder')

@section('jobseeker-content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 fw-bold text-dark">Resume Builder</h4>
                            <p class="text-muted mb-0">Upload and manage your resume files</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadResumeModal">
                            <i class="fas fa-plus me-2"></i>Upload Resume
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Current Resume Display -->
                    @if(Auth::user()->jobSeekerProfile && Auth::user()->jobSeekerProfile->resume_file)
                        <div class="resume-item p-4 border rounded-3 bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-1 text-center">
                                    <i class="fas fa-file-pdf text-danger fa-2x"></i>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-1 fw-semibold">{{ basename(Auth::user()->jobSeekerProfile->resume_file) }}</h6>
                                    <small class="text-muted">Uploaded: {{ Auth::user()->jobSeekerProfile->updated_at->format('M d, Y') }}</small>
                                </div>
                                <div class="col-md-3 text-center">
                                    <span class="badge bg-success">Active Resume</span>
                                </div>
                                <div class="col-md-2 text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ asset('storage/' . Auth::user()->jobSeekerProfile->resume_file) }}" 
                                           class="btn btn-outline-primary btn-sm" 
                                           target="_blank" 
                                           title="View Resume">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ asset('storage/' . Auth::user()->jobSeekerProfile->resume_file) }}" 
                                           class="btn btn-outline-success btn-sm" 
                                           download 
                                           title="Download Resume">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="empty-state text-center py-5">
                            <i class="fas fa-file-upload fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted mb-2">No Resume Uploaded</h5>
                            <p class="text-muted mb-4">Upload your resume to start applying for jobs</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadResumeModal">
                                <i class="fas fa-plus me-2"></i>Upload Your First Resume
                            </button>
                        </div>
                    @endif
                    
                    <!-- Resume Tips -->
                    <div class="mt-4">
                        <div class="alert alert-info border-0">
                            <h6 class="alert-heading mb-2"><i class="fas fa-lightbulb me-2"></i>Resume Tips</h6>
                            <ul class="mb-0 ps-3">
                                <li>Keep your resume to 1-2 pages maximum</li>
                                <li>Use a clear, professional format</li>
                                <li>Include relevant keywords from job descriptions</li>
                                <li>Supported formats: PDF, DOC, DOCX (Max 5MB)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Resume Modal -->
<div class="modal fade" id="uploadResumeModal" tabindex="-1" aria-labelledby="uploadResumeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadResumeModalLabel">
                    <i class="fas fa-upload me-2"></i>Upload Resume
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('account.uploadResume') }}" method="POST" enctype="multipart/form-data" id="uploadResumeForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="resume" class="form-label fw-semibold">Choose Resume File</label>
                        <input type="file" 
                               class="form-control @error('resume') is-invalid @enderror" 
                               id="resume" 
                               name="resume" 
                               accept=".pdf,.doc,.docx" 
                               required>
                        <div class="form-text">
                            Supported formats: PDF, DOC, DOCX. Maximum file size: 5MB.
                        </div>
                        @error('resume')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="upload-preview d-none" id="uploadPreview">
                        <div class="alert alert-light border">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-alt fa-2x text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-1 fw-semibold" id="fileName"></h6>
                                    <small class="text-muted" id="fileSize"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i>Upload Resume
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resumeInput = document.getElementById('resume');
    const uploadPreview = document.getElementById('uploadPreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    
    resumeInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            uploadPreview.classList.remove('d-none');
        } else {
            uploadPreview.classList.add('d-none');
        }
    });
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Show modal if there are validation errors
    @if($errors->has('resume'))
        var uploadModal = new bootstrap.Modal(document.getElementById('uploadResumeModal'));
        uploadModal.show();
    @endif
});
</script>
@endsection
