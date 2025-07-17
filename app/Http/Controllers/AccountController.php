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
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
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
        ]);

        if($validator->passes()){
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

            // Log the user in
            Auth::login($user);

            $message = "You have registered successfully.";
            Session()->flash('success',$message);

            // Return JSON response with appropriate redirect based on role
            return response()->json([
                'status' => true,
                'message' => $message,
                'redirect' => $request->role === 'employer' 
                    ? route('employer.dashboard')
                    : route('account.login')
            ]);
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
                return redirect()->route('account.myProfile');
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

        // Calculate profile completion percentage for job seekers
        $completionPercentage = 0;
        if ($user->isJobSeeker()) {
            $fields = [
                'name' => 5,
                'email' => 5,
                'mobile' => 10,
                'designation' => 10,
                'image' => 10,
                'skills' => 15,
                'education' => 15,
                'experience_years' => 10,
                'bio' => 10,
                'jobSeekerProfile.is_kyc_verified' => 10
            ];

            foreach ($fields as $field => $weight) {
                if (str_contains($field, '.')) {
                    // Handle nested relationship fields
                    [$relation, $relationField] = explode('.', $field);
                    if ($user->$relation && $user->$relation->$relationField) {
                        $completionPercentage += $weight;
                    }
                } else {
                    // Handle direct user fields
                    if ($field === 'skills' || $field === 'education') {
                        if (!empty($user->$field) && is_array($user->$field)) {
                            $completionPercentage += $weight;
                        }
                    } else {
                        if (!empty($user->$field)) {
                            $completionPercentage += $weight;
                        }
                    }
                }
            }
        }

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
                'name' => 'required|min:5|max:20',
                'email' => 'required|email|unique:users,email,'.$id.',id',
                'mobile' => 'nullable|string|max:15',
                'designation' => 'nullable|string|max:50'
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
                    'categories' => 'nullable|string'
                ]);
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                \Log::warning('Profile Update Validation Failed', [
                    'errors' => $validator->errors()->toArray(),
                    'data' => $request->all()
                ]);

                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ]);
            }

            // Log the validated data
            \Log::info('Profile Update Validation Passed', [
                'validated_data' => $validator->validated()
            ]);

            // Update user data
            $user->fill($validator->validated());

            try {
                $user->save();
                \Log::info('Profile Update Successful', ['user_id' => $user->id]);

                if ($request->wantsJson()) {
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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
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

                try {
                    // Move original image
                    $image->move($profilePath, $imageName);
                    \Log::info('Original image moved successfully', ['path' => $profilePath.'/'.$imageName]);

                // Create thumbnail using Intervention Image
                $manager = new ImageManager(new Driver());
                $img = $manager->read($profilePath.'/'.$imageName);
                $img->scale(width: 150);
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
                    'errors' => ['image' => 'Error processing image: ' . $e->getMessage()]
                ]);
            }

            } catch (\Exception $e) {
                \Log::error('Image processing error: ' . $e->getMessage(), [
                    'userId' => $id,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'status' => false,
                    'errors' => ['image' => 'Error processing image: ' . $e->getMessage()]
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
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
        $user = auth()->user();
        $savedJobs = $user->savedJobs()
            ->with(['jobType'])
            ->paginate(10);

        return view('front.account.job.saved-jobs', [
            'savedJobs' => $savedJobs
        ]);
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

        // Create JobSeekerProfile if it doesn't exist for job seekers
        if ($user->isJobSeeker() && !$user->jobSeekerProfile) {
            $user->jobSeekerProfile()->create([]);
            $user->load('jobSeekerProfile'); // Reload the relationship
        }

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
        
        $experiences = json_decode($profile->work_experience ?? '[]', true);
        $experiences[] = $request->all();
        
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
        
        $experiences = json_decode($profile->work_experience ?? '[]', true);
        if (isset($experiences[$request->index])) {
            $experiences[$request->index] = $request->except('index');
            
            $profile->work_experience = $experiences;
            $profile->save();

            Session::flash('success', 'Work experience updated successfully.');
            return response()->json([
                'status' => true
            ]);
        }

        return response()->json([
            'status' => false,
            'errors' => ['index' => 'Invalid experience index']
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
        
        $experiences = json_decode($profile->work_experience ?? '[]', true);
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
            'errors' => ['index' => 'Invalid experience index']
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
        
        $education = json_decode($profile->education ?? '[]', true);
        $education[] = $request->all();
        
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
        
        $education = json_decode($profile->education ?? '[]', true);
        if (isset($education[$request->index])) {
            $education[$request->index] = $request->except('index');
            
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
        
        $education = json_decode($profile->education ?? '[]', true);
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
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        try {
            $user = Auth::user();
            $profile = $user->jobSeekerProfile;

            // Delete old resume if exists
            if ($profile->resume_file) {
                Storage::delete('public/resumes/' . $profile->resume_file);
            }

            // Store new resume
            $resume = $request->file('resume');
            $resumeName = time() . '_' . $user->id . '_' . $resume->getClientOriginalName();
            $resume->storeAs('public/resumes', $resumeName);

            $profile->resume_file = $resumeName;
            $profile->save();

            Session::flash('success', 'Resume uploaded successfully.');
            return response()->json([
                'status' => true
            ]);

        } catch (\Exception $e) {
            \Log::error('Resume upload error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'errors' => ['resume' => 'Failed to upload resume. Please try again.']
            ]);
        }
    }

    // Handle social links
    public function updateSocialLinks(Request $request) {
        $validator = Validator::make($request->all(), [
            'social_links.linkedin' => 'nullable|url',
            'social_links.github' => 'nullable|url',
            'social_links.portfolio' => 'nullable|url',
            'social_links.other' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $user = Auth::user();
        $profile = $user->jobSeekerProfile;
        
        $profile->social_links = $request->social_links;
        $profile->save();

        Session::flash('success', 'Social links updated successfully.');
        return response()->json([
            'status' => true
        ]);
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

    // Helper method to calculate profile completion
    private function calculateProfileCompletion($user) {
        if (!$user->isJobSeeker()) {
            return 0;
        }

        $profile = $user->jobSeekerProfile;
        $totalFields = 8; // Total number of important profile fields
        $completedFields = 0;

        // Basic info (30%)
        if (!empty($user->name)) $completedFields++;
        if (!empty($user->email)) $completedFields++;
        if (!empty($user->phone)) $completedFields++;

        // Professional info (70%)
        if (!empty($user->designation)) $completedFields++;
        if (!empty($user->bio)) $completedFields++;
        if (!empty($profile->work_experience)) $completedFields++;
        if (!empty($profile->education)) $completedFields++;
        if (!empty($profile->resume_file)) $completedFields++;

        return round(($completedFields / $totalFields) * 100);
    }

    public function dashboard()
    {
        $user = auth()->user();
        
        // Get statistics
        $stats = [
            'applications' => $user->jobApplications()->count(),
            'saved_jobs' => $user->savedJobs()->count(),
            'profile_views' => $user->jobSeekerProfile->profile_views ?? 0
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
        // Get user's skills and preferences
        $userSkills = $user->jobSeekerProfile->skills ?? [];
        $userCategory = $user->jobSeekerProfile->preferred_category_id;
        $userJobType = $user->jobSeekerProfile->preferred_job_type_id;

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
        if (!empty($userSkills)) {
            $query->where(function($q) use ($userSkills) {
                foreach ($userSkills as $skill) {
                    $q->orWhere('required_skills', 'like', '%' . $skill . '%');
                }
            });
        }

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
                'errors' => ['old_password' => 'Old password is incorrect']
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
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return redirect()->back()
                ->with('error', 'Current password is incorrect');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()
            ->with('success', 'Password updated successfully');
    }

}
