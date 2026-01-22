<?php

namespace App\Http\Controllers;

use App\Mail\JobNotificationEmail;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\BookmarkedJob;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Services\LocationService;
use App\Notifications\JobSavedNotification;
use App\Notifications\AdminNewApplicationNotification;
use App\Notifications\NewApplicationReceived;
use App\Models\JobView;

class JobsController extends Controller
{
    //This method will show jobs page
    public function index(Request $request)
    {
        // Only show active jobs to jobseekers (status=1 means active/approved)
        $query = Job::where('status', 1);

        // Keyword search
        if (!empty($request->keyword)) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->keyword . '%')
                    ->orWhere('description', 'like', '%' . $request->keyword . '%')
                    ->orWhere('requirements', 'like', '%' . $request->keyword . '%');
            });
        }

        // Location-based search with Mapbox integration
        if (!empty($request->location)) {
            // Check if we have coordinates for distance-based search
            if (!empty($request->location_filter_latitude) && !empty($request->location_filter_longitude)) {
                $latitude = $request->location_filter_latitude;
                $longitude = $request->location_filter_longitude;
                $radius = $request->radius ?? 10; // Default 10km radius

                $query->withinDistance($latitude, $longitude, $radius);
            } else {
                // Fallback to text-based location search
                $query->where(function ($q) use ($request) {
                    $q->where('location', 'like', '%' . $request->location . '%')
                        ->orWhere('address', 'like', '%' . $request->location . '%')
                        ->orWhere('barangay', 'like', '%' . $request->location . '%');
                });
            }
        }

        // Job type filter
        if (!empty($request->jobType)) {
            $query->whereHas('jobType', function ($q) use ($request) {
                $q->where('name', $request->jobType);
            });
        }

        // Category filter
        if (!empty($request->category)) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        $jobs = $query->with(['jobType', 'category', 'employer.employerProfile'])
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        $jobTypes = JobType::where('status', 1)->get();

        return view('front.modern-jobs', [
            'jobs' => $jobs,
            'jobTypes' => $jobTypes
        ]);
    }

    // This method will show job detail page
    public function jobDetail($id)
    {
        $job = Job::with([
            'jobType',
            'employer' => function ($query) {
                $query->with('employerProfile');
            },
            'applications'
        ])->findOrFail($id);

        // Record job view
        JobView::recordView($job, request());

        $count = 0;
        if (Auth::check()) {
            $count = BookmarkedJob::where([
                'user_id' => Auth::user()->id,
                'job_id' => $id
            ])->count();
        }

        // Get related jobs (status=1 means active)
        $relatedJobs = Job::where('status', 1)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($job) {
                $query->where('location', 'like', '%' . explode(',', $job->location)[0] . '%')
                    ->orWhere('job_type_id', $job->job_type_id);
            })
            ->with(['jobType', 'employer.employerProfile'])
            ->take(3)
            ->get();

        return view('front.modern-job-detail', [
            'job' => $job,
            'count' => $count,
            'relatedJobs' => $relatedJobs,
            'applications' => $job->applications
        ]);
    }

    /**
     * Start the step-by-step application process
     */
    public function startApplication($id)
    {
        try {
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'Please login to apply for jobs');
            }

            $user = Auth::user();
            if ($user->role !== 'jobseeker') {
                return back()->with('error', 'Only job seekers can apply for jobs');
            }

            // Check KYC verification status
            if (!$user->isKycVerified()) {
                return back()->with('error', 'You must complete KYC verification before applying for jobs. Please verify your identity first.');
            }

            $job = Job::findOrFail($id);

            // Check existing incomplete application first
            $application = JobApplication::where('user_id', $user->id)
                ->where('job_id', $job->id)
                ->where('application_step', '!=', 'submitted')
                ->first();

            // If no incomplete application found, check if user has already completed application
            if (!$application && $job->hasUserApplied($user->id)) {
                return back()->with('error', 'You have already applied for this job');
            }

            if (!$application) {
                // Create new application
                $application = JobApplication::create([
                    'job_id' => $job->id,
                    'user_id' => $user->id,
                    'employer_id' => $job->employer_id,
                    'status' => 'draft',
                    'application_step' => 'basic_info',
                    'applied_date' => null // Will be set when submitted
                ]);
            }

            return view('front.job-application-wizard', [
                'job' => $job,
                'application' => $application,
                'currentStep' => $this->getStepNumber($application->application_step, $job)
            ]);

        } catch (\Exception $e) {
            \Log::error('Application start error', [
                'error' => $e->getMessage(),
                'job_id' => $id,
                'user_id' => Auth::id()
            ]);
            return back()->with('error', 'Error starting application. Please try again.');
        }
    }

    /**
     * Cancel an incomplete job application and start fresh
     */
    public function cancelApplication($id)
    {
        try {
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'Please login to apply for jobs');
            }

            $user = Auth::user();
            if ($user->role !== 'jobseeker') {
                return back()->with('error', 'Only job seekers can apply for jobs');
            }

            $job = Job::findOrFail($id);

            // Find and delete incomplete application
            $application = $job->getIncompleteApplication($user->id);
            if ($application) {
                $application->delete();
            }

            // Redirect to start a fresh application
            return redirect()->route('job.application.start', $id)
                ->with('success', 'Previous incomplete application cleared. You can now start fresh.');

        } catch (\Exception $e) {
            \Log::error('Application cancel error', [
                'error' => $e->getMessage(),
                'job_id' => $id,
                'user_id' => Auth::id()
            ]);
            return back()->with('error', 'Error canceling application. Please try again.');
        }
    }

    /**
     * Process step-by-step application
     */
    public function processApplication($id, Request $request)
    {
        try {
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'Please login to apply for jobs');
            }

            $user = Auth::user();
            $job = Job::findOrFail($id);
            $currentStep = (int) $request->input('current_step', 1);
            $applicationId = $request->input('application_id');

            // Get or create application
            if ($applicationId) {
                $application = JobApplication::findOrFail($applicationId);
            } else {
                $application = JobApplication::where('user_id', $user->id)
                    ->where('job_id', $job->id)
                    ->first();

                if (!$application) {
                    $application = JobApplication::create([
                        'job_id' => $job->id,
                        'user_id' => $user->id,
                        'employer_id' => $job->employer_id,
                        'status' => 'draft',
                        'application_step' => 'basic_info'
                    ]);
                }
            }

            // Process based on current step
            switch ($currentStep) {
                case 1:
                    return $this->processProfileStep($application, $request, $job);
                case 2:
                    if ($job->requires_screening) {
                        return $this->processScreeningStep($application, $request, $job);
                    } else {
                        return $this->processDocumentsStep($application, $request, $job);
                    }
                case 3:
                    if ($job->requires_screening) {
                        return $this->processDocumentsStep($application, $request, $job);
                    } else {
                        return $this->processSubmitStep($application, $request, $job);
                    }
                case 4:
                    return $this->processSubmitStep($application, $request, $job);
                default:
                    return back()->with('error', 'Invalid application step');
            }

        } catch (\Exception $e) {
            \Log::error('Application process error', [
                'error' => $e->getMessage(),
                'job_id' => $id,
                'user_id' => Auth::id(),
                'step' => $currentStep
            ]);
            return back()->with('error', 'Error processing application. Please try again.');
        }
    }

    private function getStepNumber($stepName, $job)
    {
        $steps = ['basic_info' => 1];

        if ($job->requires_screening && !empty($job->preliminary_questions)) {
            $steps['screening'] = 2;
            $steps['documents'] = 3;
            $steps['review'] = 4;
        } else {
            $steps['documents'] = 2;
            $steps['review'] = 3;
        }

        return $steps[$stepName] ?? 1;
    }

    private function processProfileStep($application, $request, $job)
    {
        $request->validate([
            'profile_confirmed' => 'required'
        ]);

        $application->update([
            'application_step' => $job->requires_screening ? 'screening' : 'documents',
            'profile_updated' => true
        ]);

        $nextStep = $job->requires_screening ? 2 : 2; // Always 2 for next step

        return view('front.job-application-wizard', [
            'job' => $job,
            'application' => $application,
            'currentStep' => $nextStep
        ]);
    }

    private function processScreeningStep($application, $request, $job)
    {
        $preliminaryAnswers = $request->input('preliminary_answers', []);

        // Validate required questions
        if ($job->preliminary_questions) {
            foreach ($job->preliminary_questions as $index => $question) {
                if (($question['required'] ?? false) && empty($preliminaryAnswers[$index])) {
                    return back()->withErrors(["preliminary_answers.{$index}" => 'This question is required'])->withInput();
                }
            }
        }

        $application->update([
            'application_step' => 'documents',
            'preliminary_answers' => $preliminaryAnswers
        ]);

        return view('front.job-application-wizard', [
            'job' => $job,
            'application' => $application,
            'currentStep' => 3
        ]);
    }

    private function processDocumentsStep($application, $request, $job)
    {
        $resumeOption = $request->input('resume_option', 'new');
        $existingResume = $request->input('existing_resume');

        // Validate based on resume option
        if ($resumeOption === 'new' || !$existingResume) {
            $request->validate([
                'resume' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB
                'cover_letter' => 'nullable|string|max:2000'
            ]);

            // Handle new resume upload
            if ($request->hasFile('resume')) {
                $resumePath = $request->file('resume')->store('resumes', 'public');
                $application->resume = $resumePath;
            }
        } else {
            // Use existing resume from profile
            $request->validate([
                'cover_letter' => 'nullable|string|max:2000',
                'existing_resume' => 'required|string'
            ]);

            // Copy existing resume path to application
            $application->resume = $existingResume;
        }

        // Build update data - always include resume and cover_letter
        $updateData = [
            'application_step' => 'review',
            'cover_letter' => $request->input('cover_letter'),
            'resume' => $application->resume  // Include the resume path that was set above
        ];

        $application->update($updateData);

        $nextStep = $job->requires_screening ? 4 : 3;

        return view('front.job-application-wizard', [
            'job' => $job,
            'application' => $application,
            'currentStep' => $nextStep
        ]);
    }

    private function processSubmitStep($application, $request, $job)
    {
        $request->validate([
            'final_confirmation' => 'required'
        ]);

        // Handle resume upload (since this is a client-side wizard, files come with final submission)
        $resumePath = $application->resume; // Keep existing if already set

        $resumeOption = $request->input('resume_option', 'new');
        $existingResume = $request->input('existing_resume');

        if ($request->hasFile('resume')) {
            // New resume uploaded
            $resumePath = $request->file('resume')->store('resumes', 'public');
        } elseif ($resumeOption === 'existing' && $existingResume) {
            // Use existing resume from profile
            $resumePath = $existingResume;
        }

        // Get cover letter
        $coverLetter = $request->input('cover_letter') ?? $application->cover_letter;

        // Final submission with resume and cover letter
        $application->update([
            'application_step' => 'submitted',
            'status' => 'pending',
            'applied_date' => now(),
            'resume' => $resumePath,
            'cover_letter' => $coverLetter
        ]);

        // Create application status history
        $application->statusHistory()->create([
            'status' => 'pending',
            'notes' => 'Application submitted via step-by-step wizard'
        ]);

        // Send notifications to employer (in-app + email)
        try {
            // Create in-app notification in custom notifications table
            \App\Models\Notification::create([
                'user_id' => $job->employer_id,
                'title' => 'New Application Received',
                'message' => $application->user->name . ' has applied for "' . $job->title . '"',
                'type' => 'new_application',
                'data' => [
                    'message' => $application->user->name . ' has applied for "' . $job->title . '"',
                    'type' => 'new_application',
                    'job_application_id' => $application->id,
                    'job_id' => $job->id,
                    'job_title' => $job->title,
                    'applicant_name' => $application->user->name,
                    'applicant_id' => $application->user_id,
                ],
                'action_url' => route('employer.applications.show', $application->id),
                'read_at' => null
            ]);

            // Send email notification
            $job->employer->notify(new NewApplicationReceived($application));

            \Log::info('=== EMPLOYER NOTIFICATION SENT (WIZARD) ===', [
                'employer_id' => $job->employer_id,
                'employer_email' => $job->employer->email,
                'application_id' => $application->id
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send employer notification (wizard): ' . $e->getMessage());
        }

        // Send notifications to all admins (in-app + email)
        try {
            $admins = User::where('role', 'admin')
                ->orWhere('role', 'superadmin')
                ->get();

            // Get company name for notification
            $companyName = 'Unknown Company';
            if ($job->employer) {
                $employerProfile = \App\Models\Employer::where('user_id', $job->employer->id)->first();
                $companyName = $employerProfile->company_name ?? $job->employer->name;
            }

            foreach ($admins as $admin) {
                // Create in-app notification in custom notifications table
                \App\Models\Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'New Job Application Received',
                    'message' => $application->user->name . ' has applied for "' . $job->title . '" at ' . $companyName,
                    'type' => 'admin_new_application',
                    'data' => [
                        'job_application_id' => $application->id,
                        'job_id' => $job->id,
                        'job_title' => $job->title,
                        'applicant_name' => $application->user->name,
                        'applicant_id' => $application->user_id,
                        'company_name' => $companyName,
                        'icon' => 'user-plus',
                        'color' => 'info'
                    ],
                    'action_url' => route('admin.jobs.applicants', $job->id),
                    'read_at' => null
                ]);

                // Send email notification
                $admin->notify(new AdminNewApplicationNotification($application));
            }

            \Log::info('=== ADMIN NOTIFICATIONS SENT (WIZARD) ===', [
                'admins_notified' => $admins->count(),
                'application_id' => $application->id
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send admin notifications (wizard): ' . $e->getMessage());
        }

        return redirect()->route('jobDetail', $job->id)
            ->with('success', 'Application submitted successfully! We will notify you of any updates.');
    }

    /**
     * Apply for a job (legacy method - kept for backward compatibility)
     */
    public function applyJob($id, Request $request)
    {
        \Log::info('=== APPLY JOB METHOD CALLED ===', [
            'job_id' => $id,
            'method' => $request->method(),
            'url' => $request->url()
        ]);

        try {
            \Log::info('Job application attempt', [
                'job_id' => $id,
                'request_data' => $request->except(['resume']), // Don't log file contents
                'has_resume' => $request->hasFile('resume'),
                'is_ajax' => $request->ajax()
            ]);

            if (!Auth::check()) {
                \Log::info('User not authenticated');
                if ($request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Please login to apply for jobs',
                        'redirect' => route('login')
                    ]);
                }
                return redirect()->route('login')->with('error', 'Please login to apply for jobs');
            }

            $user = Auth::user();
            \Log::info('User info', [
                'user_id' => $user->id,
                'role' => $user->role,
                'is_jobseeker' => $user->role === 'jobseeker'
            ]);

            // Check if user is a job seeker
            if ($user->role !== 'jobseeker') {
                \Log::info('User is not a job seeker');
                if ($request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Only job seekers can apply for jobs'
                    ]);
                }
                return back()->with('error', 'Only job seekers can apply for jobs');
            }

            // Check KYC verification status
            if (!$user->isKycVerified()) {
                \Log::info('User KYC not verified');
                if ($request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You must complete KYC verification before applying for jobs. Please verify your identity first.',
                        'redirect' => route('account.kyc.index')
                    ]);
                }
                return back()->with('error', 'You must complete KYC verification before applying for jobs. Please verify your identity first.');
            }

            // Check if user has completed their profile
            if (!$user->jobSeekerProfile) {
                \Log::info('User profile not complete');
                if ($request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Please complete your profile before applying',
                        'redirect' => route('account.myProfile')
                    ]);
                }
                return redirect()->route('account.myProfile')->with('error', 'Please complete your profile before applying');
            }

            $job = Job::findOrFail($id);
            \Log::info('Job info', [
                'job_id' => $job->id,
                'employer_id' => $job->employer_id,
                'status' => $job->status,
                'application_deadline' => $job->application_deadline
            ]);

            // Check if job is still active
            if (!$job->status || ($job->application_deadline && $job->application_deadline < now())) {
                \Log::info('Job is not active');
                if ($request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'This job is no longer accepting applications'
                    ]);
                }
                return back()->with('error', 'This job is no longer accepting applications');
            }

            // Check if user has already applied
            if ($job->hasUserApplied($user->id)) {
                \Log::info('User has already applied');
                if ($request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You have already applied for this job'
                    ]);
                }
                return back()->with('error', 'You have already applied for this job');
            }

            // Validate resume file
            if (!$request->hasFile('resume')) {
                \Log::info('No resume file provided');
                if ($request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Please upload your resume'
                    ]);
                }
                return back()->with('error', 'Please upload your resume');
            }

            $resume = $request->file('resume');

            // Validate file type
            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!in_array($resume->getMimeType(), $allowedTypes)) {
                \Log::info('Invalid resume file type', ['mime_type' => $resume->getMimeType()]);
                if ($request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Please upload a PDF or Word document'
                    ]);
                }
                return back()->with('error', 'Please upload a PDF or Word document');
            }

            // Validate file size (5MB)
            if ($resume->getSize() > 5 * 1024 * 1024) {
                \Log::info('Resume file too large', ['size' => $resume->getSize()]);
                if ($request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Resume file must be less than 5MB'
                    ]);
                }
                return back()->with('error', 'Resume file must be less than 5MB');
            }

            // Handle resume upload
            try {
                $resumePath = $resume->store('resumes', 'public');
                \Log::info('Resume uploaded', ['path' => $resumePath]);
            } catch (\Exception $e) {
                \Log::error('Resume upload failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                if ($request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Error uploading resume. Please try again.'
                    ]);
                }
                return back()->with('error', 'Error uploading resume. Please try again.');
            }

            // Create job application
            $application = new JobApplication();
            $application->user_id = $user->id;
            $application->job_id = $job->id;
            $application->employer_id = $job->employer_id;
            $application->status = 'pending';
            $application->cover_letter = $request->input('cover_letter');
            $application->resume = $resumePath;
            $application->applied_date = now();
            $application->save();

            \Log::info('Application created', [
                'application_id' => $application->id,
                'user_id' => $application->user_id,
                'job_id' => $application->job_id,
                'employer_id' => $application->employer_id
            ]);

            // Create application status history
            $application->statusHistory()->create([
                'status' => 'pending',
                'notes' => 'Application submitted'
            ]);

            // Send notifications to employer (in-app + email)
            \Log::info('=== ABOUT TO SEND NOTIFICATIONS ===', [
                'employer_id' => $job->employer_id,
                'job_id' => $job->id,
                'application_id' => $application->id
            ]);

            try {
                // Create in-app notification in custom notifications table
                \App\Models\Notification::create([
                    'user_id' => $job->employer_id,
                    'title' => 'New Application Received',
                    'message' => $user->name . ' has applied for "' . $job->title . '"',
                    'type' => 'new_application',
                    'data' => [
                        'message' => $user->name . ' has applied for "' . $job->title . '"',
                        'type' => 'new_application',
                        'job_application_id' => $application->id,
                        'job_id' => $job->id,
                        'job_title' => $job->title,
                        'applicant_name' => $user->name,
                        'applicant_id' => $user->id,
                    ],
                    'action_url' => route('employer.applications.show', $application->id),
                    'read_at' => null
                ]);

                // Send email notification
                $job->employer->notify(new NewApplicationReceived($application));

                \Log::info('=== EMPLOYER NOTIFICATION SENT ===', [
                    'employer_id' => $job->employer_id,
                    'employer_email' => $job->employer->email,
                    'application_id' => $application->id
                ]);
            } catch (\Exception $e) {
                \Log::error('=== FAILED TO SEND EMPLOYER NOTIFICATION ===', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // Send notifications to all admins (in-app + email)
            try {
                $admins = User::where('role', 'admin')
                    ->orWhere('role', 'superadmin')
                    ->get();

                // Get company name for notification
                $companyName = 'Unknown Company';
                if ($job->employer) {
                    $employerProfile = \App\Models\Employer::where('user_id', $job->employer->id)->first();
                    $companyName = $employerProfile->company_name ?? $job->employer->name;
                }

                foreach ($admins as $admin) {
                    // Create in-app notification in custom notifications table
                    \App\Models\Notification::create([
                        'user_id' => $admin->id,
                        'title' => 'New Job Application Received',
                        'message' => $user->name . ' has applied for "' . $job->title . '" at ' . $companyName,
                        'type' => 'admin_new_application',
                        'data' => [
                            'job_application_id' => $application->id,
                            'job_id' => $job->id,
                            'job_title' => $job->title,
                            'applicant_name' => $user->name,
                            'applicant_id' => $user->id,
                            'company_name' => $companyName,
                            'icon' => 'user-plus',
                            'color' => 'info'
                        ],
                        'action_url' => route('admin.jobs.applicants', $job->id),
                        'read_at' => null
                    ]);

                    // Send email notification
                    $admin->notify(new AdminNewApplicationNotification($application));
                }

                \Log::info('=== ADMIN NOTIFICATIONS SENT ===', [
                    'admins_notified' => $admins->count(),
                    'application_id' => $application->id
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to send admin notifications: ' . $e->getMessage());
            }

            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Application submitted successfully'
                ]);
            }

            return redirect()->route('jobDetail', $job->id)
                ->with('success', 'Application submitted successfully! We will notify you of any updates.');

        } catch (\Exception $e) {
            \Log::error('Job application error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error submitting application. Please try again.'
                ]);
            }
            return back()->with('error', 'Error submitting application. Please try again.');
        }
    }

    /**
     * Bookmark a job
     */
    public function bookmarkJob($id)
    {
        try {
            \Log::info('Bookmark job attempt', [
                'job_id' => $id,
                'user_id' => Auth::id()
            ]);

            if (!Auth::check()) {
                \Log::info('User not authenticated');
                return response()->json([
                    'status' => false,
                    'message' => 'Please login to bookmark jobs',
                    'redirect' => route('login')
                ]);
            }

            $user = Auth::user();
            if ($user->role !== 'jobseeker') {
                \Log::info('User is not a job seeker');
                return response()->json([
                    'status' => false,
                    'message' => 'Only job seekers can bookmark jobs'
                ]);
            }

            // Check if job exists
            $job = Job::findOrFail($id);

            // Check if job is already bookmarked
            $existingBookmark = BookmarkedJob::where('user_id', $user->id)
                ->where('job_id', $id)
                ->first();

            if ($existingBookmark) {
                \Log::info('Job already bookmarked');
                return response()->json([
                    'status' => false,
                    'message' => 'Job is already bookmarked'
                ]);
            }

            // Bookmark the job
            $bookmarkedJob = new BookmarkedJob();
            $bookmarkedJob->user_id = $user->id;
            $bookmarkedJob->job_id = $id;
            $bookmarkedJob->save();

            \Log::info('Job bookmarked successfully', [
                'bookmarked_job_id' => $bookmarkedJob->id,
                'user_id' => $user->id,
                'job_id' => $id
            ]);

            // Notify the employer
            $this->notifyEmployer($job, $user);

            return response()->json([
                'status' => true,
                'message' => 'Job bookmarked successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Bookmark job error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Notify employer when their job is saved
     */
    private function notifyEmployer($job, $user)
    {
        try {
            // Get employer
            $employer = $job->employer;

            // Create notification data
            $notificationData = [
                'title' => 'Job Saved',
                'message' => "{$user->name} saved your job posting: {$job->title}",
                'type' => 'job_saved',
                'data' => [
                    'job_id' => $job->id,
                    'job_title' => $job->title,
                    'user_id' => $user->id,
                    'user_name' => $user->name
                ],
                'action_url' => route('account.employer.jobs.show', $job->id)
            ];

            // Send notification
            $employer->notify(new JobSavedNotification($notificationData));

        } catch (\Exception $e) {
            \Log::error('Error sending employer notification: ' . $e->getMessage());
        }
    }

    /**
     * Notify job seeker when they save a job
     */
    private function notifyJobSeeker($job, $user)
    {
        try {
            // Create notification data
            $notificationData = [
                'title' => 'Job Saved Successfully',
                'message' => "You have saved the job: {$job->title} at {$job->employer->employerProfile->company_name}",
                'type' => 'job_saved',
                'data' => [
                    'job_id' => $job->id,
                    'job_title' => $job->title,
                    'company_name' => $job->employer->employerProfile->company_name,
                    'company_id' => $job->employer_id
                ],
                'action_url' => route('jobDetail', $job->id)
            ];

            // Send notification
            $user->notify(new JobSavedNotification($notificationData));

        } catch (\Exception $e) {
            \Log::error('Error sending job seeker notification: ' . $e->getMessage());
        }
    }

    /**
     * Remove bookmark from a job
     */
    public function unbookmarkJob($id)
    {
        try {
            \Log::info('Unbookmark job attempt', [
                'job_id' => $id,
                'user_id' => Auth::id()
            ]);

            if (!Auth::check()) {
                \Log::info('User not authenticated');
                return response()->json([
                    'status' => false,
                    'message' => 'Please login to manage bookmarked jobs',
                    'redirect' => route('login')
                ]);
            }

            $user = Auth::user();
            if ($user->role !== 'jobseeker') {
                \Log::info('User is not a job seeker');
                return response()->json([
                    'status' => false,
                    'message' => 'Only job seekers can manage bookmarked jobs'
                ]);
            }

            // Find and delete the bookmarked job
            $bookmarkedJob = BookmarkedJob::where('user_id', $user->id)
                ->where('job_id', $id)
                ->first();

            if (!$bookmarkedJob) {
                \Log::info('Job not found in bookmarked jobs');
                return response()->json([
                    'status' => false,
                    'message' => 'Job is not in your bookmarks'
                ]);
            }

            $bookmarkedJob->delete();

            \Log::info('Job unbookmarked successfully', [
                'user_id' => $user->id,
                'job_id' => $id
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Job removed from bookmarks'
            ]);

        } catch (\Exception $e) {
            \Log::error('Unbookmark job error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error removing job from bookmarks. Please try again.'
            ]);
        }
    }

    /**
     * Show the job creation form
     */
    public function create()
    {
        if (!Auth::check() || Auth::user()->role !== 'employer') {
            return redirect()->route('login')->with('error', 'Only employers can post jobs.');
        }

        return view('front.account.employer.jobs.create');
    }

    /**
     * Store a new job posting
     */
    public function store(Request $request)
    {
        try {
            if (!Auth::check() || Auth::user()->role !== 'employer') {
                return redirect()->route('login')->with('error', 'Only employers can post jobs.');
            }

            $user = Auth::user();

            // Validate the request
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:5000',
                'requirements' => 'required|string|max:3000',
                'benefits' => 'nullable|string|max:2000',
                'location' => 'required|string|max:255',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'job_type_id' => 'required|exists:job_types,id',
                'category_id' => 'required|exists:categories,id',
                'vacancy' => 'required|integer|min:1|max:100',
                'experience_level' => 'required|in:entry,mid,senior,executive',
                'education_level' => 'nullable|in:high_school,vocational,associate,bachelor,master,doctorate',
                'salary_min' => 'nullable|numeric|min:0',
                'salary_max' => 'nullable|numeric|min:0',
                'deadline' => 'nullable|date|after:today',
                'is_remote' => 'boolean',
                'is_featured' => 'boolean',
                'skills' => 'nullable|string'
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
            $job->benefits = $validated['benefits'];
            $job->location = $validated['location'];
            $job->latitude = $validated['latitude'];
            $job->longitude = $validated['longitude'];
            $job->job_type_id = $validated['job_type_id'];
            $job->category_id = $validated['category_id'];
            $job->employer_id = $user->id;
            $job->vacancy = $validated['vacancy'];
            $job->experience_level = $validated['experience_level'];
            $job->education_level = $validated['education_level'];
            $job->salary_min = $validated['salary_min'];
            $job->salary_max = $validated['salary_max'];
            $job->deadline = $validated['deadline'];
            $job->is_remote = $request->boolean('is_remote');
            $job->is_featured = $request->boolean('is_featured');
            $job->status = Job::STATUS_PENDING; // Jobs need approval
            $job->meta_data = [
                'skills' => $skills,
                'created_via' => 'web_form',
                'form_version' => '1.0'
            ];

            $job->save();

            \Log::info('Job created successfully', [
                'job_id' => $job->id,
                'employer_id' => $user->id,
                'title' => $job->title
            ]);

            // Clear autosave data
            session()->forget('job_form_autosave');

            return redirect()->route('employer.jobs.index')
                ->with('success', 'Job posted successfully! It will be reviewed and published soon.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Job creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create job posting. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show form to submit requirements for stage 2 of application
     */
    public function showSubmitRequirements(JobApplication $application)
    {
        // Verify the application belongs to the current user
        if ($application->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action');
        }

        // Check if the application is in the requirements stage
        if ($application->stage !== JobApplication::STAGE_REQUIREMENTS) {
            return redirect()->route('account.myJobApplications')
                ->with('error', 'This application is not in the document submission stage.');
        }

        // Check if stage status is pending (waiting for documents)
        if ($application->stage_status !== JobApplication::STAGE_STATUS_PENDING) {
            return redirect()->route('account.myJobApplications')
                ->with('info', 'Your documents have already been submitted and are being reviewed.');
        }

        $application->load(['job.jobRequirements', 'job.employer.employerProfile']);

        return view('front.account.job.submit-requirements', compact('application'));
    }

    /**
     * Process requirements submission for stage 2
     */
    public function processSubmitRequirements(Request $request, JobApplication $application)
    {
        // Verify the application belongs to the current user
        if ($application->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action');
        }

        // Check if the application is in the requirements stage
        if ($application->stage !== JobApplication::STAGE_REQUIREMENTS) {
            return redirect()->route('account.myJobApplications')
                ->with('error', 'This application is not in the document submission stage.');
        }

        // Check if stage status is pending
        if ($application->stage_status !== JobApplication::STAGE_STATUS_PENDING) {
            return redirect()->route('account.myJobApplications')
                ->with('info', 'Your documents have already been submitted.');
        }

        $application->load(['job.jobRequirements']);

        // Build validation rules based on job requirements
        $rules = [];
        $messages = [];
        $requiredCount = 0;

        foreach ($application->job->jobRequirements as $requirement) {
            $fieldName = 'requirement_' . $requirement->id;

            if ($requirement->is_required) {
                $rules[$fieldName] = 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240';
                $messages[$fieldName . '.required'] = 'The ' . $requirement->name . ' is required.';
                $requiredCount++;
            } else {
                $rules[$fieldName] = 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240';
            }
            $messages[$fieldName . '.mimes'] = 'The ' . $requirement->name . ' must be a PDF, DOC, DOCX, JPG, or PNG file.';
            $messages[$fieldName . '.max'] = 'The ' . $requirement->name . ' must not exceed 10MB.';
        }

        // If no requirements defined, just update the status
        if ($application->job->jobRequirements->isEmpty()) {
            $application->submitted_documents = [];
            $application->save();

            // Create status history
            $application->statusHistory()->create([
                'status' => 'documents_submitted',
                'notes' => 'No documents required - auto-submitted'
            ]);

            // Notify employer
            \App\Models\Notification::create([
                'user_id' => $application->job->employer_id,
                'title' => 'Documents Ready for Review',
                'message' => $application->user->name . ' has completed the document stage for "' . $application->job->title . '".',
                'type' => 'documents_submitted',
                'data' => [
                    'job_application_id' => $application->id,
                    'job_id' => $application->job_id,
                    'applicant_name' => $application->user->name,
                ],
                'action_url' => route('employer.applications.show', $application->id),
                'read_at' => null
            ]);

            return redirect()->route('account.myJobApplications')
                ->with('success', 'Documents submitted successfully! The employer will review them shortly.');
        }

        $request->validate($rules, $messages);

        try {
            $submittedDocuments = [];

            foreach ($application->job->jobRequirements as $requirement) {
                $fieldName = 'requirement_' . $requirement->id;

                if ($request->hasFile($fieldName)) {
                    $file = $request->file($fieldName);

                    // Generate unique filename
                    $filename = 'req_' . $application->id . '_' . $requirement->id . '_' . time() . '.' . $file->getClientOriginalExtension();

                    // Store file
                    $path = $file->storeAs('application_documents/' . $application->id, $filename, 'public');

                    // Store with requirement_id as key for easy lookup
                    $submittedDocuments[$requirement->id] = [
                        'requirement_id' => $requirement->id,
                        'requirement_name' => $requirement->name,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'uploaded_at' => now()->toDateTimeString()
                    ];
                }
            }

            // Update application with submitted documents
            $application->submitted_documents = $submittedDocuments;
            $application->save();

            // Create status history
            $application->statusHistory()->create([
                'status' => 'documents_submitted',
                'notes' => 'Submitted ' . count($submittedDocuments) . ' document(s)'
            ]);

            // Notify employer
            \App\Models\Notification::create([
                'user_id' => $application->job->employer_id,
                'title' => 'Documents Submitted for Review',
                'message' => $application->user->name . ' has submitted the required documents for "' . $application->job->title . '". Please review them.',
                'type' => 'documents_submitted',
                'data' => [
                    'job_application_id' => $application->id,
                    'job_id' => $application->job_id,
                    'applicant_name' => $application->user->name,
                    'documents_count' => count($submittedDocuments),
                ],
                'action_url' => route('employer.applications.documents', $application->id),
                'read_at' => null
            ]);

            \Log::info('Documents submitted for application', [
                'application_id' => $application->id,
                'user_id' => Auth::id(),
                'documents_count' => count($submittedDocuments)
            ]);

            return redirect()->route('account.myJobApplications')
                ->with('success', 'Documents submitted successfully! The employer will review them shortly.');

        } catch (\Exception $e) {
            \Log::error('Error submitting requirements', [
                'error' => $e->getMessage(),
                'application_id' => $application->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to submit documents. Please try again.')
                ->withInput();
        }
    }
}
