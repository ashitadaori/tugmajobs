@extends('layouts.employer')

@section('page_title', 'Post New Job')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Enhanced Job Creation Form Styling */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --card-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            --card-shadow-hover: 0 20px 60px rgba(0, 0, 0, 0.12);
            --border-radius-lg: 20px;
            --border-radius-md: 12px;
            --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .job-form-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 20px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* Enhanced Page Header */
        .job-create-header {
            background: var(--primary-gradient);
            border-radius: var(--border-radius-lg);
            padding: 2.5rem;
            color: white;
            margin-bottom: 2rem;
            box-shadow: 0 15px 50px rgba(102, 126, 234, 0.35);
            position: relative;
            overflow: hidden;
        }

        .job-create-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .job-create-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        .page-title {
            font-size: 2.25rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }

        .icon-circle {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.75rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 1;
            flex-shrink: 0;
        }

        .icon-circle i {
            font-size: 1.75rem;
            line-height: 1;
        }

        /* Back to Jobs Button */
        .btn-back-jobs {
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 24px;
            border-radius: var(--border-radius-md);
            font-weight: 600;
            transition: var(--transition-smooth);
            backdrop-filter: blur(10px);
        }

        .btn-back-jobs:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
            transform: translateY(-2px);
        }

        .stat-item {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.15);
            padding: 10px 18px;
            border-radius: 30px;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
        }

        .stat-item i {
            color: white !important;
            opacity: 0.9;
        }

        .stat-item span {
            color: rgba(255, 255, 255, 0.95) !important;
            font-weight: 500;
        }

        .quick-stats {
            margin-top: 1.5rem;
        }

        /* Enhanced Progress Steps */
        .progress-card {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--card-shadow);
            padding: 0;
            border: none;
            overflow: hidden;
        }

        .progress-card .card-body {
            padding: 30px 40px;
        }

        .progress-container {
            background: transparent;
            border-radius: 0;
            box-shadow: none;
            padding: 0;
        }

        .progress {
            height: 6px !important;
            border-radius: 3px;
            background: #e9ecef;
            overflow: visible;
            margin-bottom: 2rem;
        }

        .progress-bar {
            background: var(--primary-gradient) !important;
            border-radius: 3px;
            position: relative;
            transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            right: -6px;
            top: -5px;
            width: 16px;
            height: 16px;
            background: #667eea;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.4);
        }

        .progress-steps-wrapper {
            display: flex;
            justify-content: space-between;
            position: relative;
        }

        .progress-step {
            text-align: center;
            cursor: pointer;
            transition: var(--transition-smooth);
            flex: 1;
            position: relative;
        }

        .progress-step::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 50%;
            right: -50%;
            height: 3px;
            background: #e9ecef;
            z-index: 0;
        }

        .progress-step:last-child::before {
            display: none;
        }

        .progress-step.completed::before {
            background: var(--primary-gradient);
        }

        .progress-step .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0 auto 12px;
            transition: var(--transition-smooth);
            border: 3px solid #e9ecef;
            position: relative;
            z-index: 1;
        }

        .progress-step.active .step-circle {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .progress-step.completed .step-circle {
            background: var(--success-gradient);
            color: white;
            border-color: transparent;
            box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
        }

        .progress-step.completed .step-circle::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .progress-step .step-label {
            color: #6c757d;
            font-weight: 500;
            font-size: 0.9rem;
            transition: var(--transition-smooth);
        }

        .progress-step.active .step-label,
        .progress-step.completed .step-label {
            color: #333;
            font-weight: 600;
        }

        /* Form Card */
        .job-form-card {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--card-shadow);
            border: none;
            transition: var(--transition-smooth);
        }

        .job-form-card:hover {
            box-shadow: var(--card-shadow-hover);
        }

        .job-form-card>.card-body {
            padding: 45px;
        }

        /* Section Headers */
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .section-header .section-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
            border-radius: var(--border-radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        .section-header .section-icon i {
            font-size: 1.25rem;
            color: #667eea;
        }

        .section-header h4 {
            margin: 0;
            font-weight: 700;
            color: #1a1a2e;
            font-size: 1.5rem;
        }

        .section-header p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* Wizard Sections */
        .wizard-section {
            display: none;
            animation: slideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .wizard-section:first-child {
            display: block;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 10px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }

        .form-label i {
            margin-right: 8px;
            color: #667eea;
            font-size: 0.9rem;
        }

        .form-label .required-badge {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a5a 100%);
            color: white;
            font-size: 0.65rem;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control,
        .form-select {
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-md);
            padding: 14px 18px;
            font-size: 0.95rem;
            transition: var(--transition-smooth);
            background-color: #fafbfc;
        }

        .form-control:hover,
        .form-select:hover {
            border-color: #d0d5dd;
            background-color: white;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            background-color: white;
        }

        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1);
        }

        .invalid-feedback {
            display: flex;
            align-items: center;
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 8px;
            padding: 8px 12px;
            background: #fff5f5;
            border-radius: 8px;
        }

        .invalid-feedback::before {
            content: '\f06a';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-right: 8px;
        }

        /* Input Helper Text */
        .form-text {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 8px;
            display: flex;
            align-items: center;
        }

        .form-text i {
            margin-right: 6px;
            color: #667eea;
        }

        /* Enhanced Buttons */
        .btn {
            padding: 14px 28px;
            border-radius: var(--border-radius-md);
            font-weight: 600;
            transition: var(--transition-smooth);
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn i {
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #5a72d4 0%, #6a4190 100%);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-outline-secondary {
            border: 2px solid #e0e0e0;
            color: #555;
            background: transparent;
        }

        .btn-outline-secondary:hover {
            background: #f8f9fa;
            border-color: #d0d0d0;
            color: #333;
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--success-gradient);
            border: none;
            color: white;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(17, 153, 142, 0.4);
        }

        .btn-lg {
            padding: 16px 36px;
            font-size: 1.05rem;
        }

        /* Enhanced Skills Tags */
        .skill-tag {
            background: linear-gradient(135deg, #e8f4fd 0%, #d4e8f9 100%);
            color: #1976d2;
            border: 1px solid #bbdefb;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            margin: 4px;
            font-weight: 500;
            transition: var(--transition-smooth);
        }

        .skill-tag:hover {
            background: linear-gradient(135deg, #d4e8f9 0%, #c4daf5 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(25, 118, 210, 0.2);
        }

        .skill-tag .btn-close,
        .skill-tag .remove-skill {
            font-size: 0.7rem;
            margin-left: 10px;
            opacity: 0.7;
            transition: var(--transition-smooth);
            background: none;
            border: none;
            color: #1976d2;
            cursor: pointer;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .skill-tag .btn-close:hover,
        .skill-tag .remove-skill:hover {
            opacity: 1;
            background: rgba(25, 118, 210, 0.2);
        }

        /* Character Counter */
        .character-counter {
            text-align: right;
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 8px;
            padding: 4px 8px;
            background: #f8f9fa;
            border-radius: 6px;
            display: inline-block;
            float: right;
        }

        .character-counter.warning {
            color: #f39c12;
            background: #fff8e6;
        }

        .character-counter.text-danger {
            color: #dc3545 !important;
            background: #fff5f5;
        }

        /* Enhanced Location Input */
        .location-input-wrapper {
            position: relative;
        }

        .location-input-wrapper .input-group {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border-radius: var(--border-radius-md);
            overflow: hidden;
        }

        .location-input-wrapper .form-control {
            border-right: none;
            border-radius: var(--border-radius-md) 0 0 var(--border-radius-md);
        }

        /* Location Input Group */
        .location-input-group {
            border-radius: var(--border-radius-md);
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .location-input-group .form-control {
            border-right: none;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .btn-location {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 14px 20px;
            transition: var(--transition-smooth);
            white-space: nowrap;
        }

        .btn-location:hover {
            background: linear-gradient(135deg, #5a72d4 0%, #6a4190 100%);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-location:active {
            transform: translateY(0);
        }

        .btn-location:disabled {
            opacity: 0.7;
            transform: none !important;
        }

        #use-current-location {
            border: 2px solid #e9ecef;
            border-left: none;
            background: white;
            color: #667eea;
            font-weight: 500;
            padding: 14px 20px;
            transition: var(--transition-smooth);
        }

        #use-current-location:hover {
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
            color: #5a72d4;
        }

        /* Mapbox Location Autocomplete Styles */
        .location-autocomplete-wrapper {
            position: relative;
        }

        .location-suggestions {
            position: absolute;
            top: calc(100% + 5px);
            left: 0;
            right: 0;
            z-index: 1050;
            background: white;
            border: none;
            border-radius: var(--border-radius-md);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            max-height: 350px;
            overflow-y: auto;
            display: none;
        }

        .location-suggestions.show {
            display: block;
            animation: dropdownFade 0.3s ease;
        }

        @keyframes dropdownFade {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .location-suggestion-item {
            padding: 14px 18px;
            cursor: pointer;
            border-bottom: 1px solid #f5f5f5;
            transition: var(--transition-smooth);
            display: flex;
            align-items: flex-start;
        }

        .location-suggestion-item:last-child {
            border-bottom: none;
        }

        .location-suggestion-item:hover,
        .location-suggestion-item.active {
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
        }

        .location-suggestion-item .suggestion-name {
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 4px;
        }

        .location-suggestion-item .suggestion-address {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .location-suggestion-item .suggestion-icon {
            color: #667eea;
            margin-right: 12px;
            font-size: 1.1rem;
            margin-top: 2px;
        }

        .location-loading {
            padding: 20px;
            text-align: center;
            color: #667eea;
        }

        .location-loading i {
            margin-right: 10px;
        }

        .location-no-results {
            padding: 20px;
            text-align: center;
            color: #6c757d;
        }

        /* Location Notification Styles */
        .location-notification {
            position: relative !important;
            margin-top: 10px !important;
            border-radius: var(--border-radius-md) !important;
            padding: 12px 16px !important;
            font-size: 0.9rem;
            animation: slideDown 0.3s ease;
        }

        .location-notification.alert-danger {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%) !important;
            color: #c62828 !important;
            border: 1px solid #ffcdd2 !important;
        }

        .location-notification.alert-success {
            background: linear-gradient(135deg, #e8f9ed 0%, #d4f5df 100%) !important;
            color: #1b5e20 !important;
            border: 1px solid #c8e6c9 !important;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Highlight matched text in suggestions */
        .location-suggestion-item .highlight {
            background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);
            padding: 1px 4px;
            border-radius: 4px;
            font-weight: 600;
        }

        /* Enhanced Preview Section */
        .job-preview {
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
            border-radius: var(--border-radius-lg);
            padding: 30px;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .job-preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary-gradient);
        }

        .job-preview-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid rgba(102, 126, 234, 0.1);
        }

        .job-preview h5 {
            color: #1a1a2e;
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 8px;
        }

        .job-preview .preview-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .job-preview .preview-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .job-preview .preview-meta-item i {
            color: #667eea;
        }

        .job-preview h6 {
            color: #1a1a2e;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .job-preview h6 i {
            color: #667eea;
            font-size: 0.9rem;
        }

        .job-preview .preview-content {
            color: #555;
            line-height: 1.7;
            white-space: pre-line;
        }

        .job-preview .preview-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: white;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
        }

        /* Enhanced Alert Styling */
        .alert {
            border-radius: var(--border-radius-md);
            border: none;
            padding: 18px 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .alert i {
            font-size: 1.2rem;
            margin-top: 2px;
        }

        .alert-info {
            background: linear-gradient(135deg, #e8f4fd 0%, #d4e8f9 100%);
            color: #1565c0;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%);
            color: #c62828;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);
            color: #856404;
        }

        .alert-success {
            background: linear-gradient(135deg, #e8f9ed 0%, #d4f5df 100%);
            color: #1b5e20;
        }

        /* Form Row Enhancements */
        .form-row {
            background: #fafbfc;
            border-radius: var(--border-radius-md);
            padding: 20px;
            margin-bottom: 1.5rem;
            border: 1px solid #f0f0f0;
        }

        .form-row-header {
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }

        /* Toggle Switch Enhancement */
        .form-check-input {
            width: 50px;
            height: 26px;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .form-check-label {
            cursor: pointer;
            font-weight: 500;
            color: #333;
        }

        /* Option Cards for Checkboxes */
        .option-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius-md);
            padding: 18px 20px;
            transition: var(--transition-smooth);
            cursor: pointer;
        }

        .option-card:hover {
            border-color: #667eea;
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
        }

        .option-card.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
        }

        .option-card .option-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .option-card .option-icon i {
            color: #667eea;
            font-size: 1.1rem;
        }

        .option-card.selected .option-icon {
            background: var(--primary-gradient);
        }

        .option-card.selected .option-icon i {
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .job-form-container {
                padding: 15px;
            }

            .job-create-header {
                padding: 1.5rem;
            }

            .job-form-card>.card-body {
                padding: 25px;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .icon-circle {
                width: 55px;
                height: 55px;
                font-size: 1.25rem;
            }

            .progress-step .step-circle {
                width: 40px;
                height: 40px;
                font-size: 0.95rem;
            }

            .progress-step .step-label {
                font-size: 0.75rem;
            }

            .btn {
                padding: 12px 20px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .job-form-card>.card-body {
                padding: 20px;
            }

            .progress-card .card-body {
                padding: 20px;
            }

            .quick-stats {
                flex-direction: column;
                gap: 10px !important;
            }

            .section-header {
                flex-direction: column;
                text-align: center;
            }

            .section-header .section-icon {
                margin-right: 0;
                margin-bottom: 1rem;
            }

            .wizard-navigation {
                flex-direction: column;
                gap: 1rem;
            }

            .wizard-navigation .btn {
                width: 100%;
            }
        }

        /* Loading State */
        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }

        .fa-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Spin Animation for Bootstrap Icons */
        .spin-animation {
            animation: spin 1s linear infinite;
            display: inline-block;
        }

        /* Floating Labels Effect */
        .floating-label-group {
            position: relative;
        }

        .floating-label-group label {
            position: absolute;
            top: 14px;
            left: 18px;
            color: #6c757d;
            transition: var(--transition-smooth);
            pointer-events: none;
            background: white;
            padding: 0 4px;
        }

        .floating-label-group input:focus+label,
        .floating-label-group input:not(:placeholder-shown)+label {
            top: -10px;
            font-size: 0.8rem;
            color: #667eea;
        }

        /* Preliminary Questions Styling */
        .question-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
        }

        .question-item .question-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 10px;
        }

        .question-item .question-number {
            background: #007bff;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
            margin-right: 10px;
        }

        .question-item .remove-question {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .question-item .remove-question:hover {
            background: #c82333;
            transform: scale(1.1);
        }

        .question-type-selector {
            margin-bottom: 10px;
        }

        .question-type-selector select {
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 0.9rem;
        }

        .question-input textarea,
        .question-input input {
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 0.9rem;
            width: 100%;
            margin-bottom: 8px;
        }

        .question-options {
            margin-top: 10px;
        }

        .option-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .option-item input {
            flex: 1;
            margin-right: 10px;
            margin-bottom: 0;
        }

        .option-item .remove-option {
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 0.7rem;
            cursor: pointer;
        }

        .add-option-btn {
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            font-size: 0.8rem;
            cursor: pointer;
            margin-top: 5px;
        }

        .add-option-btn:hover {
            background: #218838;
        }

        #preliminary_questions_container {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
        }

        #preliminary_questions_container.active {
            border-color: #007bff;
            background-color: #f8f9ff;
        }

        /* Job Requirements Styling */
        .requirement-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
        }

        .requirement-item .remove-requirement {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .requirement-item .remove-requirement:hover {
            background: #c82333;
            transform: scale(1.1);
        }

        #job_requirements_container {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            min-height: 60px;
        }

        #job_requirements_container:empty::before {
            content: "No document requirements added yet. Click the button below to add requirements.";
            color: #6c757d;
            font-style: italic;
            display: block;
            text-align: center;
            padding: 10px;
        }
    </style>
@endpush

@section('content')
    <div class="job-form-container">
        <!-- Maintenance Notice -->
        @if(\App\Models\MaintenanceSetting::isMaintenanceActive('employer'))
            <div class="alert alert-warning text-center mb-4">
                <i class="bi bi-exclamation-triangle-fill fs-1 d-block mb-3"></i>
                <h4>Feature Under Maintenance</h4>
                <p class="mb-3">{{ \App\Models\MaintenanceSetting::getMaintenanceMessage('employer') }}</p>
                <a href="{{ route('employer.dashboard') }}" class="btn btn-primary">
                    <i class="bi bi-house-door me-2"></i>Return to Dashboard
                </a>
            </div>
        @else
            <!-- Enhanced Page Header -->
            <div class="job-create-header mb-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle me-3">
                                <i class="bi bi-briefcase-fill"></i>
                            </div>
                            <div>
                                <h1 class="page-title mb-1">Create New Job Posting</h1>
                                <p class="page-subtitle mb-0">Find the perfect candidates for your company</p>
                            </div>
                        </div>
                        <div class="quick-stats d-flex gap-3 flex-wrap">
                            <div class="stat-item">
                                <i class="bi bi-clock me-2"></i>
                                <span>~5 minutes to complete</span>
                            </div>
                            <div class="stat-item">
                                <i class="bi bi-people-fill me-2"></i>
                                <span>Reach 1000+ candidates</span>
                            </div>
                            <div class="stat-item">
                                <i class="bi bi-shield-check me-2"></i>
                                <span>Admin reviewed</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="{{ route('employer.jobs.index') }}" class="btn btn-back-jobs">
                            <i class="bi bi-arrow-left me-2"></i>Back to Jobs
                        </a>
                    </div>
                </div>
            </div>

            <!-- Enhanced Progress Indicator -->
            <div class="card progress-card mb-4">
                <div class="card-body">
                    <div class="progress-container">
                        <div class="progress mb-4" style="height: 6px;">
                            <div class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0"
                                aria-valuemax="100"></div>
                        </div>
                        <div class="progress-steps-wrapper">
                            <div class="progress-step active" data-step="0">
                                <div class="step-circle">1</div>
                                <span class="step-label">Basic Info</span>
                            </div>
                            <div class="progress-step" data-step="1">
                                <div class="step-circle">2</div>
                                <span class="step-label">Details</span>
                            </div>
                            <div class="progress-step" data-step="2">
                                <div class="step-circle">3</div>
                                <span class="step-label">Qualifications</span>
                            </div>
                            <div class="progress-step" data-step="3">
                                <div class="step-circle">4</div>
                                <span class="step-label">Review</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Job Form -->
            <div class="card job-form-card">
                <div class="card-body">
                    <form id="jobForm" action="{{ route('employer.jobs.store') }}" method="POST" enctype="multipart/form-data"
                        autocomplete="off">
                        @csrf

                        <!-- Global Error Display -->
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif



                        <!-- Step 1: Basic Information -->
                        <div class="wizard-section" id="step-1">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div>
                                    <h4>Basic Job Information</h4>
                                    <p>Start with the essential details about your job opening</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="mb-4">
                                        <label for="title" class="form-label">
                                            <i class="fas fa-heading"></i> Job Title
                                            <span class="required-badge">Required</span>
                                        </label>
                                        <input type="text" class="form-control" id="title" name="title" required
                                            placeholder="e.g. Senior Software Developer, Marketing Manager"
                                            value="{{ old('title') }}">
                                        <div class="form-text">
                                            <i class="fas fa-lightbulb"></i> Use a clear, descriptive title that candidates will
                                            search for
                                        </div>
                                        @error('title')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-4">
                                        <label for="job_type_id" class="form-label">
                                            <i class="fas fa-clock"></i> Job Type
                                            <span class="required-badge">Required</span>
                                        </label>
                                        <select class="form-select" id="job_type_id" name="job_type_id" required>
                                            <option value="">Select Job Type</option>
                                            @if(isset($jobTypes) && $jobTypes->count() > 0)
                                                @foreach($jobTypes as $jobType)
                                                    <option value="{{ $jobType->id }}" {{ old('job_type_id') == $jobType->id ? 'selected' : '' }}>
                                                        {{ $jobType->name }}
                                                    </option>
                                                @endforeach
                                            @else
                                                <option value="" disabled>No job types available</option>
                                            @endif
                                        </select>
                                        @error('job_type_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-4">
                                        <label for="category_id" class="form-label">
                                            <i class="fas fa-folder"></i> Category
                                            <span class="required-badge">Required</span>
                                        </label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            @if(isset($categories) && $categories->count() > 0)
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            @else
                                                <option value="" disabled>No categories available</option>
                                            @endif
                                        </select>
                                        @error('category_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-4">
                                        <label for="vacancy" class="form-label">
                                            <i class="fas fa-user-plus"></i> Number of Positions
                                            <span class="required-badge">Required</span>
                                        </label>
                                        <input type="number" class="form-control" id="vacancy" name="vacancy" required min="1"
                                            max="100" value="{{ old('vacancy', 1) }}">
                                        <div class="form-text">
                                            <i class="fas fa-info-circle"></i> How many people do you want to hire?
                                        </div>
                                        @error('vacancy')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> Job Location
                                    <span class="required-badge">Required</span>
                                </label>

                                <!-- Mapbox Search -->
                                <div class="location-autocomplete-wrapper mb-3">
                                    <div class="input-group location-input-group">
                                        <span class="input-group-text bg-white border-end-0"><i
                                                class="bi bi-search text-muted"></i></span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="locationSearch"
                                            placeholder="Search for business location in Sta. Cruz..." autocomplete="off">
                                        <button class="btn btn-light border" type="button" id="detectLocationBtn"
                                            title="Use current location">
                                            <i class="bi bi-crosshair"></i>
                                        </button>
                                    </div>
                                    <div class="location-suggestions" id="locationSuggestions" style="display: none;"></div>
                                </div>

                                <!-- Map Container -->
                                <div id="locationMap" class="mb-3"
                                    style="height: 300px; border-radius: 8px; border: 2px solid #e9ecef;"></div>

                                <!-- Hidden Coordinates -->
                                <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
                                <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">

                                <!-- Location Details Grid -->
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">City</label>
                                        <input type="text" class="form-control bg-light" id="cityInput" name="city" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Province</label>
                                        <input type="text" class="form-control bg-light" id="stateInput" name="province"
                                            readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Country</label>
                                        <input type="text" class="form-control bg-light" id="countryInput" name="country"
                                            value="Philippines" readonly>
                                    </div>
                                </div>

                                <!-- Full Address (The main field validated by controller) -->
                                <div class="mb-2">
                                    <label for="location" class="form-label small text-muted">Full Street Address</label>
                                    <textarea class="form-control" id="streetAddress" name="location" rows="2"
                                        placeholder="Specific address will appear here..." readonly
                                        required>{{ old('location') }}</textarea>
                                </div>

                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i> Search and select a location from the map to
                                    auto-fill details.
                                </div>
                                @error('location')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Work Options -->
                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <div class="option-card {{ old('is_remote') ? 'selected' : '' }}"
                                        onclick="toggleOptionCard(this, 'is_remote')">
                                        <div class="d-flex align-items-start">
                                            <div class="option-icon me-3">
                                                <i class="fas fa-home"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="mb-1" style="font-weight: 600; color: #1a1a2e;">Remote Work</h6>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" id="is_remote"
                                                            name="is_remote" value="1" {{ old('is_remote') ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                                <small class="text-muted">Allow candidates to work from home</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="option-card {{ old('is_featured') ? 'selected' : '' }}"
                                        onclick="toggleOptionCard(this, 'is_featured')">
                                        <div class="d-flex align-items-start">
                                            <div class="option-icon me-3">
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="mb-1" style="font-weight: 600; color: #1a1a2e;">Featured Job</h6>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" id="is_featured"
                                                            name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                                <small class="text-muted">Highlight this job to attract more candidates</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end wizard-navigation">
                                <button type="button" class="btn btn-primary btn-next-step btn-lg">
                                    Continue to Details <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Job Details -->
                        <div class="wizard-section" id="step-2" style="display: none;">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <h4>Job Details</h4>
                                    <p>Provide comprehensive information about the position</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left"></i> Job Description
                                    <span class="required-badge">Required</span>
                                </label>
                                <textarea class="form-control" id="description" name="description" rows="8" required
                                    maxlength="5000"
                                    placeholder="Describe the role, responsibilities, and what makes this position exciting...">{{ old('description') }}</textarea>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="form-text mb-0">
                                        <i class="fas fa-lightbulb"></i> Include responsibilities, team structure, and growth
                                        opportunities
                                    </div>
                                    <div class="character-counter"></div>
                                </div>
                                @error('description')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-4">
                                        <label for="experience_level" class="form-label">
                                            <i class="fas fa-chart-line"></i> Experience Level
                                            <span class="required-badge">Required</span>
                                        </label>
                                        <select class="form-select" id="experience_level" name="experience_level" required>
                                            <option value="">Select Experience Level</option>
                                            <option value="entry" {{ old('experience_level') == 'entry' ? 'selected' : '' }}>Entry
                                                Level (0-2 years)</option>
                                            <option value="intermediate" {{ old('experience_level') == 'intermediate' ? 'selected' : '' }}>Intermediate Level (3-5 years)</option>
                                            <option value="expert" {{ old('experience_level') == 'expert' ? 'selected' : '' }}>
                                                Expert Level (6+ years)</option>
                                        </select>
                                        @error('experience_level')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-4">
                                        <label for="education_level" class="form-label">
                                            <i class="fas fa-graduation-cap"></i> Education Level
                                        </label>
                                        <select class="form-select" id="education_level" name="education_level">
                                            <option value="">Select Education Level (Optional)</option>
                                            <option value="high_school" {{ old('education_level') == 'high_school' ? 'selected' : '' }}>High School</option>
                                            <option value="vocational" {{ old('education_level') == 'vocational' ? 'selected' : '' }}>Vocational/Technical</option>
                                            <option value="associate" {{ old('education_level') == 'associate' ? 'selected' : '' }}>Associate Degree</option>
                                            <option value="bachelor" {{ old('education_level') == 'bachelor' ? 'selected' : '' }}>
                                                Bachelor's Degree</option>
                                            <option value="master" {{ old('education_level') == 'master' ? 'selected' : '' }}>
                                                Master's Degree</option>
                                            <option value="doctorate" {{ old('education_level') == 'doctorate' ? 'selected' : '' }}>Doctorate</option>
                                        </select>
                                        @error('education_level')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Salary Section -->
                            <div class="form-row mb-4">
                                <div class="form-row-header">
                                    <i class="fas fa-money-bill-wave me-2" style="color: #667eea;"></i> Salary Range (PHP per
                                    month)
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <label class="form-label small text-muted mb-2">Minimum Salary</label>
                                        <div class="input-group">
                                            <span class="input-group-text"
                                                style="background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%); border-color: #e9ecef; color: #667eea; font-weight: 600;">₱</span>
                                            <input type="number" class="form-control" id="salary_min" name="salary_min"
                                                placeholder="15,000" value="{{ old('salary_min') }}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label small text-muted mb-2">Maximum Salary</label>
                                        <div class="input-group">
                                            <span class="input-group-text"
                                                style="background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%); border-color: #e9ecef; color: #667eea; font-weight: 600;">₱</span>
                                            <input type="number" class="form-control" id="salary_max" name="salary_max"
                                                placeholder="50,000" value="{{ old('salary_max') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text mt-3">
                                    <i class="fas fa-info-circle"></i> Displaying salary range helps attract qualified
                                    candidates. Leave blank to hide salary.
                                </div>
                                @error('salary_min')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @error('salary_max')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="deadline" class="form-label">
                                    <i class="fas fa-calendar-alt"></i> Application Deadline
                                </label>
                                <input type="date" class="form-control" id="deadline" name="deadline" min="{{ date('Y-m-d') }}"
                                    value="{{ old('deadline') }}" style="max-width: 300px;">
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> Leave blank for no deadline - applications will be
                                    accepted indefinitely
                                </div>
                                @error('deadline')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between wizard-navigation">
                                <button type="button" class="btn btn-outline-secondary btn-prev-step btn-lg">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Basic Info
                                </button>
                                <button type="button" class="btn btn-primary btn-next-step btn-lg">
                                    Continue to Qualifications <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Qualifications & Benefits -->
                        <div class="wizard-section" id="step-3" style="display: none;">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-award"></i>
                                </div>
                                <div>
                                    <h4>Qualifications & Benefits</h4>
                                    <p>Define what you're looking for and what you offer</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="qualifications" class="form-label">
                                    <i class="fas fa-check-circle"></i> Qualifications
                                    <span class="required-badge">Required</span>
                                </label>
                                <div class="form-text mb-2">
                                    <i class="fas fa-lightbulb"></i> List the qualifications required for this position (one per
                                    line)
                                </div>
                                <textarea class="form-control" id="qualifications" name="qualifications" rows="8" required
                                    maxlength="3000" placeholder="- Graduate of a 4-year BUSINESS-related course (preferably Accountancy)
                                            - With experience as an advantage, or without experience as long as trainable
                                            - Knowledge in accounting and business management
                                            - Has keen attention to detail and paperwork
                                            - Good communication skills
                                            - Honest and trustworthy">{{ old('qualifications') }}</textarea>
                                <div class="d-flex justify-content-end mt-2">
                                    <div class="character-counter"></div>
                                </div>
                                @error('qualifications')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="requirements" class="form-label">
                                    <i class="fas fa-clipboard-list"></i> Additional Requirements
                                </label>
                                <div class="form-text mb-2">
                                    <i class="fas fa-info-circle"></i> Any special requirements (licenses, certifications,
                                    physical requirements, etc.)
                                </div>
                                <textarea class="form-control" id="requirements" name="requirements" rows="4" maxlength="2000"
                                    placeholder="- Must have own motorcycle (with driver's license)
                                            - Must be willing to be assigned for field work
                                            - With valid professional driver's license">{{ old('requirements') }}</textarea>
                                <div class="d-flex justify-content-end mt-2">
                                    <div class="character-counter"></div>
                                </div>
                                @error('requirements')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="benefits" class="form-label">
                                    <i class="fas fa-gift"></i> Benefits & Perks
                                </label>
                                <div class="form-text mb-2">
                                    <i class="fas fa-star"></i> Highlight what makes working at your company great
                                </div>
                                <textarea class="form-control" id="benefits" name="benefits" rows="4" maxlength="2000"
                                    placeholder="- Competitive salary
                                            - Health insurance
                                            - 13th month pay
                                            - Paid leave
                                            - Free lunch/snacks">{{ old('benefits') }}</textarea>
                                <div class="d-flex justify-content-end mt-2">
                                    <div class="character-counter"></div>
                                </div>
                                @error('benefits')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Skills Section -->
                            <div class="form-row mb-4">
                                <div class="form-row-header">
                                    <i class="fas fa-tools me-2" style="color: #667eea;"></i> Required Skills
                                </div>
                                <input type="text" class="form-control" id="skills_input"
                                    placeholder="Type a skill and press Enter or comma to add (e.g., Microsoft Office, Communication)">
                                <div id="skills_container" class="mt-3"></div>
                                <input type="hidden" id="skills" name="skills" value="{{ old('skills') }}">
                                <div class="form-text mt-2">
                                    <i class="fas fa-info-circle"></i> Add relevant skills that candidates should have - these
                                    help with job matching
                                </div>
                            </div>

                            <!-- Required Documents Section -->
                            <div class="form-row mb-4">
                                <div class="form-row-header">
                                    <i class="fas fa-folder-open me-2" style="color: #667eea;"></i> Required Documents
                                </div>
                                <div class="form-text mb-3">
                                    <i class="fas fa-info-circle"></i> Specify documents that applicants must submit if their
                                    application is approved (e.g., 2x2 ID Photo, certificates, valid IDs)
                                </div>

                                <div id="job_requirements_container">
                                    <!-- Existing requirements will be added here -->
                                </div>

                                <button type="button" class="btn btn-outline-primary" id="add_requirement_btn">
                                    <i class="fas fa-plus me-2"></i>Add Document Requirement
                                </button>
                            </div>

                            <div class="d-flex justify-content-between wizard-navigation">
                                <button type="button" class="btn btn-outline-secondary btn-prev-step btn-lg">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Details
                                </button>
                                <button type="button" class="btn btn-primary btn-next-step btn-lg">
                                    Review Job Posting <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 4: Review & Submit -->
                        <div class="wizard-section" id="step-4" style="display: none;">
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div>
                                    <h4>Review Your Job Posting</h4>
                                    <p>Preview how your job will appear to candidates</p>
                                </div>
                            </div>

                            <!-- Job Preview Card -->
                            <div class="job-preview">
                                <div class="job-preview-header">
                                    <div class="flex-grow-1">
                                        <h5 id="preview-title">Job Title</h5>
                                        <div class="preview-meta">
                                            <span class="preview-meta-item">
                                                <i class="fas fa-building"></i>
                                                <span
                                                    id="preview-company">{{ Auth::user()->employerProfile->company_name ?? Auth::user()->name }}</span>
                                            </span>
                                            <span class="preview-meta-item">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span id="preview-location">Location</span>
                                            </span>
                                            <span class="preview-meta-item">
                                                <i class="fas fa-briefcase"></i>
                                                <span id="preview-type">Job Type</span>
                                            </span>
                                            <span class="preview-meta-item">
                                                <i class="fas fa-users"></i>
                                                <span id="preview-vacancy">1</span> position(s)
                                            </span>
                                        </div>
                                    </div>
                                    <div id="preview-badges" class="d-flex flex-wrap gap-2">
                                        <!-- Dynamic badges will be added here -->
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6><i class="fas fa-align-left"></i> Description</h6>
                                    <div id="preview-description" class="preview-content">Job description will appear here...
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6><i class="fas fa-check-circle"></i> Qualifications</h6>
                                    <div id="preview-qualifications" class="preview-content">Job qualifications will appear
                                        here...</div>
                                </div>

                                <div class="mb-4" id="preview-requirements-section" style="display: none;">
                                    <h6><i class="fas fa-clipboard-list"></i> Additional Requirements</h6>
                                    <div id="preview-requirements" class="preview-content">Additional requirements will appear
                                        here...</div>
                                </div>

                                <div class="mb-4" id="preview-benefits-section" style="display: none;">
                                    <h6><i class="fas fa-gift"></i> Benefits & Perks</h6>
                                    <div id="preview-benefits" class="preview-content">Benefits will appear here...</div>
                                </div>

                                <div class="mb-4" id="preview-salary-section" style="display: none;">
                                    <h6><i class="fas fa-money-bill-wave"></i> Salary Range</h6>
                                    <div id="preview-salary" class="preview-content">Salary range will appear here...</div>
                                </div>

                                <div class="mb-4" id="preview-skills-section" style="display: none;">
                                    <h6><i class="fas fa-tools"></i> Required Skills</h6>
                                    <div id="preview-skills" class="d-flex flex-wrap gap-2">
                                        <!-- Skills tags will appear here -->
                                    </div>
                                </div>
                            </div>

                            <!-- Submission Notice -->
                            <div class="alert alert-info mt-4">
                                <i class="fas fa-shield-alt"></i>
                                <div>
                                    <strong>Admin Review Required</strong><br>
                                    <small>Your job posting will be reviewed by our team before being published. You will
                                        receive a notification once it's approved. This usually takes less than 24
                                        hours.</small>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between wizard-navigation mt-4">
                                <button type="button" class="btn btn-outline-secondary btn-prev-step btn-lg">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Qualifications
                                </button>
                                <button type="submit" class="btn btn-success btn-lg" id="submitJobBtn">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Job Posting
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Autosave Indicator -->
            <div id="autosave-indicator" class="autosave-indicator">
                <i class="fas fa-cloud-upload-alt"></i> <span>Saving draft...</span>
            </div>
        @endif
    </div>

    <style>
        /* Enhanced Autosave Indicator */
        .autosave-indicator {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--success-gradient);
            color: white;
            padding: 12px 24px;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 500;
            box-shadow: 0 8px 30px rgba(17, 153, 142, 0.3);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1050;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .autosave-indicator.show {
            transform: translateY(0);
            opacity: 1;
        }

        .autosave-indicator i {
            font-size: 1rem;
        }
    </style>
@endsection

@push('styles')
    <!-- Mapbox CSS -->
    <link href='https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.css' rel='stylesheet' />
    <style>
        .location-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
        }

        .location-suggestion-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }

        .location-suggestion-item:hover {
            background-color: #f8f9fa;
        }

        .location-suggestion-name {
            font-weight: 600;
            color: #333;
        }

        .location-suggestion-address {
            font-size: 0.85rem;
            color: #666;
        }
    </style>

    <!-- Mapbox JS -->
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
                    locationMapContainer.innerHTML = '<div class="alert alert-warning">Map unavailable</div>';
                });

            function initializeMap(config) {
                if (!config.public_token) return;

                mapboxgl.accessToken = config.public_token;

                // Get initial coordinates or use default
                const initialLat = parseFloat(latitudeInput.value) || config.default_center.lat;
                const initialLng = parseFloat(longitudeInput.value) || config.default_center.lng;

                map = new mapboxgl.Map({
                    container: 'locationMap',
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: [initialLng, initialLat],
                    zoom: config.default_zoom || 13
                });

                map.addControl(new mapboxgl.NavigationControl(), 'top-right');

                // Add marker if coordinates exist
                if (latitudeInput.value && longitudeInput.value) {
                    addMarker(parseFloat(longitudeInput.value), parseFloat(latitudeInput.value));
                }

                // Click map to set location
                map.on('click', function (e) {
                    const lng = e.lngLat.lng;
                    const lat = e.lngLat.lat;

                    if (!isWithinStaCruz(lng, lat)) {
                        alert('Please select a location within Sta. Cruz, Davao del Sur only.');
                        return;
                    }

                    addMarker(lng, lat);
                    updateCoordinates(lng, lat);
                    reverseGeocode(lng, lat);
                });
            }

            function addMarker(lng, lat) {
                if (marker) marker.remove();

                marker = new mapboxgl.Marker({ color: '#667eea', draggable: true })
                    .setLngLat([lng, lat])
                    .addTo(map);

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

                // Trigger input event for preview updates if any
                streetAddress.dispatchEvent(new Event('input'));
            }

            function isWithinStaCruz(lng, lat) {
                if (!mapboxConfig || !mapboxConfig.stacruz_bounds) return true;
                const bounds = mapboxConfig.stacruz_bounds;
                return lng >= bounds.southwest[0] && lng <= bounds.northeast[0] &&
                    lat >= bounds.southwest[1] && lat <= bounds.northeast[1];
            }

            // Search Functionality
            if (locationSearch) {
                locationSearch.addEventListener('input', function () {
                    const query = this.value.trim();
                    if (query.length < 2) {
                        locationSuggestions.style.display = 'none';
                        return;
                    }

                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => searchPlaces(query), 300);
                });

                // Hide suggestions on outside click
                document.addEventListener('click', function (e) {
                    if (!locationSearch.contains(e.target) && !locationSuggestions.contains(e.target)) {
                        locationSuggestions.style.display = 'none';
                    }
                });
            }

            function searchPlaces(query) {
                fetch(`/api/location/search?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.suggestions && data.suggestions.length > 0) {
                            showSuggestions(data.suggestions);
                        } else {
                            locationSuggestions.style.display = 'none';
                        }
                    })
                    .catch(err => console.error(err));
            }

            function showSuggestions(places) {
                locationSuggestions.innerHTML = '';
                places.forEach(place => {
                    const div = document.createElement('div');
                    div.className = 'location-suggestion-item';
                    div.innerHTML = `
                                        <div class="location-suggestion-name">${place.name || extractLocationName(place.place_name)}</div>
                                        <div class="location-suggestion-address">${place.place_name || place.full_address}</div>
                                    `;
                    div.addEventListener('click', () => selectPlace(place));
                    locationSuggestions.appendChild(div);
                });
                locationSuggestions.style.display = 'block';
            }

            function selectPlace(place) {
                const coords = place.geometry ? place.geometry.coordinates :
                    (place.coordinates ? [place.coordinates.longitude, place.coordinates.latitude] : null);

                if (coords) {
                    const [lng, lat] = coords;
                    if (!isWithinStaCruz(lng, lat)) {
                        alert('Please select a location within Sta. Cruz, Davao del Sur only.');
                        return;
                    }

                    locationSearch.value = place.name || extractLocationName(place.place_name);
                    updateCoordinates(lng, lat);
                    if (map) addMarker(lng, lat);
                    fillAddressFields(place);
                    locationSuggestions.style.display = 'none';
                }
            }

            function fillAddressFields(place) {
                const placeName = place.place_name || place.full_address || '';
                const streetParts = placeName.split(',');
                if (streetParts.length > 0) streetAddress.value = streetParts[0].trim();

                // Auto-fill context
                if (!cityInput.value || !cityInput.value.includes('Sta. Cruz')) cityInput.value = 'Sta. Cruz';
                if (!stateInput.value || !stateInput.value.includes('Davao')) stateInput.value = 'Davao del Sur';
                countryInput.value = 'Philippines';

                // Trigger preview update
                streetAddress.dispatchEvent(new Event('input'));
            }

            function reverseGeocode(lng, lat) {
                fetch(`/api/location/reverse-geocode?lng=${lng}&lat=${lat}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.features && data.features.length > 0) {
                            const feature = data.features[0];
                            locationSearch.value = extractLocationName(feature.place_name);
                            parseAddressComponents(feature);
                        }
                    });
            }

            function parseAddressComponents(feature) {
                const placeName = feature.place_name || '';
                const parts = placeName.split(',').map(p => p.trim());
                if (parts.length > 0) streetAddress.value = parts[0];

                if (feature.context) {
                    feature.context.forEach(ctx => {
                        if (ctx.id.startsWith('locality') || ctx.id.startsWith('place')) cityInput.value = ctx.text;
                        if (ctx.id.startsWith('region')) stateInput.value = ctx.text;
                    });
                }
                // Fallbacks
                if (!cityInput.value) cityInput.value = 'Sta. Cruz';
                if (!stateInput.value) stateInput.value = 'Davao del Sur';

                // Trigger preview update
                streetAddress.dispatchEvent(new Event('input'));
            }

            function extractLocationName(name) {
                if (!name) return '';
                const parts = name.split(',');
                let extracted = parts[0].trim();
                if (extracted.length < 3 || /^\d+$/.test(extracted)) {
                    extracted = parts[1] ? parts[1].trim() : extracted;
                }
                return extracted;
            }

            // Detect Location Button
            if (detectLocationBtn) {
                detectLocationBtn.addEventListener('click', function () {
                    if (!navigator.geolocation) {
                        alert('Geolocation not supported');
                        return;
                    }

                    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                    navigator.geolocation.getCurrentPosition(
                        pos => {
                            const { latitude: lat, longitude: lng } = pos.coords;
                            if (!isWithinStaCruz(lng, lat)) {
                                alert('Location outside Sta. Cruz');
                                this.innerHTML = '<i class="bi bi-crosshair"></i>';
                                return;
                            }
                            updateCoordinates(lng, lat);
                            if (map) addMarker(lng, lat);
                            reverseGeocode(lng, lat);
                            this.innerHTML = '<i class="bi bi-crosshair"></i>';
                        },
                        err => {
                            alert('Location error: ' + err.message);
                            this.innerHTML = '<i class="bi bi-crosshair"></i>';
                        },
                        { enableHighAccuracy: true, timeout: 5000 }
                    );
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/nouislider@14.6.0/distribute/nouislider.min.js"></script>
    <script src="{{ asset('assets/js/job-form-wizard-fixed.js') }}"></script>

    <script>
        /**
         * Form Reset on Fresh Page Load
         * Clears form data when creating a new job (unless there are validation errors)
         */
        (function () {
            'use strict';

            document.addEventListener('DOMContentLoaded', function () {
                // Only reset if there are no validation errors (old() would have data)
                @if(!$errors->any() && !old('title'))
                    resetJobForm();
                @endif
                    });

            function resetJobForm() {
                const form = document.getElementById('jobForm');
                if (!form) return;

                // Reset all text inputs
                form.querySelectorAll('input[type="text"], input[type="number"], input[type="date"], textarea').forEach(function (input) {
                    if (input.name !== '_token') {
                        input.value = '';
                    }
                });

                // Reset all selects to first option
                form.querySelectorAll('select').forEach(function (select) {
                    select.selectedIndex = 0;
                });

                // Uncheck all checkboxes
                form.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
                    checkbox.checked = false;
                });

                // Reset hidden fields (except CSRF token)
                form.querySelectorAll('input[type="hidden"]').forEach(function (hidden) {
                    if (hidden.name !== '_token') {
                        hidden.value = '';
                    }
                });

                // Reset vacancy to default value of 1
                const vacancyInput = document.getElementById('vacancy');
                if (vacancyInput) {
                    vacancyInput.value = '1';
                }

                // Clear skills container
                const skillsContainer = document.getElementById('skills_container');
                if (skillsContainer) {
                    skillsContainer.innerHTML = '';
                }

                // Clear job requirements container
                const reqContainer = document.getElementById('job_requirements_container');
                if (reqContainer) {
                    reqContainer.innerHTML = '';
                }

                // Clear questions list
                const questionsList = document.getElementById('questions_list');
                if (questionsList) {
                    questionsList.innerHTML = '';
                }

                // Reset option cards (remove selected class)
                document.querySelectorAll('.option-card').forEach(function (card) {
                    card.classList.remove('selected');
                });

                // Hide preliminary questions container
                const prelimContainer = document.getElementById('preliminary_questions_container');
                if (prelimContainer) {
                    prelimContainer.style.display = 'none';
                }

                console.log('Job form reset for new entry');
            }

            // Expose function globally if needed
            window.resetJobForm = resetJobForm;
        })();




    </script>

    <script>
        // Add form submission debugging
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('jobForm');
            const submitBtn = document.getElementById('submitJobBtn');

            // Check if there are validation errors on page load
            const errorAlert = document.querySelector('.alert-danger');
            if (errorAlert) {
                // Determine which step has the error based on field names
                const stepFieldMap = {
                    1: ['title', 'job_type_id', 'category_id', 'vacancy', 'location', 'is_remote', 'is_featured'],
                    2: ['description', 'experience_level', 'education_level', 'salary_min', 'salary_max', 'deadline'],
                    3: ['qualifications', 'requirements', 'benefits', 'skills', 'job_requirements'],
                    4: []
                };

                // Find the step with the first error
                let errorStep = 1;
                const invalidFields = document.querySelectorAll('.invalid-feedback.d-block');

                if (invalidFields.length > 0) {
                    // Get the first invalid field's name
                    const firstInvalidField = invalidFields[0].closest('.form-group, .col-lg-6, .col-lg-4, .col-md-6, .mb-3, .mb-4, .form-row');
                    if (firstInvalidField) {
                        const input = firstInvalidField.querySelector('input, select, textarea');
                        if (input && input.name) {
                            const fieldName = input.name.replace(/\[.*\]/, ''); // Remove array notation
                            for (let step = 1; step <= 4; step++) {
                                if (stepFieldMap[step].includes(fieldName)) {
                                    errorStep = step;
                                    break;
                                }
                            }
                        }
                    }
                }

                // Show the step with the error
                const targetStep = document.getElementById('step-' + errorStep);
                if (targetStep) {
                    // Hide all steps
                    document.querySelectorAll('.wizard-section').forEach(section => {
                        section.style.display = 'none';
                    });
                    // Show target step
                    targetStep.style.display = 'block';

                    // Update progress indicator
                    document.querySelectorAll('.progress-step').forEach(step => {
                        step.classList.remove('active');
                    });
                    const stepIndex = errorStep - 1;
                    const progressStepEl = document.querySelector('[data-step="' + stepIndex + '"]');
                    if (progressStepEl) {
                        progressStepEl.classList.add('active');
                    }

                    // Update progress bar
                    const progressBar = document.querySelector('.progress-bar');
                    if (progressBar) {
                        progressBar.style.width = (errorStep * 25) + '%';
                    }

                    // Scroll to the first error within the step
                    setTimeout(() => {
                        const firstError = targetStep.querySelector('.invalid-feedback.d-block');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        } else {
                            errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }, 100);
                }
            }

            if (form && submitBtn) {
                // Track form submission attempts
                form.addEventListener('submit', function (e) {
                    console.log('🚀 Form submission started');
                    console.log('Form action:', form.action);
                    console.log('Form method:', form.method);

                    // Show loading state
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Job...';

                    // Add a temporary success message
                    const tempAlert = document.createElement('div');
                    tempAlert.className = 'alert alert-info mt-3';
                    tempAlert.innerHTML = '<i class="fas fa-info-circle me-2"></i>Submitting your job posting...';
                    form.insertBefore(tempAlert, form.firstChild);

                    // Log form data
                    const formData = new FormData(form);
                    console.log('Form data:');
                    for (let [key, value] of formData.entries()) {
                        console.log(`  ${key}: ${value}`);
                    }
                });

                // Track any form errors
                window.addEventListener('error', function (e) {
                    console.error('JavaScript error:', e.error);
                });
            }
        });

        // Job Requirements Management
        (function () {
            'use strict';

            let requirementIndex = 0;

            document.addEventListener('DOMContentLoaded', function () {
                initJobRequirements();

                // Repopulate requirements if old input exists
                @if(old('job_requirements'))
                    @foreach(old('job_requirements') as $req)
                        addRequirement(
                            '{{ $req['name'] ?? '' }}',
                            '{{ $req['description'] ?? '' }}',
                            {{ isset($req['is_required']) ? 'true' : 'false' }}
                        );
                    @endforeach
                @endif
                        });

            function initJobRequirements() {
                const container = document.getElementById('job_requirements_container');
                const addBtn = document.getElementById('add_requirement_btn');

                if (!container || !addBtn) return;

                // Add button click handler
                addBtn.addEventListener('click', function () {
                    addRequirement();
                });

                // Event delegation for remove buttons
                container.addEventListener('click', function (e) {
                    if (e.target.closest('.remove-requirement')) {
                        const item = e.target.closest('.requirement-item');
                        if (item) {
                            item.remove();
                        }
                    }
                });
            }

            function addRequirement(name = '', description = '', isRequired = true) {
                const container = document.getElementById('job_requirements_container');
                const index = requirementIndex++;

                const html = `
                            <div class="requirement-item">
                                <button type="button" class="remove-requirement" title="Remove requirement">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Document Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="job_requirements[${index}][name]"
                                               value="${escapeHtml(name)}" placeholder="e.g., 2x2 ID Photo" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Description</label>
                                        <input type="text" class="form-control" name="job_requirements[${index}][description]"
                                               value="${escapeHtml(description)}" placeholder="e.g., Recent photo with white background">
                                    </div>
                                </div>
                                <div class="form-check form-switch mt-2">
                                    <input type="checkbox" class="form-check-input" name="job_requirements[${index}][is_required]"
                                           id="req_required_${index}" ${isRequired ? 'checked' : ''} value="1">
                                    <label class="form-check-label align-middle" for="req_required_${index}">
                                        This document is mandatory
                                    </label>
                                </div>
                            </div>
                        `;

                container.insertAdjacentHTML('beforeend', html);
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Make addRequirement globally accessible
            window.addRequirement = addRequirement;
        })();

        // Toggle Option Card functionality
        function toggleOptionCard(card, inputId) {
            const input = document.getElementById(inputId);
            if (input) {
                input.checked = !input.checked;
                if (input.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            }
        }

        // Enhanced Preview Update
        document.addEventListener('DOMContentLoaded', function () {
            // Update preview when moving to step 4
            const updatePreview = function () {
                // Update title
                const title = document.getElementById('title');
                if (title) {
                    document.getElementById('preview-title').textContent = title.value || 'Job Title';
                }

                // Update location
                const location = document.getElementById('location');
                if (location) {
                    document.getElementById('preview-location').textContent = location.value || 'Location';
                }

                // Update job type
                const jobType = document.getElementById('job_type_id');
                if (jobType && jobType.selectedOptions[0]) {
                    document.getElementById('preview-type').textContent = jobType.selectedOptions[0].text || 'Job Type';
                }

                // Update vacancy
                const vacancy = document.getElementById('vacancy');
                if (vacancy) {
                    document.getElementById('preview-vacancy').textContent = vacancy.value || '1';
                }

                // Update description
                const description = document.getElementById('description');
                if (description) {
                    document.getElementById('preview-description').textContent = description.value || 'Job description will appear here...';
                }

                // Update qualifications
                const qualifications = document.getElementById('qualifications');
                if (qualifications) {
                    document.getElementById('preview-qualifications').textContent = qualifications.value || 'Job qualifications will appear here...';
                }

                // Update requirements
                const requirements = document.getElementById('requirements');
                const reqSection = document.getElementById('preview-requirements-section');
                if (requirements && requirements.value) {
                    document.getElementById('preview-requirements').textContent = requirements.value;
                    reqSection.style.display = 'block';
                } else {
                    reqSection.style.display = 'none';
                }

                // Update benefits
                const benefits = document.getElementById('benefits');
                const benefitsSection = document.getElementById('preview-benefits-section');
                if (benefits && benefits.value) {
                    document.getElementById('preview-benefits').textContent = benefits.value;
                    benefitsSection.style.display = 'block';
                } else {
                    benefitsSection.style.display = 'none';
                }

                // Update salary
                const salaryMin = document.getElementById('salary_min');
                const salaryMax = document.getElementById('salary_max');
                const salarySection = document.getElementById('preview-salary-section');
                if ((salaryMin && salaryMin.value) || (salaryMax && salaryMax.value)) {
                    let salaryText = '';
                    if (salaryMin.value && salaryMax.value) {
                        salaryText = '₱' + Number(salaryMin.value).toLocaleString() + ' - ₱' + Number(salaryMax.value).toLocaleString() + ' per month';
                    } else if (salaryMin.value) {
                        salaryText = 'Starting from ₱' + Number(salaryMin.value).toLocaleString() + ' per month';
                    } else {
                        salaryText = 'Up to ₱' + Number(salaryMax.value).toLocaleString() + ' per month';
                    }
                    document.getElementById('preview-salary').textContent = salaryText;
                    salarySection.style.display = 'block';
                } else {
                    salarySection.style.display = 'none';
                }

                // Update skills preview
                const skills = document.getElementById('skills');
                const skillsSection = document.getElementById('preview-skills-section');
                const skillsContainer = document.getElementById('preview-skills');
                if (skills && skills.value && skillsContainer) {
                    const skillsList = skills.value.split(',').filter(s => s.trim());
                    if (skillsList.length > 0) {
                        skillsContainer.innerHTML = skillsList.map(skill =>
                            `<span class="skill-tag">${skill.trim()}</span>`
                        ).join('');
                        skillsSection.style.display = 'block';
                    } else {
                        skillsSection.style.display = 'none';
                    }
                } else {
                    if (skillsSection) skillsSection.style.display = 'none';
                }

                // Update badges
                const badgesContainer = document.getElementById('preview-badges');
                if (badgesContainer) {
                    let badges = '';
                    const isRemote = document.getElementById('is_remote');
                    const isFeatured = document.getElementById('is_featured');

                    if (isRemote && isRemote.checked) {
                        badges += '<span class="preview-badge"><i class="fas fa-home"></i> Remote</span>';
                    }
                    if (isFeatured && isFeatured.checked) {
                        badges += '<span class="preview-badge"><i class="fas fa-star"></i> Featured</span>';
                    }
                    badgesContainer.innerHTML = badges;
                }
            };

            // Attach to next step buttons
            document.querySelectorAll('.btn-next-step').forEach(btn => {
                btn.addEventListener('click', function () {
                    setTimeout(updatePreview, 100);
                });
            });

            // Also update on step click
            document.querySelectorAll('.progress-step').forEach(step => {
                step.addEventListener('click', function () {
                    setTimeout(updatePreview, 100);
                });
            });
        });
    </script>
@endpush