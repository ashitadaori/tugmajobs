<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEmployerProfileRequest;
use App\Repositories\EmployerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployerController extends Controller
{
    protected EmployerRepository $employerRepository;

    public function __construct(EmployerRepository $employerRepository)
    {
        $this->employerRepository = $employerRepository;
    }

    /**
     * Get list of employers
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 15), 50);
        $employers = $this->employerRepository->getActive($perPage);

        return response()->json([
            'success' => true,
            'data' => $employers->items(),
            'meta' => [
                'current_page' => $employers->currentPage(),
                'last_page' => $employers->lastPage(),
                'per_page' => $employers->perPage(),
                'total' => $employers->total(),
            ],
        ]);
    }

    /**
     * Get featured employers
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 6), 20);
        $employers = $this->employerRepository->getFeatured($limit);

        return response()->json([
            'success' => true,
            'data' => $employers,
        ]);
    }

    /**
     * Get top hiring employers
     */
    public function topHiring(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 10), 50);
        $employers = $this->employerRepository->getTopHiring($limit);

        return response()->json([
            'success' => true,
            'data' => $employers,
        ]);
    }

    /**
     * Get a specific employer
     */
    public function show(int $id): JsonResponse
    {
        $employer = $this->employerRepository->findById($id);

        if (!$employer) {
            return response()->json([
                'success' => false,
                'message' => 'Employer not found',
            ], 404);
        }

        // Increment profile views
        $this->employerRepository->incrementViews($employer);

        return response()->json([
            'success' => true,
            'data' => $employer,
        ]);
    }

    /**
     * Get employer by slug
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $employer = $this->employerRepository->findBySlug($slug);

        if (!$employer) {
            return response()->json([
                'success' => false,
                'message' => 'Employer not found',
            ], 404);
        }

        $this->employerRepository->incrementViews($employer);

        return response()->json([
            'success' => true,
            'data' => $employer,
        ]);
    }

    /**
     * Get current employer profile (authenticated employer)
     */
    public function profile(): JsonResponse
    {
        $employer = $this->employerRepository->findByUserId(auth()->id());

        if (!$employer) {
            return response()->json([
                'success' => false,
                'message' => 'Employer profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $employer,
        ]);
    }

    /**
     * Update employer profile
     */
    public function updateProfile(UpdateEmployerProfileRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Handle logo upload
        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('company_logos', 'public');
            $data['company_logo'] = $path;
        }

        $employer = $this->employerRepository->createOrUpdate(auth()->id(), $data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $employer,
        ]);
    }

    /**
     * Search employers
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $perPage = min($request->get('per_page', 15), 50);
        $employers = $this->employerRepository->search($request->q, $perPage);

        return response()->json([
            'success' => true,
            'data' => $employers->items(),
            'meta' => [
                'current_page' => $employers->currentPage(),
                'last_page' => $employers->lastPage(),
                'per_page' => $employers->perPage(),
                'total' => $employers->total(),
            ],
        ]);
    }

    /**
     * Get employer statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->employerRepository->getStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
