<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Display all reviews for the employer
     */
    public function index(Request $request)
    {
        $employerId = Auth::id();
        
        // Get filter type
        $filterType = $request->get('type', 'all'); // all, job, company
        
        // Base query
        $query = Review::where('employer_id', $employerId)
            ->with(['user', 'job']);
        
        // Apply filter
        if ($filterType !== 'all') {
            $query->where('review_type', $filterType);
        }
        
        // Get reviews
        $reviews = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Get statistics
        $stats = [
            'total_reviews' => Review::where('employer_id', $employerId)->count(),
            'job_reviews' => Review::where('employer_id', $employerId)->where('review_type', 'job')->count(),
            'company_reviews' => Review::where('employer_id', $employerId)->where('review_type', 'company')->count(),
            'avg_rating' => Review::getCompanyAverageRating($employerId),
            'rating_distribution' => Review::getCompanyRatingDistribution($employerId),
        ];
        
        return view('front.account.employer.reviews.index', compact('reviews', 'stats', 'filterType'));
    }
    
    /**
     * Add response to a review
     */
    public function respond(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'response' => 'required|string|min:10|max:1000'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
        
        try {
            $review = Review::where('id', $id)
                ->where('employer_id', Auth::id())
                ->with(['user', 'job', 'employer.employerProfile'])
                ->firstOrFail();
            
            $review->employer_response = $request->response;
            $review->employer_responded_at = now();
            $review->save();
            
            // Send notification to the jobseeker who wrote the review
            try {
                if ($review->user && !$review->is_anonymous) {
                    $this->sendReviewResponseNotification($review, 'posted');
                }
            } catch (\Exception $notifError) {
                \Log::error('Notification error: ' . $notifError->getMessage());
                // Continue even if notification fails
            }
            
            return response()->json([
                'status' => true,
                'message' => 'Response posted successfully!'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Review response error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error posting response. Please try again.'
            ]);
        }
    }
    
    /**
     * Update employer response
     */
    public function updateResponse(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'response' => 'required|string|min:10|max:1000'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
        
        try {
            $review = Review::where('id', $id)
                ->where('employer_id', Auth::id())
                ->with(['user', 'job', 'employer.employerProfile'])
                ->firstOrFail();
            
            $review->employer_response = $request->response;
            $review->save();
            
            // Send notification to the jobseeker about the updated response
            try {
                if ($review->user && !$review->is_anonymous) {
                    $this->sendReviewResponseNotification($review, 'updated');
                }
            } catch (\Exception $notifError) {
                \Log::error('Notification error: ' . $notifError->getMessage());
                // Continue even if notification fails
            }
            
            return response()->json([
                'status' => true,
                'message' => 'Response updated successfully!'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Update response error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error updating response.'
            ]);
        }
    }
    
    /**
     * Delete employer response
     */
    public function deleteResponse($id)
    {
        try {
            $review = Review::where('id', $id)
                ->where('employer_id', Auth::id())
                ->with(['user', 'job', 'employer.employerProfile'])
                ->firstOrFail();
            
            // Send notification before deleting (so we still have the response text)
            try {
                if ($review->user && !$review->is_anonymous) {
                    $this->sendReviewResponseNotification($review, 'deleted');
                }
            } catch (\Exception $notifError) {
                \Log::error('Notification error: ' . $notifError->getMessage());
                // Continue even if notification fails
            }
            
            $review->employer_response = null;
            $review->employer_responded_at = null;
            $review->save();
            
            return response()->json([
                'status' => true,
                'message' => 'Response deleted successfully!'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Delete response error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error deleting response.'
            ]);
        }
    }
    
    /**
     * Send notification to jobseeker about employer response
     */
    private function sendReviewResponseNotification($review, $action)
    {
        $employer = $review->employer;
        
        // Safely get company name
        $companyName = 'The employer';
        if ($employer) {
            if ($employer->employerProfile && $employer->employerProfile->company_name) {
                $companyName = $employer->employerProfile->company_name;
            } else {
                $companyName = $employer->name;
            }
        }
        
        $title = match($action) {
            'updated' => "Response Updated",
            'deleted' => "Response Removed",
            default => "New Response to Your Review"
        };
        
        $message = match($action) {
            'updated' => "{$companyName} updated their response to your review",
            'deleted' => "{$companyName} removed their response to your review",
            default => "{$companyName} responded to your review"
        };
        
        // Add response preview if available
        if ($action !== 'deleted' && $review->employer_response) {
            $responsePreview = strlen($review->employer_response) > 100 
                ? substr($review->employer_response, 0, 100) . '...' 
                : $review->employer_response;
            $message .= ': "' . $responsePreview . '"';
        }
        
        // Check if a similar notification already exists (within last 5 seconds to prevent duplicates)
        $recentNotification = \DB::table('notifications')
            ->where('user_id', $review->user_id)
            ->where('type', 'review_response')
            ->where('created_at', '>=', now()->subSeconds(5))
            ->whereRaw('JSON_EXTRACT(data, "$.review_id") = ?', [$review->id])
            ->whereRaw('JSON_EXTRACT(data, "$.action") = ?', [$action])
            ->exists();
        
        // Only insert if no recent duplicate exists
        if (!$recentNotification) {
            // Insert notification directly into custom notifications table
            \DB::table('notifications')->insert([
                'user_id' => $review->user_id,
                'title' => $title,
                'message' => $message,
                'type' => 'review_response',
                'data' => json_encode([
                    'type' => 'review_response',
                    'action' => $action,
                    'review_id' => $review->id,
                    'employer_id' => $review->employer_id,
                    'company_name' => $companyName,
                    'review_type' => $review->review_type,
                    'job_id' => $review->job_id,
                    'job_title' => $review->job ? $review->job->title : null,
                    'response' => $action !== 'deleted' ? $review->employer_response : null,
                    'icon' => 'fas fa-reply',
                    'color' => 'primary'
                ]),
                'action_url' => url('/account/my-job-applications'),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
