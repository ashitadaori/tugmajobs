<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\ApplicationStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = JobApplication::orderBy('applied_date', 'DESC')
            ->with(['job', 'user', 'employer', 'job.jobType']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $jobApplications = $query->paginate(10);

        return view('admin.job-applications.list', [
            'jobApplications' => $jobApplications
        ]);
    }

    public function show(JobApplication $application)
    {
        $application->load(['job', 'user', 'employer', 'statusHistory', 'job.jobType']);
        
        return view('admin.job-applications.show', [
            'application' => $application
        ]);
    }

    public function destroy(Request $request)
    {
        $jobApplication = JobApplication::find($request->job_id);

        if (!$jobApplication) {
            Session::flash('error', 'Job application not found');
            return response()->json([
                'status' => false,
                'message' => 'Job application not found'
            ]);
        }

        try {
            // Delete the resume file if it exists
            if ($jobApplication->resume && Storage::exists('public/resumes/' . $jobApplication->resume)) {
                Storage::delete('public/resumes/' . $jobApplication->resume);
            }

            // Delete the application and its related records
            $jobApplication->statusHistory()->delete();
            $jobApplication->delete();

            Session::flash('success', 'Job application deleted successfully');
            return response()->json([
                'status' => true,
                'message' => 'Job application deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting job application: ' . $e->getMessage());
            Session::flash('error', 'Error deleting job application');
            return response()->json([
                'status' => false,
                'message' => 'Error deleting job application'
            ]);
        }
    }

    public function updateStatus(Request $request, JobApplication $application)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,reviewing,approved,rejected'
            ]);

            $oldStatus = $application->status;
            $newStatus = $request->status;

            // Update application status
            $application->status = $newStatus;
            $application->save();

            // Create status history entry
            $application->statusHistory()->create([
                'status' => $newStatus,
                'notes' => "Status changed from " . ucfirst($oldStatus) . " to " . ucfirst($newStatus)
            ]);

            // TODO: Send notification to job seeker about status change
            // You can implement this using Laravel's notification system

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating application status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating status'
            ], 500);
        }
    }
}
