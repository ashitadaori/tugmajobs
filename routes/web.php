<?php
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JobApplicationController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\JobTypeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\JobsController;
use App\Http\Controllers\JobsControllerKMeans;
use App\Http\Controllers\KycController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\EmployerAuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\SavedJobController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Include module routes
require __DIR__ . '/modules.php';

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', function() {
        return redirect()->route('home')->with('info', 'Please use the Sign In button to access your account.');
    })->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', function() {
        return redirect()->route('home')->with('info', 'Please use the Get Started button to create your account.');
    })->name('register');
    Route::post('register', [RegisterController::class, 'register']);

    // Employer Authentication Routes
    Route::get('employer/login', [EmployerAuthController::class, 'showLogin'])->name('employer.login');
    Route::post('employer/login', [EmployerAuthController::class, 'login'])->name('employer.login.submit');
    Route::get('employer/register', [EmployerAuthController::class, 'showRegister'])->name('employer.register');
    Route::post('employer/register', [EmployerAuthController::class, 'register'])->name('employer.register.submit');

    // Social Authentication Routes - Redirect only
    Route::get('auth/{provider}', [SocialAuthController::class, 'redirectToProvider'])->name('social.redirect');
    Route::get('auth/error', [SocialAuthController::class, 'handleError'])->name('social.error');
});

// Social Authentication Callback - Must be outside guest middleware
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback'])->name('social.callback');

// Magic Link / Email Sign-in Verification - Must be outside guest middleware
Route::get('auth/verify/{token}', [\App\Http\Controllers\Auth\MagicLinkController::class, 'verify'])->name('auth.verify-token');



Route::middleware('guest')->group(function () {
    // Account specific guest routes
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/register', [AccountController::class, 'registration'])->name('registration');
        Route::post('/process-register', [AccountController::class, 'processRegistration'])->name('processRegistration');
        Route::get('/forgot-password', [AccountController::class, 'forgotPassword'])->name('forgotPassword');
        Route::post('/process-forgot-password', [AccountController::class, 'processForgotPassword'])->name('processForgotPassword');
        Route::get('/reset-password/{token}', [AccountController::class, 'resetPassword'])->name('resetPassword');
        Route::post('/process-reset-password', [AccountController::class, 'processResetPassword'])->name('processResetPassword');
    });
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/',[HomeController::class,'index'])->name('home');

// Profile redirect route for all users
Route::get('/profile', function() {
    $user = Auth::user();
    
    if ($user->isEmployer()) {
        return redirect()->route('employer.profile.edit');
    } elseif ($user->isJobSeeker()) {
        return redirect()->route('account.myProfile');
    } elseif ($user->isAdmin()) {
        return redirect()->route('admin.profile.edit');
    } else {
        // Default fallback
        return redirect()->route('account.myProfile');
    }
})->middleware('auth')->name('profile.index');

// Job browsing routes - require authentication (jobseekers only for job listing)
Route::middleware(['auth', 'role:jobseeker'])->group(function() {
    Route::get('/jobs',[JobsControllerKMeans::class,'index'])->name('jobs');
});

// Job detail page - accessible by all authenticated users (jobseekers, employers, admins)
Route::middleware(['auth'])->group(function() {
    Route::get('/jobs/detail/{id}',[JobsControllerKMeans::class,'jobDetail'])->name('jobDetail');
});

// K-means specific routes
Route::middleware(['auth'])->group(function() {
    // Category selection routes for jobseekers
    Route::get('/jobs/select-categories', [JobsControllerKMeans::class, 'requireCategorySelection'])->name('jobs.select-categories');
    Route::post('/jobs/save-preferences', [JobsControllerKMeans::class, 'saveJobPreferences'])->name('jobs.save-preferences');
    
    // Job recommendations API
    Route::get('/api/jobs/recommendations', [JobsControllerKMeans::class, 'getRecommendations'])->name('api.jobs.recommendations');
    
    // Jobseeker dashboard with clustering
    Route::get('/jobseeker/dashboard', [JobsControllerKMeans::class, 'dashboard'])->name('jobseeker.dashboard.kmeans');
    
    // K-means Enhanced Profile Routes (JobSeeker only)
    Route::middleware(['check.jobseeker'])->prefix('kmeans/profile')->name('kmeans.profile.')->group(function() {
        Route::get('/', [\App\Http\Controllers\JobseekerProfileKMeansController::class, 'profile'])->name('index');
        Route::post('/update', [\App\Http\Controllers\JobseekerProfileKMeansController::class, 'updateProfile'])->name('update');
        Route::get('/dashboard', [\App\Http\Controllers\JobseekerProfileKMeansController::class, 'getProfileDashboard'])->name('dashboard');
        Route::post('/reset', [\App\Http\Controllers\JobseekerProfileKMeansController::class, 'resetProfileForKMeans'])->name('reset');
    });
});

// Companies routes - require authentication
Route::middleware(['auth', 'role:jobseeker'])->group(function() {
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies');
    Route::get('/companies/{id}', [CompanyController::class, 'show'])->name('companies.show');
});

// Location API Routes
Route::prefix('api/location')->name('api.location.')->group(function () {
    Route::get('/search', [LocationController::class, 'searchPlaces'])->name('search');
    Route::get('/geocode', [LocationController::class, 'geocode'])->name('geocode');
    Route::get('/reverse-geocode', [LocationController::class, 'reverseGeocode'])->name('reverse-geocode');
    Route::get('/config', [LocationController::class, 'getConfig'])->name('config');
});

// Job Application and Save Routes (must be authenticated and KYC verified for jobseekers)
Route::middleware(['auth', 'jobseeker.kyc'])->group(function() {
    // Step-by-step application process
    Route::get('/jobs/{id}/apply', [JobsController::class, 'startApplication'])->name('job.application.start');
    Route::post('/jobs/{id}/apply/process', [JobsController::class, 'processApplication'])->name('job.application.process');
    Route::delete('/jobs/{id}/apply/cancel', [JobsController::class, 'cancelApplication'])->name('job.application.cancel');

    // Legacy quick apply (kept for backward compatibility)
    Route::post('/jobs/{id}/apply', [JobsController::class, 'applyJob'])->name('jobs.apply');

    // Submit requirements for stage 2 of application process
    Route::get('/application/{application}/submit-requirements', [JobsController::class, 'showSubmitRequirements'])->name('job.submitRequirements');
    Route::post('/application/{application}/submit-requirements', [JobsController::class, 'processSubmitRequirements'])->name('job.submitRequirements.process');
});

// Save/unsave jobs (authenticated but no KYC required)
Route::middleware(['auth'])->group(function() {
    Route::post('/jobs/{id}/save', [JobsController::class, 'saveJob'])->name('jobs.save');
    Route::post('/jobs/{id}/unsave', [JobsController::class, 'unsaveJob'])->name('jobs.unsave');
    Route::post('/jobs/{job}/toggle-save', [SavedJobController::class, 'toggleSave'])
        ->name('jobs.toggle-save');

    // Employer Document Routes
    Route::prefix('employer/documents')->name('employer.documents.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Employer\DocumentController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Employer\DocumentController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Employer\DocumentController::class, 'store'])->name('store');
        Route::get('/{document}', [\App\Http\Controllers\Employer\DocumentController::class, 'show'])->name('show');
        Route::get('/{document}/edit', [\App\Http\Controllers\Employer\DocumentController::class, 'edit'])->name('edit');
        Route::put('/{document}', [\App\Http\Controllers\Employer\DocumentController::class, 'update'])->name('update');
        Route::delete('/{document}', [\App\Http\Controllers\Employer\DocumentController::class, 'destroy'])->name('destroy');
        Route::get('/{document}/download', [\App\Http\Controllers\Employer\DocumentController::class, 'download'])->name('download');
        Route::get('/progress', [\App\Http\Controllers\Employer\DocumentController::class, 'getVerificationProgress'])->name('progress');
    });

    // Admin Employer Document Routes
    Route::prefix('admin/employers/documents')->name('admin.employers.documents.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\EmployerDocumentController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\EmployerDocumentController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\EmployerDocumentController::class, 'store'])->name('store');
        Route::get('/{document}', [\App\Http\Controllers\Admin\EmployerDocumentController::class, 'show'])->name('show');
        Route::post('/{document}/approve', [\App\Http\Controllers\Admin\EmployerDocumentController::class, 'approve'])->name('approve');
        Route::post('/{document}/reject', [\App\Http\Controllers\Admin\EmployerDocumentController::class, 'reject'])->name('reject');
        Route::delete('/{document}', [\App\Http\Controllers\Admin\EmployerDocumentController::class, 'destroy'])->name('destroy');
    });
});

// Guest routes for saving jobs will be handled in authenticated group

// KYC Routes (Auth Required)
Route::middleware(['auth'])->prefix('kyc')->name('kyc.')->group(function () {
    Route::get('/', function() { return view('kyc.start'); })->name('index');
    Route::get('/start', function() { return view('kyc.start'); })->name('start.form');
    Route::post('/start', [KycController::class, 'startVerification'])->name('start');
    Route::post('/reset', [KycController::class, 'resetVerification'])->name('reset');
    Route::post('/check-status', [KycController::class, 'checkStatus'])->name('check-status');
    Route::post('/dismiss-banner', function() { 
        session(['kyc_banner_dismissed' => true]); 
        return response()->json(['status' => 'dismissed']); 
    })->name('dismiss-banner');
    
    Route::post('/mobile-completion-notify', [KycController::class, 'mobileCompletionNotify'])->name('mobile-completion-notify');
    

});

// KYC redirect routes (No auth required - users return from external Didit verification)
Route::prefix('kyc')->name('kyc.')->group(function () {
    Route::get('/success', [KycController::class, 'redirectHandler'])->name('success');
    Route::get('/failure', [KycController::class, 'failure'])->name('failure');
    
    // Mock verification route for development/testing (disabled - use DIDIT_USE_MOCK=true to enable)
    // Route::get('/mock-verify', function() {
    //     $user = Auth::user();
    //     
    //     if (!$user) {
    //         return redirect('/')->with('error', 'Please login to mock verification');
    //     }
    //     
    //     // Update user status to verified
    //     $user->update([
    //         'kyc_status' => 'verified',
    //         'kyc_verified_at' => now(),
    //         'kyc_data' => [
    //             'session_id' => \Illuminate\Support\Str::uuid()->toString(),
    //             'status' => 'completed',
    //             'completed_at' => now()->toIso8601String(),
    //             'mock' => true
    //         ]
    //     ]);
    //     
    //     return redirect()->route('kyc.success', [
    //         'session_id' => $user->kyc_data['session_id'],
    //         'status' => 'completed'
    //     ])->with('success', 'Mock verification completed successfully!');
    // })->middleware('auth')->name('mock-verify');
});

// Didit Webhook moved to API routes (see routes/api.php)

// User redirects from Didit are now handled via the API route with GET support
// Route::get('/kyc/webhook', [KycController::class, 'handleUserRedirect'])->name('kyc.user-redirect');

// Development-only test routes - only available in local environment
if (app()->environment('local', 'testing')) {
    Route::prefix('test')->name('test.')->group(function () {
        // KYC test redirect
        Route::get('/kyc/redirect/{userId}', function($userId) {
            $user = \App\Models\User::find($userId);
            if (!$user) {
                return 'User not found';
            }
            Auth::login($user);
            $user->update([
                'kyc_status' => 'verified',
                'kyc_verified_at' => now(),
                'kyc_data' => ['session_id' => 'test-' . time(), 'status' => 'completed', 'test' => true]
            ]);
            $dashboardRoute = $user->isEmployer() ? 'employer.dashboard' : 'account.dashboard';
            return redirect()->route($dashboardRoute)->with('success', 'Test KYC verification completed!');
        })->middleware('web')->name('kyc.redirect');

        // Verify employer for testing
        Route::get('/verify-employer', function() {
            $user = Auth::user();
            if (!$user || !$user->isEmployer()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            $user->update([
                'kyc_status' => 'verified',
                'kyc_verified_at' => now(),
            ]);
            return response()->json(['success' => true, 'message' => 'Employer verified']);
        })->middleware(['auth', 'role:employer'])->name('verify-employer');
    });
}

// Add KYC middleware to job application and job posting routes
// Note: Job application routes are already defined above in the authenticated middleware group
// This section is reserved for additional KYC-verified routes if needed

// Admin Routes - Now loaded via admin.php through modules.php
// Removed duplicate admin routes to avoid conflicts with admin.php

// Notification Routes
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    // List and view notifications (with auto-mark middleware)
    Route::middleware(['mark.notification.read'])->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/{notification}', [NotificationController::class, 'markAsRead'])->name('view');
    });

    // Get notification counts and recent notifications (no auto-mark)
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
    Route::get('/check-new', [NotificationController::class, 'checkNew'])->name('check-new');
    Route::get('/recent', [NotificationController::class, 'getRecentNotifications'])->name('recent');

    // Mark as read operations
    Route::post('/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('markAsRead');
    Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
    Route::post('/mark-as-read-batch', [NotificationController::class, 'markAsReadBatch'])->name('markAsReadBatch');
    Route::post('/auto-mark-as-read/{id}', [NotificationController::class, 'autoMarkAsRead'])->name('autoMarkAsRead');
    Route::post('/mark-old-as-read', [NotificationController::class, 'markOldAsRead'])->name('markOldAsRead');

    // Delete operations
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/batch/delete', [NotificationController::class, 'destroyBatch'])->name('destroyBatch');

    // Notification preferences
    Route::get('/preferences/view', [NotificationController::class, 'getPreferences'])->name('preferences');
    Route::post('/preferences/update', [NotificationController::class, 'updatePreferences'])->name('updatePreferences');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::prefix('account')->name('account.')->group(function () {
        // Remove the old profile route and keep only my-profile
        Route::match(['post', 'put'], '/update-profile', [AccountController::class, 'updateProfile'])->name('updateProfile');
        Route::post('/update-profile-img', [AccountController::class, 'updateProfileImg'])->name('updateProfileimg');
        Route::post('/remove-profile-img', [AccountController::class, 'removeProfileImage'])->name('removeProfileImage');
        Route::post('/store-message', [AccountController::class, 'storeMessage'])->name('storeMessage');
        
        // Job Seeker Routes
        Route::group(['middleware' => ['auth', 'role:jobseeker']], function() {
            Route::get('/dashboard', [AccountController::class, 'dashboard'])->name('dashboard');
            Route::get('/my-job-applications', [AccountController::class, 'myJobApplications'])->name('myJobApplications');
            Route::get('/my-job-applications/{id}', [AccountController::class, 'showJobApplication'])->name('showJobApplication');
            Route::post('/remove-job-application', [AccountController::class, 'removeJobs'])->name('removeJobs');
            // Enhanced Saved Jobs System
            Route::get('/saved-jobs', [SavedJobController::class, 'index'])->name('saved-jobs.index');
            Route::post('/saved-jobs/toggle', [SavedJobController::class, 'toggle'])->name('saved-jobs.toggle');
            Route::post('/saved-jobs/store', [SavedJobController::class, 'store'])->name('saved-jobs.store');
            Route::delete('/saved-jobs/destroy', [SavedJobController::class, 'destroy'])->name('saved-jobs.destroy');
            Route::get('/saved-jobs/count', [SavedJobController::class, 'count'])->name('saved-jobs.count');
            
            // Legacy routes (keep for backward compatibility)
            Route::get('/old-saved-jobs', [AccountController::class, 'savedJobs'])->name('savedJobs');
            Route::post('/remove-saved-job', [AccountController::class, 'removeSavedJob'])->name('removeSavedJob');
            Route::post('/save-job-to-favorites', [AccountController::class, 'saveJobToFavorites'])->name('saveJobToFavorites');
            Route::get('/delete-profile', [AccountController::class, 'deleteProfile'])->name('deleteProfile');
            Route::post('/delete-account', [AccountController::class, 'deleteAccount'])->name('delete-account');
            Route::post('/deactivate', [AccountController::class, 'deactivateAccount'])->name('deactivate');

            // Analytics Route
            Route::get('/analytics', [AnalyticsController::class, 'jobSeekerAnalytics'])->name('analytics');
            
            // Notifications
            Route::post('/notifications/mark-as-read/{id}', [AccountController::class, 'markNotificationAsRead'])->name('notifications.mark-as-read');
            Route::post('/notifications/mark-all-as-read', [AccountController::class, 'markAllNotificationsAsRead'])->name('notifications.mark-all-as-read');
            Route::get('/notifications', [AccountController::class, 'notifications'])->name('notifications.index');
            
            // Profile routes
            Route::get('/my-profile', [AccountController::class, 'myProfile'])->name('myProfile');
            Route::post('/update-my-profile', [AccountController::class, 'updateProfile'])->name('updateMyProfile');
            Route::post('/update-social-links', [AccountController::class, 'updateSocialLinks'])->name('updateSocialLinks');
            Route::post('/add-experience', [AccountController::class, 'addExperience'])->name('addExperience');
            Route::post('/update-experience', [AccountController::class, 'updateExperience'])->name('updateExperience');
            Route::delete('/delete-experience', [AccountController::class, 'deleteExperience'])->name('deleteExperience');
            Route::post('/add-education', [AccountController::class, 'addEducation'])->name('addEducation');
            Route::post('/update-education', [AccountController::class, 'updateEducation'])->name('updateEducation');
            Route::delete('/delete-education', [AccountController::class, 'deleteEducation'])->name('deleteEducation');
            
            // Resume Management
            Route::post('/upload-resume', [AccountController::class, 'uploadResume'])->name('uploadResume');
            
            // Resume Builder
            Route::prefix('resume-builder')->name('resume-builder.')->group(function () {
                Route::get('/', [\App\Http\Controllers\ResumeBuilderController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\ResumeBuilderController::class, 'create'])->name('create');
                Route::post('/store', [\App\Http\Controllers\ResumeBuilderController::class, 'store'])->name('store');
                Route::get('/{resume}/edit', [\App\Http\Controllers\ResumeBuilderController::class, 'edit'])->name('edit');
                Route::put('/{resume}', [\App\Http\Controllers\ResumeBuilderController::class, 'update'])->name('update');
                Route::delete('/{resume}', [\App\Http\Controllers\ResumeBuilderController::class, 'destroy'])->name('destroy');
                Route::get('/{resume}/preview', [\App\Http\Controllers\ResumeBuilderController::class, 'preview'])->name('preview');
                Route::get('/{resume}/download', [\App\Http\Controllers\ResumeBuilderController::class, 'download'])->name('download');
            });
            
            // Review System
            Route::post('/reviews/store', [ReviewController::class, 'store'])->name('reviews.store');
            Route::put('/reviews/{id}', [ReviewController::class, 'update'])->name('reviews.update');
            Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
            Route::get('/reviews/check-eligibility/{jobId}/{reviewType}', [ReviewController::class, 'checkEligibility'])->name('reviews.checkEligibility');
            Route::get('/my-reviews', [ReviewController::class, 'myReviews'])->name('myReviews');
            
        });
        
        // Job Recommendations
        Route::get('/job-recommendations',[AnalyticsController::class,'jobRecommendations'])->name('account.jobRecommendations');
        Route::get('/candidate-recommendations/{jobId}',[AnalyticsController::class,'candidateRecommendations'])->name('account.candidateRecommendations');

        // Profile routes moved to job seeker middleware group

        // Settings routes
        Route::get('/settings', [AccountController::class, 'settings'])->name('settings');
        Route::post('/update-notifications', [AccountController::class, 'updateNotifications'])->name('updateNotifications');
        Route::post('/update-privacy', [AccountController::class, 'updatePrivacy'])->name('updatePrivacy');
        
        // Password management routes
        Route::match(['GET', 'POST'], '/change-password', [AccountController::class, 'changePassword'])->name('changePassword');
        Route::post('/update-password', [AccountController::class, 'updatePassword'])->name('updatePassword');
        Route::get('/resumes', [AccountController::class, 'resumes'])->name('resumes');
        Route::get('/job-alerts', [AccountController::class, 'jobAlerts'])->name('jobAlerts');
        Route::post('/job-alerts', [AccountController::class, 'updateJobAlerts'])->name('updateJobAlerts');
        Route::delete('/job-alerts/{id}', [AccountController::class, 'deleteJobAlert'])->name('deleteJobAlert');
    });
});

// Employer Routes
Route::group(['prefix' => 'employer', 'middleware' => ['auth', 'role:employer'], 'as' => 'employer.'], function () {
    // Dashboard
    Route::get('/dashboard', [EmployerController::class, 'dashboard'])->name('dashboard');

    // Notifications
    Route::post('/notifications/mark-as-read/{id}', [EmployerController::class, 'markNotificationAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [EmployerController::class, 'markAllNotificationsAsRead'])->name('notifications.mark-all-as-read');
    Route::post('/test-notification', [EmployerController::class, 'testNotification'])->name('notifications.test');

    // Dashboard Data API
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/activities', [EmployerController::class, 'getActivities'])->name('activities');
        Route::get('/chart-data', [EmployerController::class, 'getChartData'])->name('chart-data');
    });

    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [EmployerController::class, 'analytics'])->name('index');
        Route::get('/overview', [EmployerController::class, 'analytics'])->name('overview');
        Route::get('/jobs', [EmployerController::class, 'jobAnalytics'])->name('jobs');
        Route::get('/applicants', [EmployerController::class, 'applicantAnalytics'])->name('applicants');
        Route::get('/export', [EmployerController::class, 'exportAnalytics'])->name('export');
        Route::get('/data', [EmployerController::class, 'getAnalyticsData'])->name('data');
        Route::get('/sources', [EmployerController::class, 'getApplicationSources'])->name('sources');
        Route::get('/update-range', [AnalyticsController::class, 'updateRange'])->name('update-range');
    });

    // Profile Management
    Route::get('/profile', [EmployerController::class, 'editProfile'])->name('profile.edit');
    Route::match(['POST', 'PUT'], '/profile', [EmployerController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile/logo', [EmployerController::class, 'removeLogo'])->name('profile.remove-logo');
    Route::delete('/profile/gallery/{index}', [EmployerController::class, 'removeGalleryImage'])->name('profile.remove-gallery-image');

    // Job Management
    Route::prefix('jobs')->name('jobs.')->group(function () {
        Route::get('/', [EmployerController::class, 'jobs'])->name('index');
        
        // Routes that require KYC verification for unverified employers - TEMPORARILY DISABLED
        // Route::middleware(['employer.kyc'])->group(function () {
            Route::get('/create', [EmployerController::class, 'createJob'])->name('create');
            Route::post('/', [EmployerController::class, 'storeJob'])->name('store');
            Route::get('/{job}/edit', [EmployerController::class, 'editJob'])->name('edit');
            Route::put('/{job}', [EmployerController::class, 'updateJob'])->name('update');
        // });
        
        // Routes that don't require KYC verification
        Route::get('/{job}', [EmployerController::class, 'showJob'])->name('show');
        Route::delete('/{job}', [EmployerController::class, 'deleteJob'])->name('delete');
        Route::get('/drafts', [EmployerController::class, 'draftJobs'])->name('drafts');
    });
    
    // Applications Management
    Route::prefix('applications')->name('applications.')->group(function () {
        Route::get('/', [EmployerController::class, 'jobApplications'])->name('index');
        Route::get('/shortlisted', [EmployerController::class, 'shortlistedApplications'])->name('shortlisted');
        Route::get('/{application}', [EmployerController::class, 'showApplication'])->name('show');
        Route::patch('/{application}/status', [EmployerController::class, 'updateApplicationStatus'])->name('updateStatus');
        Route::post('/{application}/shortlist', [EmployerController::class, 'toggleShortlist'])->name('toggleShortlist');

        // New multi-stage application routes
        Route::patch('/{application}/stage', [EmployerController::class, 'updateApplicationStage'])->name('updateStage');
        Route::post('/{application}/schedule-interview', [EmployerController::class, 'scheduleInterview'])->name('scheduleInterview');
        Route::post('/{application}/reschedule-interview', [EmployerController::class, 'rescheduleInterview'])->name('rescheduleInterview');
        Route::post('/{application}/mark-hired', [EmployerController::class, 'markAsHired'])->name('markHired');
        Route::get('/{application}/documents', [EmployerController::class, 'viewSubmittedDocuments'])->name('documents');
    });
    
    // Jobseeker Profile Viewing
    Route::get('/jobseeker/{userId}/profile', [EmployerController::class, 'viewJobseekerProfile'])->name('jobseeker.profile');
    
    // Job Applicants
    Route::get('/jobs/{jobId}/applicants', [EmployerController::class, 'viewJobApplicants'])->name('jobs.applicants');
    
    // Settings Management
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [EmployerController::class, 'settings'])->name('index');
        Route::post('/', [EmployerController::class, 'updateSettings'])->name('update');
        Route::get('/notifications', [EmployerController::class, 'notificationSettings'])->name('notifications');
        Route::post('/notifications', [EmployerController::class, 'updateNotificationSettings'])->name('notifications.update');
        Route::get('/security', [EmployerController::class, 'securitySettings'])->name('security');
        Route::post('/security/password', [EmployerController::class, 'updatePassword'])->name('password.update');
        Route::post('/security/2fa/enable', [EmployerController::class, 'enable2FA'])->name('2fa.enable');
        Route::post('/security/2fa/disable', [EmployerController::class, 'disable2FA'])->name('2fa.disable');
        Route::post('/account/deactivate', [EmployerController::class, 'deactivateAccount'])->name('account.deactivate');
    });
    
    // Review Management
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Employer\ReviewController::class, 'index'])->name('index');
        Route::post('/{id}/respond', [\App\Http\Controllers\Employer\ReviewController::class, 'respond'])->name('respond');
        Route::put('/{id}/response', [\App\Http\Controllers\Employer\ReviewController::class, 'updateResponse'])->name('updateResponse');
        Route::delete('/{id}/response', [\App\Http\Controllers\Employer\ReviewController::class, 'deleteResponse'])->name('deleteResponse');
    });
    
    // Account Management
    Route::delete('/account/delete', [EmployerController::class, 'deleteAccount'])->name('delete-account');
    
    // Team Management
    Route::delete('/team/members/{teamMember}', [EmployerController::class, 'removeTeamMember'])->name('team.remove');
});

