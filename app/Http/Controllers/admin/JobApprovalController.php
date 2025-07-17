<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;

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

        // You might want to notify the employer here that their job was approved

        return redirect()->back()
            ->with('success', 'Job has been approved successfully.');
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

        // You might want to notify the employer here that their job was rejected

        return redirect()->back()
            ->with('success', 'Job has been rejected successfully.');
    }
}
