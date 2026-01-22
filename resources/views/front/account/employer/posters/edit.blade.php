@extends('layouts.employer')

@section('page_title', 'Edit Poster')

@section('content')
<div class="edit-poster-page">
    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <a href="{{ route('employer.posters.index') }}" class="back-link">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title">Edit Poster</h1>
                <p class="page-description">Update your hiring poster details</p>
            </div>
        </div>
        <div class="header-actions">
            <a href="{{ route('employer.posters.download', $poster->id) }}" class="btn-download">
                <i class="bi bi-download me-2"></i>Download PDF
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form Column -->
        <div class="col-lg-5">
            <div class="form-card">
                <div class="card-header">
                    <h5><i class="bi bi-pencil-square me-2"></i>Poster Details</h5>
                </div>
                <div class="card-body">
                    <form id="posterForm">
                        @csrf
                        @method('PUT')

                        <!-- Link to Job (Optional) -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-link-45deg me-1"></i>Link to Job (Optional)
                            </label>
                            <select name="job_id" id="job_id" class="form-select">
                                <option value="">-- Standalone poster --</option>
                                @foreach($jobs as $job)
                                    <option value="{{ $job->id }}"
                                            data-title="{{ $job->title }}"
                                            data-requirements="{{ $job->description }}"
                                            {{ $poster->job_id == $job->id ? 'selected' : '' }}>
                                        {{ $job->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Template Selection -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-palette me-1"></i>Template Design
                            </label>
                            <div class="template-grid">
                                @foreach($templates as $t)
                                    <div class="template-option {{ $t->id == $poster->template_id ? 'selected' : '' }}"
                                         data-template-id="{{ $t->id }}"
                                         data-template-slug="{{ $t->slug }}">
                                        <div class="template-preview template-{{ $t->slug }}">
                                            <i class="bi bi-megaphone"></i>
                                        </div>
                                        <span class="template-name">{{ $t->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="template_id" id="template_id" value="{{ $poster->template_id }}">
                        </div>

                        <!-- Job Title -->
                        <div class="form-group">
                            <label for="job_title" class="form-label">
                                Job Title <span class="required">*</span>
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
                        <div class="form-group">
                            <label for="requirements" class="form-label">
                                Requirements <span class="required">*</span>
                            </label>
                            <textarea class="form-control"
                                      id="requirements"
                                      name="requirements"
                                      rows="4"
                                      required>{{ $poster->requirements }}</textarea>
                            <div class="invalid-feedback" id="requirements_error"></div>
                        </div>

                        <!-- Company Name -->
                        <div class="form-group">
                            <label for="company_name" class="form-label">
                                Company Name <span class="required">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="company_name"
                                   name="company_name"
                                   value="{{ $poster->company_name }}"
                                   required>
                            <div class="invalid-feedback" id="company_name_error"></div>
                        </div>

                        <!-- Customization (Collapsible) -->
                        <div class="form-group mb-4">
                            <button type="button" class="btn btn-link p-0 text-decoration-none d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#customizationFields">
                                <i class="bi bi-palette me-2"></i>Customization (Optional)
                                <i class="bi bi-chevron-down ms-auto"></i>
                            </button>
                            <div class="collapse {{ $poster->primary_color || $poster->secondary_color ? 'show' : '' }}" id="customizationFields">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="primary_color" class="form-label">Primary Color</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" 
                                                value="{{ old('primary_color', $poster->primary_color ?? '#0d6efd') }}" title="Choose your color">
                                            <input type="text" class="form-control" id="primary_color_text" placeholder="#0d6efd"
                                                value="{{ old('primary_color', $poster->primary_color ?? '#0d6efd') }}">
                                        </div>
                                        <small class="form-hint">Main background or accent color</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="secondary_color" class="form-label">Secondary Color</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" id="secondary_color" name="secondary_color" 
                                                value="{{ old('secondary_color', $poster->secondary_color ?? '#ffffff') }}" title="Choose your color">
                                            <input type="text" class="form-control" id="secondary_color_text" placeholder="#ffffff"
                                                value="{{ old('secondary_color', $poster->secondary_color ?? '#ffffff') }}">
                                        </div>
                                        <small class="form-hint">Text or contrasting accent color</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Details -->
                        <div class="additional-details">
                            <button type="button" class="toggle-additional" data-bs-toggle="collapse" data-bs-target="#additionalFields">
                                <i class="bi bi-plus-circle me-2"></i>Additional Details
                                <i class="bi bi-chevron-down ms-auto"></i>
                            </button>

                            <div class="collapse {{ $poster->contact_email || $poster->location || $poster->salary_range ? 'show' : '' }}" id="additionalFields">
                                <div class="additional-fields-content">
                                    <div class="form-group">
                                        <label for="contact_email" class="form-label">Contact Email</label>
                                        <input type="email"
                                               class="form-control"
                                               id="contact_email"
                                               name="contact_email"
                                               value="{{ $poster->contact_email }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="contact_phone" class="form-label">Contact Phone</label>
                                        <input type="text"
                                               class="form-control"
                                               id="contact_phone"
                                               name="contact_phone"
                                               value="{{ $poster->contact_phone }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="location" class="form-label">Location</label>
                                        <input type="text"
                                               class="form-control"
                                               id="location"
                                               name="location"
                                               value="{{ $poster->location }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="salary_range" class="form-label">Salary Range</label>
                                        <input type="text"
                                               class="form-control"
                                               id="salary_range"
                                               name="salary_range"
                                               value="{{ $poster->salary_range }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="employment_type" class="form-label">Employment Type</label>
                                        <select name="employment_type" id="employment_type" class="form-select">
                                            <option value="">-- Select type --</option>
                                            <option value="Full-time" {{ $poster->employment_type == 'Full-time' ? 'selected' : '' }}>Full-time</option>
                                            <option value="Part-time" {{ $poster->employment_type == 'Part-time' ? 'selected' : '' }}>Part-time</option>
                                            <option value="Contract" {{ $poster->employment_type == 'Contract' ? 'selected' : '' }}>Contract</option>
                                            <option value="Freelance" {{ $poster->employment_type == 'Freelance' ? 'selected' : '' }}>Freelance</option>
                                            <option value="Internship" {{ $poster->employment_type == 'Internship' ? 'selected' : '' }}>Internship</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="deadline" class="form-label">Application Deadline</label>
                                        <input type="date"
                                               class="form-control"
                                               id="deadline"
                                               name="deadline"
                                               value="{{ $poster->deadline?->format('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-submit" id="submitBtn">
                                <i class="bi bi-check-circle me-2"></i>Update Poster
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Column -->
        <div class="col-lg-7">
            <div class="preview-card">
                <div class="card-header">
                    <h5><i class="bi bi-eye me-2"></i>Live Preview</h5>
                </div>
                <div class="card-body">
                    <div id="posterPreview" class="poster-preview-container"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.edit-poster-page {
    padding: 0;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.back-link {
    width: 40px;
    height: 40px;
    background: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    text-decoration: none;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.back-link:hover {
    background: #f3f4f6;
    color: #1f2937;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.page-description {
    color: #6b7280;
    margin: 0;
    font-size: 0.9rem;
}

.btn-download {
    display: inline-flex;
    align-items: center;
    padding: 0.625rem 1.25rem;
    background: #10b981;
    color: white;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-download:hover {
    background: #059669;
    color: white;
}

/* Cards */
.form-card, .preview-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    border: 1px solid #f3f4f6;
    overflow: hidden;
}

.card-header {
    padding: 1.25rem;
    border-bottom: 1px solid #f3f4f6;
}

.card-header h5 {
    margin: 0;
    font-weight: 600;
    color: #1f2937;
}

.card-body {
    padding: 1.5rem;
}

/* Form Styles */
.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.required {
    color: #ef4444;
}

.form-control, .form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.form-control:focus, .form-select:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    outline: none;
}

/* Template Selection */
.template-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

.template-option {
    cursor: pointer;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    text-align: center;
    transition: all 0.2s;
}

.template-option:hover {
    border-color: #c7d2fe;
    background: #f5f3ff;
}

.template-option.selected {
    border-color: #6366f1;
    background: #eef2ff;
}

.template-preview {
    height: 50px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
}

.template-blue-megaphone {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

.template-yellow-attention {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
}

.template-modern-corporate {
    background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
}

.template-minimalist-green {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}
.template-minimalist-clean {
    background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
    color: #374151;
}

.template-bold-red {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.template-gradient-purple {
    background: linear-gradient(135deg, #667eea, #764ba2);
}
.template-tech-dark {
    background: linear-gradient(135deg, #18181b, #27272a);
}
.template-elegant-navy {
    background: linear-gradient(135deg, #0f172a, #1e293b);
}
.template-fresh-teal {
    background: linear-gradient(135deg, #14b8a6, #0d9488);
}
.template-sunset-orange {
    background: linear-gradient(135deg, #f97316, #ea580c);
}

.template-name {
    font-size: 0.7rem;
    color: #6b7280;
    font-weight: 500;
}

/* Additional Details */
.additional-details {
    margin-top: 1.5rem;
    border-top: 1px solid #f3f4f6;
    padding-top: 1rem;
}

.toggle-additional {
    width: 100%;
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.toggle-additional:hover {
    background: #f3f4f6;
    color: #374151;
}

.additional-fields-content {
    padding-top: 1rem;
}

/* Form Actions */
.form-actions {
    margin-top: 1.5rem;
}

.btn-submit {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

/* Preview Container */
.poster-preview-container {
    min-height: 500px;
    border: 2px dashed #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
}

/* Live Preview Styles */
/* Live Preview Styles */
.preview-blue {
    background: #2563eb;
    padding: 25px;
    min-height: 500px;
    color: white;
}
.preview-yellow {
    background: #fbbf24;
    padding: 25px;
    min-height: 500px;
}
.preview-modern {
    background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
    padding: 25px;
    min-height: 500px;
    color: white;
}
.preview-minimalist-green {
    background: #ffffff;
    border: 3px solid #10b981;
    padding: 25px;
    min-height: 500px;
}
.preview-purple {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 25px;
    min-height: 500px;
    color: white;
}
.preview-tech {
    background: #18181b;
    padding: 25px;
    min-height: 500px;
    color: white;
    font-family: monospace;
}
.preview-red {
    background: #dc2626;
    padding: 25px;
    min-height: 500px;
    color: white;
}
.preview-navy {
    background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
    padding: 25px;
    min-height: 500px;
    color: white;
}
.preview-teal {
    background: #f0fdfa;
    padding: 25px;
    min-height: 500px;
    color: #134e4a;
}
.preview-orange {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 50%, #dc2626 100%);
    padding: 25px;
    min-height: 500px;
    color: white;
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

    const location = document.getElementById('location');
    const salaryRange = document.getElementById('salary_range');
    
    const primaryColorInput = document.getElementById('primary_color');
    const secondaryColorInput = document.getElementById('secondary_color');
    const primaryColorText = document.getElementById('primary_color_text');
    const secondaryColorText = document.getElementById('secondary_color_text');

    // Template selection
    document.querySelectorAll('.template-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.template-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            templateId.value = this.dataset.templateId;
            updatePreview();
        });
    });

    // Sync color inputs
    primaryColorInput.addEventListener('input', function() {
        primaryColorText.value = this.value;
        updatePreview();
    });
    primaryColorText.addEventListener('input', function() {
        primaryColorInput.value = this.value;
        updatePreview();
    });
    secondaryColorInput.addEventListener('input', function() {
        secondaryColorText.value = this.value;
        updatePreview();
    });
    secondaryColorText.addEventListener('input', function() {
        secondaryColorInput.value = this.value;
        updatePreview();
    });

    function getTemplateSlug(id) {
        const option = document.querySelector(`.template-option[data-template-id="${id}"]`);
        return option ? option.dataset.templateSlug : 'blue-megaphone';
    }

    function updatePreview() {
        const title = jobTitle.value || 'Job Title';
        const reqs = requirements.value || 'Requirements';
        const company = companyName.value || 'Company Name';
        const loc = location ? location.value : '';
        const salary = salaryRange ? salaryRange.value : '';
        const templateSlug = getTemplateSlug(templateId.value);
        
        // Colors
        const pColor = primaryColorInput.value;
        const sColor = secondaryColorInput.value;
        const useCustom = pColor !== '#0d6efd' || sColor !== '#ffffff';

        let previewHtml = '';

        if (templateSlug === 'blue-megaphone') {
            const bg = useCustom ? pColor : '#2563eb';
            const accent = useCustom ? sColor : '#fbbf24';
            previewHtml = `
                <div class="preview-blue" style="background: ${bg};">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="background: ${accent}; color: #000; padding: 8px 25px; font-weight: bold; display: inline-block; transform: rotate(-2deg);">WE ARE</div>
                        <div style="font-size: 3rem; font-weight: 900; letter-spacing: -1px; margin-top: 5px;">HIRING</div>
                    </div>
                    <div style="text-align: right; margin: 20px 0;">
                        <div style="display: inline-block; vertical-align: middle; margin-right: 10px;">
                            <div style="width: 30px; height: 3px; background: ${accent}; margin: 5px 0 5px auto;"></div>
                            <div style="width: 45px; height: 3px; background: ${accent}; margin: 5px 0 5px auto;"></div>
                            <div style="width: 30px; height: 3px; background: ${accent}; margin: 5px 0 5px auto;"></div>
                        </div>
                    </div>
                    <div style="margin: 20px 0;">
                        <p style="font-weight: bold; margin: 0 0 8px;">Title:</p>
                        <p style="font-size: 1.3rem; font-weight: bold; margin: 0;">${title}</p>
                    </div>
                    <div style="margin: 20px 0;">
                        <div style="background: ${accent}; color: #000; padding: 5px 12px; display: inline-block; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; margin-bottom: 10px;">Requirement:</div>
                        <div style="font-size: 0.85rem; line-height: 1.8; white-space: pre-line;">${reqs}</div>
                    </div>
                    <div style="margin-top: 25px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: bold;">${company}</span>
                        <span style="background: ${accent}; color: #000; padding: 8px 20px; font-size: 0.75rem; font-weight: bold; border-radius: 20px;">Apply Now</span>
                    </div>
                </div>
            `;
        } else if (templateSlug === 'yellow-attention') {
            const bg = useCustom ? pColor : '#fbbf24';
            const accent = useCustom ? sColor : '#f59e0b';
            previewHtml = `
                <div class="preview-yellow" style="background: ${bg};">
                    <div style="background: #000; color: #fff; padding: 20px; text-align: center; font-size: 1.8rem; font-weight: 900; margin-bottom: 20px;">ATTENTION<br>PLEASE!</div>
                    <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <div style="margin-bottom: 15px;">
                            <p style="font-weight: bold; margin: 0 0 5px; color: #333;">Title:</p>
                            <p style="font-size: 1.2rem; font-weight: bold; margin: 0; color: #1f2937;">${title}</p>
                        </div>
                        <div style="margin: 15px 0;">
                            <div style="background: ${accent}; padding: 5px 12px; display: inline-block; font-size: 0.75rem; font-weight: bold; margin-bottom: 10px;">Requirements:</div>
                            <div style="font-size: 0.85rem; line-height: 1.8; white-space: pre-line; color: #374151;">${reqs}</div>
                        </div>
                        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: bold; color: #1f2937;">${company}</span>
                            <span style="background: ${accent}; padding: 8px 20px; font-size: 0.75rem; font-weight: bold; border-radius: 20px;">Apply Now</span>
                        </div>
                    </div>
                </div>
            `;
        } else if (templateSlug === 'modern-corporate') {
            const bg = useCustom ? `linear-gradient(135deg, ${pColor} 0%, ${sColor} 100%)` : 'linear-gradient(135deg, #1f2937 0%, #374151 100%)';
            previewHtml = `
                <div class="preview-modern" style="background: ${bg};">
                    <div style="text-align: center; margin-bottom: 25px;">
                        <div style="display: inline-block; background: #fbbf24; color: #000; padding: 10px 25px; font-weight: bold; margin-bottom: 8px;">WE'RE</div><br>
                        <div style="display: inline-block; background: #4b5563; padding: 10px 25px; font-weight: bold;">HIRING</div>
                    </div>
                    <p style="text-align: center; font-size: 0.85rem; opacity: 0.8; margin-bottom: 25px; font-style: italic;">Join our team and be part of something amazing</p>
                    <div style="display: flex; gap: 20px;">
                        <div style="flex: 1;">
                            <p style="font-weight: bold; margin: 0 0 5px; font-size: 0.9rem;">Position:</p>
                            <p style="font-size: 1.1rem; font-weight: bold; margin: 0;">${title}</p>
                        </div>
                        <div style="flex: 1;">
                            <div style="background: #fbbf24; color: #000; padding: 5px 12px; display: inline-block; font-size: 0.7rem; font-weight: bold; margin-bottom: 8px;">Requirements:</div>
                            <div style="font-size: 0.8rem; line-height: 1.7; white-space: pre-line;">${reqs}</div>
                        </div>
                    </div>
                    <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.2); display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="background: #374151; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9rem;">${company.charAt(0)}</div>
                            <span style="font-weight: bold;">${company}</span>
                        </div>
                        <span style="background: #fbbf24; color: #000; padding: 8px 20px; font-size: 0.75rem; font-weight: bold; border-radius: 20px;">Apply Now</span>
                    </div>
                </div>
            `;
        } else if (templateSlug === 'gradient-purple') {
            const bg = useCustom ? `linear-gradient(135deg, ${pColor} 0%, ${sColor} 100%)` : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            previewHtml = `
                <div class="preview-purple" style="background: ${bg}; position: relative; overflow: hidden;">
                    <div style="position: absolute; width: 100px; height: 100px; border-radius: 50%; background: rgba(255,255,255,0.1); top: -30px; right: -30px;"></div>
                    <div style="text-align: center; margin-bottom: 15px;">
                        <div style="background: rgba(255,255,255,0.2); color: #fff; padding: 6px 20px; font-size: 0.7rem; font-weight: 600; border-radius: 20px; display: inline-block; letter-spacing: 1px; margin-bottom: 10px;">JOIN OUR TEAM</div>
                        <div style="font-size: 2.5rem; font-weight: 900; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">HIRING</div>
                        <div style="font-size: 1rem; font-weight: 300; letter-spacing: 5px;">NOW</div>
                    </div>
                    <div style="background: rgba(255,255,255,0.95); border-radius: 12px; padding: 18px; margin: 15px 0;">
                        <p style="font-size: 0.65rem; font-weight: 600; color: #764ba2; margin: 0 0 3px; text-transform: uppercase; letter-spacing: 1px;">Position</p>
                        <p style="font-size: 1.1rem; font-weight: 700; color: #1f2937; margin: 0 0 12px;">${title}</p>
                        <div style="background: ${bg}; color: #fff; padding: 4px 10px; font-size: 0.6rem; font-weight: 600; border-radius: 10px; display: inline-block; margin-bottom: 8px;">Requirements</div>
                        <div style="font-size: 0.75rem; color: #4b5563; line-height: 1.7; white-space: pre-line;">${reqs}</div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <p style="font-weight: 700; margin: 0; text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">${company}</p>
                            ${loc ? `<p style="font-size: 0.65rem; opacity: 0.8; margin: 2px 0 0;">${loc}</p>` : ''}
                        </div>
                        <div style="background: #fff; color: #764ba2; padding: 10px 18px; font-size: 0.7rem; font-weight: 700; border-radius: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">Apply Now</div>
                    </div>
                </div>
            `;
        } else if (templateSlug === 'minimalist-green') {
            const accent = useCustom ? pColor : '#10b981';
            const accent2 = useCustom ? sColor : '#059669';
            previewHtml = `
                <div class="preview-minimalist-green" style="border-color: ${accent};">
                    <div style="height: 6px; background: linear-gradient(90deg, ${accent}, ${accent2}); margin: -25px -25px 20px; border-radius: 0;"></div>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                        <div style="width: 45px; height: 45px; background: ${accent}; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem; font-weight: 700;">${company.charAt(0).toUpperCase()}</div>
                        <div>
                            <p style="font-size: 0.95rem; font-weight: 700; color: #1f2937; margin: 0;">${company}</p>
                            ${loc ? `<p style="font-size: 0.7rem; color: #6b7280; margin: 2px 0 0;">${loc}</p>` : ''}
                        </div>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <span style="background: ${accent}; color: white; padding: 4px 12px; font-size: 0.65rem; font-weight: 600; border-radius: 15px; text-transform: uppercase; letter-spacing: 1px;">We're Hiring</span>
                    </div>
                    <h1 style="font-size: 1.5rem; font-weight: 800; color: #1f2937; margin: 0 0 8px;">${title}</h1>
                    <div style="height: 1px; background: #e5e7eb; margin: 15px 0;"></div>
                    <p style="font-size: 0.7rem; font-weight: 600; color: ${accent}; margin: 0 0 10px; text-transform: uppercase; letter-spacing: 1px;">What We're Looking For</p>
                    <div style="font-size: 0.8rem; color: #374151; line-height: 1.8; white-space: pre-line;">${reqs}</div>
                    ${salary ? `
                    <div style="background: #f0fdf4; border-radius: 8px; padding: 10px 12px; margin-top: 15px; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.1rem;">&#128176;</span>
                        <div>
                            <p style="font-size: 0.6rem; color: #6b7280; margin: 0;">Salary Range</p>
                            <p style="font-size: 0.85rem; font-weight: 600; color: #1f2937; margin: 2px 0 0;">${salary}</p>
                        </div>
                    </div>
                    ` : ''}
                    <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                        <div style="background: linear-gradient(135deg, ${accent}, ${accent2}); color: white; padding: 10px 20px; font-size: 0.75rem; font-weight: 700; border-radius: 6px; text-transform: uppercase; letter-spacing: 1px;">Apply Now</div>
                    </div>
                </div>
            `;
        } else if (templateSlug === 'tech-dark') {
            const bg = useCustom ? pColor : '#18181b';
            const accent = useCustom ? sColor : '#3b82f6';
            previewHtml = `
                <div class="preview-tech" style="background: ${bg}; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: linear-gradient(rgba(59,130,246,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(59,130,246,0.03) 1px, transparent 1px); background-size: 20px 20px; pointer-events: none;"></div>
                    <div style="position: relative; z-index: 1;">
                        <div style="color: ${accent}; font-size: 0.7rem; font-weight: 700; letter-spacing: 3px; margin-bottom: 10px;">&lt;/&gt; TECH CAREERS</div>
                        <div style="font-size: 2.5rem; font-weight: 900; line-height: 1.1; margin-bottom: 20px;">
                            WE'RE<br><span style="color: ${accent}">HIRING</span>
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                                <div style="color: #71717a; font-size: 0.65rem; letter-spacing: 2px; margin-bottom: 5px;">// OPEN POSITION</div>
                                <div style="font-size: 1.5rem; font-weight: 700;">${title}</div>
                                ${salary ? `<div style="font-size: 0.7rem; color: #a1a1aa; margin-top: 5px;">${salary}</div>` : ''}
                        </div>

                        <div style="background: rgba(39,39,42,0.5); border: 1px solid #3f3f46; border-left: 2px solid ${accent}; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                            <div style="color: ${accent}; font-size: 0.65rem; font-weight: 700; letter-spacing: 1px; margin-bottom: 10px;">REQUIREMENTS.TXT</div>
                            <div style="font-size: 0.75rem; color: #d4d4d8; line-height: 1.6; white-space: pre-line;">${reqs}</div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #3f3f46; padding-top: 15px;">
                            <div>
                                <div style="font-weight: 700; font-size: 0.9rem;">${company}</div>
                                <div style="font-size: 0.6rem; color: #71717a;">Building the future</div>
                            </div>
                            <div style="background: ${accent}; color: #fff; padding: 8px 16px; font-size: 0.7rem; font-weight: 700; border-radius: 4px; text-transform: uppercase;">Apply Now</div>
                        </div>
                    </div>
                </div>
            `;
        } else if (templateSlug === 'bold-red') {
            const bg = useCustom ? pColor : '#dc2626';
            const accent = useCustom ? sColor : '#991b1b';
            previewHtml = `
                <div class="preview-red" style="background: ${bg}; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -30px; right: -30px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); transform: rotate(45deg);"></div>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <div style="font-size: 0.8rem; font-weight: 800; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 5px;">JOIN OUR TEAM</div>
                        <div style="background: #fff; color: ${bg}; display: inline-block; padding: 10px 30px; font-size: 2.5rem; font-weight: 900;">HIRING</div>
                    </div>

                    <div style="padding: 25px;">
                        <div style="margin-bottom: 20px;">
                            <div style="background: ${accent}; color: #fecaca; display: inline-block; padding: 4px 10px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; border-radius: 3px; margin-bottom: 5px;">Open Position</div>
                            <div style="font-size: 1.5rem; font-weight: 800;">${title}</div>
                        </div>

                        <div style="background: #fff; border-radius: 8px; padding: 20px; color: #374151;">
                            <div style="color: ${bg}; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; border-bottom: 2px solid #fecaca; padding-bottom: 5px; margin-bottom: 10px;">What We're Looking For</div>
                            <div style="font-size: 0.8rem; line-height: 1.6; white-space: pre-line;">${reqs}</div>
                        </div>

                        <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 800; font-size: 1rem;">${company}</div>
                                ${loc ? `<div style="font-size: 0.7rem; opacity: 0.8;">${loc}</div>` : ''}
                            </div>
                            <div style="background: #fff; color: ${bg}; padding: 10px 20px; font-size: 0.75rem; font-weight: 800; border-radius: 20px; text-transform: uppercase;">Apply Now</div>
                        </div>
                    </div>
                </div>
            `;
        } else if (templateSlug === 'elegant-navy') {
            const bg = useCustom ? `linear-gradient(180deg, ${pColor} 0%, ${sColor} 100%)` : 'linear-gradient(180deg, #0f172a 0%, #1e293b 100%)';
            const accent = '#d4af37'; // Gold is fixed
            previewHtml = `
                <div class="preview-navy" style="background: ${bg};">
                    <div style="height: 4px; background: linear-gradient(90deg, #d4af37, #f4d03f, #d4af37); margin: -25px -25px 20px;"></div>
                    
                    <div style="text-align: center; margin-bottom: 25px;">
                        <div style="color: ${accent}; font-size: 0.7rem; font-weight: 600; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 5px;">Career Opportunity</div>
                        <div style="font-size: 2rem; font-weight: 300; letter-spacing: 4px;">WE ARE</div>
                        <div style="color: ${accent}; font-size: 2.5rem; font-weight: 800; letter-spacing: 2px; margin-top: -5px;">HIRING</div>
                    </div>

                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="display: inline-block; width: 40px; height: 1px; background: ${accent}; vertical-align: middle;"></div>
                        <div style="display: inline-block; width: 6px; height: 6px; background: ${accent}; border-radius: 50%; margin: 0 10px; vertical-align: middle;"></div>
                        <div style="display: inline-block; width: 40px; height: 1px; background: ${accent}; vertical-align: middle;"></div>
                    </div>

                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="color: #94a3b8; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 2px;">Position</div>
                        <div style="font-size: 1.4rem; font-weight: 700;">${title}</div>
                    </div>

                    <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(212,175,55,0.3); border-radius: 6px; padding: 20px; font-size: 0.8rem;">
                            <div style="color: ${accent}; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; text-align: center; margin-bottom: 10px;">Requirements</div>
                            <div style="line-height: 1.6; white-space: pre-line; color: #e2e8f0;">${reqs}</div>
                    </div>

                    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(212,175,55,0.3); display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-weight: 700;">${company}</div>
                        <div style="background: ${accent}; color: #0f172a; padding: 8px 16px; font-size: 0.65rem; font-weight: 800; border-radius: 2px; text-transform: uppercase;">Apply Now</div>
                    </div>
                </div>
            `;
        } else if (templateSlug === 'fresh-teal') {
            const bg = useCustom ? pColor : '#f0fdfa';
            const accent = useCustom ? sColor : '#0d9488';
            previewHtml = `
                <div class="preview-teal" style="background: ${bg};">
                    <div style="text-align: center; margin-bottom: 25px;">
                        <div style="background: ${accent}; color: #fff; display: inline-block; padding: 6px 20px; font-size: 0.7rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; border-radius: 20px; margin-bottom: 10px;">We're Hiring</div>
                        <div style="color: ${accent}; font-size: 2.5rem; font-weight: 900; line-height: 1; letter-spacing: -1px;">GROW WITH <br> US</div>
                    </div>

                    <div style="background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <div style="text-align: center; margin-bottom: 15px;">
                            <div style="color: ${accent}; font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px;">Open Position</div>
                            <div style="color: #134e4a; font-size: 1.4rem; font-weight: 800; line-height: 1.2;">${title}</div>
                        </div>
                        
                        <div style="height: 1px; background: ${accent}; opacity: 0.2; margin-bottom: 15px;"></div>

                        <div style="color: ${accent}; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; text-align: center; margin-bottom: 10px;">What You'll Need</div>
                        <div style="font-size: 0.8rem; color: #374151; line-height: 1.6; white-space: pre-line;">${reqs}</div>
                    </div>

                    <div style="background: ${accent}; padding: 15px; border-radius: 0 0 12px 12px; margin: 20px -25px -25px; color: white; display: flex; justify-content: space-between; align-items: center;">
                            <div style="font-weight: 800;">${company}</div>
                            <div style="background: #fff; color: ${accent}; padding: 6px 14px; font-size: 0.65rem; font-weight: 800; border-radius: 20px; text-transform: uppercase;">Apply Now</div>
                    </div>
                </div>
            `;
        } else if (templateSlug === 'sunset-orange') {
            const bg = useCustom ? `linear-gradient(135deg, ${pColor}, ${sColor})` : 'linear-gradient(135deg, #f97316 0%, #ea580c 50%, #dc2626 100%)';
            previewHtml = `
                <div class="preview-orange" style="background: ${bg}; overflow: hidden; position: relative;">
                    <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; border: 20px solid rgba(255,255,255,0.1); border-radius: 50%;"></div>
                    
                    <div style="text-align: center; margin-top: 10px;">
                        <div style="background: #fff; color: #ea580c; display: inline-block; padding: 6px 20px; font-size: 0.7rem; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; border-radius: 20px; margin-bottom: 10px;">Now Hiring</div>
                        <div style="font-size: 2.8rem; font-weight: 900; line-height: 1; letter-spacing: -2px;">JOIN US!</div>
                    </div>

                    <div style="background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); margin-top: 25px; color: #333;">
                        <div style="margin-bottom: 15px;">
                            <div style="color: #9ca3af; font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">We're Looking For</div>
                            <div style="color: #1f2937; font-size: 1.3rem; font-weight: 800;">${title}</div>
                        </div>

                        <div style="height: 2px; background: linear-gradient(90deg, #f97316, #ea580c); margin-bottom: 15px;"></div>

                        <div style="color: #ea580c; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; margin-bottom: 8px;">Requirements</div>
                        <div style="font-size: 0.8rem; color: #4b5563; line-height: 1.6; white-space: pre-line;">${reqs}</div>
                    </div>

                    <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-weight: 800; font-size: 1rem;">${company}</div>
                        <div style="background: #fff; color: #ea580c; padding: 8px 18px; font-size: 0.7rem; font-weight: 800; border-radius: 20px; text-transform: uppercase;">Apply Now</div>
                    </div>
                </div>
            `;
        } else {
            previewDiv.innerHTML = previewHtml;
        }

        previewDiv.innerHTML = previewHtml;
    }

    jobTitle.addEventListener('input', updatePreview);
    requirements.addEventListener('input', updatePreview);
    companyName.addEventListener('input', updatePreview);

    updatePreview();

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

        const formData = new FormData(form);

        fetch('{{ route("employer.posters.update", $poster->id) }}', {
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
