<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\EmployerProfile;
use App\Models\User;
use App\Models\JobType;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Notifications\ApplicationStatusUpdated;
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

        // Get profile completion percentage
        $profile = $employer->employerProfile;
        $profileCompletion = $profile ? $profile->getProfileCompletionPercentage() : 0;

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
            'applicationTrendsLabels',
            'applicationTrendsData',
            'jobPerformanceLabels',
            'jobPerformanceViews',
            'jobPerformanceApplications',
            'profileCompletion',
            'jobPerformance'
        ));
    }

    public function editProfile()
    {
        $employer = Auth::user();
        $profile = $employer->employerProfile ?? new EmployerProfile();
        return view('front.account.employer.profile.edit', compact('employer', 'profile'));
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            $profile = $user->employerProfile;
            
            if (!$profile) {
                $profile = new EmployerProfile(['user_id' => $user->id]);
            }

            // Validate the request
            $request->validate([
                'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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

            // Handle logo upload
            if ($request->hasFile('company_logo')) {
                try {
                    $file = $request->file('company_logo');
                    
                    // Delete old logo if it exists
                    if ($profile->company_logo) {
                        $oldPath = str_replace('storage/', '', $profile->company_logo);
                        Storage::disk('public')->delete($oldPath);
                    }
                    
                    // Generate a unique filename
                    $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    
                    // Ensure the company_logos directory exists
                    Storage::disk('public')->makeDirectory('company_logos');
                    
                    // Store the new file
                    $path = $file->storeAs('company_logos', $filename, 'public');
                    
                    if (!$path) {
                        throw new \Exception('Failed to store company logo');
                    }
                    
                    $profile->company_logo = $path;
                    
                } catch (\Exception $e) {
                    Log::error('Error uploading company logo: ' . $e->getMessage());
                    throw new \Exception('Failed to upload company logo: ' . $e->getMessage());
                }
            }

            // Update other fields
            $profile->fill([
                'company_name' => $request->company_name,
                'company_description' => $request->company_description,
                'industry' => $request->industry,
                'company_size' => $request->company_size,
                'website' => $request->website,
                'location' => $request->location,
                'social_links' => $request->social_links ?? [],
                'company_culture' => $request->company_culture ?? [],
                'benefits_offered' => $request->benefits_offered ?? [],
                'founded_year' => $request->founded_year,
                'headquarters' => $request->headquarters,
                'specialties' => $request->specialties ?? [],
                'contact_email' => $request->contact_email,
                'contact_phone' => $request->contact_phone,
                'status' => $request->has('save_draft') ? 'draft' : 'published'
            ]);

            // Save the profile
            if (!$profile->save()) {
                throw new \Exception('Failed to save profile');
            }

            // Redirect back with success message and scroll position
            return redirect()
                ->route('employer.profile.edit')
                ->with('success', 'Profile updated successfully!')
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
        $profile = $employer->employerProfile;

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
        $profile = $employer->employerProfile;

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

    public function showApplication(JobApplication $application)
    {
        // Verify that the employer owns this application
        if ($application->job->employer_id !== Auth::id()) {
            abort(403, 'Unauthorized action');
        }

        return view('front.account.employer.applications.show', [
            'application' => $application->load(['user', 'job', 'statusHistory'])
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

        // Create status history
        $application->statusHistory()->create([
            'status' => $request->status,
            'notes' => $request->notes ?? 'Status updated to ' . ucfirst($request->status)
        ]);

        // Send notification to job seeker
        try {
            $application->user->notify(new ApplicationStatusUpdated($application));
        } catch (\Exception $e) {
            \Log::error('Failed to send application status notification: ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Application status updated successfully'
        ]);
    }

    public function analytics()
    {
        $employer = Auth::user();
        $profile = $employer->employerProfile;
        
        // Get job metrics
        $jobMetrics = [
            'total_jobs' => $profile->total_jobs_posted,
            'active_jobs' => $profile->active_jobs,
            'total_applications' => $profile->total_applications_received,
            'profile_views' => $profile->profile_views
        ];

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

        // Get application trends (last 30 days)
        $applicationTrends = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = JobApplication::whereHas('job', function($query) use ($employer) {
                $query->where('employer_id', $employer->id);
            })
            ->whereDate('created_at', $date)
            ->count();
            
            $applicationTrends->push([
                'date' => now()->subDays($i)->format('M d'),
                'count' => $count
            ]);
        }

        // Get top performing jobs
        $topJobs = Job::where('employer_id', $employer->id)
            ->withCount(['applications', 'views'])
            ->orderBy('applications_count', 'desc')
            ->take(5)
            ->get();

        return view('front.account.employer.analytics.overview', compact(
            'jobMetrics',
            'recentActivity',
            'applicationTrends',
            'topJobs'
        ));
    }

    public function exportAnalytics(Request $request)
    {
        $employer = Auth::user();
        
        // Implement analytics export logic here
        // This could generate CSV/Excel files with detailed analytics
        
        return response()->download($filePath);
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



    public function jobs(Request $request)
    {
        $employer = Auth::user();
        $query = Job::where('employer_id', $employer->id)
                   ->withCount(['applications', 'views'])
                   ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
        }

        // Apply status filter
        if ($request->has('status')) {
            switch ($request->get('status')) {
                case 'active':
                    $query->where('status', 1);
                    break;
                case 'inactive':
                    $query->where('status', 0);
                    break;
                case 'draft':
                    $query->where('status', 2);
                    break;
            }
        }

        $jobs = $query->paginate(10)->withQueryString();
        
        return view('front.account.employer.jobs.index', compact('jobs'));
    }

    public function createJob()
    {
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
        $rules = [
            'title' => 'required|min:5|max:200',
            'category_id' => 'required|exists:categories,id',
            'job_type_id' => 'required|exists:job_types,id',
            'location' => 'required|max:255',
            'location_latitude' => 'nullable|numeric|between:-90,90',
            'location_longitude' => 'nullable|numeric|between:-180,180',
            'location_address' => 'nullable|string|max:500',
            'description' => 'required',
            'requirements' => 'nullable',
            'benefits' => 'nullable',
            'salary_min' => 'required|numeric|min:0',
            'salary_max' => 'required|numeric|gt:salary_min',
            'experience_level' => 'required|in:entry,intermediate,expert',
            'vacancy' => 'required|integer|min:1'
        ];

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $job = new Job();
            $job->title = $request->title;
            $job->category_id = $request->category_id;
            $job->job_type_id = $request->job_type_id;
            $job->employer_id = Auth::id();
            
            // Handle location data from Mapbox - use both old and new field structure
            $job->location = $request->location;
            $job->location_name = $request->location;
            $job->location_address = $request->location_address;
            $job->address = $request->location_address;
            $job->latitude = $request->location_latitude;
            $job->longitude = $request->location_longitude;
            $job->city = 'Digos City';
            $job->province = 'Davao del Sur';
            
            // Extract barangay from location if possible
            if (strpos($request->location, ',') !== false) {
                $locationParts = explode(',', $request->location);
                $job->barangay = trim($locationParts[0]);
            } else {
                $job->barangay = $request->location;
            }
            
            $job->description = $request->description;
            $job->requirements = $request->requirements;
            $job->benefits = $request->benefits;
            $job->salary_min = $request->salary_min;
            $job->salary_max = $request->salary_max;
            $job->experience_level = $request->experience_level;
            $job->vacancy = $request->vacancy;
            $job->status = $request->is_draft ? 'draft' : 'pending';
            $job->save();

            return response()->json([
                'status' => true,
                'message' => $request->is_draft ? 'Job saved as draft.' : 'Job posted successfully and pending approval.',
                'redirect' => route('employer.jobs.index')
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating job: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error creating job: ' . $e->getMessage()
            ], 500);
        }
    }

    public function editJob(Job $job)
    {
        $this->authorize('update', $job);
        
        $categories = \App\Models\Category::all();
        $jobTypes = \App\Models\JobType::all();
        
        return view('front.account.employer.jobs.edit', compact('job', 'categories', 'jobTypes'));
    }

    public function updateJob(Request $request, Job $job)
    {
        // Verify ownership
        if ($job->employer_id !== Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $rules = [
            'title' => 'required|min:5|max:200',
            'job_type_id' => 'required|exists:job_types,id',
            'location' => 'required|max:50',
            'description' => 'required',
            'requirements' => 'nullable',
            'benefits' => 'nullable',
            'salary_range' => 'nullable|string',
            'deadline' => 'nullable|date|after:today',
            'featured' => 'boolean'
        ];

        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $job->title = $request->title;
            $job->job_type_id = $request->job_type_id;
            $job->location = $request->location;
            $job->location_name = $request->location;
            $job->location_address = $request->location_address;
            $job->latitude = $request->latitude;
            $job->longitude = $request->longitude;
            $job->description = $request->description;
            $job->requirements = $request->requirements;
            $job->benefits = $request->benefits;
            $job->salary_range = $request->salary_range;
            $job->deadline = $request->deadline;
            $job->featured = $request->featured ?? false;
            $job->status = !$request->is_draft;
            $job->save();

            return response()->json([
                'status' => true,
                'message' => $request->is_draft ? 'Job saved as draft.' : 'Job updated successfully.',
                'redirect' => route('employer.jobs.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error updating job: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteJob(Job $job)
    {
        $this->authorize('delete', $job);
        
        $job->delete();
        
        return redirect()->route('employer.jobs.index')
                        ->with('success', 'Job deleted successfully.');
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
        $profile = $employer->employerProfile;
        
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
}