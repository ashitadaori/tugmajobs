<?php

// Temporary debug route - add this to routes/web.php
Route::get('/debug-profile-image', function() {
    $user = auth()->user();
    
    if (!$user) {
        return 'Please login first';
    }
    
    $profileImage = $user->image ?? $user->profile_image;
    
    $data = [
        'User ID' => $user->id,
        'User Name' => $user->name,
        'Image Field' => $user->image,
        'Profile Image Field' => $user->profile_image,
        'Selected Image' => $profileImage,
        'Is URL' => filter_var($profileImage, FILTER_VALIDATE_URL) ? 'YES' : 'NO',
        'Starts with storage/' => str_starts_with($profileImage, 'storage/') ? 'YES' : 'NO',
        'File Path' => 'storage/app/public/' . $profileImage,
        'File Exists' => Storage::disk('public')->exists($profileImage) ? 'YES' : 'NO',
        'Asset URL' => asset('storage/' . $profileImage),
        'Storage URL' => Storage::url($profileImage),
    ];
    
    $html = '<h1>Profile Image Debug</h1>';
    $html .= '<table border="1" cellpadding="10">';
    foreach ($data as $key => $value) {
        $html .= '<tr><td><strong>' . $key . '</strong></td><td>' . $value . '</td></tr>';
    }
    $html .= '</table>';
    
    $html .= '<h2>Image Preview</h2>';
    $html .= '<img src="' . asset('storage/' . $profileImage) . '" style="width: 200px; height: 200px; object-fit: cover; border: 2px solid #000;">';
    
    $html .= '<h2>Direct File Test</h2>';
    $html .= '<a href="' . asset('storage/' . $profileImage) . '" target="_blank">Open image in new tab</a>';
    
    return $html;
})->middleware('auth');
