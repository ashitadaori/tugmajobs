<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class SavedJobController extends Controller
{
    /**
     * Toggle the saved status of a job for the authenticated user.
     *
     * @param  \App\Models\Job  $job
     * @return \Illuminate\Http\Response
     */
    public function toggleSave(Job $job)
    {
        $user = auth()->user();
        $user->savedJobs()->toggle($job->id);

        return response()->json([
            'saved' => $user->savedJobs()->where('job_id', $job->id)->exists(),
            'message' => 'Job save status updated successfully'
        ]);
    }
} 