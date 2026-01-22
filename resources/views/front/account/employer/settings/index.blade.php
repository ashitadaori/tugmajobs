@extends('layouts.employer')

@section('page_title', 'Settings')

@section('content')
    <!-- Account Information -->
    <div class="ep-card ep-mb-6">
        <div class="ep-card-header">
            <h3 class="ep-card-title">
                <i class="bi bi-person-circle"></i>
                Account Information
            </h3>
        </div>
        <div class="ep-card-body">
            <form method="POST" action="{{ route('employer.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="account">
                <div class="settings-grid">
                    <div class="ep-form-group">
                        <label class="ep-form-label">Full Name</label>
                        <input type="text" name="name" class="ep-form-input" value="{{ Auth::user()->name ?? '' }}"
                            required>
                    </div>
                    <div class="ep-form-group">
                        <label class="ep-form-label">Email Address</label>
                        <input type="email" name="email" class="ep-form-input" value="{{ Auth::user()->email ?? '' }}"
                            required>
                    </div>
                    <div class="ep-form-group">
                        <label class="ep-form-label">Job Title</label>
                        <input type="text" name="job_title" class="ep-form-input"
                            value="{{ $profile->contact_person_designation ?? '' }}" placeholder="e.g. HR Manager">
                    </div>
                    <div class="ep-form-group">
                        <label class="ep-form-label">Phone Number</label>
                        <input type="tel" name="phone" class="ep-form-input" value="{{ $profile->business_phone ?? '' }}"
                            placeholder="e.g. +63 912 345 6789">
                    </div>
                </div>
                <button type="submit" class="ep-btn ep-btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    <!-- Preferences -->
    <div class="ep-card ep-mb-6">
        <div class="ep-card-header">
            <h3 class="ep-card-title">
                <i class="bi bi-sliders"></i>
                Preferences
            </h3>
        </div>
        <div class="ep-card-body">
            <form method="POST" action="{{ route('employer.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="preferences">
                <div class="settings-grid">
                    <div class="ep-form-group">
                        <label class="ep-form-label">Time Zone</label>
                        <select name="timezone" class="ep-form-select">
                            <option value="Asia/Manila" {{ ($profile->settings['timezone'] ?? 'Asia/Manila') == 'Asia/Manila' ? 'selected' : '' }}>Philippine Time (UTC+8)</option>
                            <option value="Asia/Singapore" {{ ($profile->settings['timezone'] ?? '') == 'Asia/Singapore' ? 'selected' : '' }}>Singapore Time (UTC+8)</option>
                            <option value="Asia/Hong_Kong" {{ ($profile->settings['timezone'] ?? '') == 'Asia/Hong_Kong' ? 'selected' : '' }}>Hong Kong Time (UTC+8)</option>
                            <option value="UTC" {{ ($profile->settings['timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC
                                (UTC+0)</option>
                        </select>
                    </div>
                    <div class="ep-form-group">
                        <label class="ep-form-label">Language</label>
                        <select name="language" class="ep-form-select">
                            <option value="en" {{ ($profile->settings['language'] ?? 'en') == 'en' ? 'selected' : '' }}>
                                English</option>
                            <option value="fil" {{ ($profile->settings['language'] ?? '') == 'fil' ? 'selected' : '' }}>
                                Filipino</option>
                        </select>
                    </div>
                    <div class="ep-form-group">
                        <label class="ep-form-label">Date Format</label>
                        <select name="date_format" class="ep-form-select">
                            <option value="MM/DD/YYYY" {{ ($profile->settings['date_format'] ?? 'MM/DD/YYYY') == 'MM/DD/YYYY' ? 'selected' : '' }}>MM/DD/YYYY</option>
                            <option value="DD/MM/YYYY" {{ ($profile->settings['date_format'] ?? '') == 'DD/MM/YYYY' ? 'selected' : '' }}>DD/MM/YYYY</option>
                            <option value="YYYY-MM-DD" {{ ($profile->settings['date_format'] ?? '') == 'YYYY-MM-DD' ? 'selected' : '' }}>YYYY-MM-DD</option>
                        </select>
                    </div>
                    <div class="ep-form-group">
                        <label class="ep-form-label">Currency</label>
                        <select name="currency" class="ep-form-select">
                            <option value="PHP" {{ ($profile->settings['currency'] ?? 'PHP') == 'PHP' ? 'selected' : '' }}>PHP
                                (₱)</option>
                            <option value="USD" {{ ($profile->settings['currency'] ?? '') == 'USD' ? 'selected' : '' }}>USD
                                ($)</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="ep-btn ep-btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Save Preferences
                </button>
            </form>
        </div>
    </div>

    <!-- Application Settings -->
    <div class="ep-card ep-mb-6">
        <div class="ep-card-header">
            <h3 class="ep-card-title">
                <i class="bi bi-file-earmark-text"></i>
                Application Settings
            </h3>
        </div>
        <div class="ep-card-body">
            <form method="POST" action="{{ route('employer.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="application">
                <div class="settings-grid">
                    <div class="ep-form-group">
                        <label class="ep-form-label">Auto-close jobs after</label>
                        <select name="auto_close_days" class="ep-form-select">
                            <option value="30" {{ ($profile->settings['auto_close_days'] ?? '30') == '30' ? 'selected' : '' }}>30 days</option>
                            <option value="60" {{ ($profile->settings['auto_close_days'] ?? '') == '60' ? 'selected' : '' }}>
                                60 days</option>
                            <option value="90" {{ ($profile->settings['auto_close_days'] ?? '') == '90' ? 'selected' : '' }}>
                                90 days</option>
                            <option value="never" {{ ($profile->settings['auto_close_days'] ?? '') == 'never' ? 'selected' : '' }}>Never</option>
                        </select>
                    </div>
                    <div class="ep-form-group">
                        <label class="ep-form-label">Maximum applications per job</label>
                        <input type="number" name="max_applications" class="ep-form-input"
                            value="{{ $profile->settings['max_applications'] ?? '100' }}" min="1" max="1000">
                    </div>
                </div>

                <div class="checkbox-options">
                    <label class="checkbox-option">
                        <input type="checkbox" name="require_cover_letter" id="requireCoverLetter" {{ ($profile->settings['require_cover_letter'] ?? true) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <span class="checkbox-label">Require cover letter for all applications</span>
                    </label>
                    <label class="checkbox-option">
                        <input type="checkbox" name="auto_reply" id="autoReply" {{ ($profile->settings['auto_reply'] ?? true) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        <span class="checkbox-label">Send automatic reply to applicants</span>
                    </label>
                </div>

                <button type="submit" class="ep-btn ep-btn-primary" style="margin-top: 20px;">
                    <i class="bi bi-check-circle"></i>
                    Save Settings
                </button>
            </form>
        </div>

    </div>

    <!-- Deactivate Account Modal -->
    
    @push('styles')
        <style>
            .settings-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: var(--ep-space-4);
                margin-bottom: var(--ep-space-5);
            }

            /* Toggle Switches */
            .settings-switches {
                display: flex;
                flex-direction: column;
                gap: var(--ep-space-4);
            }

            .settings-switch {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: var(--ep-space-4);
                background: var(--ep-gray-50);
                border-radius: var(--ep-radius-lg);
                cursor: pointer;
                transition: all var(--ep-transition-base);
            }

            .settings-switch:hover {
                background: var(--ep-primary-50);
            }

            .switch-content {
                flex: 1;
            }

            .switch-title {
                font-weight: 600;
                color: var(--ep-gray-800);
                margin-bottom: 4px;
            }

            .switch-description {
                font-size: var(--ep-font-size-sm);
                color: var(--ep-gray-500);
            }

            .switch-toggle {
                position: relative;
                width: 52px;
                height: 28px;
                flex-shrink: 0;
                margin-left: var(--ep-space-4);
            }

            .switch-toggle input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .toggle-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: var(--ep-gray-300);
                transition: 0.3s;
                border-radius: 28px;
            }

            .toggle-slider:before {
                position: absolute;
                content: "";
                height: 22px;
                width: 22px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: 0.3s;
                border-radius: 50%;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .switch-toggle input:checked+.toggle-slider {
                background-color: var(--ep-primary);
            }

            .switch-toggle input:checked+.toggle-slider:before {
                transform: translateX(24px);
            }

            /* Checkbox Options */
            .checkbox-options {
                display: flex;
                flex-direction: column;
                gap: var(--ep-space-3);
            }

            .checkbox-option {
                display: flex;
                align-items: center;
                gap: var(--ep-space-3);
                cursor: pointer;
                padding: var(--ep-space-3) 0;
            }

            .checkbox-option input {
                display: none;
            }

            .checkmark {
                width: 22px;
                height: 22px;
                border: 2px solid var(--ep-gray-300);
                border-radius: var(--ep-radius-sm);
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                transition: all var(--ep-transition-base);
            }

            .checkbox-option input:checked+.checkmark {
                background: var(--ep-primary);
                border-color: var(--ep-primary);
            }

            .checkbox-option input:checked+.checkmark::after {
                content: '\2713';
                color: white;
                font-size: 12px;
                font-weight: bold;
            }

            .checkbox-label {
                font-size: var(--ep-font-size-sm);
                color: var(--ep-gray-700);
            }

            /* Responsive */
            @media (max-width: 768px) {
                .settings-grid {
                    grid-template-columns: 1fr;
                }

                .settings-switch {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: var(--ep-space-3);
                }

                .switch-toggle {
                    margin-left: 0;
                }

                .danger-action {
                    flex-direction: column;
                    align-items: flex-start;
                }
            }
        </style>
    @endpush
@endsection
