<?php

// Admin Routes - Commented out to avoid namespace conflicts
// Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin'], 'namespace' => 'App\Modules\Admin\Http\Controllers'], function () {
//     require __DIR__ . '/admin.php';
// });

// Employer Routes - Commented out to avoid conflicts with web.php routes
// Route::group(['prefix' => 'employer', 'middleware' => ['auth', 'employer'], 'namespace' => 'App\Modules\Employer\Http\Controllers'], function () {
//     Route::get('/dashboard', 'EmployerController@dashboard')->name('employer.dashboard');
//     // ... Add other employer routes
// });

// JobSeeker Routes - Commented out to avoid namespace conflicts
// Route::group(['middleware' => ['auth'], 'namespace' => 'App\Modules\JobSeeker\Http\Controllers'], function () {
//     Route::get('/jobs', 'JobsController@index')->name('jobs.index');
//     Route::get('/jobs/{job}', 'JobsController@show')->name('jobs.show');
//     Route::post('/jobs/{job}/save', 'SavedJobController@store')->name('jobs.save');
//     // ... Add other job seeker routes
// }); 