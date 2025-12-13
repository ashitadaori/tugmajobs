<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Job;
use App\Models\Category;
use App\Models\JobType;
use App\Models\Employer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary categories and job types
        Category::factory()->count(3)->create();
        JobType::factory()->count(3)->create();
    }

    /**
     * Test can list approved jobs publicly.
     */
    public function test_can_list_approved_jobs()
    {
        Job::factory()->count(5)->create(['status' => 1]);
        Job::factory()->count(2)->create(['status' => 0]); // Pending jobs

        $response = $this->getJson('/api/v1/jobs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'title', 'location'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        // Should only show approved jobs
        $this->assertCount(5, $response->json('data'));
    }

    /**
     * Test can filter jobs by category.
     */
    public function test_can_filter_jobs_by_category()
    {
        $category = Category::first();
        Job::factory()->count(3)->create([
            'status' => 1,
            'category_id' => $category->id,
        ]);
        Job::factory()->count(2)->create([
            'status' => 1,
            'category_id' => Category::factory()->create()->id,
        ]);

        $response = $this->getJson("/api/v1/jobs?category_id={$category->id}");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test can filter jobs by job type.
     */
    public function test_can_filter_jobs_by_job_type()
    {
        $jobType = JobType::first();
        Job::factory()->count(4)->create([
            'status' => 1,
            'job_type_id' => $jobType->id,
        ]);

        $response = $this->getJson("/api/v1/jobs?job_type_id={$jobType->id}");

        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data'));
    }

    /**
     * Test can filter jobs by location.
     */
    public function test_can_filter_jobs_by_location()
    {
        Job::factory()->count(2)->create([
            'status' => 1,
            'location' => 'New York',
        ]);
        Job::factory()->count(3)->create([
            'status' => 1,
            'location' => 'Los Angeles',
        ]);

        $response = $this->getJson('/api/v1/jobs?location=New York');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test can search jobs by keyword.
     */
    public function test_can_search_jobs_by_keyword()
    {
        Job::factory()->create([
            'status' => 1,
            'title' => 'Senior PHP Developer',
        ]);
        Job::factory()->create([
            'status' => 1,
            'title' => 'Junior Python Developer',
        ]);
        Job::factory()->create([
            'status' => 1,
            'title' => 'Marketing Manager',
        ]);

        $response = $this->getJson('/api/v1/jobs?keyword=Developer');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test can get single job details.
     */
    public function test_can_get_job_details()
    {
        $job = Job::factory()->create(['status' => 1]);

        $response = $this->getJson("/api/v1/jobs/{$job->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'requirements',
                    'location',
                    'category',
                    'job_type',
                ],
            ])
            ->assertJson([
                'data' => ['id' => $job->id],
            ]);
    }

    /**
     * Test returns 404 for non-existent job.
     */
    public function test_returns_404_for_nonexistent_job()
    {
        $response = $this->getJson('/api/v1/jobs/99999');

        $response->assertStatus(404);
    }

    /**
     * Test cannot view pending job via public API.
     */
    public function test_cannot_view_pending_job_publicly()
    {
        $job = Job::factory()->create(['status' => 0]);

        $response = $this->getJson("/api/v1/jobs/{$job->id}");

        $response->assertStatus(404);
    }

    /**
     * Test can get featured jobs.
     */
    public function test_can_get_featured_jobs()
    {
        Job::factory()->count(3)->create([
            'status' => 1,
            'featured' => true,
        ]);
        Job::factory()->count(5)->create([
            'status' => 1,
            'featured' => false,
        ]);

        $response = $this->getJson('/api/v1/jobs/featured');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);

        // Should only return featured jobs
        foreach ($response->json('data') as $job) {
            $this->assertTrue($job['featured']);
        }
    }

    /**
     * Test pagination works correctly.
     */
    public function test_pagination_works_correctly()
    {
        Job::factory()->count(25)->create(['status' => 1]);

        $response = $this->getJson('/api/v1/jobs?per_page=10&page=2');

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                    'per_page' => 10,
                    'total' => 25,
                ],
            ]);

        $this->assertCount(10, $response->json('data'));
    }

    /**
     * Test employer can create a job.
     */
    public function test_employer_can_create_job()
    {
        $user = User::factory()->create(['role' => 'employer']);
        $employer = Employer::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $category = Category::first();
        $jobType = JobType::first();

        $response = $this->postJson('/api/v1/employer/jobs', [
            'title' => 'Senior Developer',
            'description' => 'We are looking for a senior developer.',
            'requirements' => '5+ years experience',
            'category_id' => $category->id,
            'job_type_id' => $jobType->id,
            'location' => 'Remote',
            'salary_min' => 80000,
            'salary_max' => 120000,
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('jobs', [
            'title' => 'Senior Developer',
            'employer_id' => $employer->id,
        ]);
    }

    /**
     * Test employer can update their job.
     */
    public function test_employer_can_update_their_job()
    {
        $user = User::factory()->create(['role' => 'employer']);
        $employer = Employer::factory()->create(['user_id' => $user->id]);
        $job = Job::factory()->create(['employer_id' => $employer->id]);
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/employer/jobs/{$job->id}", [
            'title' => 'Updated Job Title',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('jobs', [
            'id' => $job->id,
            'title' => 'Updated Job Title',
        ]);
    }

    /**
     * Test employer cannot update another employer's job.
     */
    public function test_employer_cannot_update_others_job()
    {
        $user1 = User::factory()->create(['role' => 'employer']);
        $employer1 = Employer::factory()->create(['user_id' => $user1->id]);

        $user2 = User::factory()->create(['role' => 'employer']);
        $employer2 = Employer::factory()->create(['user_id' => $user2->id]);
        $job = Job::factory()->create(['employer_id' => $employer2->id]);

        Sanctum::actingAs($user1);

        $response = $this->putJson("/api/v1/employer/jobs/{$job->id}", [
            'title' => 'Hijacked Title',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test employer can delete their job.
     */
    public function test_employer_can_delete_their_job()
    {
        $user = User::factory()->create(['role' => 'employer']);
        $employer = Employer::factory()->create(['user_id' => $user->id]);
        $job = Job::factory()->create(['employer_id' => $employer->id]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/employer/jobs/{$job->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertSoftDeleted('jobs', ['id' => $job->id]);
    }

    /**
     * Test jobseeker cannot create jobs.
     */
    public function test_jobseeker_cannot_create_job()
    {
        $user = User::factory()->create(['role' => 'jobseeker']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/employer/jobs', [
            'title' => 'Test Job',
            'description' => 'Test description',
        ]);

        $response->assertStatus(403);
    }
}
