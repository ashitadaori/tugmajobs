<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Job;
use App\Models\Category;
use App\Models\JobType;
use App\Models\Employer;
use App\Models\EmployerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function createEmployer()
    {
        $user = User::factory()->create(['user_type' => 'employer']);
        $employer = Employer::factory()->create(['user_id' => $user->id]);
        EmployerProfile::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    /**
     * Test employer can view job creation page.
     */
    public function test_employer_can_view_job_creation_page()
    {
        $employer = $this->createEmployer();

        $response = $this->actingAs($employer)->get('/account/employer/jobs/create');

        $response->assertStatus(200);
    }

    /**
     * Test employer can create a job posting.
     */
    public function test_employer_can_create_job_posting()
    {
        $employer = $this->createEmployer();
        $category = Category::factory()->create();
        $jobType = JobType::factory()->create();

        $jobData = [
            'title' => 'Software Developer',
            'description' => 'We are looking for a skilled software developer.',
            'requirements' => 'PHP, Laravel, MySQL',
            'category_id' => $category->id,
            'job_type_id' => $jobType->id,
            'location' => 'Poblacion, Sta. Cruz, Davao del Sur',
            'salary_min' => 30000,
            'salary_max' => 50000,
            'vacancies' => 2,
        ];

        $response = $this->actingAs($employer)->post('/account/employer/jobs', $jobData);

        $this->assertDatabaseHas('jobs', [
            'title' => 'Software Developer',
            'user_id' => $employer->id,
        ]);

        $response->assertRedirect();
    }

    /**
     * Test job seeker can view all jobs.
     */
    public function test_jobseeker_can_view_all_jobs()
    {
        $jobseeker = User::factory()->create(['user_type' => 'jobseeker']);

        Job::factory()->count(5)->create([
            'status' => 1, // approved
        ]);

        $response = $this->actingAs($jobseeker)->get('/jobs');

        $response->assertStatus(200);
    }

    /**
     * Test job seeker can view job details.
     */
    public function test_jobseeker_can_view_job_details()
    {
        $jobseeker = User::factory()->create(['user_type' => 'jobseeker']);
        $job = Job::factory()->create(['status' => 1]);

        $response = $this->actingAs($jobseeker)->get("/jobs/{$job->id}");

        $response->assertStatus(200);
        $response->assertSee($job->title);
    }

    /**
     * Test employer can edit their own job.
     */
    public function test_employer_can_edit_own_job()
    {
        $employer = $this->createEmployer();
        $job = Job::factory()->create(['user_id' => $employer->id]);

        $response = $this->actingAs($employer)->get("/account/employer/jobs/{$job->id}/edit");

        $response->assertStatus(200);
    }

    /**
     * Test employer cannot edit another employer's job.
     */
    public function test_employer_cannot_edit_another_employers_job()
    {
        $employer1 = $this->createEmployer();
        $employer2 = $this->createEmployer();

        $job = Job::factory()->create(['user_id' => $employer2->id]);

        $response = $this->actingAs($employer1)->get("/account/employer/jobs/{$job->id}/edit");

        $response->assertStatus(403);
    }

    /**
     * Test employer can update their own job.
     */
    public function test_employer_can_update_own_job()
    {
        $employer = $this->createEmployer();
        $job = Job::factory()->create(['user_id' => $employer->id]);

        $updateData = [
            'title' => 'Updated Job Title',
            'description' => $job->description,
            'requirements' => $job->requirements,
            'category_id' => $job->category_id,
            'job_type_id' => $job->job_type_id,
            'location' => $job->location,
            'salary_min' => $job->salary_min,
            'salary_max' => $job->salary_max,
            'vacancies' => $job->vacancies,
        ];

        $response = $this->actingAs($employer)->put("/account/employer/jobs/{$job->id}", $updateData);

        $this->assertDatabaseHas('jobs', [
            'id' => $job->id,
            'title' => 'Updated Job Title',
        ]);
    }

    /**
     * Test employer can delete their own job.
     */
    public function test_employer_can_delete_own_job()
    {
        $employer = $this->createEmployer();
        $job = Job::factory()->create(['user_id' => $employer->id]);

        $response = $this->actingAs($employer)->delete("/account/employer/jobs/{$job->id}");

        $this->assertSoftDeleted('jobs', ['id' => $job->id]);
    }

    /**
     * Test job requires validation.
     */
    public function test_job_creation_requires_valid_data()
    {
        $employer = $this->createEmployer();

        $response = $this->actingAs($employer)->post('/account/employer/jobs', [
            'title' => '',
            'description' => '',
            'requirements' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'description', 'requirements']);
    }

    /**
     * Test guest cannot create job.
     */
    public function test_guest_cannot_create_job()
    {
        $response = $this->get('/account/employer/jobs/create');

        $response->assertRedirect('/login');
    }

    /**
     * Test job seeker cannot create job.
     */
    public function test_jobseeker_cannot_create_job()
    {
        $jobseeker = User::factory()->create(['user_type' => 'jobseeker']);

        $response = $this->actingAs($jobseeker)->get('/account/employer/jobs/create');

        $response->assertStatus(403);
    }
}
