<?php

use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JobApplicationController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\KycController;
use App\Http\Controllers\Admin\PosterController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/export', [DashboardController::class, 'exportStatistics'])->name('dashboard.export');
    Route::post('/dashboard/clear-cache', [DashboardController::class, 'clearCache'])->name('dashboard.clear-cache');

    // Profile Management
    Route::prefix('profile')->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/password', [ProfileController::class, 'password'])->name('profile.password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
        Route::get('/security', [ProfileController::class, 'security'])->name('profile.security');
    });

    // User Management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/search', [UserController::class, 'search'])->name('users.search');
        Route::get('/export', [UserController::class, 'export'])->name('users.export');
        Route::post('/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
        Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Admin Management
    Route::prefix('admins')->group(function () {
        Route::get('/', [AdminManagementController::class, 'index'])->name('admins.index');
        Route::get('/create', [AdminManagementController::class, 'create'])->name('admins.create');
        Route::post('/', [AdminManagementController::class, 'store'])->name('admins.store');
        Route::get('/{admin}/edit', [AdminManagementController::class, 'edit'])->name('admins.edit');
        Route::put('/{admin}', [AdminManagementController::class, 'update'])->name('admins.update');
        Route::delete('/{admin}', [AdminManagementController::class, 'destroy'])->name('admins.destroy');
    });

    // KYC Management
    Route::prefix('kyc')->group(function () {
        // Manual KYC Document Review (for Philippine IDs and documents not supported by DiDit)
        Route::get('/manual-documents', [KycController::class, 'manualDocuments'])->name('kyc.manual-documents');
        Route::get('/manual-documents/{document}', [KycController::class, 'showManualDocument'])->name('kyc.show-manual-document');
        Route::patch('/manual-documents/{document}/verify', [KycController::class, 'verifyManualDocument'])->name('kyc.verify-manual-document');
        Route::patch('/manual-documents/{document}/reject', [KycController::class, 'rejectManualDocument'])->name('kyc.reject-manual-document');
        Route::get('/manual-documents/{document}/download', [KycController::class, 'downloadManualDocument'])->name('kyc.download-manual-document');
        Route::get('/manual-documents/{document}/view-image/{type}', [KycController::class, 'viewManualDocumentImage'])->name('kyc.view-manual-document-image');

        // DiDit KYC Management
        Route::get('/didit-verifications', [KycController::class, 'diditVerifications'])->name('kyc.didit-verifications');
        Route::get('/user/{user}/verification', [KycController::class, 'showDiditVerification'])->name('kyc.show-didit-verification');
        Route::patch('/user/{user}/approve', [KycController::class, 'approveDiditVerification'])->name('kyc.approve-didit-verification');
        Route::patch('/user/{user}/reject', [KycController::class, 'rejectDiditVerification'])->name('kyc.reject-didit-verification');
        Route::post('/refresh-verification/{user}', [KycController::class, 'refreshVerification'])->name('kyc.refresh-verification');
        Route::delete('/user/{user}/reset', [KycController::class, 'resetKyc'])->name('kyc.reset');
    });

    // Job Management Routes
    Route::prefix('jobs')->name('jobs.')->group(function () {
        Route::get('/', [JobController::class, 'index'])->name('index');
        Route::get('/search', [JobController::class, 'search'])->name('search');
        Route::get('/my-posted', [JobController::class, 'myPostedJobs'])->name('my-posted');
        Route::get('/pending', [JobController::class, 'pending'])->name('pending');
        Route::get('/create', [JobController::class, 'create'])->name('create');
        Route::post('/', [JobController::class, 'store'])->name('store');

        // Application management routes (hiring pipeline) - MUST be before {job} wildcard routes
        Route::get('/applications/{application}', [JobController::class, 'viewApplication'])->name('application.show');
        Route::get('/applications/{application}/documents', [JobController::class, 'viewSubmittedDocuments'])->name('application.documents');
        Route::patch('/applications/{application}/stage', [JobController::class, 'updateApplicationStage'])->name('application.updateStage');
        Route::post('/applications/{application}/schedule-interview', [JobController::class, 'scheduleInterview'])->name('application.scheduleInterview');
        Route::post('/applications/{application}/reschedule-interview', [JobController::class, 'rescheduleInterview'])->name('application.rescheduleInterview');
        Route::post('/applications/{application}/mark-hired', [JobController::class, 'markAsHired'])->name('application.markHired');

        // Job-specific routes with {job} wildcard - MUST be after literal routes like /applications
        Route::get('/{job}', [JobController::class, 'show'])->name('show');
        Route::get('/{job}/edit', [JobController::class, 'edit'])->name('edit');
        Route::put('/{job}', [JobController::class, 'update'])->name('update');
        Route::delete('/{job}', [JobController::class, 'destroy'])->name('destroy');
        Route::post('/{job}/restore', [JobController::class, 'restore'])->name('restore');
        Route::patch('/{job}/approve', [JobController::class, 'approve'])->name('approve');
        Route::patch('/{job}/reject', [JobController::class, 'reject'])->name('reject');
        Route::get('/{job}/applicants', [JobController::class, 'viewApplicants'])->name('applicants');
    });

    // Categories Management
    Route::resource('categories', CategoryController::class);

    // Company Management (Standalone Companies)
    Route::prefix('company-management')->name('company-management.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CompanyManagementController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\CompanyManagementController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\CompanyManagementController::class, 'store'])->name('store');
        Route::get('/{company}', [\App\Http\Controllers\Admin\CompanyManagementController::class, 'show'])->name('show');
        Route::get('/{company}/edit', [\App\Http\Controllers\Admin\CompanyManagementController::class, 'edit'])->name('edit');
        Route::put('/{company}', [\App\Http\Controllers\Admin\CompanyManagementController::class, 'update'])->name('update');
        Route::delete('/{company}', [\App\Http\Controllers\Admin\CompanyManagementController::class, 'destroy'])->name('destroy');
    });

    // Employer Companies (User-based)
    Route::prefix('companies')->name('companies.')->group(function () {
        Route::get('/', [CompanyController::class, 'index'])->name('index');
        Route::get('/{id}', [CompanyController::class, 'show'])->name('show');
    });

    // Analytics Dashboard (K-Means, Geolocation, Labor Trends)
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PesoAnalyticsController::class, 'index'])->name('dashboard');

        Route::get('/skills', [\App\Http\Controllers\Admin\PesoAnalyticsController::class, 'getSkillTrends'])->name('skills');
        Route::get('/map', [\App\Http\Controllers\Admin\PesoAnalyticsController::class, 'getJobMapData'])->name('map');
        Route::get('/density', [\App\Http\Controllers\Admin\PesoAnalyticsController::class, 'getApplicantDensityData'])->name('density');
        Route::get('/jobfair', [\App\Http\Controllers\Admin\PesoAnalyticsController::class, 'getJobFairPlanningData'])->name('jobfair');
        Route::get('/export', [\App\Http\Controllers\Admin\PesoAnalyticsController::class, 'exportClusterReport'])->name('export');
        Route::post('/clear-cache', [\App\Http\Controllers\Admin\PesoAnalyticsController::class, 'clearCache'])->name('clear-cache');

        // Azure ML K-Means Clustering API routes
        Route::get('/kmeans', [\App\Http\Controllers\Admin\KMeansVisualizationController::class, 'index'])->name('kmeans');
        Route::get('/kmeans/data', [\App\Http\Controllers\Admin\KMeansVisualizationController::class, 'getData'])->name('kmeans.data');
        Route::get('/kmeans/health', [\App\Http\Controllers\Admin\KMeansVisualizationController::class, 'healthCheck'])->name('kmeans.health');
        Route::post('/kmeans/refresh', [\App\Http\Controllers\Admin\KMeansVisualizationController::class, 'refreshClusters'])->name('kmeans.refresh');
    });

    // Audit Reports
    Route::prefix('audit-reports')->name('audit-reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AuditReportController::class, 'index'])->name('index');
        Route::get('/export', [\App\Http\Controllers\Admin\AuditReportController::class, 'export'])->name('export');
    });

    // Settings
    Route::prefix('settings')->group(function () {
        Route::get('/', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::put('/', [AdminSettingsController::class, 'update'])->name('settings.update');
        Route::post('/clear-cache', [AdminSettingsController::class, 'clearCache'])->name('settings.clear-cache');
        Route::get('/security-log', [AdminSettingsController::class, 'securityLog'])->name('settings.security-log');
        Route::get('/audit-log', [AdminSettingsController::class, 'auditLog'])->name('settings.audit-log');
    });

    // Maintenance Mode
    Route::prefix('maintenance')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::put('/update', [\App\Http\Controllers\Admin\MaintenanceController::class, 'update'])->name('maintenance.update');
    });

    // Quick Actions Routes
    Route::prefix('quick-actions')->name('quick-actions.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\QuickActionsController::class, 'getDashboardData'])->name('dashboard');
        Route::post('/bulk-approve-jobs', [\App\Http\Controllers\Admin\QuickActionsController::class, 'bulkApproveJobs'])->name('bulk-approve-jobs');
        Route::post('/bulk-reject-jobs', [\App\Http\Controllers\Admin\QuickActionsController::class, 'bulkRejectJobs'])->name('bulk-reject-jobs');
        Route::post('/bulk-verify-kyc', [\App\Http\Controllers\Admin\QuickActionsController::class, 'bulkVerifyKyc'])->name('bulk-verify-kyc');
        Route::post('/send-mass-email', [\App\Http\Controllers\Admin\QuickActionsController::class, 'sendMassEmail'])->name('send-mass-email');
    });

    // Global Search Routes
    Route::prefix('search')->name('search.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\GlobalSearchController::class, 'search'])->name('global');
        Route::post('/presets', [\App\Http\Controllers\Admin\GlobalSearchController::class, 'savePreset'])->name('save-preset');
        Route::get('/presets', [\App\Http\Controllers\Admin\GlobalSearchController::class, 'getPresets'])->name('get-presets');
        Route::delete('/presets/{id}', [\App\Http\Controllers\Admin\GlobalSearchController::class, 'deletePreset'])->name('delete-preset');
        Route::post('/recent', [\App\Http\Controllers\Admin\GlobalSearchController::class, 'saveRecentSearch'])->name('save-recent');
        Route::get('/recent', [\App\Http\Controllers\Admin\GlobalSearchController::class, 'getRecentSearches'])->name('get-recent');
        Route::delete('/recent', [\App\Http\Controllers\Admin\GlobalSearchController::class, 'clearRecentSearches'])->name('clear-recent');
    });

    // Notification Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('index');
        Route::post('/mark-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'delete'])->name('delete');
        Route::post('/clear-read', [\App\Http\Controllers\Admin\NotificationController::class, 'clearRead'])->name('clear-read');
    });

    // Graphic Poster Builder Routes
    Route::prefix('posters')->name('posters.')->group(function () {
        Route::get('/', [PosterController::class, 'index'])->name('index');
        Route::get('/create', [PosterController::class, 'create'])->name('create');
        Route::post('/', [PosterController::class, 'store'])->name('store');
        Route::get('/{poster}/edit', [PosterController::class, 'edit'])->name('edit');
        Route::put('/{poster}', [PosterController::class, 'update'])->name('update');
        Route::delete('/{poster}', [PosterController::class, 'destroy'])->name('destroy');
        Route::get('/{poster}/preview', [PosterController::class, 'preview'])->name('preview');
        Route::get('/{poster}/download', [PosterController::class, 'download'])->name('download');

        // Template management
        Route::get('/templates/manage', [PosterController::class, 'templates'])->name('templates');
        Route::post('/templates/{template}/toggle', [PosterController::class, 'toggleTemplate'])->name('templates.toggle');
    });
});
