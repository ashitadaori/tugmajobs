<?php

/**
 * Update social links
 */
public function updateSocialLinks(Request $request)
{
    $user = Auth::user();
    
    // Check if the user is a job seeker
    if (!$user->isJobSeeker()) {
        return back()->with('error', 'This feature is for job seekers only.');
    }
    
    $validator = Validator::make($request->all(), [
        'social_links.linkedin' => 'nullable|url',
        'social_links.github' => 'nullable|url', 
        'social_links.portfolio' => 'nullable|url',
        'social_links.other' => 'nullable|url'
    ]);
    
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }
    
    try {
        // Ensure the user has a job seeker profile
        if (!$user->jobSeekerProfile) {
            $user->jobSeekerProfile()->create([]);
            $user->load('jobSeekerProfile');
        }
        
        // Update social links
        $user->jobSeekerProfile->update([
            'linkedin_url' => $request->social_links['linkedin'] ?? null,
            'github_url' => $request->social_links['github'] ?? null,
            'portfolio_url' => $request->social_links['portfolio'] ?? null,
            'twitter_url' => $request->social_links['other'] ?? null
        ]);
        
        return back()->with('success', 'Social links updated successfully.');
        
    } catch (\Exception $e) {
        \Log::error('Social links update error: ' . $e->getMessage());
        return back()->with('error', 'There was an error updating your social links. Please try again.');
    }
}

/**
 * Add work experience
 */
public function addExperience(Request $request)
{
    $user = Auth::user();
    
    // Check if the user is a job seeker
    if (!$user->isJobSeeker()) {
        return response()->json(['success' => false, 'message' => 'This feature is for job seekers only.'], 403);
    }
    
    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'company' => 'required|string|max:255',
        'location' => 'nullable|string|max:255',
        'start_date' => 'required|string',
        'end_date' => 'nullable|string',
        'currently_working' => 'boolean',
        'description' => 'nullable|string'
    ]);
    
    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }
    
    try {
        // Ensure the user has a job seeker profile
        if (!$user->jobSeekerProfile) {
            $user->jobSeekerProfile()->create([]);
            $user->load('jobSeekerProfile');
        }
        
        // Get existing experience or initialize empty array
        $experiences = $user->jobSeekerProfile->work_experience ? json_decode($user->jobSeekerProfile->work_experience, true) : [];
        
        // Add new experience
        $newExperience = [
            'id' => uniqid(), // Simple unique ID
            'title' => $request->title,
            'company' => $request->company,
            'location' => $request->location,
            'start_date' => $request->start_date,
            'end_date' => $request->currently_working ? null : $request->end_date,
            'currently_working' => $request->boolean('currently_working'),
            'description' => $request->description
        ];
        
        $experiences[] = $newExperience;
        
        // Update the profile
        $user->jobSeekerProfile->update([
            'work_experience' => json_encode($experiences)
        ]);
        
        return response()->json([
            'success' => true, 
            'message' => 'Work experience added successfully.',
            'experience' => $newExperience
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Add experience error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'There was an error adding your experience. Please try again.'], 500);
    }
}

/**
 * Update work experience
 */
public function updateExperience(Request $request)
{
    $user = Auth::user();
    
    // Check if the user is a job seeker
    if (!$user->isJobSeeker()) {
        return response()->json(['success' => false, 'message' => 'This feature is for job seekers only.'], 403);
    }
    
    $validator = Validator::make($request->all(), [
        'id' => 'required|string',
        'title' => 'required|string|max:255',
        'company' => 'required|string|max:255',
        'location' => 'nullable|string|max:255',
        'start_date' => 'required|string',
        'end_date' => 'nullable|string',
        'currently_working' => 'boolean',
        'description' => 'nullable|string'
    ]);
    
    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }
    
    try {
        if (!$user->jobSeekerProfile || !$user->jobSeekerProfile->work_experience) {
            return response()->json(['success' => false, 'message' => 'Experience not found.'], 404);
        }
        
        $experiences = json_decode($user->jobSeekerProfile->work_experience, true);
        $experienceIndex = array_search($request->id, array_column($experiences, 'id'));
        
        if ($experienceIndex === false) {
            return response()->json(['success' => false, 'message' => 'Experience not found.'], 404);
        }
        
        // Update the experience
        $experiences[$experienceIndex] = [
            'id' => $request->id,
            'title' => $request->title,
            'company' => $request->company,
            'location' => $request->location,
            'start_date' => $request->start_date,
            'end_date' => $request->boolean('currently_working') ? null : $request->end_date,
            'currently_working' => $request->boolean('currently_working'),
            'description' => $request->description
        ];
        
        // Update the profile
        $user->jobSeekerProfile->update([
            'work_experience' => json_encode($experiences)
        ]);
        
        return response()->json([
            'success' => true, 
            'message' => 'Work experience updated successfully.',
            'experience' => $experiences[$experienceIndex]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Update experience error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'There was an error updating your experience. Please try again.'], 500);
    }
}

/**
 * Delete work experience
 */
public function deleteExperience(Request $request)
{
    $user = Auth::user();
    
    // Check if the user is a job seeker
    if (!$user->isJobSeeker()) {
        return response()->json(['success' => false, 'message' => 'This feature is for job seekers only.'], 403);
    }
    
    $validator = Validator::make($request->all(), [
        'id' => 'required|string'
    ]);
    
    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }
    
    try {
        if (!$user->jobSeekerProfile || !$user->jobSeekerProfile->work_experience) {
            return response()->json(['success' => false, 'message' => 'Experience not found.'], 404);
        }
        
        $experiences = json_decode($user->jobSeekerProfile->work_experience, true);
        $experienceIndex = array_search($request->id, array_column($experiences, 'id'));
        
        if ($experienceIndex === false) {
            return response()->json(['success' => false, 'message' => 'Experience not found.'], 404);
        }
        
        // Remove the experience
        unset($experiences[$experienceIndex]);
        $experiences = array_values($experiences); // Re-index array
        
        // Update the profile
        $user->jobSeekerProfile->update([
            'work_experience' => json_encode($experiences)
        ]);
        
        return response()->json([
            'success' => true, 
            'message' => 'Work experience deleted successfully.'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Delete experience error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'There was an error deleting your experience. Please try again.'], 500);
    }
}

/**
 * Add education
 */
public function addEducation(Request $request)
{
    $user = Auth::user();
    
    // Check if the user is a job seeker
    if (!$user->isJobSeeker()) {
        return response()->json(['success' => false, 'message' => 'This feature is for job seekers only.'], 403);
    }
    
    $validator = Validator::make($request->all(), [
        'school' => 'required|string|max:255',
        'degree' => 'required|string|max:255',
        'field_of_study' => 'nullable|string|max:255',
        'start_date' => 'required|string',
        'end_date' => 'nullable|string',
        'currently_studying' => 'boolean'
    ]);
    
    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }
    
    try {
        // Ensure the user has a job seeker profile
        if (!$user->jobSeekerProfile) {
            $user->jobSeekerProfile()->create([]);
            $user->load('jobSeekerProfile');
        }
        
        // Get existing education or initialize empty array
        $educations = $user->jobSeekerProfile->education ? json_decode($user->jobSeekerProfile->education, true) : [];
        
        // Add new education
        $newEducation = [
            'id' => uniqid(), // Simple unique ID
            'school' => $request->school,
            'degree' => $request->degree,
            'field_of_study' => $request->field_of_study,
            'start_date' => $request->start_date,
            'end_date' => $request->boolean('currently_studying') ? null : $request->end_date,
            'currently_studying' => $request->boolean('currently_studying')
        ];
        
        $educations[] = $newEducation;
        
        // Update the profile
        $user->jobSeekerProfile->update([
            'education' => json_encode($educations)
        ]);
        
        return response()->json([
            'success' => true, 
            'message' => 'Education added successfully.',
            'education' => $newEducation
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Add education error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'There was an error adding your education. Please try again.'], 500);
    }
}

/**
 * Update education
 */
public function updateEducation(Request $request)
{
    $user = Auth::user();
    
    // Check if the user is a job seeker
    if (!$user->isJobSeeker()) {
        return response()->json(['success' => false, 'message' => 'This feature is for job seekers only.'], 403);
    }
    
    $validator = Validator::make($request->all(), [
        'id' => 'required|string',
        'school' => 'required|string|max:255',
        'degree' => 'required|string|max:255',
        'field_of_study' => 'nullable|string|max:255',
        'start_date' => 'required|string',
        'end_date' => 'nullable|string',
        'currently_studying' => 'boolean'
    ]);
    
    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }
    
    try {
        if (!$user->jobSeekerProfile || !$user->jobSeekerProfile->education) {
            return response()->json(['success' => false, 'message' => 'Education not found.'], 404);
        }
        
        $educations = json_decode($user->jobSeekerProfile->education, true);
        $educationIndex = array_search($request->id, array_column($educations, 'id'));
        
        if ($educationIndex === false) {
            return response()->json(['success' => false, 'message' => 'Education not found.'], 404);
        }
        
        // Update the education
        $educations[$educationIndex] = [
            'id' => $request->id,
            'school' => $request->school,
            'degree' => $request->degree,
            'field_of_study' => $request->field_of_study,
            'start_date' => $request->start_date,
            'end_date' => $request->boolean('currently_studying') ? null : $request->end_date,
            'currently_studying' => $request->boolean('currently_studying')
        ];
        
        // Update the profile
        $user->jobSeekerProfile->update([
            'education' => json_encode($educations)
        ]);
        
        return response()->json([
            'success' => true, 
            'message' => 'Education updated successfully.',
            'education' => $educations[$educationIndex]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Update education error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'There was an error updating your education. Please try again.'], 500);
    }
}

/**
 * Delete education
 */
public function deleteEducation(Request $request)
{
    $user = Auth::user();
    
    // Check if the user is a job seeker
    if (!$user->isJobSeeker()) {
        return response()->json(['success' => false, 'message' => 'This feature is for job seekers only.'], 403);
    }
    
    $validator = Validator::make($request->all(), [
        'id' => 'required|string'
    ]);
    
    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }
    
    try {
        if (!$user->jobSeekerProfile || !$user->jobSeekerProfile->education) {
            return response()->json(['success' => false, 'message' => 'Education not found.'], 404);
        }
        
        $educations = json_decode($user->jobSeekerProfile->education, true);
        $educationIndex = array_search($request->id, array_column($educations, 'id'));
        
        if ($educationIndex === false) {
            return response()->json(['success' => false, 'message' => 'Education not found.'], 404);
        }
        
        // Remove the education
        unset($educations[$educationIndex]);
        $educations = array_values($educations); // Re-index array
        
        // Update the profile
        $user->jobSeekerProfile->update([
            'education' => json_encode($educations)
        ]);
        
        return response()->json([
            'success' => true, 
            'message' => 'Education deleted successfully.'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Delete education error: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'There was an error deleting your education. Please try again.'], 500);
    }
}
