@extends('layouts.employer')

@section('title', 'Upload Document')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <a href="{{ route('employer.documents.index') }}"
                    class="text-decoration-none text-muted small hover-primary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Documents
                </a>
            </div>

            <div class="ep-card">
                <div class="ep-card-header">
                    <h3 class="ep-card-title">
                        <i class="bi bi-cloud-upload"></i>
                        Upload Document
                    </h3>
                </div>
                <div class="ep-card-body">
                    <form action="{{ route('employer.documents.store') }}" method="POST" enctype="multipart/form-data"
                        id="uploadForm">
                        @csrf

                        <!-- Document Type -->
                        <div class="ep-form-group mb-4">
                            <label for="document_type" class="ep-form-label">Document Type <span
                                    class="text-danger">*</span></label>
                            <select class="ep-form-select @error('document_type') is-invalid @enderror" name="document_type"
                                id="document_type" required>
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
                            <div class="form-text text-muted small mt-2" id="documentDescription">
                                <i class="bi bi-info-circle me-1"></i> Select the type of document you want to upload
                            </div>
                        </div>

                        <!-- Document Name -->
                        <div class="ep-form-group mb-4">
                            <label for="document_name" class="ep-form-label">Document Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="ep-form-input @error('document_name') is-invalid @enderror"
                                name="document_name" id="document_name" value="{{ old('document_name') }}"
                                placeholder="e.g. Business Registration 2024" required>
                            @error('document_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted small mt-2">
                                <i class="bi bi-lightbulb me-1"></i> Give your document a descriptive name
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div class="ep-form-group mb-4">
                            <label for="file" class="ep-form-label">Upload File <span class="text-danger">*</span></label>
                            <div class="upload-area @error('file') is-invalid @enderror" id="uploadArea">
                                <input type="file" class="d-none" name="file" id="file" accept=".pdf,.jpg,.jpeg,.png"
                                    required>
                                <div class="upload-content text-center py-5" id="uploadContent">
                                    <div class="mb-3 text-muted opacity-50">
                                        <i class="bi bi-cloud-arrow-up display-4"></i>
                                    </div>
                                    <h6 class="fw-semibold text-secondary mb-2">Click or drag file to upload</h6>
                                    <p class="text-muted small mb-0">PDF, JPG, PNG (Max 2MB)</p>
                                </div>
                                <div class="file-preview-card d-none p-3" id="filePreview">
                                    <div class="d-flex align-items-center justify-content-between bg-light rounded p-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="text-primary">
                                                <i class="bi bi-file-earmark-text fs-4"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium text-dark" id="fileName"></div>
                                                <div class="text-muted small" id="fileSize"></div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                            id="removeFile">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @error('file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Guidelines -->
                        <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis mb-4">
                            <div class="d-flex gap-2">
                                <i class="bi bi-info-circle-fill mt-1"></i>
                                <div>
                                    <h6 class="fw-semibold mb-1">Upload Guidelines</h6>
                                    <ul class="mb-0 ps-3 small">
                                        <li>Ensure text is clear and readable</li>
                                        <li>Accepted formats: PDF, JPG, PNG</li>
                                        <li>Maximum file size: 2MB</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                            <a href="{{ route('employer.documents.index') }}" class="ep-btn ep-btn-outline">
                                Cancel
                            </a>
                            <button type="submit" class="ep-btn ep-btn-primary" id="submitBtn">
                                <i class="bi bi-upload me-2"></i> Upload Document
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .upload-area {
            border: 2px dashed #e2e8f0;
            border-radius: 0.5rem;
            background-color: #f8fafc;
            transition: all 0.2s ease;
            cursor: pointer;
            overflow: hidden;
        }

        .upload-area:hover,
        .upload-area.dragover {
            border-color: var(--ep-primary);
            background-color: #f0f7ff;
        }

        .upload-area.is-invalid {
            border-color: var(--bs-danger);
            background-color: #fff5f5;
        }

        .hover-primary:hover {
            color: var(--ep-primary) !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
            documentTypeSelect.addEventListener('change', function () {
                const selectedType = this.value;
                if (selectedType && documentTypes[selectedType]) {
                    documentDescription.innerHTML = `<i class="bi bi-info-circle me-1"></i> ${documentTypes[selectedType].description}`;
                } else {
                    documentDescription.innerHTML = '<i class="bi bi-info-circle me-1"></i> Select the type of document you want to upload';
                }
            });

            // File upload handling
            uploadArea.addEventListener('click', function (e) {
                if (e.target.closest('#removeFile')) return;
                fileInput.click();
            });

            uploadArea.addEventListener('dragover', function (e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', function (e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', function (e) {
                e.preventDefault();
                this.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileSelection(files[0]);
                }
            });

            fileInput.addEventListener('change', function () {
                if (this.files.length > 0) {
                    handleFileSelection(this.files[0]);
                }
            });

            removeFileBtn.addEventListener('click', function (e) {
                e.stopPropagation(); // Prevent triggering upload area click
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

            // Form submission
            document.getElementById('uploadForm').addEventListener('submit', function (e) {
                if (!fileInput.files.length) {
                    e.preventDefault();
                    alert('Please select a file to upload');
                    return;
                }
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
                submitBtn.disabled = true;
            });
        });
    </script>
@endpush