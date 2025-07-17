<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Expr\FuncCall;

class JobController extends Controller
{
    public function index(){

        $jobs = Job::orderBy('created_at','DESC')->with('employer','applications')->paginate(10);
        return view('admin.jobs.list',[
            'jobs' => $jobs,
        ]);
    }

    public function create(){
        // Get only active categories and job types, ordered by name
        $categories = Category::where('status', 1)
            ->orderBy('name', 'ASC')
            ->get();
            
        $job_types = JobType::where('status', 1)
            ->orderBy('name', 'ASC')
            ->get();
            
        return view('admin.jobs.create',[
            'categories' => $categories,
            'job_types' => $job_types,
        ]);
    }

    public function save(Request $request){
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

        $validator = Validator::make($request->all(),$data);
        if($validator->passes()){

            $job = new Job();
            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->jobType;
            $job->employer_id = Auth::user()->id;
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

            Session()->flash('success','Job created successfully.');
            return response()->json([
                'status' => true,
                'errors' => [],
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit($id){
        $job = Job::findOrFail($id);
        
        // Get only active categories and job types, ordered by name
        $categories = Category::where('status', 1)
            ->orderBy('name', 'ASC')
            ->get();
            
        $job_types = JobType::where('status', 1)
            ->orderBy('name', 'ASC')
            ->get();
            
        return view("admin.jobs.edit",[
            'job' => $job,
            'categories' => $categories,
            'job_types' => $job_types,
        ]);
    }


    public function update(Request $request, $jobId){

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

        $validator = Validator::make($request->all(),$data);
        if($validator->passes()){

            $job = Job::find($jobId);
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

            Session()->flash('success','Job updated successfully.');
            return response()->json([
                'status' => true,
                'errors' => [],
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }

    }

    public function show(Job $job)
    {
        return view('admin.jobs.show', compact('job'));
    }

    public function approve(Job $job)
    {
        try {
            $job->update([
                'status' => 'active',
                'approved_at' => now()
            ]);

            // You could add notification to employer here
            // Notification::send($job->employer->user, new JobApprovedNotification($job));

            return redirect()->back()->with('success', 'Job has been approved successfully.');
        } catch (\Exception $e) {
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
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now()
            ]);

            // You could add notification to employer here
            // Notification::send($job->employer->user, new JobRejectedNotification($job));

            return redirect()->back()->with('success', 'Job has been rejected successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to reject job. Please try again.');
        }
    }

    public function destroy(Job $job)
    {
        try {
            // Delete associated applications first
            $job->applications()->delete();
            
            // Delete the job
            $job->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting job'], 500);
        }
    }

    public function pending()
    {
        $jobs = Job::where('status', 'pending')
                   ->orderBy('created_at', 'DESC')
                   ->with('employer', 'applications')
                   ->paginate(10);

        return view('admin.jobs.pending', [
            'jobs' => $jobs,
        ]);
    }

    public function updateStatus(Request $request, Job $job)
    {
        $validStatuses = ['pending', 'approved', 'rejected'];
        
        $request->validate([
            'status' => 'required|in:' . implode(',', $validStatuses)
        ]);

        $job->status = $request->status;
        $job->save();

        return response()->json(['success' => true]);
    }
}
