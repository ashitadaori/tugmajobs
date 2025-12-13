<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Jobseeker;
use App\Models\Category;
use App\Models\JobType;
use App\Services\KMeansClusteringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class JobseekerProfileKMeansController extends Controller
{
    protected $kmeansService;

    public function __construct(KMeansClusteringService $kmeansService)
    {
        $this->kmeansService = $kmeansService;
        $this->middleware('auth');
    }

    /**
     * Display the K-means enhanced profile page
     */
    public function profile()
    {
        $user = Auth::user();
        
        if (!$user->isJobSeeker()) {
            return redirect()->route('profile.index')->with('error', 'This profile is for job seekers only.');
        }

        // Load or create jobseeker profile
        $jobseekerProfile = $user->jobSeekerProfile;
        if (!$jobseekerProfile) {
            $jobseekerProfile = $this->createDefaultJobseekerProfile($user);
        }

        // Get categories and job types for form dropdowns
        $categories = Category::where('status', 1)->get();
        $jobTypes = JobType::where('status', 1)->get();

        // Calculate profile completion percentage
        $completionPercentage = $this->calculateKMeansProfileCompletion($jobseekerProfile);

        // Get job recommendations using K-means
        $recommendedJobs = $this->kmeansService->getJobRecommendations($user->id, 5);

        // Get user's cluster insights
        $clusterInsights = $this->getUserClusterInsights($user);

        return view('front.account.kmeans-profile', [
            'user' => $user,
            'profile' => $jobseekerProfile,
            'categories' => $categories,
            'jobTypes' => $jobTypes,
            'completionPercentage' => $completionPercentage,
            'recommendedJobs' => $recommendedJobs,
            'clusterInsights' => $clusterInsights
        ]);
    }

    /**
     * Update jobseeker profile with K-means optimization
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        Log::info('K-means Profile Update Started', [
            'user_id' => $user->id,
            'request_data' => $request->except(['_token', 'resume_file', 'profile_photo'])
        ]);

        if (!$user->isJobSeeker()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = $this->getProfileValidator($request);

        if ($validator->fails()) {
            Log::warning('K-means Profile Update Validation Failed', [
                'user_id' => $user->id,
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Get or create jobseeker profile
            $jobseekerProfile = $user->jobSeekerProfile;
            if (!$jobseekerProfile) {
                $jobseekerProfile = $this->createDefaultJobseekerProfile($user);
            }

            // Update basic user information
            $this->updateBasicUserInfo($user, $request);

            // Update jobseeker profile with K-means optimized fields
            $this->updateJobseekerProfileData($jobseekerProfile, $request);

            // Calculate and update profile completion
            $completionPercentage = $this->calculateKMeansProfileCompletion($jobseekerProfile);
            $jobseekerProfile->update(['profile_completion_percentage' => $completionPercentage]);

            // Update profile status based on completion
            $this->updateProfileStatus($jobseekerProfile, $completionPercentage);

            // Calculate search score for better job matching
            $searchScore = $this->calculateSearchScore($jobseekerProfile);
            $jobseekerProfile->update(['search_score' => $searchScore]);

            DB::commit();

            Log::info('K-means profile updated successfully', [
                'user_id' => $user->id,
                'completion_percentage' => $completionPercentage,
                'search_score' => $searchScore
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'completion_percentage' => $completionPercentage,
                'search_score' => $searchScore
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Profile update failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'errors' => [
                    'general' => ['Failed to save profile changes. Please try again.'],
                    'debug' => config('app.debug') ? $e->getMessage() : null
                ]
            ], 500);
        }
    }

    /**
     * Get profile completion dashboard
     */
    public function getProfileDashboard()
    {
        $user = Auth::user();
        
        if (!$user->isJobSeeker()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $jobseekerProfile = $user->jobSeekerProfile;
        if (!$jobseekerProfile) {
            $jobseekerProfile = $this->createDefaultJobseekerProfile($user);
        }

        $completionData = $this->getProfileCompletionBreakdown($jobseekerProfile);
        $clusterInsights = $this->getUserClusterInsights($user);
        $recommendations = $this->kmeansService->getJobRecommendations($user->id, 10);

        return response()->json([
            'success' => true,
            'data' => [
                'completion_percentage' => $jobseekerProfile->profile_completion_percentage,
                'completion_breakdown' => $completionData,
                'cluster_insights' => $clusterInsights,
                'job_recommendations' => $recommendations->take(5),
                'profile_status' => $jobseekerProfile->profile_status,
                'search_score' => $jobseekerProfile->search_score
            ]
        ]);
    }

    /**
     * Create default jobseeker profile
     */
    private function createDefaultJobseekerProfile($user)
    {
        return Jobseeker::create([
            'user_id' => $user->id,
            'first_name' => $user->name ? explode(' ', $user->name)[0] : null,
            'last_name' => $user->name && count(explode(' ', $user->name)) > 1 ? 
                          implode(' ', array_slice(explode(' ', $user->name), 1)) : null,
            'phone' => $user->mobile,
            'profile_status' => 'incomplete',
            'profile_completion_percentage' => 0,
            'search_score' => 0,
        ]);
    }

    /**
     * Get validator for profile updates
     */
    private function getProfileValidator($request)
    {
        return Validator::make($request->all(), [
            // Basic Information
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            
            // Address
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'current_address' => 'nullable|string',
            
            // Professional Information
            'professional_summary' => 'required|string|min:50|max:1000',
            'current_job_title' => 'nullable|string|max:255',
            'current_company' => 'nullable|string|max:255',
            'total_experience_years' => 'required|integer|min:0|max:50',
            'total_experience_months' => 'nullable|integer|min:0|max:11',
            
            // Skills (K-means critical)
            'skills' => 'required|array|min:3',
            'skills.*' => 'string|max:100',
            'soft_skills' => 'nullable|array',
            'soft_skills.*' => 'string|max:100',
            
            // Job Preferences (K-means critical)
            'preferred_categories' => 'required|array|min:1|max:5',
            'preferred_categories.*' => 'integer|exists:categories,id',
            'preferred_job_types' => 'required|array|min:1',
            'preferred_job_types.*' => 'integer|exists:job_types,id',
            'preferred_locations' => 'nullable|array',
            'preferred_locations.*' => 'string|max:255',
            
            // Salary Expectations (K-means important)
            'expected_salary_min' => 'required|numeric|min:0',
            'expected_salary_max' => 'required|numeric|gt:expected_salary_min',
            'salary_currency' => 'required|string|in:PHP,USD,EUR,GBP',
            'salary_period' => 'required|in:monthly,yearly',
            
            // Availability
            'availability' => 'required|in:immediate,1_week,2_weeks,1_month,2_months,3_months',
            'open_to_remote' => 'boolean',
            'open_to_relocation' => 'boolean',
            'currently_employed' => 'boolean',
            
            // Education and Experience
            'education' => 'required|array|min:1',
            'work_experience' => 'nullable|array',
            
            // Files
            'resume_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);
    }

    /**
     * Update basic user information
     */
    private function updateBasicUserInfo($user, $request)
    {
        $user->update([
            'name' => trim($request->first_name . ' ' . $request->last_name),
            'mobile' => $request->phone,
        ]);
    }

    /**
     * Update jobseeker profile data
     */
    private function updateJobseekerProfileData($profile, $request)
    {
        $updateData = [
            // Basic Information
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            
            // Address
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'current_address' => $request->current_address,
            
            // Professional Information
            'professional_summary' => $request->professional_summary,
            'current_job_title' => $request->current_job_title,
            'current_company' => $request->current_company,
            'total_experience_years' => $request->total_experience_years,
            'total_experience_months' => $request->total_experience_months ?? 0,
            
            // Skills (Model will auto-cast to JSON)
            'skills' => $request->skills,
            'soft_skills' => $request->soft_skills ?? [],
            
            // Job Preferences (Critical for K-means) - Model will auto-cast to JSON
            'preferred_categories' => $request->preferred_categories,
            'preferred_job_types' => $request->preferred_job_types,
            'preferred_locations' => $request->preferred_locations ?? [],
            
            // Salary
            'expected_salary_min' => $request->expected_salary_min,
            'expected_salary_max' => $request->expected_salary_max,
            'salary_currency' => $request->salary_currency,
            'salary_period' => $request->salary_period,
            
            // Availability
            'availability' => $request->availability,
            'open_to_remote' => $request->boolean('open_to_remote'),
            'open_to_relocation' => $request->boolean('open_to_relocation'),
            'currently_employed' => $request->boolean('currently_employed'),
            
            // Education and Experience - Model will auto-cast to JSON
            'education' => $request->education,
            'work_experience' => $request->work_experience ?? []
        ];

        // Handle file uploads
        if ($request->hasFile('resume_file')) {
            $updateData['resume_file'] = $this->handleFileUpload($request->file('resume_file'), 'resumes');
        }
        
        if ($request->hasFile('profile_photo')) {
            $updateData['profile_photo'] = $this->handleFileUpload($request->file('profile_photo'), 'profile_photos');
        }

        $profile->update($updateData);
    }

    /**
     * Calculate K-means optimized profile completion percentage
     */
    private function calculateKMeansProfileCompletion($profile)
    {
        $fields = [
            // Critical for K-means clustering (70% weight)
            'preferred_categories' => 15,
            'preferred_job_types' => 15,
            'skills' => 20,
            'expected_salary_min' => 10,
            'city' => 10,
            
            // Important for matching (20% weight)
            'professional_summary' => 8,
            'total_experience_years' => 7,
            'education' => 5,
            
            // Basic requirements (10% weight)
            'first_name' => 3,
            'last_name' => 3,
            'phone' => 2,
            'availability' => 2
        ];

        $totalScore = 0;
        $maxScore = array_sum($fields);

        foreach ($fields as $field => $points) {
            $value = $profile->$field;
            
            if (is_array($value) && !empty($value)) {
                $totalScore += $points;
            } elseif (is_string($value) && !empty(trim($value))) {
                $totalScore += $points;
            } elseif (is_numeric($value) && $value > 0) {
                $totalScore += $points;
            }
        }

        return round(($totalScore / $maxScore) * 100, 2);
    }

    /**
     * Get detailed profile completion breakdown
     */
    private function getProfileCompletionBreakdown($profile)
    {
        $sections = [
            'basic_info' => [
                'name' => 'Basic Information',
                'fields' => ['first_name', 'last_name', 'phone', 'city'],
                'weight' => 15,
                'completed' => 0
            ],
            'professional' => [
                'name' => 'Professional Information',
                'fields' => ['professional_summary', 'total_experience_years', 'skills'],
                'weight' => 35,
                'completed' => 0
            ],
            'preferences' => [
                'name' => 'Job Preferences',
                'fields' => ['preferred_categories', 'preferred_job_types', 'expected_salary_min'],
                'weight' => 40,
                'completed' => 0
            ],
            'additional' => [
                'name' => 'Additional Details',
                'fields' => ['education', 'availability'],
                'weight' => 10,
                'completed' => 0
            ]
        ];

        foreach ($sections as $key => &$section) {
            $completedFields = 0;
            $totalFields = count($section['fields']);
            
            foreach ($section['fields'] as $field) {
                $value = $profile->$field;
                if ((is_array($value) && !empty($value)) || 
                    (is_string($value) && !empty(trim($value))) || 
                    (is_numeric($value) && $value > 0)) {
                    $completedFields++;
                }
            }
            
            $section['completed'] = round(($completedFields / $totalFields) * 100, 1);
            $section['completed_fields'] = $completedFields;
            $section['total_fields'] = $totalFields;
        }

        return $sections;
    }

    /**
     * Update profile status based on completion
     */
    private function updateProfileStatus($profile, $completionPercentage)
    {
        if ($completionPercentage >= 90) {
            $status = 'complete';
        } elseif ($completionPercentage >= 60) {
            $status = 'incomplete';
        } else {
            $status = 'incomplete';
        }

        $profile->update(['profile_status' => $status]);
    }

    /**
     * Calculate search score for K-means matching
     */
    private function calculateSearchScore($profile)
    {
        $score = 0;

        // Base score from profile completion
        $score += $profile->profile_completion_percentage * 0.3;

        // Skills diversity bonus
        if (is_array($profile->skills) && count($profile->skills) >= 5) {
            $score += 10;
        }

        // Experience bonus
        $score += min($profile->total_experience_years * 2, 20);

        // Preference specificity bonus
        if (is_array($profile->preferred_categories) && count($profile->preferred_categories) <= 3) {
            $score += 10; // More focused preferences get higher score
        }

        // Salary reasonableness (compared to market standards)
        if ($profile->expected_salary_min && $profile->expected_salary_max) {
            $salaryRatio = $profile->expected_salary_max / $profile->expected_salary_min;
            if ($salaryRatio >= 1.2 && $salaryRatio <= 2.0) {
                $score += 5; // Reasonable salary range
            }
        }

        // Activity and recency bonus (placeholder for future implementation)
        $score += 5;

        return round(min($score, 100), 2);
    }

    /**
     * Get user cluster insights
     */
    private function getUserClusterInsights($user)
    {
        try {
            // This would ideally come from a cached clustering result
            $insights = [
                'cluster_id' => null,
                'cluster_name' => 'Not yet clustered',
                'similar_users_count' => 0,
                'top_skills_in_cluster' => [],
                'recommended_skills' => [],
                'market_demand_score' => 0
            ];

            // For now, provide basic insights based on preferences
            $profile = $user->jobSeekerProfile;
            if ($profile && $profile->preferred_categories) {
                $categoryIds = $profile->preferred_categories;
                $categories = Category::whereIn('id', $categoryIds)->pluck('name')->toArray();
                
                $insights['cluster_name'] = 'Professionals in ' . implode(', ', $categories);
                $insights['similar_users_count'] = User::where('role', 'jobseeker')
                    ->whereJsonContains('preferred_categories', $categoryIds[0])
                    ->count();
            }

            return $insights;
        } catch (\Exception $e) {
            Log::warning('Failed to get cluster insights', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return [
                'cluster_id' => null,
                'cluster_name' => 'Analysis pending',
                'similar_users_count' => 0,
                'top_skills_in_cluster' => [],
                'recommended_skills' => [],
                'market_demand_score' => 0
            ];
        }
    }

    /**
     * Handle file upload
     */
    private function handleFileUpload($file, $directory)
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs($directory, $filename, 'public');
        return $path;
    }

    /**
     * Delete old profile and create new K-means optimized one
     */
    public function resetProfileForKMeans()
    {
        $user = Auth::user();
        
        if (!$user->isJobSeeker()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            // Delete existing jobseeker profile if it exists
            if ($user->jobSeekerProfile) {
                Log::info('Deleting existing jobseeker profile for K-means reset', ['user_id' => $user->id]);
                $user->jobSeekerProfile->delete();
            }

            // Create new profile optimized for K-means
            $newProfile = $this->createDefaultJobseekerProfile($user);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile reset successfully. Please complete your new K-means optimized profile.',
                'profile_id' => $newProfile->id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to reset profile for K-means', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset profile. Please try again.'
            ], 500);
        }
    }
}
