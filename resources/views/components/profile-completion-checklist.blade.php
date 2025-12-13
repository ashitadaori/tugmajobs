@props(['user', 'completionPercentage' => 0])

@php
$jobseeker = $user->jobSeekerProfile ?? $user->jobseeker;

// Define checklist items with weights matching the dashboard calculation
// Total weights = 100 points
if (!$jobseeker) {
    $checklistItems = [
        [
            'title' => 'Basic Information',
            'description' => 'Add your name, email, phone, and profile photo',
            'completed' => false,
            'route' => route('account.myProfile'),
            'icon' => 'fas fa-user',
            'weight' => 20 // name(5) + email(5) + phone(5) + image(5)
        ],
        [
            'title' => 'Professional Title',
            'description' => 'Add your current job title',
            'completed' => false,
            'route' => route('account.myProfile'),
            'icon' => 'fas fa-id-badge',
            'weight' => 5
        ],
        [
            'title' => 'Professional Summary',
            'description' => 'Write a compelling summary (at least 100 characters)',
            'completed' => false,
            'route' => route('account.myProfile'),
            'icon' => 'fas fa-file-alt',
            'weight' => 10
        ],
        [
            'title' => 'Skills',
            'description' => 'Add at least 3 relevant skills',
            'completed' => false,
            'route' => route('account.myProfile'),
            'icon' => 'fas fa-cogs',
            'weight' => 10
        ],
        [
            'title' => 'Work Experience',
            'description' => 'Add at least one work experience entry',
            'completed' => false,
            'route' => route('account.myProfile'),
            'icon' => 'fas fa-briefcase',
            'weight' => 8
        ],
        [
            'title' => 'Education',
            'description' => 'Add your educational background',
            'completed' => false,
            'route' => route('account.myProfile'),
            'icon' => 'fas fa-graduation-cap',
            'weight' => 7
        ],
        [
            'title' => 'Resume Upload',
            'description' => 'Upload or create your resume',
            'completed' => false,
            'route' => route('account.resume-builder.index'),
            'icon' => 'fas fa-file-pdf',
            'weight' => 10
        ],
        [
            'title' => 'Job Preferences',
            'description' => 'Set your preferred categories, job types, and experience level',
            'completed' => false,
            'route' => route('account.myProfile'),
            'icon' => 'fas fa-sliders-h',
            'weight' => 15 // categories(5) + job_types(5) + experience_level(5)
        ],
        [
            'title' => 'KYC Verification',
            'description' => 'Verify your identity to increase trust with employers',
            'completed' => false,
            'route' => route('kyc.index'),
            'icon' => 'fas fa-shield-alt',
            'weight' => 15
        ]
    ];
} else {
    // Calculate completion status matching dashboard logic exactly
    // Each field is checked individually with its own weight
    $hasName = !empty($user->name);
    $hasEmail = !empty($user->email);
    $hasPhone = !empty($user->phone);
    $hasImage = !empty($user->image);
    $hasJobTitle = !empty($jobseeker->current_job_title);
    $hasProfessionalSummary = !empty($jobseeker->professional_summary) && strlen($jobseeker->professional_summary) >= 100;
    $hasSkills = is_array($jobseeker->skills) && count($jobseeker->skills) >= 3;
    $hasWorkExperience = is_array($jobseeker->work_experience) && count($jobseeker->work_experience) > 0;
    $hasEducation = is_array($jobseeker->education) && count($jobseeker->education) > 0;
    $hasResume = !empty($jobseeker->resume_file) || $user->resumes()->count() > 0;
    $hasPreferredCategories = is_array($jobseeker->preferred_categories) && count($jobseeker->preferred_categories) > 0;
    $hasPreferredJobTypes = is_array($jobseeker->preferred_job_types) && count($jobseeker->preferred_job_types) > 0;
    $hasExperienceLevel = !empty($jobseeker->experience_level);
    $hasKycVerified = $user->kyc_status === 'verified';

    // Calculate weighted percentage exactly like dashboard
    $weightedScore = 0;
    if ($hasName) $weightedScore += 5;
    if ($hasEmail) $weightedScore += 5;
    if ($hasPhone) $weightedScore += 5;
    if ($hasImage) $weightedScore += 5;
    if ($hasJobTitle) $weightedScore += 5;
    if ($hasProfessionalSummary) $weightedScore += 10;
    if ($hasSkills) $weightedScore += 10;
    if ($hasWorkExperience) $weightedScore += 8;
    if ($hasEducation) $weightedScore += 7;
    if ($hasResume) $weightedScore += 10;
    if ($hasPreferredCategories) $weightedScore += 5;
    if ($hasPreferredJobTypes) $weightedScore += 5;
    if ($hasExperienceLevel) $weightedScore += 5;
    if ($hasKycVerified) $weightedScore += 15;

    // For display purposes, group related items but use individual weights for calculation
    $hasBasicInfo = $hasName && $hasEmail && $hasPhone && $hasImage;
    $basicInfoPartial = ($hasName ? 1 : 0) + ($hasEmail ? 1 : 0) + ($hasPhone ? 1 : 0) + ($hasImage ? 1 : 0);
    $hasJobPreferences = $hasPreferredCategories && $hasPreferredJobTypes && $hasExperienceLevel;
    $jobPrefsPartial = ($hasPreferredCategories ? 1 : 0) + ($hasPreferredJobTypes ? 1 : 0) + ($hasExperienceLevel ? 1 : 0);

    $checklistItems = [
        [
            'title' => 'Basic Information',
            'description' => $hasBasicInfo ? 'All basic info complete' : 'Add your name, email, phone, and profile photo (' . $basicInfoPartial . '/4 done)',
            'completed' => $hasBasicInfo,
            'route' => route('account.myProfile'),
            'icon' => 'fas fa-user',
            'weight' => 20,
            'earned' => ($hasName ? 5 : 0) + ($hasEmail ? 5 : 0) + ($hasPhone ? 5 : 0) + ($hasImage ? 5 : 0)
        ],
        [
            'title' => 'Professional Title',
            'description' => 'Add your current job title',
            'completed' => $hasJobTitle,
            'route' => route('account.myProfile'),
            'icon' => 'fas fa-id-badge',
            'weight' => 5,
            'earned' => $hasJobTitle ? 5 : 0
        ],
        [
            'title' => 'Professional Summary',
            'description' => 'Write a compelling summary (at least 100 characters)',
            'completed' => $hasProfessionalSummary,
            'route' => route('account.myProfile'),
            'icon' => 'fas fa-file-alt',
            'weight' => 10,
            'earned' => $hasProfessionalSummary ? 10 : 0
        ],
        [
            'title' => 'Skills',
            'description' => 'Add at least 3 relevant skills',
            'completed' => $hasSkills,
            'route' => route('account.myProfile'),
            'icon' => 'fas fa-cogs',
            'weight' => 10,
            'earned' => $hasSkills ? 10 : 0
        ],
        [
            'title' => 'Work Experience',
            'description' => 'Add at least one work experience entry',
            'completed' => $hasWorkExperience,
            'route' => route('account.myProfile'),
            'icon' => 'fas fa-briefcase',
            'weight' => 8,
            'earned' => $hasWorkExperience ? 8 : 0
        ],
        [
            'title' => 'Education',
            'description' => 'Add your educational background',
            'completed' => $hasEducation,
            'route' => route('account.myProfile'),
            'icon' => 'fas fa-graduation-cap',
            'weight' => 7,
            'earned' => $hasEducation ? 7 : 0
        ],
        [
            'title' => 'Resume Upload',
            'description' => 'Upload or create your resume',
            'completed' => $hasResume,
            'route' => route('account.resume-builder.index'),
            'icon' => 'fas fa-file-pdf',
            'weight' => 10,
            'earned' => $hasResume ? 10 : 0
        ],
        [
            'title' => 'Job Preferences',
            'description' => $hasJobPreferences ? 'All preferences set' : 'Set your preferred categories, job types, and experience level (' . $jobPrefsPartial . '/3 done)',
            'completed' => $hasJobPreferences,
            'route' => route('account.myProfile'),
            'icon' => 'fas fa-sliders-h',
            'weight' => 15,
            'earned' => ($hasPreferredCategories ? 5 : 0) + ($hasPreferredJobTypes ? 5 : 0) + ($hasExperienceLevel ? 5 : 0)
        ],
        [
            'title' => 'KYC Verification',
            'description' => 'Verify your identity to increase trust with employers',
            'completed' => $hasKycVerified,
            'route' => route('kyc.index'),
            'icon' => 'fas fa-shield-alt',
            'weight' => 15,
            'earned' => $hasKycVerified ? 15 : 0
        ]
    ];
}

// Calculate weighted completion percentage (matching dashboard exactly)
$totalWeight = 100; // Total possible is always 100
$completedWeight = 0;
foreach ($checklistItems as $item) {
    // Use 'earned' if available (for partial credit), otherwise use weight if completed
    $completedWeight += $item['earned'] ?? ($item['completed'] ? $item['weight'] : 0);
}
$completionRate = $completedWeight; // Already a percentage out of 100

$completedItems = collect($checklistItems)->filter(fn($item) => $item['completed'])->count();
$totalItems = count($checklistItems);
@endphp

<div class="profile-completion-checklist">
    <div class="checklist-header">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="checklist-title">
                <i class="fas fa-tasks me-2"></i>
                Complete Your Profile
            </h4>
            <span class="completion-badge">{{ $completedItems }}/{{ $totalItems }}</span>
        </div>

        <!-- Progress Bar -->
        <div class="checklist-progress">
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-success" role="progressbar"
                     style="width: {{ $completionRate }}%;"
                     aria-valuenow="{{ $completionRate }}"
                     aria-valuemin="0"
                     aria-valuemax="100">
                </div>
            </div>
            <small class="text-muted mt-1 d-block">{{ $completionRate }}% Complete</small>
        </div>

        @if($completionRate < 80)
        <div class="alert alert-info alert-sm mt-3 mb-0">
            <i class="fas fa-lightbulb me-2"></i>
            <strong>Tip:</strong> Complete at least 80% of your profile to increase visibility to employers.
        </div>
        @elseif($completionRate >= 80 && $completionRate < 100)
        <div class="alert alert-success alert-sm mt-3 mb-0">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Great job!</strong> Your profile is looking good. Complete the remaining items to maximize your chances.
        </div>
        @else
        <div class="alert alert-success alert-sm mt-3 mb-0">
            <i class="fas fa-star me-2"></i>
            <strong>Excellent!</strong> Your profile is complete. You're ready to land your dream job!
        </div>
        @endif
    </div>

    <div class="checklist-items">
        @foreach($checklistItems as $item)
        <div class="checklist-item {{ $item['completed'] ? 'completed' : '' }}">
            <div class="item-icon">
                @if($item['completed'])
                    <i class="fas fa-check-circle text-success"></i>
                @else
                    <i class="{{ $item['icon'] }} text-muted"></i>
                @endif
            </div>
            <div class="item-content">
                <h6 class="item-title">{{ $item['title'] }}</h6>
                <p class="item-description">{{ $item['description'] }}</p>
            </div>
            <div class="item-action">
                @if(!$item['completed'])
                    <a href="{{ $item['route'] }}" class="btn btn-sm btn-outline-primary">
                        Complete
                    </a>
                @else
                    <span class="badge bg-success">Done</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
.profile-completion-checklist {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #f1f5f9;
}

.checklist-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1a202c;
    margin: 0;
}

.completion-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.checklist-progress {
    margin-bottom: 1rem;
}

.alert-sm {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    border-radius: 0.5rem;
}

.checklist-items {
    margin-top: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.checklist-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
}

.checklist-item:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.checklist-item.completed {
    background: #f0fdf4;
    border-color: #86efac;
}

.item-icon {
    font-size: 1.25rem;
    flex-shrink: 0;
}

.item-content {
    flex: 1;
}

.item-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #1a202c;
    margin: 0 0 0.25rem 0;
}

.checklist-item.completed .item-title {
    color: #166534;
}

.item-description {
    font-size: 0.8125rem;
    color: #64748b;
    margin: 0;
}

.item-action {
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .checklist-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .item-action {
        width: 100%;
    }

    .item-action .btn {
        width: 100%;
    }
}
</style>
