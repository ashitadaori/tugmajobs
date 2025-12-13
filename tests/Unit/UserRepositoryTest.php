<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Jobseeker;
use App\Models\Employer;
use App\Repositories\UserRepository;
use App\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $cacheService = $this->app->make(CacheService::class);
        $this->repository = new UserRepository($cacheService);
    }

    /**
     * Test can find user by ID.
     */
    public function test_can_find_user_by_id()
    {
        $user = User::factory()->create();

        $found = $this->repository->findById($user->id);

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    /**
     * Test returns null for non-existent user.
     */
    public function test_returns_null_for_nonexistent_user()
    {
        $found = $this->repository->findById(99999);

        $this->assertNull($found);
    }

    /**
     * Test can find user by email.
     */
    public function test_can_find_user_by_email()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $found = $this->repository->findByEmail('test@example.com');

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    /**
     * Test can get all jobseekers.
     */
    public function test_can_get_jobseekers()
    {
        User::factory()->count(5)->create(['role' => 'jobseeker', 'is_active' => true]);
        User::factory()->count(3)->create(['role' => 'employer', 'is_active' => true]);

        $jobseekers = $this->repository->getJobseekers();

        $this->assertEquals(5, $jobseekers->total());
    }

    /**
     * Test can get all employers.
     */
    public function test_can_get_employers()
    {
        User::factory()->count(3)->create(['role' => 'employer', 'is_active' => true]);
        User::factory()->count(5)->create(['role' => 'jobseeker', 'is_active' => true]);

        $employers = $this->repository->getEmployers();

        $this->assertEquals(3, $employers->total());
    }

    /**
     * Test can get admins.
     */
    public function test_can_get_admins()
    {
        User::factory()->count(2)->create(['role' => 'admin', 'is_active' => true]);
        User::factory()->count(5)->create(['role' => 'jobseeker', 'is_active' => true]);

        $admins = $this->repository->getAdmins();

        $this->assertCount(2, $admins);
    }

    /**
     * Test can create user.
     */
    public function test_can_create_user()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'jobseeker',
        ];

        $user = $this->repository->create($data);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    }

    /**
     * Test can update user.
     */
    public function test_can_update_user()
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $updated = $this->repository->update($user, ['name' => 'New Name']);

        $this->assertEquals('New Name', $updated->name);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
        ]);
    }

    /**
     * Test can delete user.
     */
    public function test_can_delete_user()
    {
        $user = User::factory()->create();

        $result = $this->repository->delete($user);

        $this->assertTrue($result);
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /**
     * Test can search users by name.
     */
    public function test_can_search_users_by_name()
    {
        User::factory()->create(['name' => 'John Smith']);
        User::factory()->create(['name' => 'Jane Doe']);
        User::factory()->create(['name' => 'Johnny Appleseed']);

        $results = $this->repository->search('John');

        $this->assertEquals(2, $results->total());
    }

    /**
     * Test can search users by email.
     */
    public function test_can_search_users_by_email()
    {
        User::factory()->create(['email' => 'john@example.com']);
        User::factory()->create(['email' => 'jane@example.com']);

        $results = $this->repository->search('john@');

        $this->assertEquals(1, $results->total());
    }

    /**
     * Test can filter search by role.
     */
    public function test_can_filter_search_by_role()
    {
        User::factory()->create(['name' => 'John Employer', 'role' => 'employer']);
        User::factory()->create(['name' => 'John Jobseeker', 'role' => 'jobseeker']);

        $results = $this->repository->search('John', 'employer');

        $this->assertEquals(1, $results->total());
        $this->assertEquals('employer', $results->first()->role);
    }

    /**
     * Test can get user statistics.
     */
    public function test_can_get_user_statistics()
    {
        User::factory()->count(5)->create(['role' => 'jobseeker', 'is_active' => true]);
        User::factory()->count(3)->create(['role' => 'employer', 'is_active' => true]);
        User::factory()->count(1)->create(['role' => 'admin', 'is_active' => true]);

        $stats = $this->repository->getStatistics();

        $this->assertEquals(9, $stats['total']);
        $this->assertEquals(5, $stats['jobseekers']);
        $this->assertEquals(3, $stats['employers']);
        $this->assertEquals(1, $stats['admins']);
    }

    /**
     * Test can get recent users.
     */
    public function test_can_get_recent_users()
    {
        User::factory()->count(15)->create();

        $recent = $this->repository->getRecent(10);

        $this->assertCount(10, $recent);
    }

    /**
     * Test can update KYC status.
     */
    public function test_can_update_kyc_status()
    {
        $user = User::factory()->create(['kyc_status' => 'pending']);

        $updated = $this->repository->updateKycStatus($user, 'verified');

        $this->assertEquals('verified', $updated->kyc_status);
        $this->assertNotNull($updated->kyc_verified_at);
    }

    /**
     * Test can get users pending KYC.
     */
    public function test_can_get_pending_kyc_users()
    {
        User::factory()->count(3)->create(['kyc_status' => 'pending']);
        User::factory()->count(2)->create(['kyc_status' => 'verified']);

        $pending = $this->repository->getPendingKyc();

        $this->assertEquals(3, $pending->total());
    }
}
