<?php
namespace App\Http\Controllers;

use App\Mail\ResetPasswordEmail;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\JobAlert;

class AccountController extends Controller
{
    // This method will show user registration page
    public function registration(){
        return view('front.account.registration');
    }

    // This method will save a user
    public function processRegistration(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required',
            'role' => 'required|in:jobseeker,employer'
        ], [
            'email.unique' => 'The email is already in use.'
        ]);

        if($validator->passes()){
            try {
                // Use database transaction to ensure both user and profile are created
                DB::beginTransaction();

                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->role = $request->role;
                $user->save();

                // Create corresponding profile based on role
                if ($request->role === 'employer') {
                    $user->employerProfile()->create([]);
                } else {
                    $user->jobSeekerProfile()->create([]);
                }

                DB::commit();

                $message = "You have registered successfully. Please login to continue.";
                Session()->flash('success', $message);

                \Log::info('User registered successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role
                ]);

                return response()->json([
                    'status' => true,
                    'message' => $message,
                    'redirect' => route('account.login')
                ]);

            } catch (\Exception $e) {
                DB::rollBack();

                \Log::error('Registration failed', [
                    'email' => $request->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'status' => false,
                    'errors' => ['general' => ['Registration failed. Please try again later.']],
                ]);
            }
        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    // This method will show user login page
    public function login(){
        return view('front.account.login');
    }

    public function authenticate(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required|min:5',
        ]);
        if($validator->passes()){
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->has('remember'))){
                $user = Auth::user();

                // Admin can login from any login page
                // Redirect based on user role
                if ($user->role === 'admin') {
                    return redirect()->route('admin.dashboard');
                } elseif ($user->role === 'employer') {
                    return redirect()->route('employer.dashboard');
                } else {
                    return redirect()->route('account.profile');
                }
            }else{
                return redirect()->route('account.login')
                    ->withInput($request->only('email'))
                    ->with('error','Either email/password is incorrect');
            }
        }else{
            return redirect()->route('account.login')
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }
    }

    public function profile(){
        $id = Auth::user()->id;
        $user = User::where('id', $id)->with('jobSeekerProfile')->first();

        // Calculate profile completion percentage using the same method as dashboard
        $completionPercentage = $this->calculateProfileCompletion($user);

        return view('front.account.profile', [
            'user' => $user,
            'completionPercentage' => $completionPercentage
        ]);
    }

    // update profile function
    public function updateProfile(Request $request)
    {
        try {
            // Log the incoming request
            \Log::info('Profile Update Request Started', [
                'method' => $request->method(),
                'all_data' => $request->all(),
                'has_name' => $request->has('name'),
                'has_email' => $request->has('email'),
                'headers' => $request->headers->all()
            ]);

            $id = Auth::user()->id;
            $user = User::find($id);

            if (!$user) {
                \Log::error('User not found', ['id' => $id]);
                return response()->json([
                    'status' => false,
                    'errors' => ['general' => ['User not found']]
                ]);
            }

            $rules = [
                'name' => 'required|min:2|max:100',
                'email' => 'required|email|unique:users,email,'.$id.',id',
                'mobile' => 'nullable|string|max:20',
                'phone' => 'nullable|string|max:20',
                'designation' => 'nullable|string|max:100'
            ];

            // Add validation rules for job seeker fields
            if ($user->isJobSeeker()) {
                $rules = array_merge($rules, [
                    'skills' => 'nullable|string',
                    'education' => 'nullable|string',
                    'experience_years' => 'nullable|integer|min:0',
                    'bio' => 'nullable|string|max:1000',
                    'location' => 'nullable|string|max:100',
                    'job_title' => 'nullable|string|max:100',
                    'salary' => 'nullable|numeric|min:0',
                    'salary_type' => 'nullable|string|in:Month,Year,Week,Hour',
                    'qualification' => 'nullable|string|max:100',
                    'language' => 'nullable|string|max:50',
                    'categories' => 'nullable|string',
                    // Job Preferences
                    'preferred_categories' => 'nullable|array',
                    'preferred_categories.*' => 'integer|exists:categories,id',
                    'preferred_job_types' => 'nullable|array',
                    'preferred_job_types.*' => 'integer|exists:job_types,id',
                    'experience_level' => 'nullable|string|in:entry,mid,senior,lead',
                    'salary_expectation_min' => 'nullable|numeric|min:0',
                    'salary_expectation_max' => 'nullable|numeric|min:0|gte:salary_expectation_min'
                ]);
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                \Log::warning('Profile Update Validation Failed', [
                    'errors' => $validator->errors()->toArray(),
                    'data' => $request->all()
                ]);

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'errors' => $validator->errors()
                    ]);
                }

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Log the validated data
            \Log::info('Profile Update Validation Passed', [
                'validated_data' => $validator->validated()
            ]);

            // Update user data
            $validatedData = $validator->validated();

            // Separate user fields from jobseeker profile fields
            $userFields = [];
            $jobseekerFields = [];

            // User table fields (only basic authentication and contact fields)
            $userOnlyFields = ['name', 'email', 'phone'];

            foreach ($validatedData as $key => $value) {
                if (in_array($key, $userOnlyFields)) {
                    $userFields[$key] = $value;
                } else {
                    // All other fields go to jobseeker profile
                    $jobseekerFields[$key] = $value;
                }
            }

            // Update user basic fields
            $user->fill($userFields);

            try {
                $user->save();
                
                // Also update jobseeker profile if user is a job seeker
                $jobseekerProfile = $user->jobSeekerProfile ?? $user->jobseeker;

                if ($user->isJobSeeker() && $jobseekerProfile) {
                    $jobseekerData = [];

                    // Map user form fields to jobseeker profile database fields
                    $fieldMapping = [
                        'phone' => 'phone',
                        'mobile' => 'alternate_phone',
                        'bio' => 'professional_summary',
                        'designation' => 'current_job_title',
                        'experience_years' => 'total_experience_years',
                        'location' => 'city',
                        'salary_expectation_min' => 'expected_salary_min',
                        'salary_expectation_max' => 'expected_salary_max',
                    ];

                    // Direct mappings
                    foreach ($fieldMapping as $requestField => $dbField) {
                        if (isset($jobseekerFields[$requestField])) {
                            $jobseekerData[$dbField] = $jobseekerFields[$requestField];
                        }
                    }

                    // Handle skills (convert comma-separated string to array)
                    if (isset($jobseekerFields['skills'])) {
                        $skills = $jobseekerFields['skills'];
                        if (is_string($skills)) {
                            $skills = array_filter(array_map('trim', explode(',', $skills)));
                        }
                        $jobseekerData['skills'] = $skills;
                    } elseif ($request->has('skills') && empty($request->skills)) {
                        // Explicitly set to empty array if field is present but empty
                        $jobseekerData['skills'] = [];
                    }

                    // Handle preferred_categories and preferred_job_types
                    if (isset($jobseekerFields['preferred_categories'])) {
                        $jobseekerData['preferred_categories'] = $jobseekerFields['preferred_categories'];
                    } elseif ($request->has('preferred_categories')) {
                        // Explicitly set to empty array if present but empty
                        $jobseekerData['preferred_categories'] = [];
                    }

                    if (isset($jobseekerFields['preferred_job_types'])) {
                        $jobseekerData['preferred_job_types'] = $jobseekerFields['preferred_job_types'];
                    } elseif ($request->has('preferred_job_types')) {
                        // Explicitly set to empty array if present but empty
                        $jobseekerData['preferred_job_types'] = [];
                    }

                    // Handle experience_level
                    if (isset($jobseekerFields['experience_level'])) {
                        $jobseekerData['experience_level'] = $jobseekerFields['experience_level'];
                    }

                    // Update name fields if provided
                    if (isset($userFields['name'])) {
                        $nameParts = explode(' ', $userFields['name'], 2);
                        $jobseekerData['first_name'] = $nameParts[0] ?? '';
                        $jobseekerData['last_name'] = $nameParts[1] ?? '';
                    }

                    if (!empty($jobseekerData)) {
                        try {
                            $jobseekerProfile->update($jobseekerData);
                            \Log::info('Jobseeker profile updated successfully', [
                                'user_id' => $user->id,
                                'fields_updated' => array_keys($jobseekerData)
                            ]);
                        } catch (\Exception $e) {
                            \Log::error('Jobseeker profile update failed', [
                                'error' => $e->getMessage(),
                                'data' => $jobseekerData,
                                'trace' => $e->getTraceAsString()
                            ]);
                            // Don't throw error, just log it and continue
                        }
                    } else {
                        \Log::info('No jobseeker data to update', ['user_id' => $user->id]);
                    }
                } else {
                    \Log::info('Skipping jobseeker profile update', [
                        'user_id' => $user->id,
                        'is_jobseeker' => $user->isJobSeeker(),
                        'has_profile' => !is_null($jobseekerProfile)
                    ]);
                }
                
                \Log::info('Profile Update Successful', ['user_id' => $user->id]);

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Profile updated successfully'
                    ]);
                }

                return redirect()->route('account.myProfile')->with('success', 'Profile updated successfully');

            } catch (\Exception $e) {
                \Log::error('Profile Save Error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'status' => false,
                    'errors' => ['general' => ['Failed to save profile changes. Please try again.']]
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Profile Update Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'errors' => ['general' => ['An error occurred while updating your profile. Please try again.']]
            ]);
        }
    }

    // /upload/change image from profile
    public function updateProfileImg(Request $request){
        $id = Auth::user()->id;

        $validator = Validator::make($request->all(),[
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB max
        ]);

        if($validator->passes()){
            try {
                $image = $request->file('image');
                $ext = $image->getClientOriginalExtension();
                $imageName = $id.'-'.time().'.'.$ext;

                // Create directories if they don't exist
                $profilePath = public_path('profile_img');
                $thumbPath = public_path('profile_img/thumb');
                
                if (!File::exists($profilePath)) {
                    File::makeDirectory($profilePath, 0777, true);
                }
                if (!File::exists($thumbPath)) {
                    File::makeDirectory($thumbPath, 0777, true);
                }

                // Log directory permissions and existence
                \Log::info('Directory check', [
                    'profile_path_exists' => File::exists($profilePath),
                    'thumb_path_exists' => File::exists($thumbPath),
                    'profile_path_writable' => is_writable($profilePath),
                    'thumb_path_writable' => is_writable($thumbPath)
                ]);

                // Move original image
                $image->move($profilePath, $imageName);
                \Log::info('Original image moved successfully', ['path' => $profilePath.'/'.$imageName]);

                // Create thumbnail using Intervention Image v2
                $sourcePath = $profilePath.'/'.$imageName;
                $img = Image::make($sourcePath);
                $img->fit(150, 150, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($thumbPath.'/'.$imageName);

                // Delete old images if they exist
                $oldImage = Auth::user()->image;
                if ($oldImage) {
                    $oldImagePath = $profilePath.'/'.$oldImage;
                    $oldThumbPath = $thumbPath.'/'.$oldImage;
                    
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }
                    if (File::exists($oldThumbPath)) {
                        File::delete($oldThumbPath);
                    }
                }

                // Update user record
                User::where('id', $id)->update(['image' => $imageName]);

                // Return full URL for the image
                return response()->json([
                    'status' => true,
                    'message' => 'Profile image updated successfully',
                    'image_name' => $imageName,
                    'image_path' => asset('profile_img/thumb/'.$imageName)
                ]);

            } catch (\Exception $e) {
                \Log::error('Image processing error: ' . $e->getMessage(), [
                    'userId' => $id,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'status' => false,
                    'errors' => ['image' => ['Error processing image: ' . $e->getMessage()]]
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    // Remove profile image
    public function removeProfileImage(Request $request) {
        try {
            $user = Auth::user();
            $oldImage = $user->image;

            if ($oldImage) {
                // Define paths
                $profilePath = public_path('profile_img');
                $thumbPath = public_path('profile_img/thumb');
                $oldImagePath = $profilePath.'/'.$oldImage;
                $oldThumbPath = $thumbPath.'/'.$oldImage;

                // Delete old images if they exist
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
                if (File::exists($oldThumbPath)) {
                    File::delete($oldThumbPath);
                }

                // Update user record
                $user->image = null;
                $user->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Profile image removed successfully'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'No profile image to remove'
            ]);

        } catch (\Exception $e) {
            \Log::error('Remove profile image error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to remove profile image. Please try again.'
            ]);
        }
    }

    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('account.login');
    }


    public function createJob(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|min:5|max:200',
                'description' => 'required',
                'requirements' => 'required',
                'job_type' => 'required',
                'location' => 'required|max:100',
                'salary_range' => 'required|max:100',
                'deadline' => 'nullable|date|after:today',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ]);
            }

            $job = new Job();
            $job->employer_id = Auth::user()->id;
            $job->title = $request->title;
            $job->description = $request->description;
            $job->requirements = $request->requirements;
            $job->benefits = $request->benefits;
            $job->job_type_id = $request->job_type;
            $job->location = $request->location;
            $job->salary_range = $request->salary_range;
            $job->deadline = $request->deadline;
            $job->status = !empty($request->status) ? $request->status : 0;
            $job->featured = !empty($request->featured) ? $request->featured : 0;
            $job->save();

            return response()->json([
                'status' => true,
                'message' => 'Job created successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error creating job: ' . $e->getMessage()
            ]);
        }
    }

    public function updateJob(Request $request, $id) {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|min:5|max:200',
                'description' => 'required',
                'requirements' => 'required',
                'job_type' => 'required',
                'location' => 'required|max:100',
                'salary_range' => 'required|max:100',
                'deadline' => 'nullable|date|after:today',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ]);
            }

            $job = Job::where('id', $id)
                      ->where('employer_id', Auth::user()->id)
                      ->firstOrFail();

            $job->title = $request->title;
            $job->description = $request->description;
            $job->requirements = $request->requirements;
            $job->benefits = $request->benefits;
            $job->job_type_id = $request->job_type;
            $job->location = $request->location;
            $job->salary_range = $request->salary_range;
            $job->deadline = $request->deadline;
            $job->status = !empty($request->status) ? $request->status : 0;
            $job->featured = !empty($request->featured) ? $request->featured : 0;
            $job->save();

            return response()->json([
                'status' => true,
                'message' => 'Job updated successfully',
                'redirect' => route('account.job.my-jobs')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error updating job: ' . $e->getMessage()
            ]);
        }
    }

    // Method for job seekers to save jobs they're interested in
    public function saveJobToFavorites(Request $request) {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        // Check if job exists
        $job = Job::find($request->job_id);
        if (!$job) {
            return response()->json([
                'status' => false,
                'errors' => ['job_id' => 'Job not found']
            ]);
        }

        // Check if already saved
        $existingSave = SavedJob::where('user_id', Auth::user()->id)
            ->where('job_id', $request->job_id)
            ->first();

        if ($existingSave) {
            return response()->json([
                'status' => false,
                'errors' => ['general' => 'You have already saved this job']
            ]);
        }

        try {
            // Save the job
            $savedJob = new SavedJob();
            $savedJob->user_id = Auth::user()->id;
            $savedJob->job_id = $request->job_id;
            $savedJob->save();

            Session::flash('success', 'Job saved successfully.');

            return response()->json([
                'status' => true,
                'message' => 'Job saved successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Save job error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'errors' => ['general' => 'There was an error saving the job. Please try again.']
            ]);
        }
    }

    // Show all jobs
    public function myJobs(){
        //metioned the code of the paginator in AppServiceProvider Class otherwise your paginator will not work perfectly
        $jobs = Job::where('employer_id', Auth::user()->id)
            ->with('jobType')
            ->orderBy('created_at','DESC')
            ->paginate(10);
            
        return view('front.account.job.my-jobs',[
            'jobs' => $jobs,
        ]);
    }

    // edit Job page
    public function editJob($id){
        $job = Job::where([
            'employer_id' => Auth::user()->id,
            'id' => $id
        ])->first();

        if($job == null){
            abort(404);
        }

        // Get only active categories and job types, ordered by name
        $categories = Category::where('status', 1)
            ->orderBy('name', 'ASC')
            ->get();
            
        $job_types = JobType::where('status', 1)
            ->orderBy('name', 'ASC')
            ->get();

        return view('front.account.job.edit',[
            'job' => $job,
            'categories' => $categories,
            'job_types' => $job_types
        ]);
    }

    // This method will delete job
    public function deleteJob($jobId)
    {
        try {
            $job = Job::where([
                'id' => $jobId,
                'employer_id' => Auth::user()->id
            ])->firstOrFail();

            $job->delete();

            return response()->json([
                'status' => true,
                'message' => 'Job deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting job'
            ], 500);
        }
    }

    public function myJobApplications(){

        $jobApplications = JobApplication::where('user_id',Auth::user()->id)->with(['job','job.jobType','job.applications'])->orderBy('created_at','DESC')->paginate(10);

        return view('front.account.job.my-job-application',[
            'jobApplications' => $jobApplications,
        ]);
    }

    /**
     * Show detailed application view for jobseeker
     * This includes all employer messages, status history, and interview details
     */
    public function showJobApplication($id)
    {
        $application = JobApplication::where('id', $id)
            ->where('user_id', Auth::user()->id)
            ->with([
                'job',
                'job.jobType',
                'job.company',
                'job.employer.employerProfile',
                'job.jobRequirements',
                'statusHistory' => function($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'statusHistory.updatedByUser'
            ])
            ->firstOrFail();

        return view('front.account.job.application-detail', [
            'application' => $application,
        ]);
    }

    // remove applied jobs
    public function removeJobs(Request $request){

        $jobApplication = JobApplication::where([
                                    'id' => $request->id,
                                    'user_id' => Auth::user()->id,]
                                )->first();
        if($jobApplication == null){
            Session()->flash('error','Job application not found');
            return response()->json([
                'status' => false,
            ]);
        }
        JobApplication::find($request->id)->delete();
        Session()->flash('success','Job application removed successfully.');
        return response()->json([
            'status' => true,
        ]);
    }

    /**
     * Display saved jobs for the authenticated user.
     */
    public function savedJobs()
    {
        // Redirect to new saved jobs route
        return redirect()->route('account.saved-jobs.index');
    }

    /**
     * Remove a job from saved jobs.
     */
    public function removeSavedJob(Request $request)
    {
        $user = auth()->user();
        $job = Job::findOrFail($request->job_id);
        
        $user->savedJobs()->detach($job->id);

        return response()->json([
            'status' => true,
            'message' => 'Job removed from saved jobs.'
        ]);
    }

    public function applyJob(Request $request) {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|integer',
            'cover_letter' => 'required|min:50',
            'resume' => 'required|mimes:pdf,doc,docx|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        // Check if user has already applied
        $existingApplication = JobApplication::where('user_id', Auth::user()->id)
            ->where('job_id', $request->job_id)
            ->first();

        if ($existingApplication) {
            return response()->json([
                'status' => false,
                'errors' => ['general' => 'You have already applied for this job.']
            ]);
        }

        try {
            // Handle resume upload
            $resume = $request->file('resume');
            $resumeName = time() . '_' . Auth::user()->id . '_' . $resume->getClientOriginalName();
            $resume->move(public_path('resumes'), $resumeName);

            // Create job application
            $application = new JobApplication();
            $application->user_id = Auth::user()->id;
            $application->job_id = $request->job_id;
            $application->cover_letter = $request->cover_letter;
            $application->resume = $resumeName;
            $application->status = 'pending';
            $application->save();

            // Send notification to employer
            $job = Job::with('employer')->find($request->job_id);
            
            \Log::info('Job application notification debug', [
                'job_id' => $request->job_id,
                'job_found' => $job ? 'yes' : 'no',
                'job_employer_id' => $job ? $job->employer_id : 'N/A',
                'employer_loaded' => ($job && $job->employer) ? 'yes' : 'no',
                'employer_id' => ($job && $job->employer) ? $job->employer->id : 'N/A',
            ]);
            
            if ($job && $job->employer_id) {
                $notification = \App\Models\Notification::create([
                    'user_id' => $job->employer_id,
                    'title' => 'New Application Received',
                    'message' => Auth::user()->name . ' has applied for "' . $job->title . '"',
                    'type' => 'new_application',
                    'data' => [
                        'message' => Auth::user()->name . ' has applied for "' . $job->title . '"',
                        'type' => 'new_application',
                        'job_application_id' => $application->id,
                        'job_id' => $job->id,
                        'job_title' => $job->title,
                        'applicant_name' => Auth::user()->name,
                        'applicant_id' => Auth::user()->id,
                    ],
                    'action_url' => route('employer.applications.show', $application->id),
                    'read_at' => null
                ]);
                
                \Log::info('Notification created successfully', [
                    'notification_id' => $notification->id,
                    'employer_id' => $job->employer_id,
                    'applicant_name' => Auth::user()->name,
                    'job_title' => $job->title
                ]);
            } else {
                \Log::warning('Could not create notification - job or employer not found', [
                    'job_id' => $request->job_id,
                    'job_exists' => $job ? 'yes' : 'no',
                    'employer_id' => $job ? $job->employer_id : 'N/A'
                ]);
            }

            Session::flash('success', 'Your job application has been submitted successfully.');

            return response()->json([
                'status' => true,
                'message' => 'Application submitted successfully'
            ]);

        } catch (\Exception $e) {
            // Log the error
            \Log::error('Job application error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'errors' => ['general' => 'There was an error submitting your application. Please try again.']
            ]);
        }
    }

    // Forgot passowrd

    public function forgotPassword(){
        return view('front.account.forgot-password');
    }

    public function processForgotPassword(Request $request){

        $validator = Validator::make($request->all(),[
            'email' => 'required|email|exists:users,email',
        ]);

        if($validator->fails()){
            return redirect()->route('account.forgotPassword')->withInput()->withErrors($validator);
        }

        try {
            $token = Str::random(60);

            \DB::table('password_resets')->where('email', $request->email)->delete();

            \DB::table('password_resets')->insert([
              'email' => $request->email,
              'token' => $token,
              'created_at' => now(),
            ]);

            // Send Email here
            $user = User::where('email',$request->email)->first();
            $mailData = [
                'token' => $token,
                'user' => $user,
                'subject' => 'You have requested to change your password.',
            ];
            
            Mail::to($request->email)->send(new ResetPasswordEmail($mailData));

            return redirect()->route('account.forgotPassword')->with('success','Reset password email has been sent to your inbox');
            
        } catch (\Exception $e) {
            \Log::error('Password Reset Email Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('account.forgotPassword')
                ->with('error', 'There was an error sending the password reset email. Please try again later.');
        }
    }

    public function resetPassword($tokenString){

        $token = \DB::table('password_resets')->where('token',$tokenString)->first();
        if($token == null){
            return redirect()->route('account.forgotPassword')->with('error','Invalid token.');
        }

        return view('front.account.reset-password',[
            'tokenString' => $tokenString,
        ]);
    }

    public function processResetPassword(Request $request){

        $token = \DB::table('password_resets')->where('token',$request->token)->first();
        if($token == null){
            return redirect()->route('account.forgotPassword')->with('error','Invalid token.');
        }

        $validator = Validator::make($request->all(),[
            'new_password' => 'required|min:5',
            'confirm_password' => 'required|min:5|same:new_password',
        ]);
        if($validator->fails()){
            return redirect()->route('account.resetPassword',$request->token)->withErrors($validator);
        }

        User::where('email', $token->email)->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('account.login',$request->token)->with('success','You have successfully changed your password.');


    }

    // My Profile page
    public function myProfile() {
        $id = Auth::user()->id;
        $user = User::where('id', $id)->with('jobSeekerProfile')->first();

        // Calculate profile completion percentage
        $completionPercentage = $this->calculateProfileCompletion($user);

        return view('front.account.my-profile', [
            'user' => $user,
            'completionPercentage' => $completionPercentage
        ]);
    }

    // Settings page
    public function settings() {
        $user = Auth::user();
        return view('front.account.settings', [
            'user' => $user
        ]);
    }

    // Update notification preferences
    public function updateNotifications(Request $request) {
        $user = Auth::user();
        $user->notification_preferences = $request->notifications;
        $user->save();

        Session::flash('success', 'Notification preferences updated successfully.');
        return response()->json([
            'status' => true
        ]);
    }

    // Update privacy settings
    public function updatePrivacy(Request $request) {
        $user = Auth::user();
        $user->privacy_settings = $request->privacy;
        $user->save();

        Session::flash('success', 'Privacy settings updated successfully.');
        return response()->json([
            'status' => true
        ]);
    }

    // Handle work experience
    public function addExperience(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'company' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $user = Auth::user();
        $profile = $user->jobSeekerProfile;
        
        // Get existing experiences (already cast as array)
        $experiences = $profile->work_experience ?? [];
        
        // Add new experience entry
        $experiences[] = [
            'title' => $request->title,
            'company' => $request->company,
            'location' => $request->location,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'currently_working' => $request->currently_working ?? 0,
            'description' => $request->description
        ];
        
        $profile->work_experience = $experiences;
        $profile->save();

        Session::flash('success', 'Work experience added successfully.');
        return response()->json([
            'status' => true
        ]);
    }

    public function updateExperience(Request $request) {
        $validator = Validator::make($request->all(), [
            'index' => 'required|integer',
            'title' => 'required|string',
            'company' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $user = Auth::user();
        $profile = $user->jobSeekerProfile;
        
        // Get existing experiences (already cast as array)
        $experiences = $profile->work_experience ?? [];
        
        if (isset($experiences[$request->index])) {
            // Update the experience at the specified index
            $experiences[$request->index] = [
                'title' => $request->title,
                'company' => $request->company,
                'location' => $request->location,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'currently_working' => $request->currently_working ?? 0,
                'description' => $request->description
            ];
            
            $profile->work_experience = $experiences;
            $profile->save();

            Session::flash('success', 'Work experience updated successfully.');
            return response()->json([
                'status' => true
            ]);
        }

        return response()->json([
            'status' => false,
            'errors' => ['index' => ['Invalid experience index']]
        ]);
    }

    public function deleteExperience(Request $request) {
        $validator = Validator::make($request->all(), [
            'index' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $user = Auth::user();
        $profile = $user->jobSeekerProfile;
        
        // Get existing experiences (already cast as array)
        $experiences = $profile->work_experience ?? [];
        
        if (isset($experiences[$request->index])) {
            array_splice($experiences, $request->index, 1);
            
            $profile->work_experience = $experiences;
            $profile->save();

            Session::flash('success', 'Work experience deleted successfully.');
            return response()->json([
                'status' => true
            ]);
        }

        return response()->json([
            'status' => false,
            'errors' => ['index' => ['Invalid experience index']]
        ]);
    }

    // Handle education
    public function addEducation(Request $request) {
        $validator = Validator::make($request->all(), [
            'school' => 'required|string',
            'degree' => 'required|string',
            'field_of_study' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $user = Auth::user();
        $profile = $user->jobSeekerProfile;
        
        // Get existing education (already cast as array)
        $education = $profile->education ?? [];
        
        // Add new education entry
        $education[] = [
            'school' => $request->school,
            'degree' => $request->degree,
            'field_of_study' => $request->field_of_study,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'currently_studying' => $request->currently_studying ?? 0
        ];
        
        $profile->education = $education;
        $profile->save();

        Session::flash('success', 'Education added successfully.');
        return response()->json([
            'status' => true
        ]);
    }

    public function updateEducation(Request $request) {
        $validator = Validator::make($request->all(), [
            'index' => 'required|integer',
            'school' => 'required|string',
            'degree' => 'required|string',
            'field_of_study' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $user = Auth::user();
        $profile = $user->jobSeekerProfile;
        
        // Get existing education (already cast as array)
        $education = $profile->education ?? [];
        
        if (isset($education[$request->index])) {
            $education[$request->index] = [
                'school' => $request->school,
                'degree' => $request->degree,
                'field_of_study' => $request->field_of_study,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'currently_studying' => $request->currently_studying ?? 0
            ];
            
            $profile->education = $education;
            $profile->save();

            Session::flash('success', 'Education updated successfully.');
            return response()->json([
                'status' => true
            ]);
        }

        return response()->json([
            'status' => false,
            'errors' => ['index' => 'Invalid education index']
        ]);
    }

    public function deleteEducation(Request $request) {
        $validator = Validator::make($request->all(), [
            'index' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $user = Auth::user();
        $profile = $user->jobSeekerProfile;
        
        // Get existing education (already cast as array)
        $education = $profile->education ?? [];
        
        if (isset($education[$request->index])) {
            array_splice($education, $request->index, 1);
            
            $profile->education = $education;
            $profile->save();

            Session::flash('success', 'Education deleted successfully.');
            return response()->json([
                'status' => true
            ]);
        }

        return response()->json([
            'status' => false,
            'errors' => ['index' => 'Invalid education index']
        ]);
    }

    // Handle resume upload
    public function uploadResume(Request $request) {
        $validator = Validator::make($request->all(), [
            'resume' => 'required|mimes:pdf,doc,docx|max:5120' // 5MB max
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = Auth::user();
            $profile = $user->jobSeekerProfile;
            
            if (!$profile) {
                return redirect()->back()->with('error', 'Profile not found. Please complete your profile first.');
            }
            
            // Delete old resume if exists
            if ($profile->resume_file) {
                Storage::disk('public')->delete($profile->resume_file);
            }

            // Store new resume
            $resume = $request->file('resume');
            $resumeName = 'resumes/' . time() . '_' . $user->id . '_' . $resume->getClientOriginalName();
            $resume->storeAs('public', $resumeName);

            // Update jobseeker profile's resume_file field
            $profile->resume_file = $resumeName;
            $profile->save();

            return redirect()->back()->with('success', 'Resume uploaded successfully!');

        } catch (\Exception $e) {
            \Log::error('Resume upload error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to upload resume. Please try again.');
        }
    }

    // Handle social links
    public function updateSocialLinks(Request $request) {
        $validator = Validator::make($request->all(), [
            'social_links.linkedin' => 'nullable|url',
            'social_links.github' => 'nullable|url',
            'social_links.portfolio' => 'nullable|url',
            'social_links.facebook' => 'nullable|url',
            'social_links.twitter' => 'nullable|url',
            'social_links.instagram' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ]);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = Auth::user();
            $profile = $user->jobSeekerProfile;

            if (!$profile) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['general' => ['Profile not found']]
                    ]);
                }
                return redirect()->back()->with('error', 'Profile not found.');
            }

            // Map social_links array to individual database fields
            $socialLinks = $request->social_links ?? [];

            $profile->linkedin_url = $socialLinks['linkedin'] ?? null;
            $profile->github_url = $socialLinks['github'] ?? null;
            $profile->portfolio_url = $socialLinks['portfolio'] ?? null;
            $profile->facebook_url = $socialLinks['facebook'] ?? null;
            $profile->twitter_url = $socialLinks['twitter'] ?? null;
            $profile->instagram_url = $socialLinks['instagram'] ?? null;

            $profile->save();

            Session::flash('success', 'Social links updated successfully.');

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Social links updated successfully.'
                ]);
            }

            return redirect()->route('account.myProfile')->with('success', 'Social links updated successfully.');

        } catch (\Exception $e) {
            \Log::error('Social links update error: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => false,
                    'errors' => ['general' => ['Failed to update social links. Please try again.']]
                ]);
            }

            return redirect()->back()->with('error', 'Failed to update social links. Please try again.');
        }
    }

    // Delete account
    public function deleteAccount(Request $request) {
        $user = Auth::user();

        // Optional: Store reason for deletion
        if ($request->has('delete_reason')) {
            // You could store this in a separate table if needed
            \Log::info('Account deletion reason for user ' . $user->id . ': ' . $request->delete_reason);
        }

        // Delete user's data
        $user->jobSeekerProfile()->delete();
        $user->jobApplications()->delete();
        $user->savedJobs()->delete();
        
        // Finally delete the user
        $user->delete();

        Auth::logout();
        Session::flash('success', 'Your account has been permanently deleted.');
        
        return response()->json([
            'status' => true,
            'redirect' => route('home')
        ]);
    }

    // Deactivate account
    public function deactivateAccount(Request $request) {
        $user = Auth::user();

        try {
            // Deactivate the account
            $user->status = 0;
            $user->deactivated_at = now();
            $user->save();

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')->with('success', 'Your account has been deactivated. You can reactivate it by logging in again.');
        } catch (\Exception $e) {
            \Log::error('Error deactivating account: ' . $e->getMessage());
            return redirect()->back()->with('error', 'There was an error deactivating your account. Please try again.');
        }
    }

    // Helper method to calculate profile completion
    private function calculateProfileCompletion($user) {
        if (!$user->isJobSeeker()) {
            return 0;
        }

        $profile = $user->jobSeekerProfile;

        // Define fields with their weights - total should be 100
        $checks = [
            // Basic Information (25 points)
            ['field' => 'name', 'model' => 'user', 'weight' => 5],
            ['field' => 'email', 'model' => 'user', 'weight' => 5],
            ['field' => 'phone', 'model' => 'user', 'weight' => 5],
            ['field' => 'image', 'model' => 'user', 'weight' => 5],
            ['field' => 'current_job_title', 'model' => 'profile', 'weight' => 5],

            // Professional Information (35 points)
            ['field' => 'professional_summary', 'model' => 'profile', 'weight' => 10, 'min_length' => 100],
            ['field' => 'skills', 'model' => 'profile', 'weight' => 10, 'type' => 'array', 'min_count' => 3],
            ['field' => 'work_experience', 'model' => 'profile', 'weight' => 8, 'type' => 'array', 'min_count' => 1],
            ['field' => 'education', 'model' => 'profile', 'weight' => 7, 'type' => 'array', 'min_count' => 1],

            // Resume (10 points)
            ['field' => 'resume_file', 'model' => 'profile', 'weight' => 10],

            // Job Preferences (15 points)
            ['field' => 'preferred_categories', 'model' => 'profile', 'weight' => 5, 'type' => 'array', 'min_count' => 1],
            ['field' => 'preferred_job_types', 'model' => 'profile', 'weight' => 5, 'type' => 'array', 'min_count' => 1],
            ['field' => 'experience_level', 'model' => 'profile', 'weight' => 5],

            // KYC Verification (15 points)
            ['field' => 'kyc_status', 'model' => 'user', 'weight' => 15, 'value' => 'verified'],
        ];

        $completedWeight = 0;

        foreach ($checks as $check) {
            $model = $check['model'] === 'user' ? $user : $profile;

            if (!$model) {
                continue;
            }

            $field = $check['field'];
            $value = $model->$field ?? null;

            // Check specific value match
            if (isset($check['value'])) {
                if ($value === $check['value']) {
                    $completedWeight += $check['weight'];
                }
                continue;
            }

            // Check array type with minimum count
            if (isset($check['type']) && $check['type'] === 'array') {
                if (is_array($value) && count($value) >= ($check['min_count'] ?? 1)) {
                    $completedWeight += $check['weight'];
                }
                continue;
            }

            // Check minimum length for strings
            if (isset($check['min_length'])) {
                if (!empty($value) && strlen($value) >= $check['min_length']) {
                    $completedWeight += $check['weight'];
                }
                continue;
            }

            // Default: check if value exists
            if (!empty($value)) {
                $completedWeight += $check['weight'];
            }
        }

        return min(100, $completedWeight); // Cap at 100%
    }

    public function dashboard()
    {
        \Log::info('Dashboard accessed', [
            'auth_check' => Auth::check(),
            'session_id' => session()->getId(),
            'user_id' => Auth::id()
        ]);

        $user = auth()->user();

        // Get statistics
        $stats = [
            'applications' => $user->jobApplications()->count(),
            'saved_jobs' => $user->savedJobs()->count(),
            'profile_views' => ($user->jobSeekerProfile && isset($user->jobSeekerProfile->profile_views)) ? $user->jobSeekerProfile->profile_views : 0
        ];

        // Get recent applications (last 5)
        $recentApplications = $user->jobApplications()
            ->with('job')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get recommended jobs based on user's profile
        $recommendedJobs = $this->getRecommendedJobs($user);

        return view('front.account.dashboard', [
            'stats' => $stats,
            'completionPercentage' => $this->calculateProfileCompletion($user),
            'recentApplications' => $recentApplications,
            'recommendedJobs' => $recommendedJobs,
            'user' => $user
        ]);
    }

    protected function getRecommendedJobs(User $user)
    {
        // Get user's skills and preferences with null safety
        $userSkills = ($user->jobSeekerProfile && isset($user->jobSeekerProfile->skills)) ? $user->jobSeekerProfile->skills : [];

        // Ensure skills is an array
        if (is_string($userSkills)) {
            $userSkills = !empty($userSkills) ? array_map('trim', explode(',', $userSkills)) : [];
        }

        $userCategory = ($user->jobSeekerProfile && isset($user->jobSeekerProfile->preferred_category_id)) ? $user->jobSeekerProfile->preferred_category_id : null;
        $userJobType = ($user->jobSeekerProfile && isset($user->jobSeekerProfile->preferred_job_type_id)) ? $user->jobSeekerProfile->preferred_job_type_id : null;

        // Query jobs based on user preferences
        $query = Job::where('status', 1)
            ->where(function($q) {
                $q->whereNull('deadline')
                  ->orWhere('deadline', '>=', now());
            });

        // Filter by category if set
        if ($userCategory) {
            $query->where('category_id', $userCategory);
        }

        // Filter by job type if set
        if ($userJobType) {
            $query->where('job_type_id', $userJobType);
        }

        // Get jobs that match user's skills
        // Note: Skills matching disabled - required_skills column doesn't exist
        // TODO: Implement skills matching using meta_data or requirements column
        
        return $query->with('jobType')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();
    }

    /**
     * Store a flash message in the session
     */
    public function storeMessage(Request $request)
    {
        $request->validate([
            'type' => 'required|in:success,error,info,warning',
            'message' => 'required|string'
        ]);

        session()->flash($request->type, $request->message);

        return response()->json(['status' => true]);
    }

    public function resumes()
    {
        return view('front.account.resumes.index');
    }

    public function jobAlerts()
    {
        $user = Auth::user();
        
        // Get all categories and job types for the form
        $categories = Category::where('status', 1)->get();
        $jobTypes = JobType::where('status', 1)->get();
        
        // Get user's current alert preferences
        $alertPreferences = JobAlert::where('user_id', $user->id)->first();
        
        // Get active alerts
        $activeAlerts = JobAlert::where('user_id', $user->id)
            ->with(['categories', 'jobTypes'])
            ->get()
            ->map(function($alert) {
                return (object)[
                    'id' => $alert->id,
                    'categories' => $alert->categories->pluck('name')->join(', '),
                    'job_types' => $alert->jobTypes->pluck('name')->join(', '),
                    'location' => $alert->location,
                    'frequency' => $alert->frequency
                ];
            });

        return view('front.account.job-alerts', [
            'categories' => $categories,
            'jobTypes' => $jobTypes,
            'selectedCategories' => $alertPreferences ? $alertPreferences->categories->pluck('id')->toArray() : [],
            'selectedJobTypes' => $alertPreferences ? $alertPreferences->jobTypes->pluck('id')->toArray() : [],
            'location' => $alertPreferences?->location,
            'salaryRange' => $alertPreferences?->salary_range,
            'frequency' => $alertPreferences?->frequency ?? 'daily',
            'emailNotifications' => $alertPreferences?->email_notifications ?? true,
            'activeAlerts' => $activeAlerts
        ]);
    }

    public function updateJobAlerts(Request $request)
    {
        $request->validate([
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'job_types' => 'array',
            'job_types.*' => 'exists:job_types,id',
            'location' => 'nullable|string|max:255',
            'salary_range' => 'nullable|numeric|min:0',
            'frequency' => 'required|in:daily,weekly,instant',
            'email_notifications' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $alert = JobAlert::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'location' => $request->location,
                    'salary_range' => $request->salary_range,
                    'frequency' => $request->frequency,
                    'email_notifications' => $request->boolean('email_notifications')
                ]
            );

            // Sync categories and job types
            $alert->categories()->sync($request->categories ?? []);
            $alert->jobTypes()->sync($request->job_types ?? []);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Job alert preferences updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating job alerts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating job alert preferences'
            ], 500);
        }
    }

    public function deleteJobAlert($id)
    {
        try {
            $alert = JobAlert::where('user_id', Auth::id())
                ->findOrFail($id);
            
            $alert->delete();

            return response()->json([
                'success' => true,
                'message' => 'Job alert deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting job alert: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting job alert'
            ], 500);
        }
    }

    public function deleteProfile()
    {
        return view('front.account.delete-profile');
    }

    public function changePassword(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('front.account.change-password');
        }

        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status' => false,
                'errors' => ['old_password' => ['Current password is incorrect']]
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password has been changed successfully'
        ]);
    }

    public function updatePassword(Request $request)
    {
        // Accept both old_password and current_password for compatibility
        $oldPasswordField = $request->has('current_password') ? 'current_password' : 'old_password';
        
        $validator = Validator::make($request->all(), [
            $oldPasswordField => 'required',
            'new_password' => 'required|min:8|confirmed',
        ], [
            $oldPasswordField . '.required' => 'Current password is required',
            'new_password.required' => 'New password is required',
            'new_password.min' => 'New password must be at least 8 characters',
            'new_password.confirmed' => 'Password confirmation does not match',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ]);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();

        if (!Hash::check($request->$oldPasswordField, $user->password)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'errors' => ['current_password' => ['Current password is incorrect']]
                ]);
            }
            return redirect()->back()
                ->with('error', 'Current password is incorrect');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Password updated successfully'
            ]);
        }

        return redirect()->back()
            ->with('success', 'Password updated successfully');
    }

    // Notification methods
    public function notifications()
    {
        $notifications = Auth::user()->notifications()->paginate(20);
        
        return view('front.account.jobseeker.notifications', [
            'notifications' => $notifications
        ]);
    }

    public function markNotificationAsRead($id)
    {
        try {
            $notification = Auth::user()->notifications()->findOrFail($id);
            $notification->markAsRead();
            
            \Log::info('Notification marked as read', [
                'notification_id' => $id,
                'user_id' => Auth::id(),
                'read_at' => $notification->read_at
            ]);
            
            return response()->json([
                'status' => true,
                'message' => 'Notification marked as read',
                'unread_count' => Auth::user()->unreadNotifications->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to mark notification as read', [
                'notification_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    public function markAllNotificationsAsRead()
    {
        try {
            $user = Auth::user();
            $count = $user->unreadNotifications->count();
            
            // Mark each unread notification as read
            foreach ($user->unreadNotifications as $notification) {
                $notification->markAsRead();
            }
            
            \Log::info('All notifications marked as read', [
                'user_id' => Auth::id(),
                'count' => $count
            ]);
            
            return response()->json([
                'status' => true,
                'message' => 'All notifications marked as read',
                'marked_count' => $count
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to mark all notifications as read', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'Failed to mark all notifications as read'
            ], 500);
        }
    }

}
