<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class BookmarkedJobController extends Controller
{
    /**
     * Toggle the bookmarked status of a job for the authenticated user.
     *
     * @param  \App\Models\Job  $job
     * @return \Illuminate\Http\Response
     */
    public function toggleBookmark(Job $job)
    {
        $user = auth()->user();
        $user->bookmarkedJobs()->toggle($job->id);

        return response()->json([
            'bookmarked' => $user->bookmarkedJobs()->where('job_id', $job->id)->exists(),
            'message' => 'Job bookmark status updated successfully'
        ]);
    }
}
