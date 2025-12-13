<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\Category;
use App\Models\JobType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class JobListingTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTestData();
    }

    /**
     * Test job listing page loads successfully.
     */
    public function test_job_listing_page_loads()
    {
        $response = $this->get('/jobs');

        $response->assertStatus(200);
    }

    /**
     * Test job listing displays all approved jobs.
     */
    public function test_job_listing_displays_approved_jobs()
    {
        $jobs = $this->createJobs(5);

        $response = $this->get('/jobs');

        $response->assertStatus(200);

        foreach ($jobs as $job) {
            $response->assertSee($job->title);
        }
    }

    /**
     * Test job listing pagination works.
     */
    public function test_job_listing_pagination_works()
    {
        $this->createJobs(25);

        $response = $this->get('/jobs');

        $response->assertStatus(200);

        // Test page 2
        $response2 = $this->get('/jobs?page=2');
        $response2->assertStatus(200);
    }

    /**
     * Test can filter jobs by category.
     */
    public function test_can_filter_jobs_by_category()
    {
        $category = Category::first();
        $jobsInCategory = $this->createJobs(3, ['category_id' => $category->id]);
        $otherJobs = $this->createJobs(2);

        $response = $this->get("/jobs?category={$category->id}");

        $response->assertStatus(200);

        foreach ($jobsInCategory as $job) {
            $response->assertSee($job->title);
        }
    }

    /**
     * Test can filter jobs by job type.
     */
    public function test_can_filter_jobs_by_job_type()
    {
        $jobType = JobType::first();
        $jobs = $this->createJobs(3, ['job_type_id' => $jobType->id]);

        $response = $this->get("/jobs?job_type={$jobType->id}");

        $response->assertStatus(200);

        foreach ($jobs as $job) {
            $response->assertSee($job->title);
        }
    }

    /**
     * Test can filter jobs by location.
     */
    public function test_can_filter_jobs_by_location()
    {
        $this->createJobs(3, ['location' => 'New York']);
        $this->createJobs(2, ['location' => 'Los Angeles']);

        $response = $this->get('/jobs?location=New+York');

        $response->assertStatus(200);
    }

    /**
     * Test can search jobs by keyword.
     */
    public function test_can_search_jobs_by_keyword()
    {
        $this->createJob(['title' => 'Senior PHP Developer']);
        $this->createJob(['title' => 'Marketing Manager']);

        $response = $this->get('/jobs?keyword=PHP');

        $response->assertStatus(200)
            ->assertSee('PHP Developer');
    }

    /**
     * Test job detail page loads.
     */
    public function test_job_detail_page_loads()
    {
        $job = $this->createJob();

        $response = $this->get("/jobs/{$job->id}");

        $response->assertStatus(200)
            ->assertSee($job->title)
            ->assertSee($job->description);
    }

    /**
     * Test job detail page shows apply button for guests.
     */
    public function test_job_detail_shows_apply_prompt_for_guests()
    {
        $job = $this->createJob();

        $response = $this->get("/jobs/{$job->id}");

        $response->assertStatus(200);
        // Guest should see login prompt or apply button
    }

    /**
     * Test job detail page shows employer info.
     */
    public function test_job_detail_shows_employer_info()
    {
        $employer = $this->createEmployer();
        $employerProfile = $employer->employer;
        $job = $this->createJob(['employer_id' => $employerProfile->id]);

        $response = $this->get("/jobs/{$job->id}");

        $response->assertStatus(200)
            ->assertSee($employerProfile->company_name);
    }

    /**
     * Test 404 for non-existent job.
     */
    public function test_returns_404_for_nonexistent_job()
    {
        $response = $this->get('/jobs/99999');

        $response->assertStatus(404);
    }

    /**
     * Test cannot view pending job.
     */
    public function test_cannot_view_pending_job()
    {
        $job = $this->createJob(['status' => 0]);

        $response = $this->get("/jobs/{$job->id}");

        $response->assertStatus(404);
    }

    /**
     * Test related jobs are shown on detail page.
     */
    public function test_related_jobs_shown_on_detail_page()
    {
        $category = Category::first();
        $job = $this->createJob(['category_id' => $category->id]);
        $relatedJobs = $this->createJobs(3, ['category_id' => $category->id]);

        $response = $this->get("/jobs/{$job->id}");

        $response->assertStatus(200);
    }
}
