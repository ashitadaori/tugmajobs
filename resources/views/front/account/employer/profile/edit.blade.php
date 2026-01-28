@extends('layouts.employer')

@section('page_title', 'Company Profile')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
    <!-- Maintenance Notice -->
    @if(\App\Models\MaintenanceSetting::isMaintenanceActive('employer'))
        <div class="ep-alert ep-alert-warning ep-mb-6">
            <i class="bi bi-exclamation-triangle ep-alert-icon"></i>
            <div class="ep-alert-content">
                <div class="ep-alert-title">Feature Under Maintenance</div>
                <div class="ep-alert-message">{{ \App\Models\MaintenanceSetting::getMaintenanceMessage('employer') }}</div>
            </div>
            <a href="{{ route('employer.dashboard') }}" class="ep-btn ep-btn-primary ep-btn-sm">
                <i class="bi bi-house-door"></i>
                Return to Dashboard
            </a>
        </div>
    @else

        @if($errors->any())
            <div class="ep-alert ep-alert-danger ep-mb-6">
                <i class="bi bi-exclamation-circle ep-alert-icon"></i>
                <div class="ep-alert-content">
                    <div class="ep-alert-title">Validation Error</div>
                    <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li style="font-size: 14px;">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="companyProfileForm" method="POST" action="{{ route('employer.profile.update') }}"
            enctype="multipart/form-data">
            @csrf

            <div class="profile-layout">
                <!-- Main Content -->
                <div class="profile-main">
                    <!-- Personal Information -->
                    <div class="ep-card ep-mb-6">
                        <div class="ep-card-header">
                            <h3 class="ep-card-title">
                                <i class="bi bi-person"></i>
                                Personal Information
                            </h3>
                        </div>
                        <div class="ep-card-body">
                            <div class="form-grid">
                                <div class="ep-form-group">
                                    <label class="ep-form-label required">Your Name</label>
                                    <input type="text" name="name" class="ep-form-input"
                                        value="{{ auth()->user()->name ?? '' }}" required placeholder="Enter your full name">
                                </div>
                                <div class="ep-form-group">
                                    <label class="ep-form-label required">Your Email</label>
                                    <input type="email" name="email" class="ep-form-input"
                                        value="{{ auth()->user()->email ?? '' }}" required
                                        placeholder="Enter your email address">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Company Information -->
                    <div class="ep-card ep-mb-6">
                        <div class="ep-card-header">
                            <h3 class="ep-card-title">
                                <i class="bi bi-building"></i>
                                Company Information
                            </h3>
                        </div>
                        <div class="ep-card-body">
                            <div class="form-grid">
                                <div class="ep-form-group">
                                    <label class="ep-form-label required">Company Name</label>
                                    <input type="text" name="company_name" class="ep-form-input"
                                        value="{{ $profile->company_name ?? '' }}" required
                                        placeholder="Enter your company name">
                                </div>
                                <div class="ep-form-group">
                                    <label class="ep-form-label required">Industry</label>
                                    <select name="industry" class="ep-form-select" required>
                                        <option value="">Select Industry</option>
                                        <option value="technology" {{ ($profile->industry ?? '') == 'technology' ? 'selected' : '' }}>Technology</option>
                                        <option value="healthcare" {{ ($profile->industry ?? '') == 'healthcare' ? 'selected' : '' }}>Healthcare</option>
                                        <option value="finance" {{ ($profile->industry ?? '') == 'finance' ? 'selected' : '' }}>
                                            Finance</option>
                                        <option value="education" {{ ($profile->industry ?? '') == 'education' ? 'selected' : '' }}>Education</option>
                                        <option value="retail" {{ ($profile->industry ?? '') == 'retail' ? 'selected' : '' }}>
                                            Retail</option>
                                        <option value="manufacturing" {{ ($profile->industry ?? '') == 'manufacturing' ? 'selected' : '' }}>Manufacturing</option>
                                        <option value="construction" {{ ($profile->industry ?? '') == 'construction' ? 'selected' : '' }}>Construction</option>
                                        <option value="agriculture" {{ ($profile->industry ?? '') == 'agriculture' ? 'selected' : '' }}>Agriculture</option>
                                        <option value="hospitality" {{ ($profile->industry ?? '') == 'hospitality' ? 'selected' : '' }}>Hospitality</option>
                                        <option value="other" {{ ($profile->industry ?? '') == 'other' ? 'selected' : '' }}>Other
                                        </option>
                                    </select>
                                </div>
                                <div class="ep-form-group">
                                    <label class="ep-form-label">Company Size</label>
                                    <select name="company_size" class="ep-form-select">
                                        <option value="">Select Size</option>
                                        <option value="1-10" {{ ($profile->company_size ?? '') == '1-10' ? 'selected' : '' }}>1-10
                                            employees</option>
                                        <option value="11-50" {{ ($profile->company_size ?? '') == '11-50' ? 'selected' : '' }}>
                                            11-50 employees</option>
                                        <option value="51-200" {{ ($profile->company_size ?? '') == '51-200' ? 'selected' : '' }}>
                                            51-200 employees</option>
                                        <option value="201-500" {{ ($profile->company_size ?? '') == '201-500' ? 'selected' : '' }}>201-500 employees</option>
                                        <option value="500+" {{ ($profile->company_size ?? '') == '500+' ? 'selected' : '' }}>500+
                                            employees</option>
                                    </select>
                                </div>
                                <div class="ep-form-group">
                                    <label class="ep-form-label">Founded Year</label>
                                    <input type="number" name="founded_year" class="ep-form-input"
                                        value="{{ $profile->founded_year ?? '' }}" min="1800" max="{{ date('Y') }}"
                                        placeholder="e.g. 2015">
                                </div>
                            </div>
                            <div class="ep-form-group">
                                <label class="ep-form-label required">Company Description</label>
                                <textarea name="company_description" class="ep-form-textarea" rows="4" required
                                    placeholder="Describe your company, mission, and what you do...">{{ $profile->company_description ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="ep-card ep-mb-6">
                        <div class="ep-card-header">
                            <h3 class="ep-card-title">
                                <i class="bi bi-telephone"></i>
                                Contact Information
                            </h3>
                        </div>
                        <div class="ep-card-body">
                            <div class="form-grid">
                                <div class="ep-form-group">
                                    <label class="ep-form-label required">Email Address</label>
                                    <input type="email" name="contact_email" class="ep-form-input"
                                        value="{{ $profile->business_email ?? '' }}" required placeholder="Enter company email">
                                </div>
                                <div class="ep-form-group">
                                    <label class="ep-form-label">Phone Number</label>
                                    <input type="tel" name="contact_phone" class="ep-form-input"
                                        value="{{ $profile->business_phone ?? '' }}" placeholder="e.g. +63 912 345 6789">
                                </div>
                                <div class="ep-form-group">
                                    <label class="ep-form-label">Website</label>
                                    <input type="url" name="website" class="ep-form-input"
                                        value="{{ $profile->company_website ?? '' }}" placeholder="https://yourcompany.com">
                                </div>
                                <div class="ep-form-group">
                                    <label class="ep-form-label">LinkedIn</label>
                                    <input type="url" name="linkedin_url" class="ep-form-input"
                                        value="{{ $profile->linkedin_url ?? '' }}"
                                        placeholder="https://linkedin.com/company/...">
                                </div>
                                <div class="ep-form-group">
                                    <label class="ep-form-label">Facebook</label>
                                    <input type="url" name="facebook_url" class="ep-form-input"
                                        value="{{ $profile->facebook_url ?? '' }}" placeholder="https://facebook.com/...">
                                </div>
                                <div class="ep-form-group">
                                    <label class="ep-form-label">Contact Person</label>
                                    <input type="text" name="contact_person_name" class="ep-form-input"
                                        value="{{ $profile->contact_person_name ?? '' }}" placeholder="e.g. John Doe">
                                </div>
                            </div>
                            <!-- Location Search with Mapbox -->
                            <div class="ep-form-group">
                                <label class="ep-form-label">Search Location</label>
                                <div class="location-search-wrapper">
                                    <div class="location-input-container">
                                        <i class="bi bi-search location-icon"></i>
                                        <input type="text" id="locationSearch" class="location-search-input"
                                            placeholder="Search for your business location in Sta. Cruz..." autocomplete="off">
                                        <button type="button" class="location-detect-btn" id="detectLocationBtn"
                                            title="Detect my current location">
                                            <i class="bi bi-crosshair"></i>
                                        </button>
                                    </div>
                                    <div class="location-suggestions-dropdown" id="locationSuggestions" style="display: none;">
                                        <!-- Suggestions will be populated here -->
                                    </div>
                                </div>
                                <div class="ep-form-help">Type your business name or street to find it on the map</div>
                            </div>

                            <!-- Map Display -->
                            <div class="ep-form-group">
                                <div id="locationMap" class="location-map-container"></div>
                                <div class="ep-form-help d-flex justify-content-between align-items-center">
                                    <span>Tip: You can also drag the marker to fine-tune your location</span>
                                    <span id="coordinateDisplay" class="text-muted" style="font-size: 11px;"></span>
                                </div>
                            </div>

                            <!-- Hidden fields for coordinates -->
                            <input type="hidden" name="latitude" id="latitude" value="{{ $profile->latitude ?? '' }}">
                            <input type="hidden" name="longitude" id="longitude" value="{{ $profile->longitude ?? '' }}">

                            <div class="form-grid form-grid-3">
                                <div class="ep-form-group">
                                    <label class="ep-form-label">City</label>
                                    <div class="input-icon-wrapper">
                                        <i class="bi bi-building input-icon"></i>
                                        <input type="text" name="city" id="cityInput" class="ep-form-input input-with-icon"
                                            value="{{ $profile->city ?? '' }}" placeholder="e.g. Sta. Cruz">
                                    </div>
                                </div>
                                <div class="ep-form-group">
                                    <label class="ep-form-label">Province</label>
                                    <div class="input-icon-wrapper">
                                        <i class="bi bi-map input-icon"></i>
                                        <input type="text" name="state" id="stateInput" class="ep-form-input input-with-icon"
                                            value="{{ $profile->state ?? '' }}" placeholder="e.g. Davao del Sur">
                                    </div>
                                </div>
                                <div class="ep-form-group">
                                    <label class="ep-form-label">Country</label>
                                    <div class="input-icon-wrapper">
                                        <i class="bi bi-globe input-icon"></i>
                                        <input type="text" name="country" id="countryInput"
                                            class="ep-form-input input-with-icon"
                                            value="{{ $profile->country ?? 'Philippines' }}" placeholder="Philippines">
                                    </div>
                                </div>
                            </div>
                            <div class="ep-form-group">
                                <label class="ep-form-label">Street Address</label>
                                <div class="input-icon-wrapper">
                                    <i class="bi bi-geo-alt input-icon" style="top: 15px; transform: none;"></i>
                                    <textarea name="location" id="streetAddress" class="ep-form-textarea input-with-icon"
                                        rows="2"
                                        placeholder="Enter your company street address...">{{ $profile->business_address ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Company Culture & Benefits -->
                    <div class="ep-card ep-mb-6">
                        <div class="ep-card-header">
                            <h3 class="ep-card-title">
                                <i class="bi bi-heart"></i>
                                Company Culture & Benefits
                            </h3>
                        </div>
                        <div class="ep-card-body">
                            <div class="ep-form-group">
                                <label class="ep-form-label">Company Culture</label>
                                <textarea name="company_culture[description]" class="ep-form-textarea" rows="3"
                                    placeholder="Describe your company culture, values, and work environment...">{{ data_get($profile->company_culture, 'description') }}</textarea>
                            </div>
                            <div class="ep-form-group">
                                <label class="ep-form-label">Benefits & Perks</label>
                                <div class="benefits-grid">
                                    @php
                                        $benefits = [
                                            'health-insurance' => 'Health Insurance',
                                            'remote-work' => 'Remote Work Options',
                                            'flexible-hours' => 'Flexible Hours',
                                            'professional-development' => 'Professional Development',
                                            'paid-time-off' => 'Paid Time Off',
                                            'retirement-plan' => 'Retirement Plan'
                                        ];
                                    @endphp
                                    @foreach($benefits as $value => $label)
                                        <label class="benefit-checkbox">
                                            <input type="checkbox" name="benefits_offered[]" value="{{ $value }}" {{ in_array($value, $profile->benefits_offered ?? []) ? 'checked' : '' }}>
                                            <span class="benefit-checkmark"></span>
                                            <span class="benefit-label">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="form-actions">
                        <button type="button" class="ep-btn ep-btn-secondary" onclick="window.location.reload()">
                            <i class="bi bi-x-circle"></i>
                            Cancel
                        </button>
                        <button type="submit" class="ep-btn ep-btn-primary" id="saveBtn">
                            <i class="bi bi-check-circle"></i>
                            Save Changes
                        </button>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="profile-sidebar">
                    <!-- Company Logo -->
                    <div class="ep-card ep-mb-6">
                        <div class="ep-card-header">
                            <h3 class="ep-card-title">
                                <i class="bi bi-image"></i>
                                Company Logo
                            </h3>
                        </div>
                        <div class="ep-card-body">
                            <div class="logo-upload-area">
                                @if($profile && $profile->company_logo)
                                    <img src="{{ $profile->logo_url }}?v={{ time() }}" alt="Company Logo" id="logoPreview"
                                        class="logo-preview">
                                    <div class="ep-badge ep-badge-success" style="margin-top: 12px;">
                                        <i class="bi bi-check-circle"></i> Logo uploaded
                                    </div>
                                @else
                                    <div class="logo-placeholder" id="logoPlaceholder">
                                        <i class="bi bi-building"></i>
                                        <span>No logo uploaded</span>
                                    </div>
                                    <img src="" alt="Company Logo" id="logoPreview" class="logo-preview" style="display: none;">
                                @endif
                            </div>
                            <div class="ep-form-group" style="margin-top: 16px;">
                                <input type="file" name="company_logo" class="ep-form-input" accept="image/*" id="logoInput">
                                <div class="ep-form-help">Recommended: 300x300px, Max: 2MB</div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Completion -->
                    <div class="ep-card ep-mb-6">
                        <div class="ep-card-header">
                            <h3 class="ep-card-title">
                                <i class="bi bi-check-circle"></i>
                                Profile Completion
                            </h3>
                        </div>
                        <div class="ep-card-body">
                            <div class="completion-progress">
                                <div class="completion-bar">
                                    <div class="completion-fill" style="width: {{ $profileCompletion }}%;"></div>
                                </div>
                                <div class="completion-text">{{ $profileCompletion }}% Complete</div>
                            </div>

                            @php
                                $hasCompanyLogo = false;
                                if (!empty($profile->company_logo)) {
                                    $logoPath = str_replace('storage/', '', $profile->company_logo);
                                    $hasCompanyLogo = Storage::disk('public')->exists($logoPath);
                                }
                            @endphp

                            <div class="completion-checklist">
                                <div
                                    class="checklist-item {{ !empty($profile->company_name) && !empty($profile->company_description) && !empty($profile->industry) ? 'completed' : '' }}">
                                    <i
                                        class="bi {{ !empty($profile->company_name) && !empty($profile->company_description) && !empty($profile->industry) ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                    <span>Company Information</span>
                                </div>
                                <div
                                    class="checklist-item {{ !empty($profile->business_email) && !empty($profile->business_address) ? 'completed' : '' }}">
                                    <i
                                        class="bi {{ !empty($profile->business_email) && !empty($profile->business_address) ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                    <span>Contact Details</span>
                                </div>
                                <div class="checklist-item {{ $hasCompanyLogo ? 'completed' : '' }}">
                                    <i class="bi {{ $hasCompanyLogo ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                    <span>Company Logo</span>
                                </div>
                                <div
                                    class="checklist-item {{ !empty($profile->company_culture) && !empty($profile->benefits_offered) ? 'completed' : '' }}">
                                    <i
                                        class="bi {{ !empty($profile->company_culture) && !empty($profile->benefits_offered) ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                    <span>Culture & Benefits</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="ep-card">
                        <div class="ep-card-header">
                            <h3 class="ep-card-title">
                                <i class="bi bi-bar-chart"></i>
                                Profile Stats
                            </h3>
                        </div>
                        <div class="ep-card-body">
                            <div class="stats-list">
                                <div class="stat-row">
                                    <span class="stat-label">Profile Views</span>
                                    <span class="stat-value">{{ $profile->profile_views ?? 0 }}</span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">Active Jobs</span>
                                    <span class="stat-value">{{ $activeJobs ?? 0 }}</span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">Total Applications</span>
                                    @php
                                        $totalApplications = \App\Models\JobApplication::whereHas('job', function ($query) {
                                            $query->where('employer_id', auth()->id());
                                        })->count();
                                    @endphp
                                    <span class="stat-value">{{ $totalApplications }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif

    @push('styles')
        <!-- Mapbox GL CSS -->
        <link href='https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.css' rel='stylesheet' />
        <style>
            /* Location Search Styles */
            .location-search-wrapper {
                position: relative;
                margin-bottom: var(--ep-space-4);
            }

            .location-input-container {
                position: relative;
                display: flex;
                align-items: center;
                background: white;
                border: 1px solid var(--ep-gray-300);
                border-radius: var(--ep-radius-md);
                transition: all var(--ep-transition-base);
            }

            .location-input-container:focus-within {
                border-color: var(--ep-primary);
                box-shadow: 0 0 0 3px var(--ep-primary-100);
            }

            .location-icon {
                padding-left: 14px;
                color: var(--ep-gray-400);
                font-size: 18px;
            }

            .location-search-input {
                flex: 1;
                border: none !important;
                background: transparent !important;
                padding: var(--ep-space-3) var(--ep-space-4) !important;
                padding-left: 10px !important;
                box-shadow: none !important;
                height: 48px;
            }

            .location-detect-btn {
                background: none;
                border: none;
                padding: 0 14px;
                color: var(--ep-primary);
                cursor: pointer;
                transition: color 0.15s ease;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .location-detect-btn:hover {
                color: var(--ep-primary-dark);
            }

            .location-detect-btn:disabled {
                color: var(--ep-gray-400);
                cursor: not-allowed;
            }

            .location-suggestions-dropdown {
                position: absolute;
                top: 105%;
                left: 0;
                right: 0;
                background: white;
                border: 1px solid var(--ep-gray-200);
                border-radius: var(--ep-radius-md);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
                z-index: 1000;
                max-height: 280px;
                overflow-y: auto;
            }

            .location-suggestion-item {
                padding: 12px 16px;
                cursor: pointer;
                border-bottom: 1px solid var(--ep-gray-50);
                display: flex;
                align-items: flex-start;
                gap: 12px;
                transition: background-color 0.15s ease;
            }

            .location-suggestion-item i {
                margin-top: 3px;
                color: var(--ep-gray-400);
            }

            .location-suggestion-item:hover {
                background-color: var(--ep-gray-50);
            }

            .location-suggestion-item:hover i {
                color: var(--ep-primary);
            }

            .location-suggestion-content {
                flex: 1;
            }

            .location-suggestion-name {
                font-weight: 600;
                color: var(--ep-gray-800);
                font-size: 14px;
            }

            .location-suggestion-address {
                font-size: 12px;
                color: var(--ep-gray-500);
                margin-top: 2px;
            }

            .location-map-container {
                height: 300px;
                border-radius: var(--ep-radius-md);
                border: 1px solid var(--ep-gray-300);
                overflow: hidden;
                background: var(--ep-gray-100);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                margin-bottom: var(--ep-space-2);
            }

            .location-map-placeholder {
                height: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                color: var(--ep-gray-400);
            }

            .location-map-placeholder i {
                font-size: 48px;
                margin-bottom: 12px;
            }

            /* Mapbox marker styling */
            .mapboxgl-marker {
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
            }

            .ep-form-group .input-icon-wrapper {
                position: relative;
            }

            .ep-form-group .input-icon {
                position: absolute;
                left: 14px;
                top: 50%;
                transform: translateY(-50%);
                color: var(--ep-gray-400);
                pointer-events: none;
            }

            .ep-form-group .input-with-icon {
                padding-left: 42px !important;
            }

            .profile-layout {
                display: grid;
                grid-template-columns: 1fr 350px;
                gap: var(--ep-space-6);
            }

            .form-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: var(--ep-space-4);
                margin-bottom: var(--ep-space-4);
            }

            .form-grid-3 {
                grid-template-columns: repeat(3, 1fr);
            }

            .ep-form-textarea {
                width: 100%;
                padding: var(--ep-space-3) var(--ep-space-4);
                background: white;
                border: 1px solid var(--ep-gray-300);
                border-radius: var(--ep-radius-md);
                font-size: var(--ep-font-size-sm);
                color: var(--ep-gray-800);
                resize: vertical;
                min-height: 80px;
                font-family: inherit;
                transition: all var(--ep-transition-base);
            }

            .ep-form-textarea:focus {
                outline: none;
                border-color: var(--ep-primary);
                box-shadow: 0 0 0 3px var(--ep-primary-100);
            }

            .form-actions {
                display: flex;
                justify-content: flex-end;
                gap: var(--ep-space-3);
                padding: var(--ep-space-6) 0;
            }

            /* Logo Upload */
            .logo-upload-area {
                text-align: center;
            }

            .logo-preview {
                max-width: 180px;
                max-height: 180px;
                border-radius: var(--ep-radius-lg);
                border: 2px solid var(--ep-gray-200);
                padding: 8px;
                background: white;
                object-fit: contain;
            }

            .logo-placeholder {
                width: 180px;
                height: 180px;
                margin: 0 auto;
                border: 2px dashed var(--ep-gray-300);
                border-radius: var(--ep-radius-lg);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                color: var(--ep-gray-400);
                background: var(--ep-gray-50);
            }

            .logo-placeholder i {
                font-size: 48px;
                margin-bottom: 8px;
            }

            .logo-placeholder span {
                font-size: var(--ep-font-size-sm);
            }

            /* Benefits Grid */
            .benefits-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: var(--ep-space-3);
            }

            .benefit-checkbox {
                display: flex;
                align-items: center;
                gap: var(--ep-space-3);
                padding: var(--ep-space-3) var(--ep-space-4);
                background: var(--ep-gray-50);
                border: 1px solid var(--ep-gray-200);
                border-radius: var(--ep-radius-md);
                cursor: pointer;
                transition: all var(--ep-transition-base);
            }

            .benefit-checkbox:hover {
                background: var(--ep-primary-50);
                border-color: var(--ep-primary);
            }

            .benefit-checkbox input {
                display: none;
            }

            .benefit-checkmark {
                width: 20px;
                height: 20px;
                border: 2px solid var(--ep-gray-300);
                border-radius: 4px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                transition: all var(--ep-transition-base);
            }

            .benefit-checkbox input:checked+.benefit-checkmark {
                background: var(--ep-primary);
                border-color: var(--ep-primary);
            }

            .benefit-checkbox input:checked+.benefit-checkmark::after {
                content: '\2713';
                color: white;
                font-size: 12px;
                font-weight: bold;
            }

            .benefit-label {
                font-size: var(--ep-font-size-sm);
                color: var(--ep-gray-700);
            }

            /* Completion Progress */
            .completion-progress {
                margin-bottom: var(--ep-space-5);
            }

            .completion-bar {
                height: 8px;
                background: var(--ep-gray-200);
                border-radius: var(--ep-radius-full);
                overflow: hidden;
                margin-bottom: var(--ep-space-2);
            }

            .completion-fill {
                height: 100%;
                background: linear-gradient(90deg, var(--ep-primary), var(--ep-primary-light));
                border-radius: var(--ep-radius-full);
                transition: width 0.5s ease;
            }

            .completion-text {
                font-size: var(--ep-font-size-sm);
                font-weight: 600;
                color: var(--ep-gray-800);
            }

            .completion-checklist {
                display: flex;
                flex-direction: column;
                gap: var(--ep-space-3);
            }

            .checklist-item {
                display: flex;
                align-items: center;
                gap: var(--ep-space-2);
                font-size: var(--ep-font-size-sm);
                color: var(--ep-gray-500);
            }

            .checklist-item i {
                font-size: 16px;
            }

            .checklist-item.completed {
                color: var(--ep-success);
            }

            /* Stats List */
            .stats-list {
                display: flex;
                flex-direction: column;
                gap: var(--ep-space-3);
            }

            .stat-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: var(--ep-space-2) 0;
                border-bottom: 1px solid var(--ep-gray-100);
            }

            .stat-row:last-child {
                border-bottom: none;
            }

            .stat-label {
                font-size: var(--ep-font-size-sm);
                color: var(--ep-gray-600);
            }

            .stat-value {
                font-size: var(--ep-font-size-base);
                font-weight: 700;
                color: var(--ep-gray-800);
            }

            /* Responsive */
            @media (max-width: 1200px) {
                .profile-layout {
                    grid-template-columns: 1fr;
                }

                .profile-sidebar {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr);
                    gap: var(--ep-space-6);
                }
            }

            @media (max-width: 768px) {

                .form-grid,
                .form-grid-3 {
                    grid-template-columns: 1fr;
                }

                .benefits-grid {
                    grid-template-columns: 1fr;
                }

                .profile-sidebar {
                    grid-template-columns: 1fr;
                }

                .form-actions {
                    flex-direction: column-reverse;
                }

                .form-actions .ep-btn {
                    width: 100%;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Logo Preview
                const logoInput = document.getElementById('logoInput');
                const logoPreview = document.getElementById('logoPreview');
                const logoPlaceholder = document.getElementById('logoPlaceholder');

                if (logoInput) {
                    logoInput.addEventListener('change', function (e) {
                        const file = e.target.files[0];
                        if (file) {
                            if (file.size > 2 * 1024 * 1024) {
                                alert('File size must be less than 2MB');
                                this.value = '';
                                return;
                            }

                            if (!file.type.startsWith('image/')) {
                                alert('Please select an image file');
                                this.value = '';
                                return;
                            }

                            const reader = new FileReader();
                            reader.onload = function (e) {
                                if (logoPreview) {
                                    logoPreview.src = e.target.result;
                                    logoPreview.style.display = 'block';
                                }
                                if (logoPlaceholder) {
                                    logoPlaceholder.style.display = 'none';
                                }
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }

                // Form submission
                const form = document.getElementById('companyProfileForm');
                const saveBtn = document.getElementById('saveBtn');

                if (form) {
                    form.addEventListener('submit', function (e) {
                        const originalText = saveBtn.innerHTML;
                        saveBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
                        saveBtn.disabled = true;
                    });
                }
            });
        </script>

        <!-- Mapbox GL JS -->
        <script src='https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.js'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Mapbox Location Integration
                const locationSearch = document.getElementById('locationSearch');
                const locationSuggestions = document.getElementById('locationSuggestions');
                const detectLocationBtn = document.getElementById('detectLocationBtn');
                const locationMapContainer = document.getElementById('locationMap');
                const latitudeInput = document.getElementById('latitude');
                const longitudeInput = document.getElementById('longitude');
                const cityInput = document.getElementById('cityInput');
                const stateInput = document.getElementById('stateInput');
                const countryInput = document.getElementById('countryInput');
                const streetAddress = document.getElementById('streetAddress');

                let map = null;
                let marker = null;
                let searchTimeout = null;
                let mapboxConfig = null;

                // Load Mapbox configuration
                fetch('/api/location/config')
                    .then(response => response.json())
                    .then(config => {
                        mapboxConfig = config;
                        initializeMap(config);
                    })
                    .catch(error => {
                        console.error('Failed to load Mapbox config:', error);
                        showMapPlaceholder();
                    });

                function initializeMap(config) {
                    if (!config.public_token) {
                        showMapPlaceholder();
                        return;
                    }

                    mapboxgl.accessToken = config.public_token;

                    // Get initial coordinates from saved data or use default
                    const initialLat = parseFloat(latitudeInput.value) || config.default_center.lat;
                    const initialLng = parseFloat(longitudeInput.value) || config.default_center.lng;

                    map = new mapboxgl.Map({
                        container: 'locationMap',
                        style: 'mapbox://styles/mapbox/streets-v12',
                        center: [initialLng, initialLat],
                        zoom: config.default_zoom || 13
                    });

                    // Add navigation controls
                    map.addControl(new mapboxgl.NavigationControl(), 'top-right');

                    // Add marker if coordinates exist
                    if (latitudeInput.value && longitudeInput.value) {
                        addMarker(parseFloat(longitudeInput.value), parseFloat(latitudeInput.value));
                    }

                    // Allow clicking on map to set location
                    map.on('click', function (e) {
                        const lng = e.lngLat.lng;
                        const lat = e.lngLat.lat;

                        // Check if within Sta. Cruz bounds
                        if (!isWithinStaCruz(lng, lat)) {
                            alert('Please select a location within Sta. Cruz, Davao del Sur only.');
                            return;
                        }

                        addMarker(lng, lat);
                        updateCoordinates(lng, lat);
                        reverseGeocode(lng, lat);
                    });
                }

                function showMapPlaceholder() {
                    locationMapContainer.innerHTML = `
                                    <div class="location-map-placeholder">
                                        <i class="bi bi-geo-alt"></i>
                                        <span>Map unavailable. Please enter your address manually.</span>
                                    </div>
                                `;
                }

                function addMarker(lng, lat) {
                    if (marker) {
                        marker.remove();
                    }

                    marker = new mapboxgl.Marker({ color: '#667eea', draggable: true })
                        .setLngLat([lng, lat])
                        .addTo(map);

                    // Handle marker drag
                    marker.on('dragend', function () {
                        const lngLat = marker.getLngLat();
                        if (!isWithinStaCruz(lngLat.lng, lngLat.lat)) {
                            alert('Please keep the marker within Sta. Cruz, Davao del Sur.');
                            marker.setLngLat([lng, lat]);
                            return;
                        }
                        updateCoordinates(lngLat.lng, lngLat.lat);
                        reverseGeocode(lngLat.lng, lngLat.lat);
                    });

                    map.flyTo({ center: [lng, lat], zoom: 15 });
                }

                function updateCoordinates(lng, lat) {
                    longitudeInput.value = lng.toFixed(8);
                    latitudeInput.value = lat.toFixed(8);

                    // Update coordinate display
                    const coordDisplay = document.getElementById('coordinateDisplay');
                    if (coordDisplay) {
                        coordDisplay.textContent = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                    }
                }

                function isWithinStaCruz(lng, lat) {
                    if (!mapboxConfig || !mapboxConfig.stacruz_bounds) return true;

                    const bounds = mapboxConfig.stacruz_bounds;
                    return lng >= bounds.southwest[0] &&
                        lng <= bounds.northeast[0] &&
                        lat >= bounds.southwest[1] &&
                        lat <= bounds.northeast[1];
                }

                // Location search functionality
                if (locationSearch) {
                    locationSearch.addEventListener('input', function () {
                        const query = this.value.trim();

                        if (query.length < 2) {
                            hideSuggestions();
                            return;
                        }

                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(() => {
                            searchPlaces(query);
                        }, 300);
                    });

                    locationSearch.addEventListener('focus', function () {
                        if (this.value.length >= 2) {
                            searchPlaces(this.value);
                        }
                    });
                }

                // Hide suggestions when clicking outside
                document.addEventListener('click', function (e) {
                    if (!locationSearch.contains(e.target) && !locationSuggestions.contains(e.target)) {
                        hideSuggestions();
                    }
                });

                function searchPlaces(query) {
                    fetch(`/api/location/search?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.suggestions && data.suggestions.length > 0) {
                                showSuggestions(data.suggestions);
                            } else {
                                hideSuggestions();
                            }
                        })
                        .catch(error => {
                            console.error('Location search error:', error);
                            hideSuggestions();
                        });
                }

                function showSuggestions(places) {
                    locationSuggestions.innerHTML = '';

                    places.forEach(place => {
                        const div = document.createElement('div');
                        div.className = 'location-suggestion-item';
                        div.innerHTML = `
                                        <i class="bi bi-geo-alt"></i>
                                        <div class="location-suggestion-content">
                                            <div class="location-suggestion-name">${place.name || extractLocationName(place.place_name)}</div>
                                            <div class="location-suggestion-address">${place.place_name || place.full_address}</div>
                                        </div>
                                    `;

                        div.addEventListener('click', () => {
                            selectPlace(place);
                        });

                        locationSuggestions.appendChild(div);
                    });

                    locationSuggestions.style.display = 'block';
                }

                function hideSuggestions() {
                    locationSuggestions.style.display = 'none';
                }

                function selectPlace(place) {
                    const coordinates = place.geometry ? place.geometry.coordinates :
                        place.coordinates ? [place.coordinates.longitude, place.coordinates.latitude] : null;

                    if (coordinates) {
                        const lng = coordinates[0];
                        const lat = coordinates[1];

                        // Validate location is within Sta. Cruz
                        if (!isWithinStaCruz(lng, lat)) {
                            alert('Please select a location within Sta. Cruz, Davao del Sur only.');
                            return;
                        }

                        // Update search input
                        locationSearch.value = place.name || extractLocationName(place.place_name);

                        // Update coordinates
                        updateCoordinates(lng, lat);

                        // Update map and marker
                        if (map) {
                            addMarker(lng, lat);
                        }

                        // Auto-fill address fields
                        fillAddressFields(place);

                        hideSuggestions();
                    }
                }

                function fillAddressFields(place) {
                    const placeName = place.place_name || place.full_address || '';

                    // Extract and fill street address
                    const streetParts = placeName.split(',');
                    if (streetParts.length > 0) {
                        streetAddress.value = streetParts[0].trim();
                    }

                    // Set city to Sta. Cruz (from the search context)
                    if (!cityInput.value || cityInput.value.toLowerCase().includes('manila')) {
                        cityInput.value = 'Sta. Cruz';
                    }

                    // Set province
                    if (!stateInput.value || stateInput.value.toLowerCase().includes('metro')) {
                        stateInput.value = 'Davao del Sur';
                    }

                    // Set country
                    if (!countryInput.value) {
                        countryInput.value = 'Philippines';
                    }
                }

                function reverseGeocode(lng, lat) {
                    fetch(`/api/location/reverse-geocode?lng=${lng}&lat=${lat}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.features && data.features.length > 0) {
                                const feature = data.features[0];
                                locationSearch.value = extractLocationName(feature.place_name);

                                // Parse address components
                                parseAddressComponents(feature);
                            }
                        })
                        .catch(error => {
                            console.error('Reverse geocode error:', error);
                        });
                }

                function parseAddressComponents(feature) {
                    const placeName = feature.place_name || '';
                    const parts = placeName.split(',').map(p => p.trim());

                    if (parts.length > 0) {
                        streetAddress.value = parts[0];
                    }

                    // Try to extract city/province from context
                    if (feature.context) {
                        feature.context.forEach(ctx => {
                            if (ctx.id.startsWith('locality') || ctx.id.startsWith('place')) {
                                cityInput.value = ctx.text;
                            }
                            if (ctx.id.startsWith('region')) {
                                stateInput.value = ctx.text;
                            }
                            if (ctx.id.startsWith('country')) {
                                countryInput.value = ctx.text;
                            }
                        });
                    }

                    // Fallback for Sta. Cruz area
                    if (!cityInput.value) {
                        cityInput.value = 'Sta. Cruz';
                    }
                    if (!stateInput.value) {
                        stateInput.value = 'Davao del Sur';
                    }
                    if (!countryInput.value) {
                        countryInput.value = 'Philippines';
                    }
                }

                function extractLocationName(placeName) {
                    if (!placeName) return '';

                    const parts = placeName.split(',');
                    let name = parts[0].trim();

                    if (name.length < 3 || /^\d+$/.test(name)) {
                        name = parts[1] ? parts[1].trim() : name;
                    }

                    return name;
                }

                // Detect current location button
                if (detectLocationBtn) {
                    detectLocationBtn.addEventListener('click', function () {
                        if (!navigator.geolocation) {
                            alert('Geolocation is not supported by your browser.');
                            return;
                        }

                        this.innerHTML = '<i class="bi bi-hourglass-split"></i>';
                        this.disabled = true;

                        navigator.geolocation.getCurrentPosition(
                            function (position) {
                                const lat = position.coords.latitude;
                                const lng = position.coords.longitude;

                                // Check if within Sta. Cruz bounds
                                if (!isWithinStaCruz(lng, lat)) {
                                    alert('Your current location is outside Sta. Cruz, Davao del Sur. Please search for a location within the area.');
                                    detectLocationBtn.innerHTML = '<i class="bi bi-crosshairs"></i>';
                                    detectLocationBtn.disabled = false;
                                    return;
                                }

                                updateCoordinates(lng, lat);
                                if (map) {
                                    addMarker(lng, lat);
                                }
                                reverseGeocode(lng, lat);

                                detectLocationBtn.innerHTML = '<i class="bi bi-crosshairs"></i>';
                                detectLocationBtn.disabled = false;
                            },
                            function (error) {
                                let message = 'Unable to retrieve your location.';
                                switch (error.code) {
                                    case error.PERMISSION_DENIED:
                                        message = 'Location permission denied. Please enable location access.';
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        message = 'Location information is unavailable.';
                                        break;
                                    case error.TIMEOUT:
                                        message = 'Location request timed out.';
                                        break;
                                }
                                alert(message);
                                detectLocationBtn.innerHTML = '<i class="bi bi-crosshairs"></i>';
                                detectLocationBtn.disabled = false;
                            },
                            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                        );
                    });
                }
            });
        </script>
    @endpush
@endsection