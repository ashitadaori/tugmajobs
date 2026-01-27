<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;
use App\Notifications\NewJobPostedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class JobController extends Controller
{
    public function index(Request $request)
    {
        // Show ALL jobs in the system for admin management
        $jobs = Job::with(['employer', 'category', 'jobType', 'company'])
            ->withCount('applications')
            ->orderBy('created_at', 'DESC')
            ->paginate(15);

        return view('admin.jobs.index', compact('jobs'));
    }

    /**
     * Search jobs in real-time (AJAX endpoint)
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $status = $request->get('status', '');

        $jobs = Job::with(['employer', 'category', 'jobType', 'company'])
            ->withCount('applications')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($subQuery) use ($query) {
                    $subQuery->where('title', 'LIKE', "%{$query}%")
                        ->orWhere('company_name', 'LIKE', "%{$query}%")
                        ->orWhere('location', 'LIKE', "%{$query}%")
                        ->orWhereHas('employer', function ($empQuery) use ($query) {
                            $empQuery->where('name', 'LIKE', "%{$query}%");
                        })
                        ->orWhereHas('category', function ($catQuery) use ($query) {
                            $catQuery->where('name', 'LIKE', "%{$query}%");
                        })
                        ->orWhereHas('jobType', function ($typeQuery) use ($query) {
                            $typeQuery->where('name', 'LIKE', "%{$query}%");
                        });
                });
            })
            ->when($status !== '', function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(15);

        // If it's an AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.jobs.partials.jobs-table-rows', compact('jobs'))->render(),
                'total' => $jobs->total(),
                'from' => $jobs->firstItem() ?? 0,
                'to' => $jobs->lastItem() ?? 0,
                'pagination' => $jobs->hasPages() ? $jobs->links('vendor.pagination.simple-admin')->render() : ''
            ]);
        }

        return view('admin.jobs.index', compact('jobs'));
    }

    /**
     * Show jobs posted by the current admin
     */
    public function myPostedJobs(Request $request)
    {
        // Show admin's posted jobs (including soft-deleted ones)
        $jobs = Job::withTrashed()
            ->where('employer_id', auth()->id())
            ->where('posted_by_admin', true)
            ->with(['category', 'jobType', 'company'])
            ->withCount('applications')
            ->orderBy('created_at', 'DESC')
            ->paginate(15);

        return view('admin.jobs.my-posted-jobs', compact('jobs'));
    }

    public function create()
    {
        // Get only active categories and job types, ordered by name
        $categories = Category::where('status', 1)
            ->orderBy('name', 'ASC')
            ->get();

        $job_types = JobType::where('status', 1)
            ->orderBy('name', 'ASC')
            ->get();

        // Get all companies
        $companies = \App\Models\Company::where('is_active', true)
            ->orderBy('name', 'ASC')
            ->get();

        // Sta. Cruz, Davao del Sur locations
        $locations = [
            ['name' => 'Aplaya', 'lat' => 6.7489, 'lng' => 125.3714],
            ['name' => 'Binaton', 'lat' => 6.7623, 'lng' => 125.3897],
            ['name' => 'Cogon', 'lat' => 6.7512, 'lng' => 125.3567],
            ['name' => 'Colorado', 'lat' => 6.7534, 'lng' => 125.3678],
            ['name' => 'Dawis', 'lat' => 6.7567, 'lng' => 125.3645],
            ['name' => 'Dulangan', 'lat' => 6.7589, 'lng' => 125.3723],
            ['name' => 'Goma', 'lat' => 6.7612, 'lng' => 125.3834],
            ['name' => 'Igpit', 'lat' => 6.7645, 'lng' => 125.3756],
            ['name' => 'Mahayag', 'lat' => 6.7678, 'lng' => 125.3867],
            ['name' => 'Matti', 'lat' => 6.7523, 'lng' => 125.3589],
            ['name' => 'Poblacion', 'lat' => 6.7545, 'lng' => 125.3578],
            ['name' => 'San Jose', 'lat' => 6.7556, 'lng' => 125.3634],
            ['name' => 'San Miguel', 'lat' => 6.7578, 'lng' => 125.3712],
            ['name' => 'Sinawilan', 'lat' => 6.7634, 'lng' => 125.3845],
            ['name' => 'Soong', 'lat' => 6.7667, 'lng' => 125.3789],
            ['name' => 'Tres De Mayo', 'lat' => 6.7689, 'lng' => 125.3856]
        ];

        return view('admin.jobs.create', [
            'categories' => $categories,
            'job_types' => $job_types,
            'companies' => $companies,
            'locations' => $locations
        ]);
    }

    public function store(Request $request)
    {
        // Log the incoming request for debugging
        Log::info('Admin job store request received', [
            'data' => $request->except(['_token']),
            'is_ajax' => $request->ajax(),
            'accepts_json' => $request->acceptsJson()
        ]);

        // Validate request data
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:5|max:200',
            'category' => 'required|exists:categories,id',
            'jobType' => 'required|exists:job_types,id',
            'vacancy' => 'required|integer|min:1',
            'location' => 'required|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_address' => 'nullable|string|max:255',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'experience_level' => 'required|in:entry,intermediate,expert',
            'description' => 'nullable|string',
            'qualifications' => 'required|string|min:10',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
            'company_name' => 'nullable|string|min:3|max:100',
            'company_website' => 'nullable|url|max:255',
            'is_draft' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            Log::warning('Admin job validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $admin = Auth::user();
            $isDraft = $request->input('is_draft', 0) == 1;

            Log::info('Creating admin job', [
                'admin_id' => $admin->id,
                'is_draft' => $isDraft
            ]);

            // Create new job posted by admin
            $job = new Job();
            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->jobType;
            $job->vacancy = $request->vacancy;
            $job->location = $request->location;
            $job->latitude = $request->latitude;
            $job->longitude = $request->longitude;
            $job->location_address = $request->location_address ?: $request->location;
            $job->salary_min = $request->salary_min;
            $job->salary_max = $request->salary_max;
            $job->experience_level = $request->experience_level;
            $job->description = $request->description ?: '';
            $job->qualifications = $request->qualifications;
            $job->requirements = $request->requirements ?: '';
            $job->benefits = $request->benefits ?: '';
            $job->company_id = $request->company_id;

            // If a company is selected, use the company's name; otherwise use provided name or 'Confidential'
            if ($request->company_id) {
                $company = \App\Models\Company::find($request->company_id);
                $job->company_name = $company ? $company->name : ($request->company_name ?: 'Confidential');
            } else {
                $job->company_name = $request->company_name ?: 'Confidential';
            }
            $job->company_website = $request->company_website;

            // Admin-posted jobs are auto-approved unless saved as draft
            $job->status = $isDraft ? Job::STATUS_PENDING : Job::STATUS_APPROVED;
            $job->employer_id = $admin->id; // Admin is the employer for this job
            $job->posted_by_admin = true; // Mark as admin-posted
            $job->approved_at = $isDraft ? null : now();

            $job->save();

            // Save job requirements (documents that applicants must submit)
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

            Log::info('Admin job created successfully', [
                'job_id' => $job->id,
                'job_title' => $job->title,
                'status' => $job->status,
                'requirements_count' => $job->jobRequirements()->count()
            ]);

            // If job is approved, notify all jobseekers (wrapped in try-catch to not fail job creation)
            $notificationMessage = '';
            if (!$isDraft) {
                try {
                    $this->notifyJobseekersAboutNewJob($job);
                    $notificationMessage = ' Jobseekers with matching preferences have been notified.';
                } catch (\Exception $notifyException) {
                    Log::error('Failed to notify jobseekers about new job (job was still created)', [
                        'job_id' => $job->id,
                        'error' => $notifyException->getMessage()
                    ]);
                    $notificationMessage = ' (Note: Job created but notifications could not be sent)';
                }
            }

            return response()->json([
                'success' => true,
                'message' => $isDraft ? 'Job saved as draft successfully!' : 'Job posted successfully!' . $notificationMessage,
                'redirect' => route('admin.jobs.index')
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to create admin job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the job: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Job $job)
    {
        $job->load(['employer', 'category', 'jobType', 'applications']);
        return view('admin.jobs.show', compact('job'));
    }

    public function edit($id)
    {
        $job = Job::findOrFail($id);

        // Authorization: Only allow editing if job was posted by admin or current admin
        // Superadmins can edit any job
        $currentAdmin = Auth::user();
        if (!$currentAdmin->isSuperAdmin() && $job->employer_id !== $currentAdmin->id) {
            abort(403, 'Unauthorized access. You can only edit jobs that you created.');
        }

        // Get only active categories and job types, ordered by name
        $categories = Category::where('status', 1)
            ->orderBy('name', 'ASC')
            ->get();

        $job_types = JobType::where('status', 1)
            ->orderBy('name', 'ASC')
            ->get();

        return view("admin.jobs.edit", [
            'job' => $job,
            'categories' => $categories,
            'job_types' => $job_types,
        ]);
    }

    public function update(Request $request, $jobId)
    {
        $job = Job::findOrFail($jobId);

        // Authorization: Only allow updating if job was posted by admin or current admin
        // Superadmins can update any job
        $currentAdmin = Auth::user();
        if (!$currentAdmin->isSuperAdmin() && $job->employer_id !== $currentAdmin->id) {
            return response()->json([
                'status' => false,
                'errors' => ['authorization' => ['You are not authorized to edit this job.']],
            ], 403);
        }

        $data = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'experience' => 'required',
            'company_name' => 'required|min:3|max:75',
        ];

        $validator = Validator::make($request->all(), $data);
        if ($validator->passes()) {
            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->jobType;
            $job->vacancy = $request->vacancy;
            $job->salary = $request->salary;
            $job->location = $request->location;
            $job->description = $request->description;
            $job->benefits = $request->benefits;
            $job->responsibility = $request->responsibility;
            $job->qualifications = $request->qualifications;
            $job->keywords = $request->keywords;
            $job->experience = $request->experience;
            $job->company_name = $request->company_name;
            $job->company_location = $request->company_location;
            $job->company_website = $request->company_website;
            $job->status = $request->status;
            $job->featured = (!empty($request->featured)) ? $request->featured : 0;
            $job->save();

            // Handle job requirements (documents that applicants must submit)
            if ($request->has('job_requirements')) {
                // Get existing requirement IDs
                $existingIds = $job->jobRequirements()->pluck('id')->toArray();
                $submittedIds = [];

                $sortOrder = 1;
                foreach ($request->job_requirements as $requirement) {
                    if (!empty($requirement['name'])) {
                        if (!empty($requirement['id'])) {
                            // Update existing requirement
                            $submittedIds[] = $requirement['id'];
                            \App\Models\JobRequirement::where('id', $requirement['id'])->update([
                                'name' => $requirement['name'],
                                'description' => $requirement['description'] ?? null,
                                'is_required' => isset($requirement['is_required']) ? true : false,
                                'sort_order' => $sortOrder++
                            ]);
                        } else {
                            // Create new requirement
                            $newReq = \App\Models\JobRequirement::create([
                                'job_id' => $job->id,
                                'name' => $requirement['name'],
                                'description' => $requirement['description'] ?? null,
                                'is_required' => isset($requirement['is_required']) ? true : false,
                                'sort_order' => $sortOrder++
                            ]);
                            $submittedIds[] = $newReq->id;
                        }
                    }
                }

                // Delete removed requirements
                $idsToDelete = array_diff($existingIds, $submittedIds);
                if (!empty($idsToDelete)) {
                    \App\Models\JobRequirement::whereIn('id', $idsToDelete)->delete();
                }
            } else {
                // If no requirements submitted, delete all existing ones
                $job->jobRequirements()->delete();
            }

            session()->flash('success', 'Job updated successfully.');
            return response()->json([
                'status' => true,
                'errors' => [],
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function approve(Job $job)
    {
        try {
            $oldStatus = $job->status;

            $job->update([
                'status' => Job::STATUS_APPROVED,
                'approved_at' => now()
            ]);

            // Send notification to employer
            if ($job->employer) {
                \DB::table('notifications')->insert([
                    'user_id' => $job->employer_id,
                    'title' => 'Job Posting Approved!',
                    'message' => 'Your job posting "' . $job->title . '" has been approved and is now live!',
                    'type' => 'job_approved',
                    'data' => json_encode([
                        'job_id' => $job->id,
                        'job_title' => $job->title,
                        'icon' => 'check-circle',
                        'color' => 'success'
                    ]),
                    'action_url' => route('employer.jobs.index'),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Notify all jobseekers about the new job
            if ($oldStatus !== Job::STATUS_APPROVED) {
                $this->notifyJobseekersAboutNewJob($job);
            }

            // Log audit action
            \App\Models\AuditLog::logAction('approved', 'Job', $job->id, ['status' => $oldStatus], ['status' => Job::STATUS_APPROVED]);

            // Clear dashboard cache to update pending jobs count immediately
            \Cache::forget('admin_dashboard_stats');

            return redirect()->back()->with('success', 'Job has been approved successfully. Jobseekers with matching preferences have been notified.');
        } catch (\Exception $e) {
            \Log::error('Failed to approve job: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to approve job. Please try again.');
        }
    }

    public function reject(Request $request, Job $job)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500'
        ]);

        try {
            $job->update([
                'status' => Job::STATUS_REJECTED,
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now()
            ]);

            // Send notification to employer
            if ($job->employer) {
                \DB::table('notifications')->insert([
                    'user_id' => $job->employer_id,
                    'title' => 'Job Posting Needs Revision',
                    'message' => 'Your job posting "' . $job->title . '" needs revision. Please review the feedback and resubmit.',
                    'type' => 'job_rejected',
                    'data' => json_encode([
                        'job_id' => $job->id,
                        'job_title' => $job->title,
                        'rejection_reason' => $request->rejection_reason,
                        'icon' => 'exclamation-triangle',
                        'color' => 'warning'
                    ]),
                    'action_url' => route('employer.jobs.edit', $job->id),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Log audit action
            \App\Models\AuditLog::logAction('rejected', 'Job', $job->id, ['status' => $job->getOriginal('status')], ['status' => Job::STATUS_REJECTED, 'rejection_reason' => $request->rejection_reason]);

            // Clear dashboard cache to update pending jobs count immediately
            \Cache::forget('admin_dashboard_stats');

            return redirect()->back()->with('success', 'Job has been rejected successfully. Employer has been notified.');
        } catch (\Exception $e) {
            \Log::error('Failed to reject job: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to reject job. Please try again.');
        }
    }

    public function destroy($id)
    {
        try {
            // Find the job including soft-deleted ones
            $job = Job::withTrashed()->find($id);

            if (!$job) {
                return response()->json(['success' => false, 'message' => 'Job not found'], 404);
            }

            // If already soft-deleted, force delete
            if ($job->trashed()) {
                // Delete associated applications first
                $job->applications()->forceDelete();
                $job->forceDelete();
            } else {
                // Soft delete the job (applications remain for reference)
                $job->delete();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error deleting job: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error deleting job: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Restore a soft-deleted job
     */
    public function restore($id)
    {
        try {
            // Find the soft-deleted job
            $job = Job::onlyTrashed()->find($id);

            if (!$job) {
                return response()->json(['success' => false, 'message' => 'Job not found or not deleted'], 404);
            }

            // Restore the job
            $job->restore();

            return response()->json(['success' => true, 'message' => 'Job restored successfully']);
        } catch (\Exception $e) {
            \Log::error('Error restoring job: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error restoring job: ' . $e->getMessage()], 500);
        }
    }

    public function pending()
    {
        $jobs = Job::where('status', Job::STATUS_PENDING)
            ->orderBy('created_at', 'DESC')
            ->with('employer', 'applications')
            ->paginate(10);

        return view('admin.jobs.pending', [
            'jobs' => $jobs,
        ]);
    }

    /**
     * Notify jobseekers about a newly approved job based on their preferences
     * Only jobseekers with matching category preferences will be notified
     */
    private function notifyJobseekersAboutNewJob(Job $job)
    {
        try {
            // Get the job's category ID and job type ID
            $jobCategoryId = $job->category_id;
            $jobTypeId = $job->job_type_id;

            // Get jobseekers whose preferences match this job
            $matchingJobseekers = User::where('role', 'jobseeker')
                ->whereHas('jobSeekerProfile', function ($query) use ($jobCategoryId, $jobTypeId) {
                    $query->where(function ($q) use ($jobCategoryId, $jobTypeId) {
                        // Match by preferred categories
                        $q->whereJsonContains('preferred_categories', $jobCategoryId)
                            ->orWhereJsonContains('preferred_categories', (string) $jobCategoryId);

                        // REMOVED: Match by preferred job types (This caused unrelated job notifications)
                        // Notifications should strictly follow the user's preferred categories.
                    });
                })
                ->get();

            // Also get jobseekers who have no preferences set (empty or null)
            // These users should receive all job notifications
            $jobseekersWithNoPreferences = User::where('role', 'jobseeker')
                ->whereHas('jobSeekerProfile', function ($query) {
                    $query->where(function ($q) {
                        $q->whereNull('preferred_categories')
                            ->orWhere('preferred_categories', '[]')
                            ->orWhere('preferred_categories', '');
                    });
                })
                ->get();

            // Also include jobseekers without a profile yet (they should still get notifications)
            $jobseekersWithoutProfile = User::where('role', 'jobseeker')
                ->whereDoesntHave('jobSeekerProfile')
                ->get();

            // Merge all matching jobseekers and remove duplicates
            $allJobseekers = $matchingJobseekers
                ->merge($jobseekersWithNoPreferences)
                ->merge($jobseekersWithoutProfile)
                ->unique('id');

            Log::info('Notifying jobseekers about new job based on preferences', [
                'job_id' => $job->id,
                'job_title' => $job->title,
                'job_category_id' => $jobCategoryId,
                'job_type_id' => $jobTypeId,
                'matching_by_preference' => $matchingJobseekers->count(),
                'no_preferences_set' => $jobseekersWithNoPreferences->count(),
                'without_profile' => $jobseekersWithoutProfile->count(),
                'total_to_notify' => $allJobseekers->count()
            ]);

            // Determine company name to display
            $companyName = 'Confidential';
            if ($job->company_id && $job->company) {
                // If linked to a company from companies table
                $companyName = $job->company->name;
            } elseif ($job->company_name) {
                // If company_name field is filled
                $companyName = $job->company_name;
            }

            // Load relationships if not already loaded
            $job->loadMissing(['jobType', 'category']);

            // Send notification to each matching jobseeker
            foreach ($allJobseekers as $jobseeker) {
                \DB::table('notifications')->insert([
                    'user_id' => $jobseeker->id,
                    'title' => 'New Job Posted!',
                    'message' => 'A new job opportunity is available: ' . $job->title . ' at ' . $companyName,
                    'type' => 'new_job',
                    'data' => json_encode([
                        'job_id' => $job->id,
                        'job_title' => $job->title,
                        'company_name' => $companyName,
                        'location' => $job->location,
                        'job_type' => $job->jobType?->name ?? 'Full Time',
                        'category' => $job->category?->name ?? 'General',
                        'status' => 'new_job'
                    ]),
                    'action_url' => route('jobDetail', $job->id),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            Log::info('Successfully notified jobseekers about new job', [
                'job_id' => $job->id,
                'notifications_sent' => $allJobseekers->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to notify jobseekers about new job', [
                'job_id' => $job->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * View applicants for a specific job
     */
    public function viewApplicants($jobId)
    {
        $job = Job::with([
            'applications' => function ($query) {
                $query->with('user')->orderBy('created_at', 'desc');
            }
        ])->findOrFail($jobId);

        return view('admin.jobs.applicants', compact('job'));
    }

    /**
     * View single application details with hiring pipeline
     */
    public function viewApplication($applicationId)
    {
        $application = \App\Models\JobApplication::with([
            'job',
            'job.jobType',
            'job.category',
            'job.jobRequirements',
            'job.employer',
            'job.company',
            'user',
            'user.jobSeekerProfile',
            'statusHistory' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ])->findOrFail($applicationId);

        // Admin can view applications for jobs they posted OR all jobs if they're an admin
        // This allows admins to manage applications from both admin-posted jobs and employer-posted jobs
        $isAdminPostedJob = $application->job->posted_by_admin && $application->job->employer_id === auth()->id();
        $isAdmin = auth()->user()->role === 'admin';

        if (!$isAdminPostedJob && !$isAdmin) {
            abort(403, 'Unauthorized');
        }

        return view('admin.jobs.application-show', compact('application'));
    }

    /**
     * Update application stage (hiring pipeline)
     */
    public function updateApplicationStage(Request $request, $applicationId)
    {
        $application = \App\Models\JobApplication::findOrFail($applicationId);

        // Admin can manage applications for jobs they posted OR all jobs if they're an admin
        $isAdminPostedJob = $application->job->posted_by_admin && $application->job->employer_id === auth()->id();
        $isAdmin = auth()->user()->role === 'admin';

        if (!$isAdminPostedJob && !$isAdmin) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'action' => 'required|in:approve,reject,advance',
            'notes' => 'nullable|string|max:1000'
        ]);

        $application->load(['user', 'job.jobRequirements']);

        $jobTitle = $application->job->title;
        $companyName = auth()->user()->name ?? 'PESO Office';
        $action = $request->action;
        $notes = $request->notes;

        try {
            \DB::beginTransaction();

            if ($action === 'reject') {
                $application->rejectApplication($notes);

                \App\Models\Notification::create([
                    'user_id' => $application->user->id,
                    'title' => 'Application Rejected',
                    'message' => 'Your application for "' . $jobTitle . '" was not successful.' . ($notes ? ' Feedback: ' . $notes : ''),
                    'type' => 'application_rejected',
                    'data' => [
                        'job_application_id' => $application->id,
                        'job_id' => $application->job_id,
                        'job_title' => $jobTitle,
                        'stage' => $application->stage,
                        'rejection_reason' => $notes,
                    ],
                    'action_url' => route('account.showJobApplication', $application->id),
                    'read_at' => null
                ]);

                \DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Application rejected successfully.'
                ]);

            } elseif ($action === 'approve') {
                $previousStage = $application->stage;
                $application->approveCurrentStage($notes);

                $this->createAdminStageApprovalNotification($application, $previousStage, $jobTitle, $companyName, $notes);

                \DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Application approved! Moved to next stage.',
                    'new_stage' => $application->stage,
                    'stage_name' => $application->getStageName()
                ]);

            } elseif ($action === 'advance') {
                $previousStage = $application->stage;
                $application->advanceToNextStage($notes);

                $this->createAdminStageAdvancementNotification($application, $previousStage, $jobTitle, $companyName);

                \DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'Application advanced to next stage.',
                    'new_stage' => $application->stage,
                    'stage_name' => $application->getStageName()
                ]);
            }

        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error updating application stage: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Schedule interview for admin's job application
     */
    public function scheduleInterview(Request $request, $applicationId)
    {
        $application = \App\Models\JobApplication::findOrFail($applicationId);

        // Admin can manage applications for jobs they posted OR all jobs if they're an admin
        $isAdminPostedJob = $application->job->posted_by_admin && $application->job->employer_id === auth()->id();
        $isAdmin = auth()->user()->role === 'admin';

        if (!$isAdminPostedJob && !$isAdmin) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($application->stage !== \App\Models\JobApplication::STAGE_INTERVIEW) {
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
            $application->load('user');
            $jobTitle = $application->job->title;
            $companyName = auth()->user()->name ?? 'PESO Office';

            $application->scheduleInterview(
                $request->interview_date,
                $request->interview_time,
                $request->interview_location,
                $request->interview_type,
                $request->interview_notes
            );

            $interviewDate = \Carbon\Carbon::parse($request->interview_date)->format('F d, Y');
            $interviewTime = \Carbon\Carbon::parse($request->interview_time)->format('h:i A');

            \App\Models\Notification::create([
                'user_id' => $application->user->id,
                'title' => 'Interview Scheduled',
                'message' => 'Your interview for "' . $jobTitle . '" has been scheduled for ' . $interviewDate . ' at ' . $interviewTime . '.',
                'type' => 'interview_scheduled',
                'data' => [
                    'job_application_id' => $application->id,
                    'job_id' => $application->job_id,
                    'job_title' => $jobTitle,
                    'interview_date' => $request->interview_date,
                    'interview_time' => $request->interview_time,
                    'interview_location' => $request->interview_location,
                    'interview_type' => $request->interview_type,
                ],
                'action_url' => route('account.showJobApplication', $application->id),
                'read_at' => null
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Interview scheduled successfully!',
                'interview_date' => $interviewDate,
                'interview_time' => $interviewTime
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
     * Reschedule interview for admin's job application
     */
    public function rescheduleInterview(Request $request, $applicationId)
    {
        $application = \App\Models\JobApplication::findOrFail($applicationId);

        // Admin can manage applications for jobs they posted OR all jobs if they're an admin
        $isAdminPostedJob = $application->job->posted_by_admin && $application->job->employer_id === auth()->id();
        $isAdmin = auth()->user()->role === 'admin';

        if (!$isAdminPostedJob && !$isAdmin) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

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
            $application->load('user');
            $jobTitle = $application->job->title;
            $companyName = auth()->user()->name ?? 'PESO Office';

            $oldDate = $application->interview_date->format('F d, Y');
            $oldTime = $application->interview_time;

            $application->update([
                'interview_date' => $request->interview_date,
                'interview_time' => $request->interview_time,
                'interview_location' => $request->interview_location,
                'interview_type' => $request->interview_type,
                'interview_notes' => $request->interview_notes,
                'interview_scheduled_at' => now()
            ]);

            $application->statusHistory()->create([
                'status' => 'interview_rescheduled',
                'notes' => 'Interview rescheduled. Reason: ' . $request->reschedule_reason . '. New date: ' . \Carbon\Carbon::parse($request->interview_date)->format('F d, Y') . ' at ' . $request->interview_time
            ]);

            $newInterviewDate = \Carbon\Carbon::parse($request->interview_date)->format('F d, Y');
            $newInterviewTime = \Carbon\Carbon::parse($request->interview_time)->format('h:i A');

            \App\Models\Notification::create([
                'user_id' => $application->user->id,
                'title' => 'Interview Rescheduled',
                'message' => 'Your interview for "' . $jobTitle . '" has been rescheduled from ' . $oldDate . ' to ' . $newInterviewDate . ' at ' . $newInterviewTime . '. Reason: ' . $request->reschedule_reason,
                'type' => 'interview_rescheduled',
                'data' => [
                    'job_application_id' => $application->id,
                    'job_id' => $application->job_id,
                    'job_title' => $jobTitle,
                    'old_date' => $oldDate,
                    'old_time' => $oldTime,
                    'new_date' => $request->interview_date,
                    'new_time' => $request->interview_time,
                    'reschedule_reason' => $request->reschedule_reason,
                ],
                'action_url' => route('account.showJobApplication', $application->id),
                'read_at' => null
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Interview rescheduled successfully!',
                'interview_date' => $newInterviewDate,
                'interview_time' => $newInterviewTime
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
    public function markAsHired(Request $request, $applicationId)
    {
        $application = \App\Models\JobApplication::findOrFail($applicationId);

        // Admin can manage applications for jobs they posted OR all jobs if they're an admin
        $isAdminPostedJob = $application->job->posted_by_admin && $application->job->employer_id === auth()->id();
        $isAdmin = auth()->user()->role === 'admin';

        if (!$isAdminPostedJob && !$isAdmin) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($application->stage !== \App\Models\JobApplication::STAGE_INTERVIEW) {
            return response()->json([
                'status' => false,
                'message' => 'Application must be in interview stage to mark as hired.'
            ], 400);
        }

        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $application->load('user');
            $jobTitle = $application->job->title;
            $companyName = auth()->user()->name ?? 'PESO Office';

            $application->markAsHired($request->notes);

            \App\Models\Notification::create([
                'user_id' => $application->user->id,
                'title' => 'Congratulations - You\'re Hired!',
                'message' => 'Congratulations! You have been hired for "' . $jobTitle . '"!' . ($request->notes ? ' Note: ' . $request->notes : ''),
                'type' => 'hired',
                'data' => [
                    'job_application_id' => $application->id,
                    'job_id' => $application->job_id,
                    'job_title' => $jobTitle,
                ],
                'action_url' => route('account.showJobApplication', $application->id),
                'read_at' => null
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Applicant marked as hired successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking as hired: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred.'
            ], 500);
        }
    }

    /**
     * View submitted documents for an application
     */
    public function viewSubmittedDocuments($applicationId)
    {
        $application = \App\Models\JobApplication::with([
            'job.jobRequirements',
            'user'
        ])->findOrFail($applicationId);

        // Admin can view documents for jobs they posted OR all jobs if they're an admin
        $isAdminPostedJob = $application->job->posted_by_admin && $application->job->employer_id === auth()->id();
        $isAdmin = auth()->user()->role === 'admin';

        if (!$isAdminPostedJob && !$isAdmin) {
            abort(403, 'Unauthorized');
        }

        return view('admin.jobs.application-documents', compact('application'));
    }

    /**
     * Create notification for stage approval
     */
    private function createAdminStageApprovalNotification($application, $previousStage, $jobTitle, $companyName, $notes = null)
    {
        $notificationData = [
            'job_application_id' => $application->id,
            'job_id' => $application->job_id,
            'job_title' => $jobTitle,
            'stage' => $previousStage,
        ];

        switch ($previousStage) {
            case \App\Models\JobApplication::STAGE_APPLICATION:
                \App\Models\Notification::create([
                    'user_id' => $application->user->id,
                    'title' => 'Application Approved - Submit Documents',
                    'message' => 'Great news! Your application for "' . $jobTitle . '" has been approved! Please submit the required documents to proceed.',
                    'type' => 'stage_approved',
                    'data' => $notificationData,
                    'action_url' => route('job.submitRequirements', $application->id),
                    'read_at' => null
                ]);
                break;

            case \App\Models\JobApplication::STAGE_REQUIREMENTS:
                \App\Models\Notification::create([
                    'user_id' => $application->user->id,
                    'title' => 'Documents Approved - Awaiting Interview',
                    'message' => 'Your documents for "' . $jobTitle . '" have been verified! Please wait for the interview schedule.',
                    'type' => 'documents_approved',
                    'data' => $notificationData,
                    'action_url' => route('account.showJobApplication', $application->id),
                    'read_at' => null
                ]);
                break;

            case \App\Models\JobApplication::STAGE_INTERVIEW:
                \App\Models\Notification::create([
                    'user_id' => $application->user->id,
                    'title' => 'Interview Successfully Completed',
                    'message' => 'Your interview for "' . $jobTitle . '" at ' . $companyName . ' has been marked as successful! Please wait for the final hiring decision.' . ($notes ? ' Note: ' . $notes : ''),
                    'type' => 'interview_passed',
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
    private function createAdminStageAdvancementNotification($application, $previousStage, $jobTitle, $companyName)
    {
        $notificationData = [
            'job_application_id' => $application->id,
            'job_id' => $application->job_id,
            'job_title' => $jobTitle,
            'previous_stage' => $previousStage,
            'new_stage' => $application->stage,
        ];

        switch ($application->stage) {
            case \App\Models\JobApplication::STAGE_REQUIREMENTS:
                \App\Models\Notification::create([
                    'user_id' => $application->user->id,
                    'title' => 'Submit Required Documents',
                    'message' => 'Your application for "' . $jobTitle . '" is now in the document submission stage. Please upload the required documents.',
                    'type' => 'submit_requirements',
                    'data' => $notificationData,
                    'action_url' => route('job.submitRequirements', $application->id),
                    'read_at' => null
                ]);
                break;

            case \App\Models\JobApplication::STAGE_INTERVIEW:
                \App\Models\Notification::create([
                    'user_id' => $application->user->id,
                    'title' => 'Proceeding to Interview Stage',
                    'message' => 'Your documents for "' . $jobTitle . '" have been approved. Please wait for the interview schedule.',
                    'type' => 'interview_stage',
                    'data' => $notificationData,
                    'action_url' => route('account.showJobApplication', $application->id),
                    'read_at' => null
                ]);
                break;

            case \App\Models\JobApplication::STAGE_HIRED:
                \App\Models\Notification::create([
                    'user_id' => $application->user->id,
                    'title' => 'Congratulations - You\'re Hired!',
                    'message' => 'Congratulations! You have been officially hired for "' . $jobTitle . '"!',
                    'type' => 'hired',
                    'data' => $notificationData,
                    'action_url' => route('account.showJobApplication', $application->id),
                    'read_at' => null
                ]);
                break;
        }
    }
}
