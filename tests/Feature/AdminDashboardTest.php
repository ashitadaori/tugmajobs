<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\Job;
use App\Models\Category;
use App\Models\JobType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function createAdmin()
    {
        $user = User::factory()->create(['user_type' => 'admin']);
        Admin::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    /**
     * Test admin can access dashboard.
     */
    public function test_admin_can_access_dashboard()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test non-admin cannot access admin dashboard.
     */
    public function test_non_admin_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create(['user_type' => 'jobseeker']);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    /**
     * Test admin can view all users.
     */
    public function test_admin_can_view_all_users()
    {
        $admin = $this->createAdmin();
        User::factory()->count(10)->create();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
    }

    /**
     * Test admin can approve pending jobs.
     */
    public function test_admin_can_approve_pending_jobs()
    {
        $admin = $this->createAdmin();
        $job = Job::factory()->create(['status' => 0]); // pending

        $response = $this->actingAs($admin)->patch("/admin/jobs/{$job->id}/approve");

        $this->assertDatabaseHas('jobs', [
            'id' => $job->id,
            'status' => 1, // approved
        ]);
    }

    /**
     * Test admin can reject pending jobs.
     */
    public function test_admin_can_reject_pending_jobs()
    {
        $admin = $this->createAdmin();
        $job = Job::factory()->create(['status' => 0]); // pending

        $response = $this->actingAs($admin)->patch("/admin/jobs/{$job->id}/reject", [
            'rejection_reason' => 'Job does not meet guidelines.',
        ]);

        $this->assertDatabaseHas('jobs', [
            'id' => $job->id,
            'status' => 2, // rejected
        ]);
    }

    /**
     * Test admin can create job categories.
     */
    public function test_admin_can_create_job_categories()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Healthcare',
            'slug' => 'healthcare',
            'icon' => 'fa-heart',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Healthcare',
        ]);
    }

    /**
     * Test admin can update job categories.
     */
    public function test_admin_can_update_job_categories()
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Updated Category',
            'slug' => $category->slug,
            'icon' => $category->icon,
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
        ]);
    }

    /**
     * Test admin can delete job categories.
     */
    public function test_admin_can_delete_job_categories()
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/categories/{$category->id}");

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    /**
     * Test admin can create job types.
     */
    public function test_admin_can_create_job_types()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/job-types', [
            'name' => 'Internship',
            'slug' => 'internship',
        ]);

        $this->assertDatabaseHas('job_types', [
            'name' => 'Internship',
        ]);
    }

    /**
     * Test admin can delete users.
     */
    public function test_admin_can_delete_users()
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/users/{$user->id}");

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /**
     * Test guest cannot access admin routes.
     */
    public function test_guest_cannot_access_admin_routes()
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }
}
