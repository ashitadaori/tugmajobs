@extends('layouts.employer')

@section('title', 'Edit Document')

@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Edit Document</h4>
                    <p class="text-muted mb-0">Resubmit your document for verification</p>
                </div>
                <a href="{{ route('employer.documents.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Documents
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <form action="{{ route('employer.documents.update', $document) }}" method="POST" enctype="multipart/form-data" id="editForm">
                                @csrf
                                @method('PUT')

                                <!-- Document Type -->
                                <div class="mb-4">
                                    <label for="document_type" class="form-label fw-medium">Document Type</label>
                                    <input type="text" class="form-control-plaintext fw-medium" value="{{ $document->document_type_config['label'] ?? ucfirst(str_replace('_', ' ', $document->document_type)) }}" readonly>
                                    <div class="text-muted small">This is the type of document you are submitting</div>
                                </div>

                                <!-- Document Name -->
                                <div class="mb-4">
                                    <label for="document_name" class="form-label fw-medium">Document Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('document_name') is-invalid @enderror" 
                                           name="document_name" 
                                           id="document_name" 
                                           value="{{ old('document_name', $document->document_name) }}" 
                                           placeholder="Enter a descriptive name for your document"
                                           required>
                                    @error('document_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Example: "ABC Corp Business Registration Certificate 2024"
                                    </div>
                                </div>

                                <!-- Current File Info -->
                                <div class="mb-4">
                                    <label class="form-label fw-medium">Current File</label>
                                    <div class="alert alert-light border">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-alt fa-2x text-primary me-3"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-medium">{{ $document->file_name }}</div>
                                                <small class="text-muted">{{ $document->formatted_file_size }}</small>
                                            </div>
                                            <a href="{{ route('employer.documents.download', $document) }}" class="btn btn-sm btn-outline-success ms-3">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- New File Upload -->
                                <div class="mb-4">
                                    <label for="file" class="form-label fw-medium">Upload New File</label>
                                    <div class="upload-area @error('file') is-invalid @enderror" id="uploadArea">
                                        <input type="file" 
                                               class="form-control d-none" 
                                               name="file" 
                                               id="file" 
                                               accept=".pdf,.jpg,.jpeg,.png">
                                        <div class="upload-content text-center py-4" id="uploadContent">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                            <h6 class="mb-2">Click to upload or drag and drop</h6>
                                            <p class="text-muted mb-2">PDF, JPG, JPEG, PNG files only</p>
                                            <p class="text-muted small">Maximum file size: 2MB</p>
                                        </div>
                                        <div class="file-preview d-none" id="filePreview">
                                            <div class="d-flex align-items-center p-3">
                                                <i class="fas fa-file-alt fa-2x text-primary me-3"></i>
                                                <div class="flex-grow-1">
                                                    <div class="fw-medium" id="fileName"></div>
                                                    <small class="text-muted" id="fileSize"></small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger" id="removeFile">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @error('file')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Form Actions -->
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('employer.documents.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-paper-plane me-2"></i>Resubmit Document
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}

.upload-area.dragover {
    border-color: #0d6efd;
    background-color: #e7f3ff;
}

.upload-area.is-invalid {
    border-color: #dc3545;
}

.file-preview {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background-color: #f8f9fa;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('file');
    const uploadContent = document.getElementById('uploadContent');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFileBtn = document.getElementById('removeFile');
    const submitBtn = document.getElementById('submitBtn');

    // File upload handling
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelection(files[0]);
        }
    });

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFileSelection(this.files[0]);
        }
    });

    removeFileBtn.addEventListener('click', function() {
        fileInput.value = '';
        showUploadArea();
    });

    function handleFileSelection(file) {
        // Validate file type
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid file type (PDF, JPG, JPEG, PNG)');
            return;
        }

        // Validate file size (2MB)
        const maxSize = 2 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File size must be less than 2MB');
            return;
        }

        // Show file preview
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        showFilePreview();
    }

    function showUploadArea() {
        uploadContent.classList.remove('d-none');
        filePreview.classList.add('d-none');
    }

    function showFilePreview() {
        uploadContent.classList.add('d-none');
        filePreview.classList.remove('d-none');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Form submission handling
    document.getElementById('editForm').addEventListener('submit', function() {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Resubmitting...';
        submitBtn.disabled = true;
    });
});
</script>
@endpush

