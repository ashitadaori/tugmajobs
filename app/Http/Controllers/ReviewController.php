<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Store a new review
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:jobs,id',
            'review_type' => 'required|in:job,company',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:200',
            'comment' => 'required|string|min:10|max:1000',
            'is_anonymous' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        try {
            $job = Job::findOrFail($request->job_id);

            // Check if user has applied
            $hasApplied = JobApplication::where('user_id', Auth::id())
                ->where('job_id', $request->job_id)
                ->exists();

            if (!$hasApplied) {
                return response()->json([
                    'status' => false,
                    'message' => 'You must apply to this job before writing a review.'
                ]);
            }

            // Check if already reviewed
            $existingReview = Review::where('user_id', Auth::id())
                ->where('job_id', $request->job_id)
                ->where('review_type', $request->review_type)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'status' => false,
                    'message' => 'You have already reviewed this ' . $request->review_type . '.'
                ]);
            }

            // Check if user got hired (optional - can be set by employer later)
            $application = JobApplication::where('user_id', Auth::id())
                ->where('job_id', $request->job_id)
                ->first();
            
            $isVerifiedHire = $application && $application->status === 'hired';

            // Create review
            $review = Review::create([
                'user_id' => Auth::id(),
                'job_id' => $request->job_id,
                'employer_id' => $job->employer_id,
                'review_type' => $request->review_type,
                'rating' => $request->rating,
                'title' => $request->title,
                'comment' => $request->comment,
                'is_anonymous' => $request->is_anonymous ?? false,
                'is_verified_hire' => $isVerifiedHire,
            ]);

            // Send notification to employer
            $employer = $job->employer;
            if ($employer) {
                try {
                    \App\Models\Notification::create([
                        'user_id' => $employer->id,
                        'title' => 'New Review Received',
                        'message' => 'A jobseeker has reviewed your ' . $request->review_type . ': "' . $job->title . '"',
                        'type' => 'new_review',
                        'data' => json_encode([
                            'review_id' => $review->id,
                            'job_id' => $job->id,
                            'job_title' => $job->title,
                            'rating' => $request->rating,
                            'review_type' => $request->review_type,
                        ]),
                        'action_url' => route('jobDetail', $job->id), // Use job detail page for now
                        'read_at' => null
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the review submission
                    \Log::warning('Failed to create review notification: ' . $e->getMessage());
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Review submitted successfully!',
                'review' => $review
            ]);

        } catch (\Exception $e) {
            \Log::error('Review submission error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while submitting your review.'
            ]);
        }
    }

    /**
     * Get reviews for a job
     */
    public function getJobReviews($jobId, $type = 'job')
    {
        $reviews = Review::where('job_id', $jobId)
            ->where('review_type', $type)
            ->with(['user', 'job'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => true,
            'reviews' => $reviews
        ]);
    }

    /**
     * Update a review (within 30 days)
     */
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        // Check ownership
        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if within 30 days
        if ($review->created_at->diffInDays(now()) > 30) {
            return response()->json([
                'status' => false,
                'message' => 'Reviews can only be edited within 30 days of posting.'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:200',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $review->update([
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Review updated successfully!',
            'review' => $review
        ]);
    }

    /**
     * Delete a review
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        // Check ownership or admin
        if ($review->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $review->delete();

        return response()->json([
            'status' => true,
            'message' => 'Review deleted successfully!'
        ]);
    }

    /**
     * Check if user can review
     */
    public function checkEligibility($jobId, $reviewType)
    {
        $canReview = Review::canUserReview(Auth::id(), $jobId, $reviewType);

        return response()->json([
            'status' => true,
            'can_review' => $canReview
        ]);
    }

    /**
     * Get all reviews by the authenticated user
     */
    public function myReviews()
    {
        $reviews = Review::where('user_id', Auth::id())
            ->with(['job', 'employer'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('front.account.my-reviews', compact('reviews'));
    }
}
