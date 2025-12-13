<?php

namespace Tests\Unit;

use App\Services\AdvancedKMeansClusteringService;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KMeansClusteringServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AdvancedKMeansClusteringService();
    }

    /**
     * Test service can be instantiated.
     */
    public function test_service_can_be_instantiated()
    {
        $this->assertInstanceOf(AdvancedKMeansClusteringService::class, $this->service);
    }

    /**
     * Test clustering returns expected structure.
     */
    public function test_clustering_returns_clusters()
    {
        // Create jobs with different categories
        Job::factory()->count(10)->create(['status' => 1]);

        $result = $this->service->clusterJobs();

        $this->assertIsArray($result);
    }

    /**
     * Test service handles empty dataset.
     */
    public function test_service_handles_empty_dataset()
    {
        $result = $this->service->clusterJobs();

        $this->assertIsArray($result);
    }
}
