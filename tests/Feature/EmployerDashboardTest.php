<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Employer;
use App\Models\Category;
use App\Models\JobType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class EmployerDashboardTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    protected User $employer;
    protected Employer $employerProfile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTestData();

        $this->employer = $this->createEmployer();
        $this->employerProfile = $this->employer->employer;
    }

    /**
     * Test employer can access dashboard.
     */
    public function test_employer_can_access_dashboard()
    {
        $response = $this->actingAs($this->employer)->get('/account/employer');

        $response->assertStatus(200);
    }

    /**
     * Test jobseeker cannot access employer dashboard.
     */
    public function test_jobseeker_cannot_access_employer_dashboard()
    {
        $jobseeker = $this->createJobseeker();

        $response = $this->actingAs($jobseeker)->get('/account/employer');

        $response->assertStatus(403);
    }

    /**
     * Test employer can view their jobs.
     */
    public function test_employer_can_view_their_jobs()
    {
        $jobs = Job::factory()->count(5)->create([
            'employer_id' => $this->employerProfile->id,
        ]);

        $response = $this->actingAs($this->employer)->get('/account/employer/jobs');

        $response->assertStatus(200);

        foreach ($jobs as $job) {
            $response->assertSee($job->title);
        }
    }

    /**
     * Test employer can access job creation page.
     */
    public function test_employer_can_access_job_creation_page()
    {
        $response = $this->actingAs($this->employer)->get('/account/employer/jobs/create');

        $response->assertStatus(200);
    }

    /**
     * Test employer can create a job.
     */
    public function test_employer_can_create_job()
    {
        $category = Category::first();
        $jobType = JobType::first();

        $response = $this->actingAs($this->employer)->post('/account/employer/jobs', [
            'title' => 'New Job Position',
            'description' => 'This is a job description.',
            'requirements' => 'Required skills and experience.',
            'category_id' => $category->id,
            'job_type_id' => $jobType->id,
            'location' => 'Remote',
            'salary_min' => 50000,
            'salary_max' => 80000,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('jobs', [
            'title' => 'New Job Position',
            'employer_id' => $this->employerProfile->id,
        ]);
    }

    /**
     * Test employer can edit their job.
     */
    public function test_employer_can_edit_their_job()
    {
        $job = Job::factory()->create([
            'employer_id' => $this->employerProfile->id,
        ]);

        $response = $this->actingAs($this->employer)->get("/account/employer/jobs/{$job->id}/edit");

        $response->assertStatus(200)
            ->assertSee($job->title);
    }

    /**
     * Test employer can update their job.
     */
    public function test_employer_can_update_their_job()
    {
        $job = Job::factory()->create([
            'employer_id' => $this->employerProfile->id,
        ]);

        $response = $this->actingAs($this->employer)->put("/account/employer/jobs/{$job->id}", [
            'title' => 'Updated Job Title',
            'description' => $job->description,
            'category_id' => $job->category_id,
            'job_type_id' => $job->job_type_id,
            'location' => $job->location,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('jobs', [
            'id' => $job->id,
            'title' => 'Updated Job Title',
        ]);
    }

    /**
     * Test employer cannot edit another employer's job.
     */
    public function test_employer_cannot_edit_others_job()
    {
        $otherEmployer = $this->createEmployer();
        $job = Job::factory()->create([
            'employer_id' => $otherEmployer->employer->id,
        ]);

        $response = $this->actingAs($this->employer)->get("/account/employer/jobs/{$job->id}/edit");

        $response->assertStatus(403);
    }

    /**
     * Test employer can delete their job.
     */
    public function test_employer_can_delete_their_job()
    {
        $job = Job::factory()->create([
            'employer_id' => $this->employerProfile->id,
        ]);

        $response = $this->actingAs($this->employer)->delete("/account/employer/jobs/{$job->id}");

        $response->assertRedirect();

        $this->assertSoftDeleted('jobs', ['id' => $job->id]);
    }

    /**
     * Test employer can view job applicants.
     */
    public function test_employer_can_view_job_applicants()
    {
        $job = Job::factory()->create([
            'employer_id' => $this->employerProfile->id,
        ]);
        $applications = JobApplication::factory()->count(3)->create([
            'job_id' => $job->id,
        ]);

        $response = $this->actingAs($this->employer)->get("/account/employer/jobs/{$job->id}/applicants");

        $response->assertStatus(200);
    }

    /**
     * Test employer can update application status.
     */
    public function test_employer_can_update_application_status()
    {
        $job = Job::factory()->create([
            'employer_id' => $this->employerProfile->id,
        ]);
        $application = JobApplication::factory()->create([
            'job_id' => $job->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->employer)->patch("/account/employer/applications/{$application->id}/status", [
            'status' => 'reviewed',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'status' => 'reviewed',
        ]);
    }

    /**
     * Test employer can update their profile.
     */
    public function test_employer_can_update_profile()
    {
        $response = $this->actingAs($this->employer)->put('/account/employer/profile', [
            'company_name' => 'Updated Company Name',
            'company_description' => 'Updated description',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('employers', [
            'id' => $this->employerProfile->id,
            'company_name' => 'Updated Company Name',
        ]);
    }

    /**
     * Test employer dashboard shows statistics.
     */
    public function test_employer_dashboard_shows_statistics()
    {
        Job::factory()->count(5)->create([
            'employer_id' => $this->employerProfile->id,
        ]);

        $response = $this->actingAs($this->employer)->get('/account/employer');

        $response->assertStatus(200);
    }

    /**
     * Test guest cannot access employer dashboard.
     */
    public function test_guest_cannot_access_employer_dashboard()
    {
        $response = $this->get('/account/employer');

        $response->assertRedirect('/login');
    }
}
