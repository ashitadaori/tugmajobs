<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JobApprovalController extends Controller
{
    public function index()
    {
        $pendingJobs = Job::where('status', 'pending')
            ->with(['employer', 'jobType', 'category'])
            ->latest()
            ->paginate(10);

        return view('admin.jobs.pending', compact('pendingJobs'));
    }

    public function approve(Job $job)
    {
        $job->update(['status' => 'approved']);

        // Clear dashboard cache so pending count updates
        Cache::forget('admin_dashboard_stats');

        // Notify the employer that their job was approved
        try {
            Notification::create([
                'user_id' => $job->employer_id,
                'title' => 'Job Post Approved',
                'message' => "Great news! Your job posting \"{$job->title}\" has been approved and is now live. Job seekers can now view and apply to your position.",
                'type' => 'job_approved',
                'data' => [
                    'job_id' => $job->id,
                    'job_title' => $job->title,
                    'approved_at' => now()->toDateTimeString(),
                ],
                'action_url' => route('employer.jobs.show', $job->id),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create job approval notification', ['error' => $e->getMessage()]);
        }

        return redirect()->back()
            ->with('success', 'Job has been approved successfully. The employer has been notified.');
    }

    public function reject(Request $request, Job $job)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10'
        ]);

        $job->update([
            'status' => 'rejected',
            'meta_data' => array_merge($job->meta_data ?? [], [
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now(),
                'rejected_by' => auth()->id()
            ])
        ]);

        // Clear dashboard cache so pending count updates
        Cache::forget('admin_dashboard_stats');

        // Notify the employer that their job was rejected
        try {
            Notification::create([
                'user_id' => $job->employer_id,
                'title' => 'Job Post Needs Revision',
                'message' => "Your job posting \"{$job->title}\" requires some changes before it can be published. Reason: {$request->rejection_reason}",
                'type' => 'job_rejected',
                'data' => [
                    'job_id' => $job->id,
                    'job_title' => $job->title,
                    'rejection_reason' => $request->rejection_reason,
                    'rejected_at' => now()->toDateTimeString(),
                ],
                'action_url' => route('employer.jobs.edit', $job->id),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create job rejection notification', ['error' => $e->getMessage()]);
        }

        return redirect()->back()
            ->with('success', 'Job has been rejected successfully. The employer has been notified.');
    }
}
