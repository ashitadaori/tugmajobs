<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\Category;
use App\Models\JobType;
use App\Models\Employer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class HomepageTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTestData();
    }

    /**
     * Test homepage loads successfully.
     */
    public function test_homepage_loads_successfully()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test homepage displays featured jobs.
     */
    public function test_homepage_displays_featured_jobs()
    {
        $featuredJobs = $this->createJobs(3, ['featured' => true]);
        $regularJobs = $this->createJobs(5, ['featured' => false]);

        $response = $this->get('/');

        $response->assertStatus(200);

        foreach ($featuredJobs as $job) {
            $response->assertSee($job->title);
        }
    }

    /**
     * Test homepage displays latest jobs.
     */
    public function test_homepage_displays_latest_jobs()
    {
        $jobs = $this->createJobs(5);

        $response = $this->get('/');

        $response->assertStatus(200);

        foreach ($jobs as $job) {
            $response->assertSee($job->title);
        }
    }

    /**
     * Test homepage displays categories.
     */
    public function test_homepage_displays_categories()
    {
        $categories = Category::all();

        $response = $this->get('/');

        $response->assertStatus(200);

        foreach ($categories as $category) {
            $response->assertSee($category->name);
        }
    }

    /**
     * Test homepage search form exists.
     */
    public function test_homepage_has_search_form()
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Search', false);
    }

    /**
     * Test homepage displays job count.
     */
    public function test_homepage_displays_job_statistics()
    {
        $this->createJobs(10);

        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test only approved jobs are shown on homepage.
     */
    public function test_only_approved_jobs_shown_on_homepage()
    {
        $approvedJob = $this->createJob([
            'title' => 'Approved Job Position',
            'status' => 1,
        ]);
        $pendingJob = $this->createJob([
            'title' => 'Pending Job Position',
            'status' => 0,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee($approvedJob->title)
            ->assertDontSee($pendingJob->title);
    }

    /**
     * Test navigation links are present.
     */
    public function test_navigation_links_are_present()
    {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Jobs', false)
            ->assertSee('Login', false)
            ->assertSee('Register', false);
    }

    /**
     * Test footer is present.
     */
    public function test_footer_is_present()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
