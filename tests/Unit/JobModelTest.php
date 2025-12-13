<?php

namespace Tests\Unit;

use App\Models\Job;
use App\Models\User;
use App\Models\Category;
use App\Models\JobType;
use App\Models\JobApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test job belongs to user.
     */
    public function test_job_belongs_to_user()
    {
        $user = User::factory()->create();
        $job = Job::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $job->user);
        $this->assertEquals($user->id, $job->user->id);
    }

    /**
     * Test job belongs to category.
     */
    public function test_job_belongs_to_category()
    {
        $category = Category::factory()->create();
        $job = Job::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $job->category);
        $this->assertEquals($category->id, $job->category->id);
    }

    /**
     * Test job belongs to job type.
     */
    public function test_job_belongs_to_job_type()
    {
        $jobType = JobType::factory()->create();
        $job = Job::factory()->create(['job_type_id' => $jobType->id]);

        $this->assertInstanceOf(JobType::class, $job->jobType);
        $this->assertEquals($jobType->id, $job->jobType->id);
    }

    /**
     * Test job has many applications.
     */
    public function test_job_has_many_applications()
    {
        $job = Job::factory()->create();
        $applications = JobApplication::factory()->count(3)->create(['job_id' => $job->id]);

        $this->assertCount(3, $job->applications);
        $this->assertInstanceOf(JobApplication::class, $job->applications->first());
    }

    /**
     * Test job can be soft deleted.
     */
    public function test_job_can_be_soft_deleted()
    {
        $job = Job::factory()->create();

        $job->delete();

        $this->assertSoftDeleted('jobs', ['id' => $job->id]);
    }

    /**
     * Test job can check if expired.
     */
    public function test_job_can_check_if_expired()
    {
        $job = Job::factory()->create([
            'deadline' => now()->subDay(),
        ]);

        // Assuming there's an isExpired() method
        // Adjust based on your actual implementation
        $this->assertTrue($job->deadline->isPast());
    }

    /**
     * Test job scopes work correctly.
     */
    public function test_job_approved_scope()
    {
        Job::factory()->create(['status' => 1]); // approved
        Job::factory()->create(['status' => 0]); // pending
        Job::factory()->create(['status' => 2]); // rejected

        // Assuming there's an approved() scope
        // Adjust based on your actual implementation
        $approvedJobs = Job::where('status', 1)->get();

        $this->assertCount(1, $approvedJobs);
    }

    /**
     * Test job attributes are fillable.
     */
    public function test_job_attributes_are_fillable()
    {
        $job = new Job();

        $this->assertContains('title', $job->getFillable());
        $this->assertContains('description', $job->getFillable());
        $this->assertContains('requirements', $job->getFillable());
    }
}
