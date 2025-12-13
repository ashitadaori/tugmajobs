@extends('layouts.employer')

@section('title', 'Upload Document')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Upload Document</h4>
                    <p class="text-muted mb-0">Upload business documents for verification</p>
                </div>
                <a href="{{ route('employer.documents.index') }}" class="btn btn-enhanced btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>Back to Documents
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="upload-form-card">
                        <div class="card-body">
                            <form action="{{ route('employer.documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                                @csrf

                                <!-- Document Type -->
                                <div class="enhanced-form-group">
                                    <label for="document_type" class="enhanced-form-label">
                                        Document Type <span class="required-asterisk">*</span>
                                    </label>
                                    <select class="enhanced-form-control @error('document_type') is-invalid @enderror" 
                                            name="document_type" id="document_type" required>
                                        <option value="">Select document type</option>
                                        @foreach($documentTypes as $type => $config)
                                            <option value="{{ $type }}" {{ old('document_type') == $type ? 'selected' : '' }}>
                                                {{ $config['label'] }}
                                                @if($config['required']) (Required) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('document_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help-text" id="documentDescription">
                                        <i class="fas fa-info-circle"></i>
                                        Select the type of document you want to upload
                                    </div>
                                </div>

                                <!-- Document Name -->
                                <div class="enhanced-form-group">
                                    <label for="document_name" class="enhanced-form-label">
                                        Document Name <span class="required-asterisk">*</span>
                                    </label>
                                    <input type="text" 
                                           class="enhanced-form-control @error('document_name') is-invalid @enderror" 
                                           name="document_name" 
                                           id="document_name" 
                                           value="{{ old('document_name') }}" 
                                           placeholder="Enter a descriptive name for your document"
                                           required>
                                    @error('document_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help-text">
                                        <i class="fas fa-lightbulb"></i>
                                        Example: "ABC Corp Business Registration Certificate 2024"
                                    </div>
                                </div>

                                <!-- File Upload -->
                                <div class="enhanced-form-group">
                                    <label for="file" class="enhanced-form-label">
                                        Upload File <span class="required-asterisk">*</span>
                                    </label>
                                    <div class="enhanced-upload-area @error('file') is-invalid @enderror" id="uploadArea">
                                        <input type="file" 
                                               class="form-control d-none" 
                                               name="file" 
                                               id="file" 
                                               accept=".pdf,.jpg,.jpeg,.png"
                                               required>
                                        <div class="upload-content" id="uploadContent">
                                            <div class="upload-icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <div class="upload-title">Click to upload or drag and drop</div>
                                            <div class="upload-description">PDF, JPG, JPEG, PNG files only</div>
                                            <div class="upload-specs">Maximum file size: 2MB</div>
                                        </div>
                                        <div class="file-preview-card d-none" id="filePreview">
                                            <div class="file-preview-content">
                                                <div class="file-preview-icon">
                                                    <i class="fas fa-file-alt"></i>
                                                </div>
                                                <div class="file-preview-info">
                                                    <div class="file-preview-name" id="fileName"></div>
                                                    <div class="file-preview-size" id="fileSize"></div>
                                                </div>
                                                <button type="button" class="file-remove-btn" id="removeFile">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @error('file')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Upload Guidelines -->
                                <div class="enhanced-alert alert-info">
                                    <div class="alert-heading">
                                        <i class="fas fa-info-circle"></i>Upload Guidelines
                                    </div>
                                    <ul class="mb-0">
                                        <li>Ensure documents are clear and all text is readable</li>
                                        <li>Upload official documents issued by relevant authorities</li>
                                        <li>Documents should be current and not expired (where applicable)</li>
                                        <li>Scanned copies or high-quality photos are acceptable</li>
                                        <li>File formats: PDF (preferred), JPG, JPEG, PNG</li>
                                        <li>Maximum file size: 2MB per document</li>
                                    </ul>
                                </div>

                                <!-- Required Documents Info -->
                                <div class="required-docs-card">
                                    <div class="card-body">
                                        <div class="required-docs-title">
                                            <i class="fas fa-list-check"></i>Required Documents
                                        </div>
                                        <div class="row">
                                            @foreach($documentTypes as $type => $config)
                                                @if($config['required'])
                                                    <div class="col-md-6">
                                                        <div class="required-doc-item">
                                                            <div class="required-doc-icon">
                                                                <i class="fas fa-check"></i>
                                                            </div>
                                                            <div class="required-doc-info">
                                                                <div class="required-doc-name">{{ $config['label'] }}</div>
                                                                <div class="required-doc-description">{{ $config['description'] }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('employer.documents.index') }}" class="btn-enhanced btn-outline-secondary">
                                        <i class="fas fa-times"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn-enhanced btn-primary" id="submitBtn">
                                        <i class="fas fa-upload"></i>Upload Document
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
    const documentTypes = @json($documentTypes);
    const documentTypeSelect = document.getElementById('document_type');
    const documentDescription = document.getElementById('documentDescription');
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('file');
    const uploadContent = document.getElementById('uploadContent');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFileBtn = document.getElementById('removeFile');
    const submitBtn = document.getElementById('submitBtn');

    // Update description when document type changes
    documentTypeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        if (selectedType && documentTypes[selectedType]) {
            documentDescription.textContent = documentTypes[selectedType].description;
        } else {
            documentDescription.textContent = 'Select the type of document you want to upload';
        }
    });

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

    // Form submission handling with AJAX to stay on the page
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();

        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
        submitBtn.disabled = true;

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Upload failed');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success message
                showSuccessMessage(data.message);
                // Reset form
                document.getElementById('uploadForm').reset();
                showUploadArea();
                // Optionally redirect after short delay to see the uploaded document
                setTimeout(() => {
                    window.location.href = '{{ route("employer.documents.index") }}';
                }, 1500);
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            showErrorMessage(error.message || 'Failed to upload document. Please try again.');
        })
        .finally(() => {
            submitBtn.innerHTML = '<i class="fas fa-upload"></i>Upload Document';
            submitBtn.disabled = false;
        });
    });

    function showSuccessMessage(message) {
        // Remove existing alerts
        document.querySelectorAll('.upload-alert').forEach(el => el.remove());

        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show upload-alert" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Success!</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHtml);
    }

    function showErrorMessage(message) {
        // Remove existing alerts
        document.querySelectorAll('.upload-alert').forEach(el => el.remove());

        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show upload-alert" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error!</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHtml);
    }
});
</script>
@endpush
