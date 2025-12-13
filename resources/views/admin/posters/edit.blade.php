@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Edit Hiring Poster</h1>
            <p class="text-muted">Update poster details</p>
        </div>
        <a href="{{ route('admin.posters.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Posters
        </a>
    </div>

    <div class="row">
        <!-- Form Column -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Poster Details</h5>
                </div>
                <div class="card-body">
                    <form id="posterForm">
                        @csrf
                        @method('PUT')

                        <!-- Template Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Template Design</label>
                            <div class="row g-2">
                                @foreach($templates as $t)
                                    <div class="col-4">
                                        <div class="template-option {{ $t->id == $poster->template_id ? 'selected' : '' }}"
                                             data-template-id="{{ $t->id }}">
                                            <div class="template-preview" style="background: linear-gradient(135deg,
                                                @if($t->slug == 'blue-megaphone') #3B5998, #1E3A8A
                                                @elseif($t->slug == 'yellow-attention') #F59E0B, #FBBF24
                                                @else #1F2937, #374151
                                                @endif);">
                                                <i class="bi bi-megaphone text-white"></i>
                                            </div>
                                            <small class="d-block text-center mt-1">{{ $t->name }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="template_id" id="template_id" value="{{ $poster->template_id }}">
                        </div>

                        <!-- Job Title -->
                        <div class="mb-3">
                            <label for="job_title" class="form-label fw-bold">
                                Job Title <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="job_title"
                                   name="job_title"
                                   value="{{ $poster->job_title }}"
                                   required>
                            <div class="invalid-feedback" id="job_title_error"></div>
                        </div>

                        <!-- Requirements -->
                        <div class="mb-3">
                            <label for="requirements" class="form-label fw-bold">
                                Requirements <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control"
                                      id="requirements"
                                      name="requirements"
                                      rows="4"
                                      required>{{ $poster->requirements }}</textarea>
                            <div class="invalid-feedback" id="requirements_error"></div>
                        </div>

                        <!-- Company Name -->
                        <div class="mb-4">
                            <label for="company_name" class="form-label fw-bold">
                                Company Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="company_name"
                                   name="company_name"
                                   value="{{ $poster->company_name }}"
                                   required>
                            <div class="invalid-feedback" id="company_name_error"></div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="bi bi-check-circle me-2"></i>Update Poster
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Column -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-eye me-2"></i>Live Preview</h5>
                    <div>
                        <a href="{{ route('admin.posters.preview', $poster->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Full Preview
                        </a>
                        <a href="{{ route('admin.posters.download', $poster->id) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-download me-1"></i>Download PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div id="posterPreview" class="poster-live-preview">
                        <!-- Preview will be rendered here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.template-option {
    cursor: pointer;
    padding: 8px;
    border: 2px solid transparent;
    border-radius: 8px;
    transition: all 0.2s;
}
.template-option:hover {
    border-color: #dee2e6;
}
.template-option.selected {
    border-color: #0d6efd;
    background: #e7f1ff;
}
.template-preview {
    height: 60px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.poster-live-preview {
    min-height: 500px;
    border: 1px dashed #dee2e6;
    border-radius: 8px;
    overflow: hidden;
}
.preview-blue {
    background: #3B5998;
    padding: 30px;
    min-height: 500px;
    color: white;
}
.preview-blue .header-badge {
    background: #F59E0B;
    color: #000;
    padding: 8px 20px;
    font-weight: bold;
    display: inline-block;
    transform: rotate(-3deg);
}
.preview-blue .hiring-text {
    font-size: 4rem;
    font-weight: 900;
    line-height: 1;
}
.preview-yellow {
    background: #FBBF24;
    padding: 30px;
    min-height: 500px;
}
.preview-yellow .attention-box {
    background: #000;
    color: #fff;
    padding: 20px;
    font-size: 2rem;
    font-weight: 900;
    text-align: center;
}
.preview-modern {
    background: linear-gradient(135deg, #1F2937 0%, #374151 100%);
    padding: 30px;
    min-height: 500px;
    color: white;
}
.preview-modern .badge-box {
    background: #F59E0B;
    color: #000;
    padding: 15px 30px;
    font-weight: bold;
    display: inline-block;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('posterForm');
    const jobTitle = document.getElementById('job_title');
    const requirements = document.getElementById('requirements');
    const companyName = document.getElementById('company_name');
    const templateId = document.getElementById('template_id');
    const previewDiv = document.getElementById('posterPreview');
    const submitBtn = document.getElementById('submitBtn');

    // Template selection
    document.querySelectorAll('.template-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.template-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            templateId.value = this.dataset.templateId;
            updatePreview();
        });
    });

    function updatePreview() {
        const title = jobTitle.value || 'Job Title';
        const reqs = requirements.value || 'Requirements';
        const company = companyName.value || 'Company Name';
        const selectedTemplateId = templateId.value;

        let templateSlug = 'blue-megaphone';
        @foreach($templates as $t)
            if ({{ $t->id }} == selectedTemplateId) {
                templateSlug = '{{ $t->slug }}';
            }
        @endforeach

        let previewHtml = '';

        if (templateSlug === 'blue-megaphone') {
            previewHtml = `
                <div class="preview-blue">
                    <div class="header-badge">WE ARE</div>
                    <div class="hiring-text">HIRING</div>
                    <div class="mt-4">
                        <div class="mb-2"><strong>Title:</strong> ${title}</div>
                        <div class="mt-3 p-2" style="background: #F59E0B; color: #000; display: inline-block;">
                            <strong>REQUIREMENT:</strong>
                        </div>
                        <div class="mt-2" style="white-space: pre-line;">${reqs}</div>
                        <div class="mt-4 pt-3 border-top border-light">
                            <strong>${company}</strong>
                        </div>
                    </div>
                </div>
            `;
        } else if (templateSlug === 'yellow-attention') {
            previewHtml = `
                <div class="preview-yellow">
                    <div class="attention-box mb-4">ATTENTION<br>PLEASE!</div>
                    <div class="bg-white p-3 rounded shadow">
                        <div class="mb-2"><strong>Title:</strong> ${title}</div>
                        <div class="mt-3 p-2" style="background: #F59E0B; display: inline-block;">
                            <strong>REQUIREMENT:</strong>
                        </div>
                        <div class="mt-2" style="white-space: pre-line;">${reqs}</div>
                        <div class="mt-4 pt-3 border-top">
                            <strong>${company}</strong>
                        </div>
                    </div>
                </div>
            `;
        } else {
            previewHtml = `
                <div class="preview-modern">
                    <div class="text-center mb-4">
                        <div class="badge-box">WE'RE</div>
                        <div class="badge-box mt-1" style="background: #374151; color: #fff;">HIRING</div>
                    </div>
                    <p class="text-center small fst-italic mb-4">We are expanding our workforce and want you to be a part of our success story</p>
                    <div class="row">
                        <div class="col-6">
                            <strong>Title:</strong><br>${title}
                        </div>
                        <div class="col-6">
                            <div class="p-2" style="background: #F59E0B; color: #000; display: inline-block;">
                                <strong>REQUIREMENT:</strong>
                            </div>
                            <div class="mt-2 small" style="white-space: pre-line;">${reqs}</div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top border-secondary text-center">
                        <strong>${company}</strong>
                    </div>
                </div>
            `;
        }

        previewDiv.innerHTML = previewHtml;
    }

    jobTitle.addEventListener('input', updatePreview);
    requirements.addEventListener('input', updatePreview);
    companyName.addEventListener('input', updatePreview);
    updatePreview();

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

        const formData = new FormData(form);

        fetch('{{ route("admin.posters.update", $poster->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                window.location.href = data.redirect;
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const input = document.getElementById(field);
                        const errorDiv = document.getElementById(field + '_error');
                        if (input) input.classList.add('is-invalid');
                        if (errorDiv) errorDiv.textContent = data.errors[field][0];
                    });
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Update Poster';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Update Poster';
        });
    });
});
</script>
@endpush
@endsection
