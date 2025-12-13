<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Jobseeker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function createJobseeker()
    {
        $user = User::factory()->create(['user_type' => 'jobseeker']);
        Jobseeker::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    /**
     * Test job seeker can apply to a job.
     */
    public function test_jobseeker_can_apply_to_job()
    {
        $jobseeker = $this->createJobseeker();
        $job = Job::factory()->create(['status' => 1]);

        $response = $this->actingAs($jobseeker)->post("/jobs/{$job->id}/apply", [
            'cover_letter' => 'I am very interested in this position.',
        ]);

        $this->assertDatabaseHas('job_applications', [
            'job_id' => $job->id,
            'user_id' => $jobseeker->id,
        ]);

        $response->assertRedirect();
    }

    /**
     * Test job seeker cannot apply to same job twice.
     */
    public function test_jobseeker_cannot_apply_to_same_job_twice()
    {
        $jobseeker = $this->createJobseeker();
        $job = Job::factory()->create(['status' => 1]);

        // First application
        JobApplication::factory()->create([
            'job_id' => $job->id,
            'user_id' => $jobseeker->id,
        ]);

        // Second application attempt
        $response = $this->actingAs($jobseeker)->post("/jobs/{$job->id}/apply", [
            'cover_letter' => 'Another application.',
        ]);

        $response->assertSessionHasErrors();
    }

    /**
     * Test employer can view applications for their jobs.
     */
    public function test_employer_can_view_job_applications()
    {
        $employer = User::factory()->create(['user_type' => 'employer']);
        $job = Job::factory()->create(['user_id' => $employer->id]);
        $applications = JobApplication::factory()->count(3)->create(['job_id' => $job->id]);

        $response = $this->actingAs($employer)->get("/account/employer/jobs/{$job->id}/applicants");

        $response->assertStatus(200);
        foreach ($applications as $application) {
            $response->assertSee($application->user->name);
        }
    }

    /**
     * Test employer can update application status.
     */
    public function test_employer_can_update_application_status()
    {
        $employer = User::factory()->create(['user_type' => 'employer']);
        $job = Job::factory()->create(['user_id' => $employer->id]);
        $application = JobApplication::factory()->create([
            'job_id' => $job->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($employer)->patch("/account/employer/applications/{$application->id}/status", [
            'status' => 'reviewed',
        ]);

        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id,
            'status' => 'reviewed',
        ]);

        // Check status history
        $this->assertDatabaseHas('job_application_status_histories', [
            'job_application_id' => $application->id,
            'status' => 'reviewed',
        ]);
    }

    /**
     * Test job seeker can view their applications.
     */
    public function test_jobseeker_can_view_their_applications()
    {
        $jobseeker = $this->createJobseeker();
        $applications = JobApplication::factory()->count(3)->create([
            'user_id' => $jobseeker->id,
        ]);

        $response = $this->actingAs($jobseeker)->get('/account/jobseeker/applications');

        $response->assertStatus(200);
    }

    /**
     * Test job seeker can withdraw application.
     */
    public function test_jobseeker_can_withdraw_application()
    {
        $jobseeker = $this->createJobseeker();
        $application = JobApplication::factory()->create([
            'user_id' => $jobseeker->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($jobseeker)->delete("/account/jobseeker/applications/{$application->id}");

        $this->assertSoftDeleted('job_applications', ['id' => $application->id]);
    }

    /**
     * Test job seeker cannot withdraw accepted application.
     */
    public function test_jobseeker_cannot_withdraw_accepted_application()
    {
        $jobseeker = $this->createJobseeker();
        $application = JobApplication::factory()->create([
            'user_id' => $jobseeker->id,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($jobseeker)->delete("/account/jobseeker/applications/{$application->id}");

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('job_applications', ['id' => $application->id]);
    }

    /**
     * Test employer cannot update another employer's application.
     */
    public function test_employer_cannot_update_other_employers_application()
    {
        $employer1 = User::factory()->create(['user_type' => 'employer']);
        $employer2 = User::factory()->create(['user_type' => 'employer']);

        $job = Job::factory()->create(['user_id' => $employer2->id]);
        $application = JobApplication::factory()->create(['job_id' => $job->id]);

        $response = $this->actingAs($employer1)->patch("/account/employer/applications/{$application->id}/status", [
            'status' => 'reviewed',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test guest cannot apply to job.
     */
    public function test_guest_cannot_apply_to_job()
    {
        $job = Job::factory()->create();

        $response = $this->post("/jobs/{$job->id}/apply");

        $response->assertRedirect('/login');
    }

    /**
     * Test employer cannot apply to jobs.
     */
    public function test_employer_cannot_apply_to_job()
    {
        $employer = User::factory()->create(['user_type' => 'employer']);
        $job = Job::factory()->create();

        $response = $this->actingAs($employer)->post("/jobs/{$job->id}/apply", [
            'cover_letter' => 'I want to apply.',
        ]);

        $response->assertStatus(403);
    }
}
