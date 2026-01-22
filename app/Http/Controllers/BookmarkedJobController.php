<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\BookmarkedJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BookmarkedJobController extends Controller
{
    /**
     * Display bookmarked jobs for the authenticated user
     */
    public function index(): View
    {
        $user = auth()->user();

        $bookmarkedJobs = BookmarkedJob::where('user_id', $user->id)
            ->with(['job.employer', 'job.jobType', 'job.category'])
            ->whereHas('job') // Only include bookmarked jobs where the job still exists
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('front.account.jobseeker.bookmarked-jobs', compact('bookmarkedJobs'));
    }

    /**
     * Bookmark a job (AJAX)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'job_id' => 'required|exists:jobs,id'
        ]);

        $user = auth()->user();
        $jobId = $request->job_id;

        // Check if job is already bookmarked
        $existingBookmark = BookmarkedJob::where('user_id', $user->id)
                                ->where('job_id', $jobId)
                                ->first();

        if ($existingBookmark) {
            return response()->json([
                'success' => false,
                'message' => 'Job is already bookmarked!'
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

        // Bookmark the job
        BookmarkedJob::create([
            'user_id' => $user->id,
            'job_id' => $jobId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job bookmarked successfully!',
            'bookmarked_count' => BookmarkedJob::getBookmarkedJobsCount($user->id)
        ]);
    }

    /**
     * Remove a bookmarked job (AJAX)
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'job_id' => 'required|exists:jobs,id'
        ]);

        $user = auth()->user();
        $jobId = $request->job_id;

        $bookmarkedJob = BookmarkedJob::where('user_id', $user->id)
                           ->where('job_id', $jobId)
                           ->first();

        if (!$bookmarkedJob) {
            return response()->json([
                'success' => false,
                'message' => 'Job is not bookmarked!'
            ], 400);
        }

        $bookmarkedJob->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job removed from bookmarks!',
            'bookmarked_count' => BookmarkedJob::getBookmarkedJobsCount($user->id)
        ]);
    }

    /**
     * Toggle bookmark status (AJAX)
     */
    public function toggle(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'job_id' => 'required|exists:jobs,id'
            ]);

            $user = auth()->user();
            $jobId = $request->job_id;

            \Log::info('BookmarkedJob toggle request', [
                'user_id' => $user->id,
                'job_id' => $jobId,
                'request_data' => $request->all()
            ]);

            $bookmarkedJob = BookmarkedJob::where('user_id', $user->id)
                               ->where('job_id', $jobId)
                               ->first();

            if ($bookmarkedJob) {
                // Remove from bookmarks
                $bookmarkedJob->delete();
                $action = 'removed';
                $message = 'Job removed from bookmarks!';
                $isBookmarked = false;
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

                // Add to bookmarks
                $newBookmark = BookmarkedJob::create([
                    'user_id' => $user->id,
                    'job_id' => $jobId
                ]);

                \Log::info('BookmarkedJob created', [
                    'bookmarked_job_id' => $newBookmark->id,
                    'user_id' => $user->id,
                    'job_id' => $jobId
                ]);

                $action = 'bookmarked';
                $message = 'Job bookmarked successfully!';
                $isBookmarked = true;
            }

            $bookmarkedCount = BookmarkedJob::getBookmarkedJobsCount($user->id);

            \Log::info('BookmarkedJob toggle completed', [
                'action' => $action,
                'is_bookmarked' => $isBookmarked,
                'bookmarked_count' => $bookmarkedCount
            ]);

            return response()->json([
                'success' => true,
                'action' => $action,
                'message' => $message,
                'is_bookmarked' => $isBookmarked,
                'bookmarked_count' => $bookmarkedCount
            ]);

        } catch (\Exception $e) {
            \Log::error('BookmarkedJob toggle error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'job_id' => $request->job_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while bookmarking the job. Please try again.'
            ], 500);
        }
    }

    /**
     * Get bookmarked jobs count for user (AJAX)
     */
    public function count(): JsonResponse
    {
        $user = auth()->user();
        $count = BookmarkedJob::getBookmarkedJobsCount($user->id);

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Toggle bookmark status using route model binding (AJAX)
     */
    public function toggleBookmark(Job $job): JsonResponse
    {
        try {
            $user = auth()->user();

            \Log::info('BookmarkedJob toggleBookmark request', [
                'user_id' => $user->id,
                'job_id' => $job->id
            ]);

            $bookmarkedJob = BookmarkedJob::where('user_id', $user->id)
                               ->where('job_id', $job->id)
                               ->first();

            if ($bookmarkedJob) {
                // Remove from bookmarks
                $bookmarkedJob->delete();
                $action = 'removed';
                $message = 'Job removed from bookmarks!';
                $isBookmarked = false;
            } else {
                // Check if job is approved
                if ($job->status !== Job::STATUS_APPROVED) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Job not available!'
                    ], 404);
                }

                // Add to bookmarks
                BookmarkedJob::create([
                    'user_id' => $user->id,
                    'job_id' => $job->id
                ]);

                $action = 'bookmarked';
                $message = 'Job bookmarked successfully!';
                $isBookmarked = true;
            }

            $bookmarkedCount = BookmarkedJob::getBookmarkedJobsCount($user->id);

            \Log::info('BookmarkedJob toggleBookmark completed', [
                'action' => $action,
                'is_bookmarked' => $isBookmarked,
                'bookmarked_count' => $bookmarkedCount
            ]);

            return response()->json([
                'success' => true,
                'bookmarked' => $isBookmarked,
                'action' => $action,
                'message' => $message,
                'is_bookmarked' => $isBookmarked,
                'bookmarked_count' => $bookmarkedCount
            ]);

        } catch (\Exception $e) {
            \Log::error('BookmarkedJob toggleBookmark error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'job_id' => $job->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while bookmarking the job. Please try again.'
            ], 500);
        }
    }
}
