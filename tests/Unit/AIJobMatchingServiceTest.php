<?php

namespace Tests\Unit;

use App\Services\AIJobMatchingService;
use App\Models\User;
use App\Models\Job;
use App\Models\JobseekerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class AIJobMatchingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AIJobMatchingService();
    }

    /**
     * Test service can be instantiated.
     */
    public function test_service_can_be_instantiated()
    {
        $this->assertInstanceOf(AIJobMatchingService::class, $this->service);
    }

    /**
     * Test getMatchingJobs returns array.
     */
    public function test_get_matching_jobs_returns_array()
    {
        $user = User::factory()->create(['user_type' => 'jobseeker']);
        $profile = JobseekerProfile::factory()->create([
            'user_id' => $user->id,
            'skills' => 'PHP, Laravel, MySQL',
            'experience_years' => 3,
        ]);

        Job::factory()->count(5)->create(['status' => 1]);

        // This test checks the structure, not the AI functionality
        // In a real scenario, you'd mock the OpenAI API calls
        $result = $this->service->getMatchingJobs($user);

        $this->assertIsArray($result);
    }

    /**
     * Test service handles empty job list.
     */
    public function test_service_handles_empty_job_list()
    {
        $user = User::factory()->create(['user_type' => 'jobseeker']);
        $profile = JobseekerProfile::factory()->create(['user_id' => $user->id]);

        $result = $this->service->getMatchingJobs($user);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
