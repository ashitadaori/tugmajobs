@extends('front.layouts.employer-layout')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card bg-white rounded-3 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 mb-2">Company Profile</h1>
                        <p class="text-muted mb-0">Update your company information and branding</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0 pt-4">
            <h5 class="card-title mb-0">Company Information</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('employer.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-4">
                    <!-- Company Logo -->
                    <div class="col-12">
                        <div class="d-flex align-items-center gap-4">
                            <div class="company-logo-wrapper">
                                @if($profile->company_logo)
                                    <img src="{{ asset('storage/' . $profile->company_logo) }}" alt="Company Logo" class="company-logo">
                                @else
                                    <div class="company-logo-placeholder">
                                        <i class="bi bi-building"></i>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h6 class="mb-1">Company Logo</h6>
                                <p class="text-muted small mb-2">Upload a high-quality image of your company logo</p>
                                <input type="file" name="company_logo" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <!-- Company Name -->
                    <div class="col-md-6">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $profile->company_name) }}" required>
                    </div>

                    <!-- Industry -->
                    <div class="col-md-6">
                        <label class="form-label">Industry</label>
                        <input type="text" name="industry" class="form-control" value="{{ old('industry', $profile->industry) }}">
                    </div>

                    <!-- Company Size -->
                    <div class="col-md-6">
                        <label class="form-label">Company Size</label>
                        <select name="company_size" class="form-select">
                            <option value="">Select company size</option>
                            <option value="1-10" {{ old('company_size', $profile->company_size) == '1-10' ? 'selected' : '' }}>1-10 employees</option>
                            <option value="11-50" {{ old('company_size', $profile->company_size) == '11-50' ? 'selected' : '' }}>11-50 employees</option>
                            <option value="51-200" {{ old('company_size', $profile->company_size) == '51-200' ? 'selected' : '' }}>51-200 employees</option>
                            <option value="201-500" {{ old('company_size', $profile->company_size) == '201-500' ? 'selected' : '' }}>201-500 employees</option>
                            <option value="501+" {{ old('company_size', $profile->company_size) == '501+' ? 'selected' : '' }}>501+ employees</option>
                        </select>
                    </div>

                    <!-- Founded Year -->
                    <div class="col-md-6">
                        <label class="form-label">Founded Year</label>
                        <input type="number" name="founded_year" class="form-control" value="{{ old('founded_year', $profile->founded_year) }}" min="1800" max="{{ date('Y') }}">
                    </div>

                    <!-- Website -->
                    <div class="col-md-6">
                        <label class="form-label">Website</label>
                        <input type="url" name="website" class="form-control" value="{{ old('website', $profile->website) }}">
                    </div>

                    <!-- Location -->
                    <div class="col-md-6">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location', $profile->location) }}">
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label">Company Description</label>
                        <textarea name="description" class="form-control" rows="5">{{ old('description', $profile->description) }}</textarea>
                    </div>

                    <!-- Social Media Links -->
                    <div class="col-12">
                        <h6 class="mb-3">Social Media Links</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-linkedin"></i></span>
                                    <input type="url" name="linkedin" class="form-control" placeholder="LinkedIn URL" value="{{ old('linkedin', $profile->linkedin) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-facebook"></i></span>
                                    <input type="url" name="facebook" class="form-control" placeholder="Facebook URL" value="{{ old('facebook', $profile->facebook) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-twitter"></i></span>
                                    <input type="url" name="twitter" class="form-control" placeholder="Twitter URL" value="{{ old('twitter', $profile->twitter) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-instagram"></i></span>
                                    <input type="url" name="instagram" class="form-control" placeholder="Instagram URL" value="{{ old('instagram', $profile->instagram) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.welcome-card {
    background: linear-gradient(to right, var(--bs-primary-bg-subtle), var(--bs-white));
    border-left: 4px solid var(--bs-primary);
}

.company-logo-wrapper {
    width: 100px;
    height: 100px;
    border-radius: 10px;
    overflow: hidden;
    background: var(--bs-light);
    display: flex;
    align-items: center;
    justify-content: center;
}

.company-logo {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.company-logo-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: var(--bs-gray-500);
}

.input-group-text {
    background: var(--bs-white);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview uploaded logo
    const logoInput = document.querySelector('input[name="company_logo"]');
    if (logoInput) {
        logoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Validate file size
                if (file.size > 2 * 1024 * 1024) { // 2MB
                    alert('File size must be less than 2MB');
                    this.value = '';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Only JPG, PNG and GIF files are allowed');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.company-logo-wrapper');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Company Logo Preview" class="company-logo">`;
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Handle logo removal
    window.handleLogoRemoval = function() {
        if (confirm('Are you sure you want to remove the company logo?')) {
            const scrollInput = document.getElementById('remove-logo-scroll-position');
            if (scrollInput) {
                scrollInput.value = window.scrollY;
            }
            document.getElementById('remove-logo-form').submit();
        }
    };

    // Handle form submission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Don't prevent default form submission
            const formData = new FormData(this);
            formData.append('scroll_position', window.scrollY);
            
            // Convert textarea inputs with line breaks to arrays
            ['company_culture', 'benefits_offered', 'specialties', 'hiring_process'].forEach(field => {
                const textarea = document.getElementById(field);
                if (textarea && textarea.value.trim()) {
                    const lines = textarea.value.split('\n').filter(line => line.trim());
                    formData.set(field, JSON.stringify(lines));
                }
            });
        });
    }

    // Restore scroll position after page load
    const urlParams = new URLSearchParams(window.location.search);
    const scrollPosition = urlParams.get('scroll_position');
    if (scrollPosition) {
        window.scrollTo(0, parseInt(scrollPosition));
    }

    // If there are any error messages, scroll to the first error
    const firstError = document.querySelector('.is-invalid');
    if (firstError) {
        const offset = 100;
        const elementPosition = firstError.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - offset;
        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
    }
});
</script>
@endpush

@endsection 