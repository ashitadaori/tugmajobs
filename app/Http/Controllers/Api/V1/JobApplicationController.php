<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Models\Job;
use App\Models\JobApplication;
use App\Repositories\JobApplicationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    protected JobApplicationRepository $applicationRepository;

    public function __construct(JobApplicationRepository $applicationRepository)
    {
        $this->applicationRepository = $applicationRepository;
    }

    /**
     * Get applications for authenticated user (jobseeker)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 15), 50);
        $applications = $this->applicationRepository->getByUser(auth()->id(), $perPage);

        return response()->json([
            'success' => true,
            'data' => $applications->items(),
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ],
        ]);
    }

    /**
     * Apply to a job
     */
    public function store(StoreJobApplicationRequest $request, int $jobId): JsonResponse
    {
        $job = Job::findOrFail($jobId);

        // Check if already applied
        if ($this->applicationRepository->hasApplied(auth()->id(), $jobId)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied to this job',
            ], 422);
        }

        // Check if job is still active
        if ($job->status !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'This job is no longer accepting applications',
            ], 422);
        }

        $data = $request->validated();
        $data['job_id'] = $jobId;
        $data['user_id'] = auth()->id();
        $data['employer_id'] = $job->employer_id;
        $data['status'] = 'pending';
        $data['applied_date'] = now();

        // Handle resume upload
        if ($request->hasFile('resume')) {
            $path = $request->file('resume')->store('resumes', 'public');
            $data['resume'] = $path;
        }

        $application = $this->applicationRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully',
            'data' => $application,
        ], 201);
    }

    /**
     * Get a specific application
     */
    public function show(int $id): JsonResponse
    {
        $application = $this->applicationRepository->findById($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found',
            ], 404);
        }

        // Ensure user owns the application or is the employer
        if ($application->user_id !== auth()->id() && $application->employer_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $application,
        ]);
    }

    /**
     * Withdraw an application (jobseeker)
     */
    public function withdraw(int $id): JsonResponse
    {
        $application = $this->applicationRepository->findById($id);

        if (!$application || $application->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found',
            ], 404);
        }

        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot withdraw a processed application',
            ], 422);
        }

        $this->applicationRepository->delete($application);

        return response()->json([
            'success' => true,
            'message' => 'Application withdrawn successfully',
        ]);
    }

    /**
     * Get applications for employer
     */
    public function employerApplications(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 15), 50);
        $applications = $this->applicationRepository->getByEmployer(auth()->id(), $perPage);

        return response()->json([
            'success' => true,
            'data' => $applications->items(),
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ],
        ]);
    }

    /**
     * Update application status (employer)
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,interviewed,hired',
        ]);

        $application = $this->applicationRepository->findById($id);

        if (!$application || $application->employer_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found',
            ], 404);
        }

        $application = $this->applicationRepository->updateStatus($application, $request->status);

        return response()->json([
            'success' => true,
            'message' => 'Application status updated',
            'data' => $application,
        ]);
    }

    /**
     * Toggle shortlist status (employer)
     */
    public function toggleShortlist(int $id): JsonResponse
    {
        $application = $this->applicationRepository->findById($id);

        if (!$application || $application->employer_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found',
            ], 404);
        }

        $application = $this->applicationRepository->toggleShortlist($application);

        return response()->json([
            'success' => true,
            'message' => $application->shortlisted ? 'Added to shortlist' : 'Removed from shortlist',
            'data' => $application,
        ]);
    }

    /**
     * Get shortlisted applications (employer)
     */
    public function shortlisted(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 15), 50);
        $applications = $this->applicationRepository->getShortlisted(auth()->id(), $perPage);

        return response()->json([
            'success' => true,
            'data' => $applications->items(),
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ],
        ]);
    }

    /**
     * Get application statistics (employer)
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->applicationRepository->getEmployerStatistics(auth()->id());

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
