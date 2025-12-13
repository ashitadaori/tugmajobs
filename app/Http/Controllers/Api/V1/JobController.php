<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Models\Job;
use App\Repositories\JobRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    protected JobRepository $jobRepository;

    public function __construct(JobRepository $jobRepository)
    {
        $this->jobRepository = $jobRepository;
    }

    /**
     * Get list of approved jobs
     *
     * @OA\Get(
     *     path="/api/v1/jobs",
     *     tags={"Jobs"},
     *     summary="Get list of approved jobs",
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="category_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="job_type_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="location", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="keyword", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 15), 100);
        $filters = $request->only(['category_id', 'job_type_id', 'location', 'keyword']);

        $jobs = $this->jobRepository->getApprovedJobs($perPage, $filters);

        return response()->json([
            'success' => true,
            'data' => $jobs->items(),
            'meta' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
            ],
        ]);
    }

    /**
     * Get featured jobs
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 8), 20);
        $jobs = $this->jobRepository->getFeaturedJobs($limit);

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    /**
     * Get recent jobs
     */
    public function recent(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 10), 50);
        $jobs = $this->jobRepository->getRecentJobs($limit);

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    /**
     * Get a specific job
     */
    public function show(int $id): JsonResponse
    {
        $job = $this->jobRepository->findById($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $job,
        ]);
    }

    /**
     * Create a new job (requires employer authentication)
     */
    public function store(StoreJobRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['employer_id'] = auth()->id();
        $data['status'] = 0; // Pending approval

        $job = $this->jobRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Job created successfully and pending approval',
            'data' => $job,
        ], 201);
    }

    /**
     * Update a job
     */
    public function update(UpdateJobRequest $request, Job $job): JsonResponse
    {
        $job = $this->jobRepository->update($job, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Job updated successfully',
            'data' => $job,
        ]);
    }

    /**
     * Delete a job
     */
    public function destroy(Job $job): JsonResponse
    {
        // Ensure the user owns the job
        if ($job->employer_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->jobRepository->delete($job);

        return response()->json([
            'success' => true,
            'message' => 'Job deleted successfully',
        ]);
    }

    /**
     * Get jobs by category
     */
    public function byCategory(int $categoryId, Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 10), 50);
        $jobs = $this->jobRepository->getByCategory($categoryId, $limit);

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    /**
     * Get job statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->jobRepository->getStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
