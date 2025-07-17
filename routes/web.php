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
use App\Http\Controllers\JobsController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\AIFeaturesController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\SavedJobController;
use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use OpenAI\Laravel\Facades\OpenAI;

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
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);

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
Route::get('/jobs',[JobsController::class,'index'])->name('jobs');
Route::get('/jobs/detail/{id}',[JobsController::class,'jobDetail'])->name('jobDetail');
Route::get('/companies', [CompanyController::class, 'index'])->name('companies');
Route::get('/companies/{id}', [CompanyController::class, 'show'])->name('companies.show');

// Location API Routes
Route::prefix('api/location')->name('api.location.')->group(function () {
    Route::get('/search', [LocationController::class, 'searchPlaces'])->name('search');
    Route::get('/geocode', [LocationController::class, 'geocode'])->name('geocode');
    Route::get('/reverse-geocode', [LocationController::class, 'reverseGeocode'])->name('reverse-geocode');
    Route::get('/config', [LocationController::class, 'getConfig'])->name('config');
});

// Job Application and Save Routes (must be authenticated)
Route::middleware(['auth'])->group(function() {
    Route::post('/jobs/{id}/apply', [JobsController::class, 'applyJob'])->name('jobs.apply');
    Route::post('/jobs/{id}/save', [JobsController::class, 'saveJob'])->name('jobs.save');
    Route::post('/jobs/{id}/unsave', [JobsController::class, 'unsaveJob'])->name('jobs.unsave');
    Route::post('/jobs/{job}/toggle-save', [SavedJobController::class, 'toggleSave'])
        ->name('jobs.toggle-save');
});

// Guest routes for saving jobs will be handled in authenticated group
// Forgot password
Route::get('/forgot-password',[AccountController::class,'forgotPassword'])->name('account.forgotPassword');
Route::post('/process-forgot-password',[AccountController::class,'processForgotPassword'])->name('account.processForgotPassword');
Route::get('/reset-password/{token}',[AccountController::class,'resetPassword'])->name('account.resetPassword');
Route::post('/process-reset-password',[AccountController::class,'processResetPassword'])->name('account.processResetPassword');

// KYC Routes (Auth Required)
Route::middleware(['auth'])->prefix('kyc')->name('kyc.')->group(function () {
    Route::get('/', function() { return view('kyc.start'); })->name('index');
    Route::get('/start', function() { return view('kyc.start'); })->name('start.form');
    Route::post('/start', [KycController::class, 'startVerification'])->name('start');
    Route::post('/check-status', [KycController::class, 'checkStatus'])->name('check-status');
    Route::post('/dismiss-banner', function() { 
        session(['kyc_banner_dismissed' => true]); 
        return response()->json(['status' => 'dismissed']); 
    })->name('dismiss-banner');
    
    // Test route for debugging
    Route::get('/test', function() { return view('kyc.test'); })->name('test');
});

// KYC redirect routes (No auth required - users return from external Didit verification)
Route::prefix('kyc')->name('kyc.')->group(function () {
    Route::get('/success', [KycController::class, 'redirectHandler'])->name('success');
    Route::get('/failure', [KycController::class, 'failure'])->name('failure');
    
    // Mock verification route for development/testing
    Route::get('/mock-verify', function() {
        $user = Auth::user();
        
        // Update user status to verified
        $user->update([
            'kyc_status' => 'verified',
            'kyc_verified_at' => now(),
            'kyc_data' => [
                'session_id' => \Illuminate\Support\Str::uuid()->toString(),
                'status' => 'completed',
                'completed_at' => now()->toIso8601String(),
                'mock' => true
            ]
        ]);
        
        return redirect()->route('kyc.success', [
            'session_id' => $user->kyc_data['session_id'],
            'status' => 'completed'
        ])->with('success', 'Mock verification completed successfully!');
    })->name('mock-verify');
});

// Didit Webhook (no auth middleware needed for webhooks)
Route::post('/kyc/webhook', [KycController::class, 'webhook'])->name('kyc.webhook');

// Add KYC middleware to job application and job posting routes
Route::middleware(['auth', 'kyc.verified'])->group(function () {
    // Job Application Routes
    Route::post('/jobs/{job}/apply', [JobApplicationController::class, 'store'])->name('jobs.apply');
    
    // Job Posting Routes (for employers) - Moved to employer route group below
});

// Admin Routes - Consolidated
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Main Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile Management
    Route::prefix('profile')->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/password', [ProfileController::class, 'password'])->name('profile.password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    });
    
    // User Management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
    
    // KYC Management
    Route::prefix('kyc')->group(function () {
        Route::get('/', [KycController::class, 'index'])->name('kyc.index');
        Route::put('/{document}/verify', [KycController::class, 'verify'])->name('kyc.verify');
        Route::put('/{document}/reject', [KycController::class, 'reject'])->name('kyc.reject');
    });
    
    // Jobs Management
    Route::resource('jobs', JobsController::class);
    Route::patch('/jobs/{job}/status', [JobsController::class, 'updateStatus'])->name('jobs.updateStatus');
    
    // Categories Management
    Route::resource('categories', CategoryController::class);
    
    // Job Types Management
    Route::resource('job-types', JobTypeController::class);
    
    // Analytics
    Route::prefix('analytics')->group(function () {
        Route::get('/', [AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
        Route::get('/job-clusters', [AnalyticsController::class, 'jobClusters'])->name('analytics.jobClusters');
        Route::get('/user-clusters', [AnalyticsController::class, 'userClusters'])->name('analytics.userClusters');
    });
    
    // Settings
    Route::prefix('settings')->group(function () {
        Route::get('/', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::post('/', [AdminSettingsController::class, 'update'])->name('settings.update');
        Route::get('/security-log', [AdminSettingsController::class, 'securityLog'])->name('settings.security-log');
        Route::get('/audit-log', [AdminSettingsController::class, 'auditLog'])->name('settings.audit-log');
    });
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::prefix('account')->name('account.')->group(function () {
        // Remove the old profile route and keep only my-profile
        Route::match(['post', 'put'], '/update-profile', [AccountController::class, 'updateProfile'])->name('updateProfile');
        Route::post('/update-profile-img', [AccountController::class, 'updateProfileImg'])->name('updateProfileimg');
        Route::post('/store-message', [AccountController::class, 'storeMessage'])->name('storeMessage');
        Route::post('/update-password', [AccountController::class, 'updatePassword'])->name('update.password');
        
        // Job Seeker Routes
        Route::group(['middleware' => ['auth', 'role:jobseeker']], function() {
            Route::get('/dashboard', [AccountController::class, 'dashboard'])->name('dashboard');
            Route::get('/my-job-applications', [AccountController::class, 'myJobApplications'])->name('myJobApplications');
            Route::post('/remove-job-application', [AccountController::class, 'removeJobs'])->name('removeJobs');
            Route::get('/saved-jobs', [AccountController::class, 'savedJobs'])->name('savedJobs');
            Route::post('/remove-saved-job', [AccountController::class, 'removeSavedJob'])->name('removeSavedJob');
            Route::post('/save-job-to-favorites', [AccountController::class, 'saveJobToFavorites'])->name('saveJobToFavorites');
            Route::get('/change-password', [AccountController::class, 'changePassword'])->name('changePassword');
            Route::get('/delete-profile', [AccountController::class, 'deleteProfile'])->name('deleteProfile');
            Route::post('/delete-account', [AccountController::class, 'deleteAccount'])->name('delete-account');
            
            // Resume Management
            Route::post('/upload-resume', [AccountController::class, 'uploadResume'])->name('uploadResume');
            
            // AI Features Routes
            Route::get('/ai/job-match', [AIFeaturesController::class, 'showJobMatch'])->name('ai.job-match');
            Route::post('/ai/job-match/analyze', [AIFeaturesController::class, 'getJobMatch'])->name('ai.job-match.analyze');
            Route::get('/ai/resume-builder', [AIFeaturesController::class, 'showResumeBuilder'])->name('ai.resume-builder');
            Route::post('/ai/resume-builder/generate', [AIFeaturesController::class, 'generateResume'])->name('ai.resume-builder.generate');
            Route::post('/ai/resume-builder/analyze', [AIFeaturesController::class, 'analyzeResume'])->name('ai.resume-builder.analyze');
        });
        
        // Job Recommendations
        Route::get('/job-recommendations',[AnalyticsController::class,'jobRecommendations'])->name('account.jobRecommendations');
        Route::get('/candidate-recommendations/{jobId}',[AnalyticsController::class,'candidateRecommendations'])->name('account.candidateRecommendations');

        // Add new routes for profile and settings
        Route::get('/my-profile', [AccountController::class, 'myProfile'])->name('myProfile');
        Route::post('/update-my-profile', [AccountController::class, 'updateProfile'])->name('updateMyProfile');
        Route::post('/update-social-links', [AccountController::class, 'updateSocialLinks'])->name('updateSocialLinks');
        Route::post('/add-experience', [AccountController::class, 'addExperience'])->name('addExperience');
        Route::post('/update-experience', [AccountController::class, 'updateExperience'])->name('updateExperience');
        Route::delete('/delete-experience', [AccountController::class, 'deleteExperience'])->name('deleteExperience');
        Route::post('/add-education', [AccountController::class, 'addEducation'])->name('addEducation');
        Route::post('/update-education', [AccountController::class, 'updateEducation'])->name('updateEducation');
        Route::delete('/delete-education', [AccountController::class, 'deleteEducation'])->name('deleteEducation');

        // Settings routes
        Route::get('/settings', [AccountController::class, 'settings'])->name('settings');
        Route::post('/update-notifications', [AccountController::class, 'updateNotifications'])->name('updateNotifications');
        Route::post('/update-privacy', [AccountController::class, 'updatePrivacy'])->name('updatePrivacy');
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
    
    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [EmployerController::class, 'analytics'])->name('index');
        Route::get('/overview', [EmployerController::class, 'analytics'])->name('overview');
        Route::get('/jobs', [EmployerController::class, 'jobAnalytics'])->name('jobs');
        Route::get('/applicants', [EmployerController::class, 'applicantAnalytics'])->name('applicants');
        Route::get('/export', [EmployerController::class, 'exportAnalytics'])->name('export');
        Route::get('/update-range', [AnalyticsController::class, 'updateRange'])->name('update-range');
    });
    
    // Profile Management
    Route::get('/profile', [EmployerController::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile', [EmployerController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile/logo', [EmployerController::class, 'removeLogo'])->name('profile.remove-logo');
    Route::delete('/profile/gallery/{index}', [EmployerController::class, 'removeGalleryImage'])->name('profile.remove-gallery-image');
    
    // Job Management
    Route::prefix('jobs')->name('jobs.')->group(function () {
        Route::get('/', [EmployerController::class, 'jobs'])->name('index');
        Route::get('/create', [EmployerController::class, 'createJob'])->name('create');
        Route::post('/', [EmployerController::class, 'storeJob'])->name('store');
        Route::get('/{job}', [EmployerController::class, 'showJob'])->name('show');
        Route::get('/{job}/edit', [EmployerController::class, 'editJob'])->name('edit');
        Route::put('/{job}', [EmployerController::class, 'updateJob'])->name('update');
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
    });
    
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
    });
    
    // Account Management
    Route::delete('/account/delete', [EmployerController::class, 'deleteAccount'])->name('delete-account');
    
    // Team Management
    Route::delete('/team/members/{teamMember}', [EmployerController::class, 'removeTeamMember'])->name('team.remove');
});
