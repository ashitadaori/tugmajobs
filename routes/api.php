<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\JobController;
use App\Http\Controllers\Api\V1\JobApplicationController;
use App\Http\Controllers\Api\V1\EmployerController;
use App\Http\Controllers\Api\V1\SearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public routes
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

    // Public job routes
    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/featured', [JobController::class, 'featured'])->name('jobs.featured');
    Route::get('/jobs/recent', [JobController::class, 'recent'])->name('jobs.recent');
    Route::get('/jobs/statistics', [JobController::class, 'statistics'])->name('jobs.statistics');
    Route::get('/jobs/category/{categoryId}', [JobController::class, 'byCategory'])->name('jobs.by-category');
    Route::get('/jobs/{id}', [JobController::class, 'show'])->name('jobs.show');

    // Public employer routes
    Route::get('/employers', [EmployerController::class, 'index'])->name('employers.index');
    Route::get('/employers/featured', [EmployerController::class, 'featured'])->name('employers.featured');
    Route::get('/employers/top-hiring', [EmployerController::class, 'topHiring'])->name('employers.top-hiring');
    Route::get('/employers/statistics', [EmployerController::class, 'statistics'])->name('employers.statistics');
    Route::get('/employers/search', [EmployerController::class, 'search'])->name('employers.search');
    Route::get('/employers/{id}', [EmployerController::class, 'show'])->name('employers.show');
    Route::get('/employers/slug/{slug}', [EmployerController::class, 'showBySlug'])->name('employers.show-by-slug');

    // Public search routes
    Route::prefix('search')->name('search.')->group(function () {
        Route::get('/autocomplete', [SearchController::class, 'autocomplete'])->name('autocomplete');
        Route::get('/trending', [SearchController::class, 'trending'])->name('trending');
        Route::get('/jobs', [SearchController::class, 'jobs'])->name('jobs');
    });

    // Authenticated routes
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/user', [AuthController::class, 'user'])->name('auth.user');
        Route::post('/auth/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
        Route::post('/auth/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');

        // Jobseeker routes
        Route::middleware(['role:jobseeker'])->group(function () {
            Route::get('/applications', [JobApplicationController::class, 'index'])->name('applications.index');
            Route::post('/jobs/{jobId}/apply', [JobApplicationController::class, 'store'])->name('applications.store');
            Route::get('/applications/{id}', [JobApplicationController::class, 'show'])->name('applications.show');
            Route::delete('/applications/{id}', [JobApplicationController::class, 'withdraw'])->name('applications.withdraw');

            // Saved searches
            Route::get('/saved-searches', [SearchController::class, 'savedSearches'])->name('saved-searches.index');
            Route::post('/saved-searches', [SearchController::class, 'saveSearch'])->name('saved-searches.store');
            Route::delete('/saved-searches/{id}', [SearchController::class, 'deleteSavedSearch'])->name('saved-searches.destroy');
            Route::get('/saved-searches/{id}/run', [SearchController::class, 'runSavedSearch'])->name('saved-searches.run');
        });

        // Employer routes
        Route::middleware(['role:employer'])->prefix('employer')->name('employer.')->group(function () {
            // Profile
            Route::get('/profile', [EmployerController::class, 'profile'])->name('profile');
            Route::put('/profile', [EmployerController::class, 'updateProfile'])->name('profile.update');

            // Jobs
            Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
            Route::put('/jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
            Route::delete('/jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');

            // Applications
            Route::get('/applications', [JobApplicationController::class, 'employerApplications'])->name('applications.index');
            Route::get('/applications/shortlisted', [JobApplicationController::class, 'shortlisted'])->name('applications.shortlisted');
            Route::get('/applications/statistics', [JobApplicationController::class, 'statistics'])->name('applications.statistics');
            Route::patch('/applications/{id}/status', [JobApplicationController::class, 'updateStatus'])->name('applications.update-status');
            Route::post('/applications/{id}/shortlist', [JobApplicationController::class, 'toggleShortlist'])->name('applications.toggle-shortlist');
        });
    });
});

// KYC Status Check API
Route::middleware('auth:sanctum')->get('/user/kyc-status', function (Request $request) {
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = Auth::user();
    return response()->json([
        'kyc_status' => $user->kyc_status,
        'is_verified' => $user->isKycVerified(),
        'status_text' => $user->kyc_status_text,
        'updated_at' => $user->updated_at->toISOString()
    ]);
});

Route::prefix('locations')->group(function () {
    Route::get('/areas', [App\Http\Controllers\Api\LocationController::class, 'getAllAreas']);
    Route::get('/search', [App\Http\Controllers\Api\LocationController::class, 'search']);
    Route::get('/nearest', [App\Http\Controllers\Api\LocationController::class, 'getNearestArea']);
});

// KYC Webhook Routes (No authentication required - external service)
Route::post('/kyc/webhook', App\Http\Controllers\KycWebhookController::class)->name('kyc.webhook');
Route::get('/kyc/webhook', [App\Http\Controllers\KycController::class, 'handleUserRedirect'])->name('kyc.webhook.redirect');
