<?php

namespace App\Modules\JobSeeker\Http\Controllers;

use App\Mail\JobNotificationEmail;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Services\LocationService;
use App\Notifications\JobSavedNotification;
use App\Models\JobView;

class JobsController extends Controller
{
    //This method will show jobs page
    public function index(Request $request){
        $jobs = Job::where('status', Job::STATUS_APPROVED)
                   ->where(function($query) use ($request) {
                       if(!empty($request->keyword)) {
                           $query->where('title', 'like', '%'.$request->keyword.'%')
                                ->orWhere('description', 'like', '%'.$request->keyword.'%')
                                ->orWhere('requirements', 'like', '%'.$request->keyword.'%');
                       }

                       if(!empty($request->location)) {
                           $query->where(function($q) use ($request) {
                               $q->where('location_name', 'like', '%'.$request->location.'%')
                                 ->orWhere('location', 'like', '%'.$request->location.'%')
                                 ->orWhere('location_address', 'like', '%'.$request->location.'%');
                           });
                       }

                       if(!empty($request->jobType)) {
                           $query->whereHas('jobType', function($q) use ($request) {
                               $q->where('name', $request->jobType);
                           });
                       }
                   })
                   ->with(['jobType', 'employer.employerProfile'])
                   ->orderBy('created_at', 'DESC')
                   ->paginate(10);

        $jobTypes = JobType::where('status', 1)->get();

        // Get Digos City locations
        $digosLocations = [
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

        return view('front.modern-jobs', [
            'jobs' => $jobs,
            'jobTypes' => $jobTypes,
            'locations' => $digosLocations
        ]);
    }

    // This method will show job detail page
    public function jobDetail($id){
        $job = Job::with(['jobType', 'employer.employerProfile', 'applications'])
                  ->findOrFail($id);

        // Record job view
        JobView::recordView($job, request());

        $count = 0;
        if(Auth::check()) {
            $count = SavedJob::where([
                'user_id' => Auth::user()->id,
                'job_id' => $id
            ])->count();
        }

        // Get related jobs
        $relatedJobs = Job::where('status', Job::STATUS_APPROVED)
                         ->where('id', '!=', $id)
                         ->where(function($query) use ($job) {
                             $query->where('location', 'like', '%'.explode(',', $job->location)[0].'%')
                                  ->orWhere('job_type_id', $job->job_type_id);
                         })
                         ->with(['jobType', 'employer.employerProfile'])
                         ->take(3)
                         ->get();

        return view('front.modern-job-detail', [
            'job' => $job,
            'count' => $count,
            'relatedJobs' => $relatedJobs
        ]);
    }

    /**
     * Apply for a job
     */
    public function applyJob($id, Request $request)
    {
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
                        'redirect' => route('account.login')
                    ]);
                }
                return redirect()->route('account.login')->with('error', 'Please login to apply for jobs');
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

            // Check if user has completed their profile
            if (!$user->jobSeekerProfile) {
                \Log::info('User profile not complete');
                if ($request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Please complete your profile before applying',
                        'redirect' => route('account.profile')
                    ]);
                }
                return redirect()->route('account.profile')->with('error', 'Please complete your profile before applying');
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

            // Send notification to employer
            try {
                Mail::to($job->employer->email)->send(new JobNotificationEmail([
                    'employer_name' => $job->employer->name,
                    'job_title' => $job->title,
                    'applicant_name' => $user->name,
                    'applicant_email' => $user->email
                ]));
                \Log::info('Notification email sent to employer');
            } catch (\Exception $e) {
                // Log email error but don't stop the process
                \Log::error('Failed to send job application notification: ' . $e->getMessage());
            }

            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Application submitted successfully'
                ]);
            }

            return redirect()->route('jobs.detail', $job->id)
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
     * Save a job
     */
    public function saveJob($id)
    {
        try {
            \Log::info('Save job attempt', [
                'job_id' => $id,
                'user_id' => Auth::id()
            ]);

            if (!Auth::check()) {
                \Log::info('User not authenticated');
                return response()->json([
                    'status' => false,
                    'message' => 'Please login to save jobs',
                    'redirect' => route('account.login')
                ]);
            }

            $user = Auth::user();
            if ($user->role !== 'jobseeker') {
                \Log::info('User is not a job seeker');
                return response()->json([
                    'status' => false,
                    'message' => 'Only job seekers can save jobs'
                ]);
            }

            // Check if job exists
            $job = Job::findOrFail($id);

            // Check if job is already saved
            $existingSave = SavedJob::where('user_id', $user->id)
                                  ->where('job_id', $id)
                                  ->first();

            if ($existingSave) {
                \Log::info('Job already saved');
                return response()->json([
                    'status' => false,
                    'message' => 'Job is already saved'
                ]);
            }

            // Save the job
            $savedJob = new SavedJob();
            $savedJob->user_id = $user->id;
            $savedJob->job_id = $id;
            $savedJob->save();

            \Log::info('Job saved successfully', [
                'saved_job_id' => $savedJob->id,
                'user_id' => $user->id,
                'job_id' => $id
            ]);

            // Notify the employer
            $this->notifyEmployer($job, $user);

            return response()->json([
                'status' => true,
                'message' => 'Job saved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Save job error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error saving job. Please try again.'
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
     * Unsave a job
     */
    public function unsaveJob($id)
    {
        try {
            \Log::info('Unsave job attempt', [
                'job_id' => $id,
                'user_id' => Auth::id()
            ]);

            if (!Auth::check()) {
                \Log::info('User not authenticated');
                return response()->json([
                    'status' => false,
                    'message' => 'Please login to manage saved jobs',
                    'redirect' => route('account.login')
                ]);
            }

            $user = Auth::user();
            if ($user->role !== 'jobseeker') {
                \Log::info('User is not a job seeker');
                return response()->json([
                    'status' => false,
                    'message' => 'Only job seekers can manage saved jobs'
                ]);
            }

            // Find and delete the saved job
            $savedJob = SavedJob::where('user_id', $user->id)
                              ->where('job_id', $id)
                              ->first();

            if (!$savedJob) {
                \Log::info('Job not found in saved jobs');
                return response()->json([
                    'status' => false,
                    'message' => 'Job is not in your saved jobs'
                ]);
            }

            $savedJob->delete();

            \Log::info('Job unsaved successfully', [
                'user_id' => $user->id,
                'job_id' => $id
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Job removed from saved jobs'
            ]);

        } catch (\Exception $e) {
            \Log::error('Unsave job error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error removing job from saved jobs. Please try again.'
            ]);
        }
    }
}
