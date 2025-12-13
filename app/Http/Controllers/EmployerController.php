<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobView;
use App\Models\Employer;
use App\Models\User;
use App\Models\JobType;
use App\Models\Category;
use App\Models\JobRequirement;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Notifications\ApplicationStatusUpdated;
use App\Notifications\InterviewScheduled;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EmployerController extends Controller
{
    public function dashboard()
    {
        $employer = Auth::user();
        $now = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();
        
        // Get current month statistics
        $postedJobs = Job::where('employer_id', $employer->id)->count();
        $activeJobs = Job::where('employer_id', $employer->id)
                        ->where('status', 'active')
                        ->count();
        $totalApplications = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })->count();

        // Get last month statistics for growth calculation
        $lastMonthPostedJobs = Job::where('employer_id', $employer->id)
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();

        $lastMonthActiveJobs = Job::where('employer_id', $employer->id)
            ->where('status', 'active')
            ->whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();

        $lastMonthApplications = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->whereMonth('created_at', $lastMonth->month)
        ->whereYear('created_at', $lastMonth->year)
        ->count();

        // Calculate growth percentages
        $postedJobsGrowth = $lastMonthPostedJobs > 0 
            ? (($postedJobs - $lastMonthPostedJobs) / $lastMonthPostedJobs) * 100 
            : 0;

        $activeJobsGrowth = $lastMonthActiveJobs > 0 
            ? (($activeJobs - $lastMonthActiveJobs) / $lastMonthActiveJobs) * 100 
            : 0;

        $applicationsGrowth = $lastMonthApplications > 0 
            ? (($totalApplications - $lastMonthApplications) / $lastMonthApplications) * 100 
            : 0;

        // Get pending applications count
        $pendingApplications = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->where('status', 'pending')
        ->count();

        // Get all jobs for performance chart
        $jobPerformance = Job::where('employer_id', $employer->id)
                            ->select('id', 'status')
                            ->get();

        // Get recent jobs
        $recentJobs = Job::where('employer_id', $employer->id)
                        ->withCount('applications')
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();

        // Get recent applications
        $recentApplications = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->with(['user', 'job'])
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();

        // Get recent activities
        $recentActivities = collect();
        
        // Add job applications to activities
        foreach ($recentApplications as $application) {
            $recentActivities->push([
                'type' => 'application',
                'title' => 'New application received',
                'description' => $application->job->title . ' position',
                'created_at' => $application->created_at,
                'icon' => 'fa-user-plus'
            ]);
        }
        
        // Add job postings to activities
        foreach ($recentJobs->take(3) as $job) {
            $recentActivities->push([
                'type' => 'job',
                'title' => 'Job posting published',
                'description' => $job->title,
                'created_at' => $job->created_at,
                'icon' => 'fa-briefcase'
            ]);
        }
        
        // Sort activities by date
        $recentActivities = $recentActivities->sortByDesc('created_at')->values();

        // Prepare application trends data (last 7 days)
        $applicationTrendsLabels = [];
        $applicationTrendsData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $applicationTrendsLabels[] = $date->format('M d');
            $applicationTrendsData[] = JobApplication::whereHas('job', function($query) use ($employer) {
                $query->where('employer_id', $employer->id);
            })
            ->whereDate('created_at', $date)
            ->count();
        }

        // Prepare job performance data
        $topJobs = Job::where('employer_id', $employer->id)
                    ->withCount('applications')
                    ->orderBy('applications_count', 'desc')
                    ->take(5)
                    ->get();

        $jobPerformanceLabels = $topJobs->pluck('title')->toArray();
        $jobPerformanceViews = $topJobs->pluck('views')->toArray();
        $jobPerformanceApplications = $topJobs->pluck('applications_count')->toArray();

        // Get profile completion percentage directly from Employer
        $profile = Employer::where('user_id', $employer->id)->with('user')->first();
        $profileCompletion = $profile ? $profile->getProfileCompletionPercentage() : 0;
        
        // Get profile and job views
        $profileViews = $profile ? ($profile->profile_views ?? 0) : 0;
        $jobViews = Job::where('employer_id', $employer->id)->sum('views');
        
        // Get shortlisted candidates
        // Note: This requires the shortlisted column to be added to the job_applications table
        // Run the migration: php artisan migrate
        try {
            $shortlistedCandidates = JobApplication::whereHas('job', function($query) use ($employer) {
                $query->where('employer_id', $employer->id);
            })
            ->where('shortlisted', true)
            ->count();
        } catch (\Exception $e) {
            // If the column doesn't exist yet, use a placeholder value
            Log::warning('Shortlisted column not found: ' . $e->getMessage());
            $shortlistedCandidates = 0;
        }

        // Remove duplicate recentApplications query since it's already defined above

        // Calculate additional metrics
        $jobGrowth = $postedJobsGrowth;
        $applicationGrowth = $applicationsGrowth;
        $shortlistedChange = 0; // Placeholder for shortlisted change percentage
        $newApplications = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->where('created_at', '>=', Carbon::today())
        ->count();

        // Use the unified dashboard layout
        return view('front.account.employer.dashboard', compact(
            'postedJobs',
            'activeJobs',
            'totalApplications',
            'pendingApplications',
            'postedJobsGrowth',
            'activeJobsGrowth',
            'applicationsGrowth',
            'recentJobs',
            'recentApplications',
            'recentActivities',
            'applicationTrendsLabels',
            'applicationTrendsData',
            'jobPerformanceLabels',
            'jobPerformanceViews',
            'jobPerformanceApplications',
            'profileCompletion',
            'jobPerformance',
            'profileViews',
            'jobViews',
            'shortlistedCandidates',
            'jobGrowth',
            'applicationGrowth',
            'shortlistedChange',
            'newApplications'
        ));
    }

    public function editProfile()
    {
        $employer = Auth::user();
        
        // Get or create the Employer profile directly
        $profile = Employer::where('user_id', $employer->id)->first();
        if (!$profile) {
            $profile = new Employer([
                'user_id' => $employer->id,
                'company_name' => 'Company Name',
                'status' => 'pending'
            ]);
            $profile->save();
        }
        
        // Load the user relationship for profile completion calculation
        $profile->load('user');
        
        // Calculate profile completion percentage
        $profileCompletion = $profile->getProfileCompletionPercentage();
        
        // Get pending applications count for sidebar
        $pendingApplications = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->where('status', 'pending')
        ->count();

        // Get active jobs count for sidebar
        $activeJobs = Job::where('employer_id', $employer->id)
                        ->where('status', 'active')
                        ->count();
                        
        return view('front.account.employer.profile.edit', compact('employer', 'profile', 'pendingApplications', 'activeJobs', 'profileCompletion'));
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Get or create the Employer profile directly
            $profile = Employer::where('user_id', $user->id)->first();
            if (!$profile) {
                $profile = new Employer([
                    'user_id' => $user->id,
                    'company_name' => 'Company Name',
                    'status' => 'pending'
                ]);
            }

            // Validate the request
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'company_name' => 'required|string|max:255',
                'company_description' => 'nullable|string',
                'industry' => 'nullable|string|max:255',
                'company_size' => 'nullable|string|max:255',
                'website' => 'nullable|url|max:255',
                'location' => 'nullable|string|max:255',
                'social_links' => 'nullable|array',
                'company_culture' => 'nullable|array',
                'benefits_offered' => 'nullable|array',
                'founded_year' => 'nullable|integer|min:1800|max:' . date('Y'),
                'headquarters' => 'nullable|string|max:255',
                'specialties' => 'nullable|array',
                'contact_email' => 'nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:20',
            ]);

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                try {
                    $file = $request->file('profile_image');
                    
                    // Delete old profile image if it exists
                    if ($user->image) {
                        $oldPath = str_replace('storage/', '', $user->image);
                        Storage::disk('public')->delete($oldPath);
                    }
                    
                    // Generate a unique filename
                    $filename = 'profile_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    
                    // Ensure the profile_images directory exists
                    Storage::disk('public')->makeDirectory('profile_images');
                    
                    // Store the new file
                    $path = $file->storeAs('profile_images', $filename, 'public');
                    
                    if (!$path) {
                        throw new \Exception('Failed to store profile image');
                    }
                    
                    $user->image = $path;
                    $user->save();
                    
                } catch (\Exception $e) {
                    Log::error('Error uploading profile image: ' . $e->getMessage());
                    throw new \Exception('Failed to upload profile image: ' . $e->getMessage());
                }
            }

            // Store logo path temporarily
            $logoPath = null;
            if ($request->hasFile('company_logo')) {
                try {
                    $file = $request->file('company_logo');
                    
                    \Log::info('Logo upload started', [
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ]);
                    
                    // Delete old logo if it exists
                    if ($profile->company_logo) {
                        $oldPath = str_replace('storage/', '', $profile->company_logo);
                        Storage::disk('public')->delete($oldPath);
                        \Log::info('Deleted old logo', ['old_path' => $oldPath]);
                    }
                    
                    // Generate a unique filename
                    $filename = 'logo_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    
                    // Ensure the company_logos directory exists
                    Storage::disk('public')->makeDirectory('company_logos');
                    
                    // Store the new file
                    $logoPath = $file->storeAs('company_logos', $filename, 'public');
                    
                    if (!$logoPath) {
                        throw new \Exception('Failed to store company logo');
                    }
                    
                    \Log::info('Logo stored successfully', [
                        'path' => $logoPath,
                        'full_url' => Storage::disk('public')->url($logoPath),
                        'file_exists' => Storage::disk('public')->exists($logoPath)
                    ]);
                    
                } catch (\Exception $e) {
                    Log::error('Error uploading company logo: ' . $e->getMessage());
                    throw new \Exception('Failed to upload company logo: ' . $e->getMessage());
                }
            }

            // Update user fields if provided
            if ($request->filled('name') && $request->name !== $user->name) {
                $user->name = $request->name;
                $user->save();
            }
            
            if ($request->filled('email') && $request->email !== $user->email) {
                $user->email = $request->email;
                $user->save();
            }
            
            // Update profile fields
            $profile->fill([
                'company_name' => $request->company_name,
                'company_description' => $request->company_description,
                'industry' => $request->industry,
                'company_size' => $request->company_size,
                'company_website' => $request->website,
                'business_email' => $request->contact_email,
                'business_phone' => $request->contact_phone,
                'business_address' => $request->location,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country ?? 'Philippines',
                'linkedin_url' => $request->linkedin_url,
                'facebook_url' => $request->facebook_url,
                'twitter_url' => $request->twitter_url,
                'instagram_url' => $request->instagram_url,
                'founded_year' => $request->founded_year,
                'contact_person_name' => $request->contact_person_name,
                'contact_person_designation' => $request->contact_person_designation,
                'status' => $request->has('save_draft') ? 'pending' : 'active'
            ]);

            // Set logo path AFTER fill() to ensure it's not overwritten
            if ($logoPath) {
                $profile->company_logo = $logoPath;
                \Log::info('Logo path set on profile BEFORE save', [
                    'logo_path' => $logoPath,
                    'profile_id' => $profile->id,
                    'company_logo_value' => $profile->company_logo
                ]);
            }

            // Save the profile
            $saved = $profile->save();
            
            if (!$saved) {
                \Log::error('Failed to save profile!');
                throw new \Exception('Failed to save profile');
            }
            
            // Refresh from database to confirm
            $profile->refresh();
            
            \Log::info('Profile saved successfully - AFTER SAVE', [
                'company_logo_in_db' => $profile->company_logo,
                'logo_path_variable' => $logoPath,
                'saved_result' => $saved,
                'profile_id' => $profile->id
            ]);
            
            // Prepare debug information for the session
            $debugInfo = [
                'timestamp' => now()->toISOString(),
                'user_id' => $user->id,
                'user_name' => $user->name,
                'profile_id' => $profile->id,
                'company_name' => $profile->company_name,
                'old_logo_path' => $profile->getOriginal('company_logo'),
                'new_logo_path' => $profile->company_logo,
                'logo_url' => $profile->company_logo ? Storage::url($profile->company_logo) : null,
                'logo_file_exists' => $profile->company_logo ? Storage::exists($profile->company_logo) : false,
                'updated_fields' => $profile->getDirty(),
                'profile_updated_at' => $profile->updated_at,
                'has_uploaded_logo' => $request->hasFile('company_logo'),
                'request_method' => $request->method(),
                'auth_user_fresh' => Auth::user()->fresh(),
                'profile_fresh' => $profile->fresh(),
                'sidebar_cache_clear' => 'Profile data refreshed for sidebar display'
            ];
            
            // Log the debug info
            \Log::info('Profile update completed successfully', $debugInfo);

            // Create success message with details
            $successMessage = 'Profile updated successfully!';
            if ($request->hasFile('company_logo')) {
                $successMessage .= ' Company logo uploaded.';
            }
            if ($request->hasFile('profile_image')) {
                $successMessage .= ' Profile image uploaded.';
            }
            
            // Redirect back with success message and scroll position
            return redirect()
                ->route('employer.profile.edit')
                ->with('success', $successMessage)
                ->with('debug_info', $debugInfo)
                ->with('scroll_position', $request->input('scroll_position'));

        } catch (\Exception $e) {
            Log::error('Error updating employer profile: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update profile: ' . $e->getMessage())
                ->with('scroll_position', $request->input('scroll_position'));
        }
    }

    public function removeLogo()
    {
        $employer = Auth::user();
        $profile = $employer->employer; // This is the Employer model

        if ($profile && $profile->company_logo) {
            try {
                // Remove the logo file from storage
                $logoPath = str_replace('storage/', '', $profile->company_logo);
                Storage::disk('public')->delete($logoPath);
                
                // Update the profile to remove the logo path
                $profile->company_logo = null;
                $profile->save();

                return redirect()->back()
                    ->with('success', 'Company logo removed successfully.')
                    ->with('scroll_position', request('scroll_position'));
            } catch (\Exception $e) {
                \Log::error('Error removing company logo: ' . $e->getMessage());
                return redirect()->back()
                    ->with('error', 'Failed to remove company logo. Please try again.')
                    ->with('scroll_position', request('scroll_position'));
            }
        }

        return redirect()->back()
            ->with('scroll_position', request('scroll_position'));
    }

    public function removeGalleryImage($index)
    {
        $employer = Auth::user();
        $profile = $employer->employer; // This is the Employer model

        if ($profile && !empty($profile->gallery_images) && isset($profile->gallery_images[$index])) {
            $imagePath = $profile->gallery_images[$index];
            Storage::disk('public')->delete($imagePath);

            $galleryImages = $profile->gallery_images;
            unset($galleryImages[$index]);
            $profile->gallery_images = array_values($galleryImages);
            $profile->save();

            return redirect()->back()
                            ->with('success', 'Gallery image removed successfully.')
                            ->with('scroll_position', request('scroll_position'));
        }

        return redirect()->back()
                        ->with('error', 'Image not found.')
                        ->with('scroll_position', request('scroll_position'));
    }

    public function jobApplications(Request $request)
    {
        $employer = Auth::user();
        
        $applications = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->with(['user', 'job', 'statusHistory'])
        ->when($request->filled('status'), function($query) use ($request) {
            $query->where('status', $request->status);
        })
        ->when($request->filled('job'), function($query) use ($request) {
            $query->whereHas('job', function($q) use ($request) {
                $q->where('id', $request->job);
            });
        })
        ->when($request->filled('search'), function($query) use ($request) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        // Get jobs for filter dropdown
        $jobs = Job::where('employer_id', $employer->id)
                   ->select('id', 'title')
                   ->orderBy('title')
                   ->get();

        return view('front.account.employer.applications.index', compact('applications', 'jobs'));
    }
    
    /**
     * Display shortlisted applications
     */
    public function shortlistedApplications(Request $request)
    {
        $employer = Auth::user();
        
        try {
            // Check if the shortlisted column exists
            $applications = JobApplication::whereHas('job', function($query) use ($employer) {
                $query->where('employer_id', $employer->id);
            })
            ->with(['user', 'job', 'statusHistory'])
            ->where('shortlisted', true)
            ->when($request->filled('job'), function($query) use ($request) {
                $query->whereHas('job', function($q) use ($request) {
                    $q->where('id', $request->job);
                });
            })
            ->when($request->filled('search'), function($query) use ($request) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        } catch (\Illuminate\Database\QueryException $e) {
            // If the shortlisted column doesn't exist, show a message
            if (str_contains($e->getMessage(), "Unknown column 'shortlisted'")) {
                \Log::warning('Shortlisted column not found. Migration needs to be run.');
                
                // Return an empty collection with pagination
                $applications = new \Illuminate\Pagination\LengthAwarePaginator(
                    [], 0, 10, 1, ['path' => $request->url()]
                );
                
                // Flash a message to the session
                session()->flash('warning', 'The shortlisted feature is not available yet. Please run the migration: php artisan migrate');
            } else {
                // If it's another error, rethrow it
                throw $e;
            }
        }

        // Get jobs for filter dropdown
        $jobs = Job::where('employer_id', $employer->id)
                   ->select('id', 'title')
                   ->orderBy('title')
                   ->get();

        return view('front.account.employer.applications.shortlisted', compact('applications', 'jobs'));
    }
    
    /**
     * Toggle shortlist status for an application
     */
    public function toggleShortlist(Request $request, JobApplication $application)
    {
        // Verify that the employer owns this application
        if ($application->job->employer_id !== Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }
        
        try {
            // Toggle shortlisted status
            $application->shortlisted = !$application->shortlisted;
            $application->save();
            
            return response()->json([
                'status' => true,
                'shortlisted' => $application->shortlisted,
                'message' => $application->shortlisted ? 'Application shortlisted' : 'Application removed from shortlist'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // If the shortlisted column doesn't exist
            if (str_contains($e->getMessage(), "Unknown column 'shortlisted'")) {
                \Log::warning('Shortlisted column not found. Migration needs to be run.');
                
                return response()->json([
                    'status' => false,
                    'message' => 'The shortlisted feature is not available yet. Please run the migration: php artisan migrate'
                ], 500);
            }
            
            // If it's another error, return a generic error message
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the application'
            ], 500);
        }
    }

    public function showApplication(JobApplication $application)
    {
        // Verify that the employer owns this application
        if ($application->job->employer_id !== Auth::id()) {
            abort(403, 'Unauthorized action');
        }

        return view('front.account.employer.applications.show', [
            'application' => $application->load(['user', 'job.jobRequirements', 'statusHistory'])
        ]);
    }

    public function updateApplicationStatus(Request $request, JobApplication $application)
    {
        // Validate that the employer owns this application
        if ($application->job->employer_id !== Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string|max:500'
        ]);

        // Update application status
        $application->status = $request->status;
        $application->save();

        // Refresh the application to ensure we have the latest data
        $application->refresh();

        // Load necessary relationships for the notification
        $application->load(['user', 'job.employer.employerProfile']);

        // Create status history
        $statusHistory = $application->statusHistory()->create([
            'status' => $request->status,
            'notes' => $request->notes ?? 'Status updated to ' . ucfirst($request->status)
        ]);

        // Auto-close job if vacancies are filled (when status is approved)
        $jobClosed = false;
        if ($request->status === 'approved') {
            $job = $application->job->fresh(['applications']);

            \Log::info('Checking auto-close for Job #' . $job->id . ': vacancy=' . $job->vacancy . ', accepted=' . $job->accepted_applications_count);

            if ($job->checkAndAutoClose()) {
                \Log::info('Job #' . $job->id . ' auto-closed: All ' . $job->vacancy . ' vacancies filled');
                $jobClosed = true;
            } else {
                \Log::info('Job #' . $job->id . ' still open: ' . $job->accepted_applications_count . '/' . $job->vacancy . ' filled');
            }
        }

        // Send notification to job seeker using custom Notification model
        try {
            \Log::info('Attempting to send notification to user: ' . $application->user->id);
            \Log::info('User email: ' . $application->user->email);
            \Log::info('Application status: ' . $application->status);
            \Log::info('Notes: ' . ($request->notes ?? 'No notes'));

            // Create notification using custom Notification model (like JobRejectedNotification)
            $status = $application->status;
            $jobTitle = $application->job->title;
            $companyName = $application->job->employer->employerProfile->company_name;

            if ($status === 'rejected') {
                $title = 'Application Rejected';
                $message = 'Your application for "' . $jobTitle . '" at ' . $companyName . ' was not successful.';
                if ($request->notes) {
                    $message .= ' Feedback: ' . $request->notes;
                }
                $type = 'application_status';
            } elseif ($status === 'approved') {
                $title = 'Application Approved!';
                $message = 'Great news! Your application for "' . $jobTitle . '" at ' . $companyName . ' has been approved!';
                if ($request->notes) {
                    $message .= ' Message: ' . $request->notes;
                }
                $type = 'application_status';
            } else {
                $title = 'Application Status Updated';
                $message = 'Your application for "' . $jobTitle . '" at ' . $companyName . ' has been updated.';
                $type = 'application_status';
            }

            \App\Models\Notification::create([
                'user_id' => $application->user->id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'data' => [
                    'job_application_id' => $application->id,
                    'job_id' => $application->job_id,
                    'job_title' => $jobTitle,
                    'company_name' => $companyName,
                    'status' => $status,
                    'rejection_reason' => $request->notes,
                ],
                'action_url' => route('account.showJobApplication', $application->id),
                'read_at' => null
            ]);

            \Log::info('Notification created successfully for user: ' . $application->user->email);
        } catch (\Exception $e) {
            \Log::error('Failed to create notification: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
        }

        // Prepare response message
        $message = 'Application status updated successfully';
        if ($jobClosed) {
            $message = 'Application approved! All vacancies filled - Job has been automatically closed.';
        }

        return response()->json([
            'status' => true,
            'message' => $message,
            'job_closed' => $jobClosed
        ]);
    }

    /**
     * Update application stage (new multi-stage process)
     */
    public function updateApplicationStage(Request $request, JobApplication $application)
    {
        // Validate that the employer owns this application
        if ($application->job->employer_id !== Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        $request->validate([
            'action' => 'required|in:approve,reject,advance',
            'notes' => 'nullable|string|max:1000'
        ]);

        $application->load(['user', 'job.employer.employerProfile', 'job.jobRequirements']);

        $jobTitle = $application->job->title;
        $companyName = $application->job->employer->employerProfile->company_name ?? 'Unknown Company';
        $action = $request->action;
        $notes = $request->notes;

        try {
            DB::beginTransaction();

            if ($action === 'reject') {
                // Reject the application
                $application->rejectApplication($notes);

                // Create notification for rejection
                Notification::create([
                    'user_id' => $application->user->id,
                    'title' => 'Application Rejected',
                    'message' => 'Your application for "' . $jobTitle . '" at ' . $companyName . ' was not successful.' . ($notes ? ' Feedback: ' . $notes : ''),
                    'type' => 'application_rejected',
                    'data' => [
                        'job_application_id' => $application->id,
                        'job_id' => $application->job_id,
                        'job_title' => $jobTitle,
                        'company_name' => $companyName,
                        'stage' => $application->stage,
                        'rejection_reason' => $notes,
                    ],
                    'action_url' => route('account.showJobApplication', $application->id),
                    'read_at' => null
                ]);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Application rejected successfully.',
                    'new_stage' => $application->stage,
                    'new_stage_status' => $application->stage_status
                ]);
            }

            if ($action === 'approve') {
                // Approve the current stage
                $application->approveCurrentStage($notes);
                $currentStage = $application->stage;

                // Create appropriate notification based on current stage
                $this->createStageApprovalNotification($application, $currentStage, $jobTitle, $companyName, $notes);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Stage approved successfully. ' . ($application->canAdvanceStage() ? 'You can now advance to the next stage.' : ''),
                    'new_stage' => $application->stage,
                    'new_stage_status' => $application->stage_status,
                    'can_advance' => $application->canAdvanceStage()
                ]);
            }

            if ($action === 'advance') {
                // Check if can advance
                if (!$application->canAdvanceStage()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Cannot advance to next stage. Current stage must be approved first.'
                    ], 400);
                }

                $previousStage = $application->stage;
                $advanced = $application->advanceToNextStage($notes);

                if ($advanced) {
                    // Create notification for stage advancement
                    $this->createStageAdvancementNotification($application, $previousStage, $jobTitle, $companyName);

                    DB::commit();

                    return response()->json([
                        'status' => true,
                        'message' => 'Application advanced to ' . $application->getStageName() . '.',
                        'new_stage' => $application->stage,
                        'new_stage_status' => $application->stage_status,
                        'stage_name' => $application->getStageName()
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to advance to next stage.'
                    ], 400);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating application stage: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the application stage.'
            ], 500);
        }
    }

    /**
     * Create notification for stage approval
     */
    private function createStageApprovalNotification($application, $stage, $jobTitle, $companyName, $notes = null)
    {
        $notificationData = [
            'job_application_id' => $application->id,
            'job_id' => $application->job_id,
            'job_title' => $jobTitle,
            'company_name' => $companyName,
            'stage' => $stage,
        ];

        switch ($stage) {
            case JobApplication::STAGE_APPLICATION:
                Notification::create([
                    'user_id' => $application->user->id,
                    'title' => 'Application Approved - Submit Documents',
                    'message' => 'Great news! Your application for "' . $jobTitle . '" at ' . $companyName . ' has been approved! Please submit the required documents to proceed.',
                    'type' => 'stage_approved',
                    'data' => $notificationData,
                    'action_url' => route('job.submitRequirements', $application->id),
                    'read_at' => null
                ]);
                break;

            case JobApplication::STAGE_REQUIREMENTS:
                Notification::create([
                    'user_id' => $application->user->id,
                    'title' => 'Documents Approved - Awaiting Interview',
                    'message' => 'Your documents for "' . $jobTitle . '" at ' . $companyName . ' have been verified! Please wait for the interview schedule.',
                    'type' => 'documents_approved',
                    'data' => $notificationData,
                    'action_url' => route('account.showJobApplication', $application->id),
                    'read_at' => null
                ]);
                break;

            case JobApplication::STAGE_INTERVIEW:
                // This case is handled when marking as hired
                Notification::create([
                    'user_id' => $application->user->id,
                    'title' => 'Congratulations - You\'re Hired!',
                    'message' => 'Congratulations! You have been hired for "' . $jobTitle . '" at ' . $companyName . '!' . ($notes ? ' Note: ' . $notes : ''),
                    'type' => 'hired',
                    'data' => $notificationData,
                    'action_url' => route('account.showJobApplication', $application->id),
                    'read_at' => null
                ]);
                break;
        }
    }

    /**
     * Create notification for stage advancement
     */
    private function createStageAdvancementNotification($application, $previousStage, $jobTitle, $companyName)
    {
        $notificationData = [
            'job_application_id' => $application->id,
            'job_id' => $application->job_id,
            'job_title' => $jobTitle,
            'company_name' => $companyName,
            'previous_stage' => $previousStage,
            'new_stage' => $application->stage,
        ];

        switch ($application->stage) {
            case JobApplication::STAGE_REQUIREMENTS:
                Notification::create([
                    'user_id' => $application->user->id,
                    'title' => 'Submit Required Documents',
                    'message' => 'Your application for "' . $jobTitle . '" at ' . $companyName . ' is now in the document submission stage. Please upload the required documents.',
                    'type' => 'submit_requirements',
                    'data' => $notificationData,
                    'action_url' => route('job.submitRequirements', $application->id),
                    'read_at' => null
                ]);
                break;

            case JobApplication::STAGE_INTERVIEW:
                Notification::create([
                    'user_id' => $application->user->id,
                    'title' => 'Proceeding to Interview Stage',
                    'message' => 'Your documents for "' . $jobTitle . '" have been approved. Please wait for the employer to schedule your interview.',
                    'type' => 'interview_stage',
                    'data' => $notificationData,
                    'action_url' => route('account.showJobApplication', $application->id),
                    'read_at' => null
                ]);
                break;

            case JobApplication::STAGE_HIRED:
                Notification::create([
                    'user_id' => $application->user->id,
                    'title' => 'Congratulations - You\'re Hired!',
                    'message' => 'Congratulations! You have been officially hired for "' . $jobTitle . '" at ' . $companyName . '!',
                    'type' => 'hired',
                    'data' => $notificationData,
                    'action_url' => route('account.showJobApplication', $application->id),
                    'read_at' => null
                ]);
                break;
        }
    }

    /**
     * Schedule interview for an application
     */
    public function scheduleInterview(Request $request, JobApplication $application)
    {
        // Validate that the employer owns this application
        if ($application->job->employer_id !== Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        // Validate the application is in interview stage
        if ($application->stage !== JobApplication::STAGE_INTERVIEW) {
            return response()->json([
                'status' => false,
                'message' => 'Application must be in interview stage to schedule an interview.'
            ], 400);
        }

        $request->validate([
            'interview_date' => 'required|date|after_or_equal:today',
            'interview_time' => 'required|string',
            'interview_location' => 'required|string|max:500',
            'interview_type' => 'required|in:in_person,video_call,phone',
            'interview_notes' => 'nullable|string|max:1000'
        ]);

        try {
            $application->load(['user', 'job.employer.employerProfile']);

            $jobTitle = $application->job->title;
            $companyName = $application->job->employer->employerProfile->company_name ?? 'Unknown Company';

            // Schedule the interview
            $application->scheduleInterview(
                $request->interview_date,
                $request->interview_time,
                $request->interview_location,
                $request->interview_type,
                $request->interview_notes
            );

            // Format date for display
            $interviewDate = Carbon::parse($request->interview_date)->format('F d, Y');
            $interviewTypeName = $application->getInterviewTypeName();

            // Create notification for interview scheduled
            Notification::create([
                'user_id' => $application->user->id,
                'title' => 'Interview Scheduled!',
                'message' => 'Your interview for "' . $jobTitle . '" at ' . $companyName . ' has been scheduled for ' . $interviewDate . ' at ' . $request->interview_time . '. Interview type: ' . $interviewTypeName . '.',
                'type' => 'interview_scheduled',
                'data' => [
                    'job_application_id' => $application->id,
                    'job_id' => $application->job_id,
                    'job_title' => $jobTitle,
                    'company_name' => $companyName,
                    'interview_date' => $request->interview_date,
                    'interview_time' => $request->interview_time,
                    'interview_location' => $request->interview_location,
                    'interview_type' => $request->interview_type,
                    'interview_notes' => $request->interview_notes,
                ],
                'action_url' => route('account.myJobApplications'),
                'read_at' => null
            ]);

            Log::info('Interview scheduled', [
                'application_id' => $application->id,
                'user_id' => $application->user->id,
                'interview_date' => $request->interview_date,
                'interview_time' => $request->interview_time
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Interview scheduled successfully! The applicant has been notified.',
                'interview_date' => $interviewDate,
                'interview_time' => $request->interview_time,
                'interview_location' => $request->interview_location,
                'interview_type' => $interviewTypeName
            ]);

        } catch (\Exception $e) {
            Log::error('Error scheduling interview: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while scheduling the interview.'
            ], 500);
        }
    }

    /**
     * Reschedule interview for an application
     */
    public function rescheduleInterview(Request $request, JobApplication $application)
    {
        // Validate that the employer owns this application
        if ($application->job->employer_id !== Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        // Validate the application has a scheduled interview
        if (!$application->hasScheduledInterview()) {
            return response()->json([
                'status' => false,
                'message' => 'No interview scheduled to reschedule.'
            ], 400);
        }

        $request->validate([
            'interview_date' => 'required|date|after_or_equal:today',
            'interview_time' => 'required|string',
            'interview_location' => 'required|string|max:500',
            'interview_type' => 'required|in:in_person,video_call,phone',
            'interview_notes' => 'nullable|string|max:1000',
            'reschedule_reason' => 'required|string|max:500'
        ]);

        try {
            $application->load(['user', 'job.employer.employerProfile']);

            $jobTitle = $application->job->title;
            $companyName = $application->job->employer->employerProfile->company_name ?? 'Unknown Company';

            // Store old interview details for notification
            $oldDate = $application->interview_date->format('F d, Y');
            $oldTime = $application->interview_time;

            // Update interview details
            $application->update([
                'interview_date' => $request->interview_date,
                'interview_time' => $request->interview_time,
                'interview_location' => $request->interview_location,
                'interview_type' => $request->interview_type,
                'interview_notes' => $request->interview_notes,
                'interview_scheduled_at' => now()
            ]);

            // Record in status history
            $application->statusHistory()->create([
                'status' => 'interview_rescheduled',
                'notes' => 'Interview rescheduled. Reason: ' . $request->reschedule_reason . '. New date: ' . Carbon::parse($request->interview_date)->format('F d, Y') . ' at ' . $request->interview_time
            ]);

            // Format new date for display
            $newInterviewDate = Carbon::parse($request->interview_date)->format('F d, Y');
            $newInterviewTime = Carbon::parse($request->interview_time)->format('h:i A');

            // Create notification for interview rescheduled
            Notification::create([
                'user_id' => $application->user->id,
                'title' => 'Interview Rescheduled',
                'message' => 'Your interview for "' . $jobTitle . '" at ' . $companyName . ' has been rescheduled from ' . $oldDate . ' to ' . $newInterviewDate . ' at ' . $newInterviewTime . '. Reason: ' . $request->reschedule_reason,
                'type' => 'interview_rescheduled',
                'data' => [
                    'job_application_id' => $application->id,
                    'job_id' => $application->job_id,
                    'job_title' => $jobTitle,
                    'company_name' => $companyName,
                    'old_date' => $oldDate,
                    'old_time' => $oldTime,
                    'new_date' => $request->interview_date,
                    'new_time' => $request->interview_time,
                    'interview_location' => $request->interview_location,
                    'interview_type' => $request->interview_type,
                    'reschedule_reason' => $request->reschedule_reason,
                ],
                'action_url' => route('account.showJobApplication', $application->id),
                'read_at' => null
            ]);

            Log::info('Interview rescheduled', [
                'application_id' => $application->id,
                'user_id' => $application->user->id,
                'old_date' => $oldDate,
                'new_date' => $request->interview_date,
                'reason' => $request->reschedule_reason
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Interview rescheduled successfully! The applicant has been notified.',
                'interview_date' => $newInterviewDate,
                'interview_time' => $newInterviewTime,
                'interview_location' => $request->interview_location,
                'interview_type' => $application->getInterviewTypeName()
            ]);

        } catch (\Exception $e) {
            Log::error('Error rescheduling interview: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while rescheduling the interview.'
            ], 500);
        }
    }

    /**
     * Mark application as hired
     */
    public function markAsHired(Request $request, JobApplication $application)
    {
        // Validate that the employer owns this application
        if ($application->job->employer_id !== Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        // Validate the application is in interview stage
        if ($application->stage !== JobApplication::STAGE_INTERVIEW) {
            return response()->json([
                'status' => false,
                'message' => 'Application must be in interview stage to mark as hired.'
            ], 400);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $application->load(['user', 'job.employer.employerProfile']);

            $jobTitle = $application->job->title;
            $companyName = $application->job->employer->employerProfile->company_name ?? 'Unknown Company';

            // Mark as hired
            $application->markAsHired($request->notes);

            // Create notification
            Notification::create([
                'user_id' => $application->user->id,
                'title' => 'Congratulations - You\'re Hired!',
                'message' => 'Congratulations! You have been officially hired for "' . $jobTitle . '" at ' . $companyName . '!' . ($request->notes ? ' Note: ' . $request->notes : ''),
                'type' => 'hired',
                'data' => [
                    'job_application_id' => $application->id,
                    'job_id' => $application->job_id,
                    'job_title' => $jobTitle,
                    'company_name' => $companyName,
                    'hired_date' => now()->toDateString(),
                ],
                'action_url' => route('account.myJobApplications'),
                'read_at' => null
            ]);

            // Check if job should be auto-closed
            $jobClosed = false;
            $job = $application->job->fresh(['applications']);
            if ($job->checkAndAutoClose()) {
                $jobClosed = true;
            }

            Log::info('Application marked as hired', [
                'application_id' => $application->id,
                'user_id' => $application->user->id,
                'job_closed' => $jobClosed
            ]);

            $message = 'Applicant has been marked as hired successfully!';
            if ($jobClosed) {
                $message .= ' All vacancies filled - Job has been automatically closed.';
            }

            return response()->json([
                'status' => true,
                'message' => $message,
                'job_closed' => $jobClosed
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking application as hired: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while marking the application as hired.'
            ], 500);
        }
    }

    /**
     * View submitted documents for an application
     */
    public function viewSubmittedDocuments(JobApplication $application)
    {
        // Validate that the employer owns this application
        if ($application->job->employer_id !== Auth::id()) {
            abort(403, 'Unauthorized action');
        }

        $application->load(['user', 'job.jobRequirements']);

        return view('front.account.employer.applications.documents', compact('application'));
    }

    public function analytics()
    {
        $employer = Auth::user();
        $dateRange = request('range', '30'); // Default to 30 days
        $startDate = now()->subDays((int)$dateRange);
        $endDate = now();
        
        // Calculate dynamic metrics
        $totalJobs = Job::where('employer_id', $employer->id)->count();
        $activeJobs = Job::where('employer_id', $employer->id)
                        ->where('status', 'active')
                        ->count();
        
        $totalApplications = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })->count();
        
        $applicationsInPeriod = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();
        
        // Calculate total views from job views table
        $totalViews = JobView::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })->count();
        
        $viewsInPeriod = JobView::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();
        
        // Calculate conversion rate (applications / views)
        $conversionRate = $totalViews > 0 ? round(($totalApplications / $totalViews) * 100, 1) : 0;
        
        // Calculate average time to hire
        $avgTimeToHire = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->where('status', 'approved')
        ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
        ->value('avg_days');
        $avgTimeToHire = $avgTimeToHire ? round($avgTimeToHire) : 0;
        
        // Calculate percentage changes (comparing current period with previous period)
        $prevStartDate = $startDate->copy()->subDays((int)$dateRange);
        $prevEndDate = $startDate->copy();
        
        $prevApplications = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
        ->count();
        
        $prevViews = JobView::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
        ->count();
        
        $viewsChange = $prevViews > 0 ? round((($viewsInPeriod - $prevViews) / $prevViews) * 100, 1) : 0;
        $applicationsChange = $prevApplications > 0 ? round((($applicationsInPeriod - $prevApplications) / $prevApplications) * 100, 1) : 0;
        
        // Get recent activity
        $recentActivity = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->with(['job', 'user'])
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get()
        ->map(function($application) {
            return (object)[
                'created_at' => $application->created_at,
                'type' => 'Application',
                'description' => "{$application->user->name} applied for {$application->job->title}"
            ];
        });

        // Get application trends (based on selected date range) - ONLY APPROVED APPLICATIONS
        $applicationTrends = collect();
        $days = (int)$dateRange;
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = JobApplication::whereHas('job', function($query) use ($employer) {
                $query->where('employer_id', $employer->id);
            })
            ->where('status', 'approved') // Only count approved applications
            ->whereDate('updated_at', $date) // Use updated_at since that's when it was approved
            ->count();
            
            $applicationTrends->push([
                'date' => now()->subDays($i)->format('M d'),
                'count' => $count
            ]);
        }

        // Get top performing jobs with actual metrics
        $topJobs = Job::where('employer_id', $employer->id)
            ->withCount([
                'applications',
                'views'
            ])
            ->orderBy('applications_count', 'desc')
            ->take(5)
            ->get()
            ->map(function($job) {
                // Calculate growth percentage for each job
                $lastWeekApplications = $job->applications()
                    ->where('created_at', '>=', now()->subWeek())
                    ->count();
                $prevWeekApplications = $job->applications()
                    ->whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])
                    ->count();
                
                $growth = $prevWeekApplications > 0 ? 
                    round((($lastWeekApplications - $prevWeekApplications) / $prevWeekApplications) * 100, 1) : 0;
                    
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'applications_count' => $job->applications_count,
                    'views_count' => $job->views_count,
                    'created_at' => $job->created_at,
                    'growth_percentage' => $growth
                ];
            });

        // Calculate hiring funnel data
        $funnelData = [
            'applications' => $totalApplications,
            'screening' => JobApplication::whereHas('job', function($query) use ($employer) {
                $query->where('employer_id', $employer->id);
            })->where('status', 'screening')->count(),
            'interviews' => JobApplication::whereHas('job', function($query) use ($employer) {
                $query->where('employer_id', $employer->id);
            })->where('status', 'interview')->count(),
            'offers' => JobApplication::whereHas('job', function($query) use ($employer) {
                $query->where('employer_id', $employer->id);
            })->where('status', 'approved')->count()
        ];
        
        // Get application sources data
        $applicationSources = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->selectRaw('COALESCE(source, "Direct") as source, COUNT(*) as count')
        ->groupBy('source')
        ->orderBy('count', 'desc')
        ->limit(5)
        ->get()
        ->mapWithKeys(function($item) {
            return [$item->source => $item->count];
        });
        
        // Get pending applications count for sidebar
        $pendingApplications = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->where('status', 'pending')
        ->count();

        // NEW: Per-Job Performance Breakdown
        $jobPerformanceBreakdown = Job::where('employer_id', $employer->id)
            ->withCount([
                'applications',
                'views'
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($job) {
                $conversionRate = $job->views_count > 0 
                    ? round(($job->applications_count / $job->views_count) * 100, 1) 
                    : 0;
                
                // Calculate weekly trend
                $lastWeekViews = JobView::where('job_id', $job->id)
                    ->where('created_at', '>=', now()->subWeek())
                    ->count();
                $prevWeekViews = JobView::where('job_id', $job->id)
                    ->whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])
                    ->count();
                $viewsTrend = $prevWeekViews > 0 
                    ? round((($lastWeekViews - $prevWeekViews) / $prevWeekViews) * 100, 1) 
                    : 0;
                
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'views' => $job->views_count,
                    'applications' => $job->applications_count,
                    'conversion_rate' => $conversionRate,
                    'posted_date' => $job->created_at,
                    'status' => $job->status,
                    'views_trend' => $viewsTrend
                ];
            });

        return view('front.account.employer.analytics.index', compact(
            'totalViews',
            'totalApplications', 
            'conversionRate',
            'avgTimeToHire',
            'viewsChange',
            'applicationsChange',
            'recentActivity',
            'applicationTrends',
            'topJobs',
            'funnelData',
            'applicationSources',
            'pendingApplications',
            'activeJobs',
            'dateRange',
            'jobPerformanceBreakdown'
        ));
    }

    public function jobAnalytics()
    {
        // Redirect to main analytics page for now
        return redirect()->route('employer.analytics.index');
    }

    public function applicantAnalytics()
    {
        // Redirect to main analytics page for now
        return redirect()->route('employer.analytics.index');
    }

    public function exportAnalytics(Request $request)
    {
        $employer = Auth::user();

        // Get all analytics data
        $totalJobs = Job::where('employer_id', $employer->id)->count();
        $activeJobs = Job::where('employer_id', $employer->id)->where('status', 'active')->count();

        $totalApplications = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })->count();

        $totalViews = JobView::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })->count();

        $conversionRate = $totalViews > 0 ? round(($totalApplications / $totalViews) * 100, 1) : 0;

        // Get job performance breakdown
        $jobs = Job::where('employer_id', $employer->id)
            ->withCount(['applications', 'views'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Build CSV content
        $csvContent = "Analytics Report - " . now()->format('Y-m-d H:i:s') . "\n\n";
        $csvContent .= "SUMMARY METRICS\n";
        $csvContent .= "Metric,Value\n";
        $csvContent .= "Total Jobs,$totalJobs\n";
        $csvContent .= "Active Jobs,$activeJobs\n";
        $csvContent .= "Total Applications,$totalApplications\n";
        $csvContent .= "Total Views,$totalViews\n";
        $csvContent .= "Conversion Rate,$conversionRate%\n\n";

        $csvContent .= "JOB PERFORMANCE BREAKDOWN\n";
        $csvContent .= "Job ID,Job Title,Status,Views,Applications,Conversion Rate,Posted Date\n";

        foreach ($jobs as $job) {
            $jobConversion = $job->views_count > 0
                ? round(($job->applications_count / $job->views_count) * 100, 1)
                : 0;
            $csvContent .= sprintf(
                "%d,\"%s\",%s,%d,%d,%s%%,%s\n",
                $job->id,
                str_replace('"', '""', $job->title),
                $job->status ?? 'inactive',
                $job->views_count,
                $job->applications_count,
                $jobConversion,
                $job->created_at->format('Y-m-d')
            );
        }

        // Return CSV download
        $filename = 'analytics-report-' . now()->format('Y-m-d') . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }
    
    /**
     * Get analytics data for AJAX requests
     */
    public function getAnalyticsData(Request $request)
    {
        $employer = Auth::user();
        $dateRange = $request->get('range', '30');
        $startDate = now()->subDays((int)$dateRange);
        $endDate = now();
        
        // Get application trends data - ONLY APPROVED APPLICATIONS
        $applicationTrends = collect();
        $days = (int)$dateRange;
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = JobApplication::whereHas('job', function($query) use ($employer) {
                $query->where('employer_id', $employer->id);
            })
            ->where('status', 'approved') // Only count approved applications
            ->whereDate('updated_at', $date) // Use updated_at since that's when it was approved
            ->count();
            
            $applicationTrends->push([
                'date' => now()->subDays($i)->format('M d'),
                'count' => $count
            ]);
        }
        
        // Get job views data
        $viewTrends = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = \App\Models\JobView::whereHas('job', function($query) use ($employer) {
                $query->where('employer_id', $employer->id);
            })
            ->whereDate('created_at', $date)
            ->count();
            
            $viewTrends->push([
                'date' => now()->subDays($i)->format('M d'),
                'count' => $count
            ]);
        }
        
        // Calculate metrics for the period
        $applicationsInPeriod = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();
        
        $viewsInPeriod = \App\Models\JobView::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();
        
        // Previous period comparison
        $prevStartDate = $startDate->copy()->subDays((int)$dateRange);
        $prevEndDate = $startDate->copy();
        
        $prevApplications = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
        ->count();
        
        $prevViews = \App\Models\JobView::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
        ->count();
        
        $viewsChange = $prevViews > 0 ? round((($viewsInPeriod - $prevViews) / $prevViews) * 100, 1) : 0;
        $applicationsChange = $prevApplications > 0 ? round((($applicationsInPeriod - $prevApplications) / $prevApplications) * 100, 1) : 0;
        
        // Calculate conversion rate
        $totalViews = \App\Models\JobView::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })->count();
        
        $totalApplications = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })->count();
        
        $conversionRate = $totalViews > 0 ? round(($totalApplications / $totalViews) * 100, 1) : 0;
        
        return response()->json([
            'applicationTrends' => $applicationTrends,
            'viewTrends' => $viewTrends,
            'metrics' => [
                'totalApplications' => $totalApplications,
                'totalViews' => $totalViews,
                'applicationsInPeriod' => $applicationsInPeriod,
                'viewsInPeriod' => $viewsInPeriod,
                'applicationsChange' => $applicationsChange,
                'viewsChange' => $viewsChange,
                'conversionRate' => $conversionRate
            ]
        ]);
    }
    
    /**
     * Get application sources data
     */
    public function getApplicationSources(Request $request)
    {
        $employer = Auth::user();
        $dateRange = $request->get('range', '30');
        $startDate = now()->subDays((int)$dateRange);
        
        $sources = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->where('created_at', '>=', $startDate)
        ->selectRaw('COALESCE(source, "Direct") as source, COUNT(*) as count')
        ->groupBy('source')
        ->orderBy('count', 'desc')
        ->limit(5)
        ->get()
        ->mapWithKeys(function($item) {
            return [$item->source => $item->count];
        });
        
        return response()->json($sources);
    }

    public function notificationSettings()
    {
        $employer = Auth::user();
        return view('front.account.employer.settings.notifications', compact('employer'));
    }

    public function updateNotificationSettings(Request $request)
    {
        $request->validate([
            'notification_preferences' => 'required|array',
        ]);

        $employer = Auth::user();
        $employer->update([
            'notification_preferences' => $request->notification_preferences,
        ]);

        return redirect()->back()->with('success', 'Notification settings updated successfully.');
    }

    public function securitySettings()
    {
        $employer = Auth::user();
        return view('front.account.employer.settings.security', compact('employer'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) {
                if (!Hash::check($value, Auth::user()->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    public function enable2FA(Request $request)
    {
        $user = Auth::user();
        $user->two_factor_enabled = true;
        $user->save();

        return redirect()->back()->with('success', 'Two-factor authentication has been enabled.');
    }

    public function disable2FA(Request $request)
    {
        $user = Auth::user();
        $user->two_factor_enabled = false;
        $user->save();

        return redirect()->back()->with('success', 'Two-factor authentication has been disabled.');
    }

    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        
        // Validate password
        $request->validate([
            'password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The password is incorrect.');
                }
            }],
            'confirm_deletion' => 'required|in:DELETE'
        ]);

        try {
            DB::beginTransaction();
            
            // Delete related data
            $user->employerProfile()->delete();
            $user->jobs()->delete();
            $user->jobApplications()->delete();
            
            // Delete the user
            $user->delete();
            
            DB::commit();
            
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('home')->with('success', 'Your account has been permanently deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting employer account: ' . $e->getMessage());
            return redirect()->back()->with('error', 'There was an error deleting your account. Please try again.');
        }
    }

    public function deactivateAccount(Request $request)
    {
        $user = Auth::user();

        // Validate password
        $request->validate([
            'password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The password is incorrect.');
                }
            }],
        ]);

        try {
            // Deactivate the account
            $user->status = 0;
            $user->deactivated_at = now();
            $user->save();

            // Also hide all job postings
            $user->jobs()->update(['status' => 0]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')->with('success', 'Your account has been deactivated. You can reactivate it by logging in again.');
        } catch (\Exception $e) {
            Log::error('Error deactivating employer account: ' . $e->getMessage());
            return redirect()->back()->with('error', 'There was an error deactivating your account. Please try again.');
        }
    }

    /**
     * Get recent activities for the dashboard
     */
    public function getActivities()
    {
        $employer = Auth::user();
        
        // Get recent applications
        $recentApplications = JobApplication::whereHas('job', function($query) use ($employer) {
            $query->where('employer_id', $employer->id);
        })
        ->with(['user', 'job'])
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();
        
        // Get recent jobs
        $recentJobs = Job::where('employer_id', $employer->id)
                        ->orderBy('created_at', 'desc')
                        ->take(3)
                        ->get();
        
        // Create activities collection
        $activities = collect();
        
        // Add job applications to activities
        foreach ($recentApplications as $application) {
            $activities->push([
                'type' => 'application',
                'title' => 'New application received',
                'description' => $application->job->title . ' position',
                'created_at' => $application->created_at->diffForHumans(),
                'icon' => 'fa-user-plus'
            ]);
        }
        
        // Add job postings to activities
        foreach ($recentJobs as $job) {
            $activities->push([
                'type' => 'job',
                'title' => 'Job posting published',
                'description' => $job->title,
                'created_at' => $job->created_at->diffForHumans(),
                'icon' => 'fa-briefcase'
            ]);
        }
        
        // Sort activities by date
        $activities = $activities->sortByDesc('created_at')->values();
        
        // Return activities as HTML
        $html = '';
        foreach ($activities as $activity) {
            $html .= '
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas ' . $activity['icon'] . '"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">' . $activity['title'] . '</div>
                    <div class="activity-description">' . $activity['description'] . '</div>
                    <div class="activity-time">' . $activity['created_at'] . '</div>
                </div>
            </div>';
        }
        
        return response($html);
    }
    
    /**
     * Get chart data for the dashboard
     */
    public function getChartData(Request $request)
    {
        $employer = Auth::user();
        $period = $request->get('period', 7);
        
        // Validate period
        $period = in_array($period, [7, 30, 90]) ? $period : 7;
        
        // Prepare application trends data
        $labels = [];
        $values = [];
        
        for ($i = ($period - 1); $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');
            
            $values[] = JobApplication::whereHas('job', function($query) use ($employer) {
                $query->where('employer_id', $employer->id);
            })
            ->whereDate('created_at', $date)
            ->count();
        }
        
        return response()->json([
            'labels' => $labels,
            'values' => $values
        ]);
    }



    public function jobs(Request $request)
    {
        $employer = Auth::user();

        // Get stats counts for all jobs (not just paginated)
        $statsQuery = Job::where('employer_id', $employer->id);
        $totalJobs = (clone $statsQuery)->count();
        $activeJobs = (clone $statsQuery)->where('status', Job::STATUS_APPROVED)->count();
        $pendingJobs = (clone $statsQuery)->where('status', Job::STATUS_PENDING)->count();
        $rejectedJobs = (clone $statsQuery)->where('status', Job::STATUS_REJECTED)->count();
        $totalApplications = Job::where('employer_id', $employer->id)
            ->withCount('applications')
            ->get()
            ->sum('applications_count');

        $query = Job::where('employer_id', $employer->id)
                   ->withCount(['applications', 'views'])
                   ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Apply status filter - accepts both constant values and friendly names
        if ($request->filled('status')) {
            $status = $request->get('status');
            // Map friendly names to constants if needed
            $statusMap = [
                'active' => Job::STATUS_APPROVED,
                'approved' => Job::STATUS_APPROVED,
                'pending' => Job::STATUS_PENDING,
                'rejected' => Job::STATUS_REJECTED,
            ];
            $statusValue = $statusMap[$status] ?? $status;
            $query->where('status', $statusValue);
        }

        // Apply category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->get('category'));
        }

        $jobs = $query->paginate(10)->withQueryString();
        $categories = Category::where('status', 1)->orderBy('name')->get();

        return view('front.account.employer.jobs.index', compact(
            'jobs',
            'categories',
            'totalJobs',
            'activeJobs',
            'pendingJobs',
            'rejectedJobs',
            'totalApplications'
        ));
    }

    public function createJob()
    {
        // Check if employer profile is complete before allowing job creation
        $employer = Auth::user();
        $profile = Employer::where('user_id', $employer->id)->first();
        
        // Define required fields for posting jobs (using correct database field names)
        $requiredFields = [
            'company_name' => 'Company Name',
            'company_description' => 'Company Description',
            'industry' => 'Industry',
            'business_email' => 'Contact Email',
            'business_address' => 'Company Location'
        ];

        $missingFields = [];

        if (!$profile) {
            // No profile exists, all fields are missing
            $missingFields = array_values($requiredFields);
        } else {
            // Check which required fields are missing
            foreach ($requiredFields as $field => $label) {
                if (empty($profile->$field)) {
                    $missingFields[] = $label;
                }
            }
        }

        // If there are missing fields, redirect to profile completion
        if (!empty($missingFields)) {
            $message = 'Please complete your company profile before posting jobs. Missing: ' . implode(', ', $missingFields);

            return redirect()->route('employer.profile.edit')
                           ->with('error', $message)
                           ->with('highlight_required', true);
        }

        $jobTypes = JobType::where('status', 1)->get();
        // Get all categories (now clean with no duplicates)
        $categories = Category::where('status', 1)->orderBy('name')->get();
        
        return view('front.account.employer.jobs.create', [
            'categories' => $categories,
            'jobTypes' => $jobTypes
        ]);
    }

    public function storeJob(Request $request)
    {
        \Log::info('🚀 JOB CREATION ATTEMPT STARTED', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => Auth::id(),
            'user_role' => Auth::user()?->role,
            'user_name' => Auth::user()?->name,
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'request_data' => $request->except(['_token']),
            'can_post_jobs' => Auth::user()?->canPostJobs(),
            'is_ajax' => $request->ajax(),
            'user_agent' => $request->userAgent()
        ]);
        
        try {
            // First, check if employer profile is complete before processing job creation
            $employer = Auth::user();
            $profile = Employer::where('user_id', $employer->id)->first();
            
            // Define required fields for posting jobs (using correct database field names)
            $requiredFields = [
                'company_name' => 'Company Name',
                'company_description' => 'Company Description',
                'industry' => 'Industry',
                'business_email' => 'Contact Email',
                'business_address' => 'Company Location'
            ];

            $missingFields = [];

            if (!$profile) {
                // No profile exists, all fields are missing
                $missingFields = array_values($requiredFields);
            } else {
                // Check which required fields are missing
                foreach ($requiredFields as $field => $label) {
                    if (empty($profile->$field)) {
                        $missingFields[] = $label;
                    }
                }
            }

            // If there are missing fields, return appropriate response
            if (!empty($missingFields)) {
                $message = 'Please complete your company profile before posting jobs. Missing: ' . implode(', ', $missingFields);

                if ($request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'message' => $message,
                        'redirect' => route('employer.profile.edit')
                    ], 422);
                }

                return redirect()->route('employer.profile.edit')
                               ->with('error', $message)
                               ->with('highlight_required', true);
            }
            
            // Validate the request
            $validated = $request->validate([
                'title' => 'required|string|min:5|max:255',
                'description' => 'required|string|min:20|max:5000',
                'requirements' => 'required|string|min:10|max:3000',
                'benefits' => 'nullable|string|max:2000',
                'location' => 'required|string|max:255',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'job_type_id' => 'required|exists:job_types,id',
                'category_id' => 'required|exists:categories,id',
                'vacancy' => 'required|integer|min:1|max:100',
                'experience_level' => 'required|in:entry,intermediate,expert',
                'education_level' => 'nullable|in:high_school,vocational,associate,bachelor,master,doctorate',
                'salary_min' => 'nullable|numeric|min:0',
                'salary_max' => 'nullable|numeric|min:0',
                'deadline' => 'nullable|date|after_or_equal:today',
                'is_remote' => 'boolean',
                'is_featured' => 'boolean',
                'skills' => 'nullable|string',
                'requires_screening' => 'boolean',
                'preliminary_questions' => 'nullable|string'
            ], [
                'title.required' => 'Job title is required',
                'title.min' => 'Job title must be at least 5 characters',
                'description.required' => 'Job description is required',
                'description.min' => 'Job description must be at least 20 characters',
                'requirements.required' => 'Job requirements are required',
                'requirements.min' => 'Job requirements must be at least 10 characters',
                'location.required' => 'Job location is required',
                'job_type_id.required' => 'Please select a job type',
                'category_id.required' => 'Please select a category',
                'vacancy.required' => 'Number of positions is required',
                'vacancy.min' => 'At least 1 position is required',
                'experience_level.required' => 'Please select an experience level'
            ]);

            $user = Auth::user();
            
            \Log::info('Job validation passed', [
                'validated_data' => $validated,
                'user_id' => $user->id
            ]);

            // Parse skills if provided
            $skills = [];
            if (!empty($validated['skills'])) {
                $skills = json_decode($validated['skills'], true) ?: [];
            }

            // Create the job
            $job = new Job();
            $job->title = $validated['title'];
            $job->description = $validated['description'];
            $job->requirements = $validated['requirements'];
            $job->benefits = $validated['benefits'] ?? null;
            $job->location = $validated['location'];
            $job->location_name = $validated['location'];
            $job->address = $validated['location']; // Use address field as well
            $job->latitude = $validated['latitude'] ?? null;
            $job->longitude = $validated['longitude'] ?? null;
            $job->job_type_id = $validated['job_type_id'];
            $job->category_id = $validated['category_id'];
            $job->employer_id = $user->id;
            $job->vacancy = $validated['vacancy'];
            $job->experience_level = $validated['experience_level'];
            $job->salary_min = $validated['salary_min'] ?? null;
            $job->salary_max = $validated['salary_max'] ?? null;
            $job->deadline = $validated['deadline'] ?? null;
            
            // Handle boolean fields as integers for database compatibility
            $job->featured = $request->boolean('is_featured') ? 1 : 0;
            
            // ALL jobs require admin approval - no auto-approval
            $job->status = Job::STATUS_PENDING; // All jobs start as pending (0)
            
            \Log::info('Job status set', [
                'job_status' => $job->status,
                'user_kyc_status' => $user->kyc_status,
                'needs_admin_approval' => true,
                'status_name' => 'Pending Approval'
            ]);
            
            $job->city = 'Sta. Cruz';
            $job->province = 'Davao del Sur';
            
            // Extract barangay from location if possible
            if (strpos($validated['location'], ',') !== false) {
                $locationParts = explode(',', $validated['location']);
                $job->barangay = trim($locationParts[0]);
            } else {
                $job->barangay = $validated['location'];
            }
            
            // Handle preliminary questions
            if ($request->boolean('requires_screening')) {
                $preliminaryQuestions = [];
                if (!empty($validated['preliminary_questions'])) {
                    $preliminaryQuestions = json_decode($validated['preliminary_questions'], true) ?: [];
                }
                
                $job->requires_screening = true;
                $job->preliminary_questions = $preliminaryQuestions;
            } else {
                $job->requires_screening = false;
                $job->preliminary_questions = null;
            }
            
            $job->meta_data = [
                'skills' => $skills,
                'created_via' => 'web_form',
                'form_version' => '1.0'
            ];

            $job->save();

            // Save job requirements (required documents)
            if ($request->has('job_requirements') && is_array($request->job_requirements)) {
                $sortOrder = 1;
                foreach ($request->job_requirements as $requirement) {
                    if (!empty($requirement['name'])) {
                        \App\Models\JobRequirement::create([
                            'job_id' => $job->id,
                            'name' => $requirement['name'],
                            'description' => $requirement['description'] ?? null,
                            'is_required' => isset($requirement['is_required']) ? true : false,
                            'sort_order' => $sortOrder++
                        ]);
                    }
                }
            }

            \Log::info('Job created successfully', [
                'job_id' => $job->id,
                'employer_id' => $user->id,
                'title' => $job->title,
                'status' => $job->status,
                'category_id' => $job->category_id,
                'job_type_id' => $job->job_type_id,
                'location' => $job->location
            ]);
            
            // Verify the job was saved correctly
            $savedJob = Job::find($job->id);
            \Log::info('Job verification after save', [
                'job_exists' => $savedJob ? 'YES' : 'NO',
                'job_status' => $savedJob ? $savedJob->status : 'N/A',
                'visible_to_jobseekers' => $savedJob && $savedJob->status == 1 ? 'YES' : 'NO'
            ]);

            // Handle AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Job posted successfully! It is now pending admin approval before being published.',
                    'job_id' => $job->id,
                    'job_status' => 'pending',
                    'redirect' => route('employer.jobs.index')
                ]);
            }

            return redirect()->route('employer.jobs.index')
                           ->with('success', 'Job posted successfully! It is now pending admin approval before being published.')
                           ->with('job_id', $job->id)
                           ->with('job_status', 'pending');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Job creation validation failed', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
                'request_data' => $request->except(['_token'])
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                           ->withErrors($e->errors())
                           ->withInput()
                           ->with('error', 'Please check the form for errors and try again.');
        } catch (\Exception $e) {
            \Log::error('Job creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => $request->except(['_token'])
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create job posting. Please try again.'
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Failed to create job posting: ' . $e->getMessage())
                           ->withInput();
        }
    }

    public function editJob(Job $job)
    {
        // Verify ownership instead of using authorize
        if ($job->employer_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Load job requirements for the edit form
        $job->load('jobRequirements');

        $categories = \App\Models\Category::all();
        $jobTypes = \App\Models\JobType::all();

        return view('front.account.employer.jobs.edit', compact('job', 'categories', 'jobTypes'));
    }

    public function updateJob(Request $request, Job $job)
    {
        \Log::info('=== JOB UPDATE STARTED ===', [
            'job_id' => $job->id,
            'employer_id' => Auth::id(),
            'request_method' => $request->method(),
            'is_ajax' => $request->ajax()
        ]);
        
        // Verify ownership
        if ($job->employer_id !== Auth::id()) {
            \Log::error('Unauthorized job update attempt', [
                'job_id' => $job->id,
                'job_employer_id' => $job->employer_id,
                'current_user_id' => Auth::id()
            ]);
            
            if ($request->ajax()) {
                return response()->json(['status' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $rules = [
            'title' => 'required|min:5|max:200',
            'category_id' => 'required|exists:categories,id',
            'job_type_id' => 'required|exists:job_types,id',
            'vacancy' => 'required|integer|min:1|max:100',
            'location' => 'required|max:50',
            'description' => 'required',
            'requirements' => 'nullable',
            'benefits' => 'nullable',
            'salary_range' => 'nullable|string',
            'deadline' => 'nullable|date',
            'featured' => 'boolean'
        ];

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            \Log::info('Job update request received', [
                'job_id' => $job->id,
                'request_vacancy' => $request->vacancy,
                'current_vacancy' => $job->vacancy,
                'request_data' => $request->except(['_token', '_method'])
            ]);
            
            // Check if this is a rejected job being resubmitted
            $wasRejected = $job->status === Job::STATUS_REJECTED;
            $wasClosed = $job->status === Job::STATUS_CLOSED;
            $oldVacancy = $job->vacancy;
            
            $job->title = $request->title;
            $job->category_id = $request->category_id;
            $job->job_type_id = $request->job_type_id;
            $job->vacancy = $request->vacancy;
            $job->location = $request->location;
            $job->location_name = $request->location;
            $job->location_address = $request->location_address ?? $request->location;
            $job->latitude = $request->latitude;
            $job->longitude = $request->longitude;
            $job->description = $request->description;
            $job->requirements = $request->requirements;
            $job->benefits = $request->benefits;
            // Note: salary_range column doesn't exist, using salary_min and salary_max instead
            // $job->salary_range = $request->salary_range;
            $job->deadline = $request->deadline;
            $job->featured = $request->featured ?? false;
            
            // If job was rejected and is being resubmitted, set status to pending
            if ($wasRejected) {
                $job->status = Job::STATUS_PENDING;
                $job->rejection_reason = null; // Clear rejection reason
                $job->rejected_at = null; // Clear rejection timestamp
                $message = 'Job resubmitted successfully! It is now pending admin approval.';
            } 
            // If job was closed, check if we should reopen it
            elseif ($wasClosed) {
                // Get current accepted applications count
                $acceptedCount = $job->applications()->where('status', 'approved')->count();
                
                \Log::info('Checking if closed job should reopen', [
                    'job_id' => $job->id,
                    'old_vacancy' => $oldVacancy,
                    'new_vacancy' => $request->vacancy,
                    'accepted_count' => $acceptedCount,
                    'should_reopen' => $request->vacancy > $acceptedCount
                ]);
                
                // If new vacancy is greater than accepted count, reopen the job
                if ($request->vacancy > $acceptedCount) {
                    $job->status = Job::STATUS_APPROVED;
                    $availableSlots = $request->vacancy - $acceptedCount;
                    $message = 'Job updated and reopened! Now hiring ' . $availableSlots . ' more position(s). Total: ' . $request->vacancy . ' (' . $acceptedCount . ' already filled).';
                    \Log::info('Job #' . $job->id . ' reopened: Vacancy=' . $request->vacancy . ', Accepted=' . $acceptedCount . ', Available=' . $availableSlots);
                } else {
                    $message = 'Job updated but remains closed (all ' . $request->vacancy . ' position(s) are filled).';
                }
            }
            else {
                // Keep current status for normal edits (don't change approval status)
                $message = 'Job updated successfully.';
            }
            
            $saved = $job->save();

            // Handle job requirements (required documents)
            if ($request->has('job_requirements')) {
                // Get existing requirement IDs from the form
                $submittedIds = collect($request->job_requirements)
                    ->filter(function($req) { return !empty($req['id']); })
                    ->pluck('id')
                    ->toArray();

                // Delete requirements that were removed
                $job->jobRequirements()->whereNotIn('id', $submittedIds)->delete();

                // Update or create requirements
                $sortOrder = 1;
                foreach ($request->job_requirements as $requirement) {
                    if (!empty($requirement['name'])) {
                        if (!empty($requirement['id'])) {
                            // Update existing requirement
                            \App\Models\JobRequirement::where('id', $requirement['id'])
                                ->where('job_id', $job->id)
                                ->update([
                                    'name' => $requirement['name'],
                                    'description' => $requirement['description'] ?? null,
                                    'is_required' => isset($requirement['is_required']) ? true : false,
                                    'sort_order' => $sortOrder++
                                ]);
                        } else {
                            // Create new requirement
                            \App\Models\JobRequirement::create([
                                'job_id' => $job->id,
                                'name' => $requirement['name'],
                                'description' => $requirement['description'] ?? null,
                                'is_required' => isset($requirement['is_required']) ? true : false,
                                'sort_order' => $sortOrder++
                            ]);
                        }
                    }
                }
            } else {
                // If no requirements submitted, delete all existing ones
                $job->jobRequirements()->delete();
            }

            \Log::info('Job updated/resubmitted', [
                'job_id' => $job->id,
                'was_rejected' => $wasRejected,
                'was_closed' => $wasClosed,
                'old_vacancy' => $oldVacancy,
                'new_vacancy' => $job->vacancy,
                'new_status' => $job->status,
                'save_result' => $saved,
                'employer_id' => Auth::id()
            ]);

            // Return JSON for AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => $message
                ]);
            }

            return redirect()->route('employer.jobs.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error updating job', [
                'error' => $e->getMessage(),
                'job_id' => $job->id
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error updating job: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->withErrors(['error' => 'Error updating job: ' . $e->getMessage()])->withInput();
        }
    }

    public function deleteJob($id)
    {
        try {
            // Use withTrashed() to find soft-deleted jobs too
            $job = Job::withTrashed()->where('id', $id)->first();

            if (!$job) {
                return response()->json(['success' => false, 'message' => 'Job not found'], 404);
            }

            // Check if user owns this job
            if ($job->employer_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // If already soft-deleted, force delete
            if ($job->trashed()) {
                $job->applications()->forceDelete();
                $job->forceDelete();
                $message = 'Job permanently deleted.';
            } else {
                $job->delete();
                $message = 'Job deleted successfully.';
            }

            // Check if request expects JSON (AJAX)
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->route('employer.jobs.index')
                            ->with('success', $message);
        } catch (\Exception $e) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error deleting job: ' . $e->getMessage()], 500);
            }

            return redirect()->route('employer.jobs.index')
                            ->with('error', 'Error deleting job: ' . $e->getMessage());
        }
    }

    public function featuredJobs()
    {
        $employer = Auth::user();
        $jobs = Job::where('employer_id', $employer->id)
                   ->where('featured', true)
                   ->withCount('applications')
                   ->orderBy('created_at', 'desc')
                   ->paginate(10);
        
        return view('front.account.employer.jobs.featured', compact('jobs'));
    }

    public function toggleJobFeature(Job $job)
    {
        $this->authorize('update', $job);
        
        $job->update(['featured' => !$job->featured]);
        
        return redirect()->back()
                        ->with('success', $job->featured ? 'Job featured successfully.' : 'Job unfeatured successfully.');
    }

    public function settings()
    {
        $employer = Auth::user();
        $profile = $employer->employer; // This is the Employer model

        return view('front.account.employer.settings.index', compact('employer', 'profile'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'marketing_emails' => 'boolean',
            'language' => 'required|string|in:en,es,fr',
            'timezone' => 'required|string|timezone'
        ]);

        $employer = Auth::user();
        
        // Update settings in the employer profile
        $settings = $employer->settings ?? [];
        $settings['email_notifications'] = $request->boolean('email_notifications');
        $settings['marketing_emails'] = $request->boolean('marketing_emails');
        $settings['language'] = $request->language;
        $settings['timezone'] = $request->timezone;
        
        $employer->settings = $settings;
        $employer->save();

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Show job details
     */
    public function showJob(Job $job)
    {
        // Check if the job belongs to the employer
        if ($job->employer_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('front.account.employer.jobs.show', compact('job'));
    }

    /**
     * Mark a single notification as read
     */
    public function markNotificationAsRead($id)
    {
        try {
            DB::table('notifications')
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->update(['read_at' => now()]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead()
    {
        try {
            DB::table('notifications')
                ->where('user_id', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Create a test notification for debugging
     */
    public function testNotification()
    {
        try {
            $user = Auth::user();
            
            // Create a test notification directly in the database
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\TestNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $user->id,
                'data' => json_encode([
                    'message' => '🧪 Test notification created at ' . now()->format('H:i:s'),
                    'type' => 'test',
                    'test' => true,
                    'created_at' => now()->toISOString()
                ]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            Log::info('Test notification created for user: ' . $user->id);
            
            return response()->json([
                'success' => true, 
                'message' => 'Test notification created successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Test notification failed: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed to create test notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View jobseeker's full profile
     */
    public function viewJobseekerProfile($userId)
    {
        $jobseeker = User::with(['jobSeekerProfile'])->findOrFail($userId);
        
        // Ensure the user is a jobseeker
        if ($jobseeker->role !== 'jobseeker') {
            abort(404, 'Profile not found');
        }
        
        // Record profile view
        \App\Models\ProfileView::recordView(
            $userId,
            Auth::id(),
            'profile_page'
        );
        
        // Get jobseeker's applications to this employer's jobs (if any)
        $applications = JobApplication::where('user_id', $userId)
            ->whereHas('job', function($query) {
                $query->where('employer_id', Auth::id());
            })
            ->with('job')
            ->latest()
            ->get();
        
        return view('front.account.employer.jobseeker-profile', compact('jobseeker', 'applications'));
    }

    /**
     * View all applicants for a specific job
     */
    public function viewJobApplicants($jobId)
    {
        $job = Job::where('id', $jobId)
            ->where('employer_id', Auth::id())
            ->firstOrFail();
        
        $applications = JobApplication::where('job_id', $jobId)
            ->with(['user', 'user.jobSeekerProfile'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('front.account.employer.job-applicants', compact('job', 'applications'));
    }
}
