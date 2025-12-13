<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Jobseeker;
use App\Models\Employer;
use App\Models\Category;
use App\Models\JobType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApplicationApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $jobseeker;
    protected User $employer;
    protected Job $job;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary categories and job types
        Category::factory()->count(3)->create();
        JobType::factory()->count(3)->create();

        // Create employer with job
        $this->employer = User::factory()->create(['role' => 'employer']);
        $employerProfile = Employer::factory()->create(['user_id' => $this->employer->id]);
        $this->job = Job::factory()->create([
            'employer_id' => $employerProfile->id,
            'status' => 1,
        ]);

        // Create jobseeker
        $this->jobseeker = User::factory()->create(['role' => 'jobseeker']);
        Jobseeker::factory()->create(['user_id' => $this->jobseeker->id]);
    }

    /**
     * Test jobseeker can apply to a job via API.
     */
    public function test_jobseeker_can_apply_to_job()
    {
        Sanctum::actingAs($this->jobseeker);

        $response = $this->postJson("/api/v1/jobs/{$this->job->id}/apply", [
            'cover_letter' => 'I am very interested in this position and believe I would be a great fit.',
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('job_applications', [
            'job_id' => $this->job->id,
            'user_id' => $this->jobseeker->id,
        ]);
    }

    /**
     * Test jobseeker cannot apply to same job twice.
     */
    public function test_jobseeker_cannot_apply_to_same_job_twice()
    {
        Sanctum::actingAs($this->jobseeker);

        // First application
        JobApplication::factory()->create([
            'job_id' => $this->job->id,
            'user_id' => $this->jobseeker->id,
        ]);

        // Second application attempt
        $response = $this->postJson("/api/v1/jobs/{$this->job->id}/apply", [
            'cover_letter' => 'Another application attempt.',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    /**
     * Test jobseeker can view their applications.
     */
    public function test_jobseeker_can_view_their_applications()
    {
        Sanctum::actingAs($this->jobseeker);

        JobApplication::factory()->count(3)->create([
            'user_id' => $this->jobseeker->id,
        ]);

        $response = $this->getJson('/api/v1/applications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'job_id', 'status', 'applied_date'],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test jobseeker can view single application.
     */
    public function test_jobseeker_can_view_single_application()
    {
        Sanctum::actingAs($this->jobseeker);

        $application = JobApplication::factory()->create([
            'user_id' => $this->jobseeker->id,
            'job_id' => $this->job->id,
        ]);

        $response = $this->getJson("/api/v1/applications/{$application->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => ['id' => $application->id],
            ]);
    }

    /**
     * Test jobseeker cannot view another user's application.
     */
    public function test_jobseeker_cannot_view_others_application()
    {
        $otherUser = User::factory()->create(['role' => 'jobseeker']);
        Jobseeker::factory()->create(['user_id' => $otherUser->id]);

        $application = JobApplication::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($this->jobseeker);

        $response = $this->getJson("/api/v1/applications/{$application->id}");

        $response->assertStatus(403);
    }

    /**
     * Test jobseeker can withdraw pending application.
     */
    public function test_jobseeker_can_withdraw_pending_application()
    {
        Sanctum::actingAs($this->jobseeker);

        $application = JobApplication::factory()->create([
            'user_id' => $this->jobseeker->id,
            'status' => 'pending',
        ]);

        $response = $this->deleteJson("/api/v1/applications/{$application->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertSoftDeleted('job_applications', ['id' => $application->id]);
    }

    /**
     * Test employer cannot apply to jobs.
     */
    public function test_employer_cannot_apply_to_job()
    {
        Sanctum::actingAs($this->employer);

        $response = $this->postJson("/api/v1/jobs/{$this->job->id}/apply", [
            'cover_letter' => 'I want to apply.',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test employer can view applications for their jobs.
     */
    public function test_employer_can_view_job_applications()
    {
        Sanctum::actingAs($this->employer);

        JobApplication::factory()->count(5)->create([
            'job_id' => $this->job->id,
        ]);

        $response = $this->getJson("/api/v1/employer/jobs/{$this->job->id}/applications");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'user_id', 'status'],
                ],
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    /**
     * Test employer can update application status.
     */
    public function test_employer_can_update_application_status()
    {
        Sanctum::actingAs($this->employer);

        $application = JobApplication::factory()->create([
            'job_id' => $this->job->id,
            'status' => 'pending',
        ]);

        $response = $this->patchJson("/api/v1/employer/applications/{$application->id}/status", [
            'status' => 'reviewed',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'status' => 'reviewed',
        ]);
    }

    /**
     * Test employer can shortlist application.
     */
    public function test_employer_can_shortlist_application()
    {
        Sanctum::actingAs($this->employer);

        $application = JobApplication::factory()->create([
            'job_id' => $this->job->id,
            'shortlisted' => false,
        ]);

        $response = $this->patchJson("/api/v1/employer/applications/{$application->id}/shortlist");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'shortlisted' => true,
        ]);
    }

    /**
     * Test employer cannot update applications for other employers' jobs.
     */
    public function test_employer_cannot_update_others_applications()
    {
        $otherEmployer = User::factory()->create(['role' => 'employer']);
        $otherEmployerProfile = Employer::factory()->create(['user_id' => $otherEmployer->id]);
        $otherJob = Job::factory()->create(['employer_id' => $otherEmployerProfile->id]);

        $application = JobApplication::factory()->create([
            'job_id' => $otherJob->id,
        ]);

        Sanctum::actingAs($this->employer);

        $response = $this->patchJson("/api/v1/employer/applications/{$application->id}/status", [
            'status' => 'reviewed',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test guest cannot apply to jobs.
     */
    public function test_guest_cannot_apply_to_job()
    {
        $response = $this->postJson("/api/v1/jobs/{$this->job->id}/apply", [
            'cover_letter' => 'I want to apply.',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test cannot apply to non-existent job.
     */
    public function test_cannot_apply_to_nonexistent_job()
    {
        Sanctum::actingAs($this->jobseeker);

        $response = $this->postJson('/api/v1/jobs/99999/apply', [
            'cover_letter' => 'I want to apply.',
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test cannot apply to closed job.
     */
    public function test_cannot_apply_to_closed_job()
    {
        $closedJob = Job::factory()->create(['status' => 0]);

        Sanctum::actingAs($this->jobseeker);

        $response = $this->postJson("/api/v1/jobs/{$closedJob->id}/apply", [
            'cover_letter' => 'I want to apply.',
        ]);

        $response->assertStatus(422);
    }
}
