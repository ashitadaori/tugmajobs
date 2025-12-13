<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Jobseeker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class JobseekerDashboardTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    protected User $jobseeker;
    protected Jobseeker $jobseekerProfile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTestData();

        $this->jobseeker = $this->createJobseeker();
        $this->jobseekerProfile = $this->jobseeker->jobseeker;
    }

    /**
     * Test jobseeker can access dashboard.
     */
    public function test_jobseeker_can_access_dashboard()
    {
        $response = $this->actingAs($this->jobseeker)->get('/account/jobseeker');

        $response->assertStatus(200);
    }

    /**
     * Test employer cannot access jobseeker dashboard.
     */
    public function test_employer_cannot_access_jobseeker_dashboard()
    {
        $employer = $this->createEmployer();

        $response = $this->actingAs($employer)->get('/account/jobseeker');

        $response->assertStatus(403);
    }

    /**
     * Test jobseeker can view their applications.
     */
    public function test_jobseeker_can_view_their_applications()
    {
        $applications = JobApplication::factory()->count(3)->create([
            'user_id' => $this->jobseeker->id,
        ]);

        $response = $this->actingAs($this->jobseeker)->get('/account/jobseeker/applications');

        $response->assertStatus(200);
    }

    /**
     * Test jobseeker can apply to a job.
     */
    public function test_jobseeker_can_apply_to_job()
    {
        $job = $this->createJob();

        $response = $this->actingAs($this->jobseeker)->post("/jobs/{$job->id}/apply", [
            'cover_letter' => 'I am interested in this position.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('job_applications', [
            'job_id' => $job->id,
            'user_id' => $this->jobseeker->id,
        ]);
    }

    /**
     * Test jobseeker cannot apply to same job twice.
     */
    public function test_jobseeker_cannot_apply_to_same_job_twice()
    {
        $job = $this->createJob();

        JobApplication::factory()->create([
            'job_id' => $job->id,
            'user_id' => $this->jobseeker->id,
        ]);

        $response = $this->actingAs($this->jobseeker)->post("/jobs/{$job->id}/apply", [
            'cover_letter' => 'Another application.',
        ]);

        $response->assertSessionHasErrors();
    }

    /**
     * Test jobseeker can view single application.
     */
    public function test_jobseeker_can_view_application_details()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->jobseeker->id,
        ]);

        $response = $this->actingAs($this->jobseeker)->get("/account/jobseeker/applications/{$application->id}");

        $response->assertStatus(200);
    }

    /**
     * Test jobseeker cannot view others' applications.
     */
    public function test_jobseeker_cannot_view_others_applications()
    {
        $otherJobseeker = $this->createJobseeker();
        $application = JobApplication::factory()->create([
            'user_id' => $otherJobseeker->id,
        ]);

        $response = $this->actingAs($this->jobseeker)->get("/account/jobseeker/applications/{$application->id}");

        $response->assertStatus(403);
    }

    /**
     * Test jobseeker can withdraw pending application.
     */
    public function test_jobseeker_can_withdraw_pending_application()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->jobseeker->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->jobseeker)->delete("/account/jobseeker/applications/{$application->id}");

        $response->assertRedirect();

        $this->assertSoftDeleted('job_applications', ['id' => $application->id]);
    }

    /**
     * Test jobseeker cannot withdraw accepted application.
     */
    public function test_jobseeker_cannot_withdraw_accepted_application()
    {
        $application = JobApplication::factory()->create([
            'user_id' => $this->jobseeker->id,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($this->jobseeker)->delete("/account/jobseeker/applications/{$application->id}");

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('job_applications', ['id' => $application->id]);
    }

    /**
     * Test jobseeker can access profile page.
     */
    public function test_jobseeker_can_access_profile_page()
    {
        $response = $this->actingAs($this->jobseeker)->get('/account/jobseeker/profile');

        $response->assertStatus(200);
    }

    /**
     * Test jobseeker can update profile.
     */
    public function test_jobseeker_can_update_profile()
    {
        $response = $this->actingAs($this->jobseeker)->put('/account/jobseeker/profile', [
            'name' => 'Updated Name',
            'email' => $this->jobseeker->email,
            'professional_summary' => 'I am a software developer.',
            'skills' => 'PHP, Laravel, JavaScript',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $this->jobseeker->id,
            'name' => 'Updated Name',
        ]);
    }

    /**
     * Test jobseeker can save a job.
     */
    public function test_jobseeker_can_save_job()
    {
        $job = $this->createJob();

        $response = $this->actingAs($this->jobseeker)->post("/jobs/{$job->id}/save");

        $response->assertRedirect();

        $this->assertDatabaseHas('saved_jobs', [
            'job_id' => $job->id,
            'user_id' => $this->jobseeker->id,
        ]);
    }

    /**
     * Test jobseeker can view saved jobs.
     */
    public function test_jobseeker_can_view_saved_jobs()
    {
        $response = $this->actingAs($this->jobseeker)->get('/account/jobseeker/saved-jobs');

        $response->assertStatus(200);
    }

    /**
     * Test jobseeker can unsave a job.
     */
    public function test_jobseeker_can_unsave_job()
    {
        $job = $this->createJob();

        // First save the job
        $this->jobseeker->savedJobs()->attach($job->id);

        $response = $this->actingAs($this->jobseeker)->delete("/jobs/{$job->id}/save");

        $response->assertRedirect();

        $this->assertDatabaseMissing('saved_jobs', [
            'job_id' => $job->id,
            'user_id' => $this->jobseeker->id,
        ]);
    }

    /**
     * Test jobseeker dashboard shows statistics.
     */
    public function test_jobseeker_dashboard_shows_statistics()
    {
        JobApplication::factory()->count(5)->create([
            'user_id' => $this->jobseeker->id,
        ]);

        $response = $this->actingAs($this->jobseeker)->get('/account/jobseeker');

        $response->assertStatus(200);
    }

    /**
     * Test guest cannot access jobseeker dashboard.
     */
    public function test_guest_cannot_access_jobseeker_dashboard()
    {
        $response = $this->get('/account/jobseeker');

        $response->assertRedirect('/login');
    }
}
