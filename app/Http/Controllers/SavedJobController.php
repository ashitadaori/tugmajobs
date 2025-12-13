<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\SavedJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SavedJobController extends Controller
{
    /**
     * Display saved jobs for the authenticated user
     */
    public function index(): View
    {
        $user = auth()->user();
        
        $savedJobs = SavedJob::where('user_id', $user->id)
            ->with(['job.employer', 'job.jobType', 'job.category'])
            ->whereHas('job') // Only include saved jobs where the job still exists
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('front.account.jobseeker.saved-jobs', compact('savedJobs'));
    }

    /**
     * Save a job (AJAX)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'job_id' => 'required|exists:jobs,id'
        ]);

        $user = auth()->user();
        $jobId = $request->job_id;

        // Check if job is already saved
        $existingSave = SavedJob::where('user_id', $user->id)
                                ->where('job_id', $jobId)
                                ->first();

        if ($existingSave) {
            return response()->json([
                'success' => false,
                'message' => 'Job is already saved!'
            ], 400);
        }

        // Check if job exists and is active
        $job = Job::where('id', $jobId)
                  ->where('status', Job::STATUS_APPROVED)
                  ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found or not available!'
            ], 404);
        }

        // Save the job
        SavedJob::create([
            'user_id' => $user->id,
            'job_id' => $jobId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job saved successfully!',
            'saved_count' => SavedJob::getSavedJobsCount($user->id)
        ]);
    }

    /**
     * Remove a saved job (AJAX)
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'job_id' => 'required|exists:jobs,id'
        ]);

        $user = auth()->user();
        $jobId = $request->job_id;

        $savedJob = SavedJob::where('user_id', $user->id)
                           ->where('job_id', $jobId)
                           ->first();

        if (!$savedJob) {
            return response()->json([
                'success' => false,
                'message' => 'Job is not saved!'
            ], 400);
        }

        $savedJob->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job removed from saved list!',
            'saved_count' => SavedJob::getSavedJobsCount($user->id)
        ]);
    }

    /**
     * Toggle save status (AJAX)
     */
    public function toggle(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'job_id' => 'required|exists:jobs,id'
            ]);

            $user = auth()->user();
            $jobId = $request->job_id;
            
            \Log::info('SavedJob toggle request', [
                'user_id' => $user->id,
                'job_id' => $jobId,
                'request_data' => $request->all()
            ]);

            $savedJob = SavedJob::where('user_id', $user->id)
                               ->where('job_id', $jobId)
                               ->first();

            if ($savedJob) {
                // Remove from saved
                $savedJob->delete();
                $action = 'removed';
                $message = 'Job removed from saved list!';
                $isSaved = false;
            } else {
                // Check if job exists and is approved
                $job = Job::where('id', $jobId)
                          ->where('status', Job::STATUS_APPROVED)
                          ->first();

                if (!$job) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Job not found or not available!'
                    ], 404);
                }

                // Add to saved
                $newSave = SavedJob::create([
                    'user_id' => $user->id,
                    'job_id' => $jobId
                ]);
                
                \Log::info('SavedJob created', [
                    'saved_job_id' => $newSave->id,
                    'user_id' => $user->id,
                    'job_id' => $jobId
                ]);
                
                $action = 'saved';
                $message = 'Job saved successfully!';
                $isSaved = true;
            }

            $savedCount = SavedJob::getSavedJobsCount($user->id);
            
            \Log::info('SavedJob toggle completed', [
                'action' => $action,
                'is_saved' => $isSaved,
                'saved_count' => $savedCount
            ]);

            return response()->json([
                'success' => true,
                'action' => $action,
                'message' => $message,
                'is_saved' => $isSaved,
                'saved_count' => $savedCount
            ]);
        
        } catch (\Exception $e) {
            \Log::error('SavedJob toggle error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'job_id' => $request->job_id ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the job. Please try again.'
            ], 500);
        }
    }

    /**
     * Get saved jobs count for user (AJAX)
     */
    public function count(): JsonResponse
    {
        $user = auth()->user();
        $count = SavedJob::getSavedJobsCount($user->id);

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Toggle save status using route model binding (AJAX)
     */
    public function toggleSave(Job $job): JsonResponse
    {
        try {
            $user = auth()->user();

            \Log::info('SavedJob toggleSave request', [
                'user_id' => $user->id,
                'job_id' => $job->id
            ]);

            $savedJob = SavedJob::where('user_id', $user->id)
                               ->where('job_id', $job->id)
                               ->first();

            if ($savedJob) {
                // Remove from saved
                $savedJob->delete();
                $action = 'removed';
                $message = 'Job removed from saved list!';
                $isSaved = false;
            } else {
                // Check if job is approved
                if ($job->status !== Job::STATUS_APPROVED) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Job not available!'
                    ], 404);
                }

                // Add to saved
                SavedJob::create([
                    'user_id' => $user->id,
                    'job_id' => $job->id
                ]);

                $action = 'saved';
                $message = 'Job saved successfully!';
                $isSaved = true;
            }

            $savedCount = SavedJob::getSavedJobsCount($user->id);

            \Log::info('SavedJob toggleSave completed', [
                'action' => $action,
                'is_saved' => $isSaved,
                'saved_count' => $savedCount
            ]);

            return response()->json([
                'success' => true,
                'saved' => $isSaved,
                'action' => $action,
                'message' => $message,
                'is_saved' => $isSaved,
                'saved_count' => $savedCount
            ]);

        } catch (\Exception $e) {
            \Log::error('SavedJob toggleSave error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'job_id' => $job->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the job. Please try again.'
            ], 500);
        }
    }
}