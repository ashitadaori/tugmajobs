<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\User;
use App\Notifications\BulkEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuickActionsController extends Controller
{
    /**
     * Bulk approve pending jobs
     */
    public function bulkApproveJobs(Request $request)
    {
        $request->validate([
            'job_ids' => 'required|array',
            'job_ids.*' => 'exists:jobs,id'
        ]);

        try {
            DB::beginTransaction();

            $jobs = Job::whereIn('id', $request->job_ids)
                ->where('status', Job::STATUS_PENDING)
                ->get();

            $count = 0;
            foreach ($jobs as $job) {
                $job->update([
                    'status' => Job::STATUS_APPROVED,
                    'approved_at' => now()
                ]);

                // Notify employer
                if ($job->employer) {
                    DB::table('notifications')->insert([
                        'user_id' => $job->employer_id,
                        'title' => 'Job Posting Approved!',
                        'message' => 'Your job posting "' . $job->title . '" has been approved and is now live!',
                        'type' => 'job_approved',
                        'data' => json_encode([
                            'job_id' => $job->id,
                            'job_title' => $job->title,
                            'icon' => 'check-circle',
                            'color' => 'success'
                        ]),
                        'action_url' => route('employer.jobs.index'),
                        'read_at' => null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // Notify all jobseekers about new job
                $this->notifyJobseekersAboutNewJob($job);

                $count++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully approved {$count} job(s). Notifications sent to employers and job seekers.",
                'count' => $count
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk approve jobs failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve jobs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk reject pending jobs
     */
    public function bulkRejectJobs(Request $request)
    {
        $request->validate([
            'job_ids' => 'required|array',
            'job_ids.*' => 'exists:jobs,id',
            'rejection_reason' => 'required|string|min:10|max:500'
        ]);

        try {
            DB::beginTransaction();

            $jobs = Job::whereIn('id', $request->job_ids)
                ->where('status', Job::STATUS_PENDING)
                ->get();

            $count = 0;
            foreach ($jobs as $job) {
                $job->update([
                    'status' => Job::STATUS_REJECTED,
                    'rejection_reason' => $request->rejection_reason,
                    'rejected_at' => now()
                ]);

                // Notify employer
                if ($job->employer) {
                    DB::table('notifications')->insert([
                        'user_id' => $job->employer_id,
                        'title' => 'Job Posting Needs Revision',
                        'message' => 'Your job posting "' . $job->title . '" needs revision. Please review the feedback and resubmit.',
                        'type' => 'job_rejected',
                        'data' => json_encode([
                            'job_id' => $job->id,
                            'job_title' => $job->title,
                            'rejection_reason' => $request->rejection_reason,
                            'icon' => 'exclamation-triangle',
                            'color' => 'warning'
                        ]),
                        'action_url' => route('employer.jobs.edit', $job->id),
                        'read_at' => null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                $count++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully rejected {$count} job(s). Employers have been notified.",
                'count' => $count
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk reject jobs failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject jobs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick verify KYC for multiple users
     */
    public function bulkVerifyKyc(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        try {
            $users = User::whereIn('id', $request->user_ids)
                ->whereIn('kyc_status', ['in_progress', 'pending'])
                ->get();

            $count = 0;
            foreach ($users as $user) {
                $user->update([
                    'kyc_status' => 'verified',
                    'kyc_verified_at' => now()
                ]);

                // Send notification
                DB::table('notifications')->insert([
                    'user_id' => $user->id,
                    'title' => 'KYC Verification Approved!',
                    'message' => 'Your identity verification has been approved. You now have full access to all features.',
                    'type' => 'kyc_approved',
                    'data' => json_encode([
                        'icon' => 'shield-check',
                        'color' => 'success'
                    ]),
                    'action_url' => $user->isEmployer() ? route('employer.dashboard') : route('account.dashboard'),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $count++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully verified KYC for {$count} user(s).",
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk KYC verification failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify KYC: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send mass email to user segments
     */
    public function sendMassEmail(Request $request)
    {
        $request->validate([
            'segment' => 'required|in:all,jobseekers,employers,verified,unverified',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|min:20',
            'send_email' => 'boolean',
            'send_notification' => 'boolean'
        ]);

        try {
            // Build query based on segment
            $query = User::query();

            switch ($request->segment) {
                case 'jobseekers':
                    $query->where('role', 'jobseeker');
                    break;
                case 'employers':
                    $query->where('role', 'employer');
                    break;
                case 'verified':
                    $query->where('kyc_status', 'verified');
                    break;
                case 'unverified':
                    $query->where('kyc_status', '!=', 'verified');
                    break;
                // 'all' - no additional filters
            }

            $users = $query->get();

            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No users found in the selected segment.'
                ], 400);
            }

            $count = 0;

            foreach ($users as $user) {
                // Send in-app notification
                if ($request->send_notification !== false) {
                    DB::table('notifications')->insert([
                        'user_id' => $user->id,
                        'title' => $request->subject,
                        'message' => $request->message,
                        'type' => 'admin_announcement',
                        'data' => json_encode([
                            'icon' => 'megaphone',
                            'color' => 'info',
                            'from_admin' => true
                        ]),
                        'action_url' => null,
                        'read_at' => null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // Send email notification
                if ($request->send_email) {
                    try {
                        $user->notify(new BulkEmailNotification($request->subject, $request->message));
                    } catch (\Exception $e) {
                        Log::warning('Failed to send email to user ' . $user->id . ': ' . $e->getMessage());
                    }
                }

                $count++;
            }

            return response()->json([
                'success' => true,
                'message' => "Message sent to {$count} user(s) in the '{$request->segment}' segment.",
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Mass email failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send messages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get quick actions dashboard data
     */
    public function getDashboardData()
    {
        $pendingJobs = Job::where('status', Job::STATUS_PENDING)->count();
        $pendingKyc = User::where('kyc_status', 'in_progress')->count();
        $recentUsers = User::where('created_at', '>=', now()->subDays(7))->count();

        return response()->json([
            'pending_jobs' => $pendingJobs,
            'pending_kyc' => $pendingKyc,
            'recent_users' => $recentUsers,
            'actions_available' => $pendingJobs + $pendingKyc
        ]);
    }

    /**
     * Notify jobseekers about new job
     */
    private function notifyJobseekersAboutNewJob(Job $job)
    {
        try {
            $jobseekers = User::where('role', 'jobseeker')->get();

            $companyName = 'Confidential';
            if ($job->company_id && $job->company) {
                $companyName = $job->company->name;
            } elseif ($job->company_name) {
                $companyName = $job->company_name;
            }

            foreach ($jobseekers as $jobseeker) {
                DB::table('notifications')->insert([
                    'user_id' => $jobseeker->id,
                    'title' => 'New Job Posted!',
                    'message' => 'A new job opportunity is available: ' . $job->title . ' at ' . $companyName,
                    'type' => 'new_job',
                    'data' => json_encode([
                        'job_id' => $job->id,
                        'job_title' => $job->title,
                        'company_name' => $companyName,
                        'location' => $job->location,
                        'job_type' => $job->jobType->name ?? 'Full Time',
                        'category' => $job->category->name ?? 'General',
                        'status' => 'new_job'
                    ]),
                    'action_url' => route('jobDetail', $job->id),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to notify jobseekers: ' . $e->getMessage());
        }
    }
}
