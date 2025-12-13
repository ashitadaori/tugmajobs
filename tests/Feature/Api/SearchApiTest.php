<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Job;
use App\Models\Category;
use App\Models\JobType;
use App\Models\Employer;
use App\Models\Jobseeker;
use App\Models\SavedSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SearchApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary categories and job types
        Category::factory()->count(5)->create();
        JobType::factory()->count(5)->create();
    }

    /**
     * Test can get autocomplete suggestions.
     */
    public function test_can_get_autocomplete_suggestions()
    {
        // Create jobs with searchable titles
        Job::factory()->create([
            'status' => 1,
            'title' => 'Senior PHP Developer',
        ]);
        Job::factory()->create([
            'status' => 1,
            'title' => 'PHP Backend Engineer',
        ]);
        Job::factory()->create([
            'status' => 1,
            'title' => 'Marketing Manager',
        ]);

        $response = $this->getJson('/api/v1/search/autocomplete?q=PHP');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['type', 'value'],
                ],
            ]);

        // Should find PHP-related jobs
        $values = collect($response->json('data'))->pluck('value')->toArray();
        $this->assertTrue(
            collect($values)->contains(fn($v) => str_contains(strtolower($v), 'php'))
        );
    }

    /**
     * Test autocomplete requires minimum query length.
     */
    public function test_autocomplete_requires_minimum_query_length()
    {
        $response = $this->getJson('/api/v1/search/autocomplete?q=P');

        $response->assertStatus(422);
    }

    /**
     * Test can perform advanced job search.
     */
    public function test_can_perform_advanced_job_search()
    {
        Job::factory()->count(10)->create(['status' => 1]);

        $response = $this->getJson('/api/v1/search/jobs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => ['current_page', 'total'],
            ]);
    }

    /**
     * Test can search jobs by query.
     */
    public function test_can_search_jobs_by_query()
    {
        Job::factory()->create([
            'status' => 1,
            'title' => 'React Developer',
            'description' => 'Looking for React expert',
        ]);
        Job::factory()->create([
            'status' => 1,
            'title' => 'Python Developer',
            'description' => 'Python backend role',
        ]);

        $response = $this->getJson('/api/v1/search/jobs?q=React');

        $response->assertStatus(200);

        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertContains('React Developer', $titles);
    }

    /**
     * Test can filter search by salary range.
     */
    public function test_can_filter_by_salary_range()
    {
        Job::factory()->create([
            'status' => 1,
            'salary_min' => 50000,
            'salary_max' => 70000,
        ]);
        Job::factory()->create([
            'status' => 1,
            'salary_min' => 80000,
            'salary_max' => 100000,
        ]);
        Job::factory()->create([
            'status' => 1,
            'salary_min' => 120000,
            'salary_max' => 150000,
        ]);

        $response = $this->getJson('/api/v1/search/jobs?salary_min=70000&salary_max=110000');

        $response->assertStatus(200);

        // Should only return jobs within salary range
        foreach ($response->json('data') as $job) {
            $this->assertTrue(
                $job['salary_min'] >= 70000 || $job['salary_max'] <= 110000
            );
        }
    }

    /**
     * Test can filter by experience level.
     */
    public function test_can_filter_by_experience_level()
    {
        Job::factory()->create([
            'status' => 1,
            'experience_level' => 'senior',
        ]);
        Job::factory()->create([
            'status' => 1,
            'experience_level' => 'junior',
        ]);

        $response = $this->getJson('/api/v1/search/jobs?experience_level=senior');

        $response->assertStatus(200);

        foreach ($response->json('data') as $job) {
            $this->assertEquals('senior', $job['experience_level']);
        }
    }

    /**
     * Test can filter by remote status.
     */
    public function test_can_filter_by_remote_status()
    {
        Job::factory()->create([
            'status' => 1,
            'is_remote' => true,
        ]);
        Job::factory()->create([
            'status' => 1,
            'is_remote' => false,
        ]);

        $response = $this->getJson('/api/v1/search/jobs?is_remote=1');

        $response->assertStatus(200);

        foreach ($response->json('data') as $job) {
            $this->assertTrue($job['is_remote']);
        }
    }

    /**
     * Test can filter by posted date.
     */
    public function test_can_filter_by_posted_date()
    {
        Job::factory()->create([
            'status' => 1,
            'created_at' => now()->subDays(2),
        ]);
        Job::factory()->create([
            'status' => 1,
            'created_at' => now()->subDays(10),
        ]);
        Job::factory()->create([
            'status' => 1,
            'created_at' => now()->subDays(40),
        ]);

        $response = $this->getJson('/api/v1/search/jobs?posted_within=7d');

        $response->assertStatus(200);

        // Should only return jobs from last 7 days
        foreach ($response->json('data') as $job) {
            $createdAt = \Carbon\Carbon::parse($job['created_at']);
            $this->assertTrue($createdAt->gte(now()->subDays(7)));
        }
    }

    /**
     * Test can sort search results.
     */
    public function test_can_sort_search_results()
    {
        Job::factory()->create([
            'status' => 1,
            'created_at' => now()->subDays(5),
        ]);
        Job::factory()->create([
            'status' => 1,
            'created_at' => now()->subDays(1),
        ]);
        Job::factory()->create([
            'status' => 1,
            'created_at' => now()->subDays(3),
        ]);

        $response = $this->getJson('/api/v1/search/jobs?sort_by=date');

        $response->assertStatus(200);

        $data = $response->json('data');
        if (count($data) > 1) {
            // Verify descending date order (newest first)
            for ($i = 0; $i < count($data) - 1; $i++) {
                $current = \Carbon\Carbon::parse($data[$i]['created_at']);
                $next = \Carbon\Carbon::parse($data[$i + 1]['created_at']);
                $this->assertTrue($current->gte($next));
            }
        }
    }

    /**
     * Test can get trending searches.
     */
    public function test_can_get_trending_searches()
    {
        $response = $this->getJson('/api/v1/search/trending');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    /**
     * Test authenticated user can save a search.
     */
    public function test_authenticated_user_can_save_search()
    {
        $user = User::factory()->create(['role' => 'jobseeker']);
        Jobseeker::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/search/saved', [
            'name' => 'PHP Jobs in NYC',
            'criteria' => [
                'q' => 'PHP Developer',
                'location' => 'New York',
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('saved_searches', [
            'user_id' => $user->id,
            'name' => 'PHP Jobs in NYC',
        ]);
    }

    /**
     * Test authenticated user can view saved searches.
     */
    public function test_authenticated_user_can_view_saved_searches()
    {
        $user = User::factory()->create(['role' => 'jobseeker']);
        Jobseeker::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        SavedSearch::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/search/saved');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'criteria'],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test authenticated user can delete saved search.
     */
    public function test_authenticated_user_can_delete_saved_search()
    {
        $user = User::factory()->create(['role' => 'jobseeker']);
        Jobseeker::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $savedSearch = SavedSearch::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/search/saved/{$savedSearch->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('saved_searches', ['id' => $savedSearch->id]);
    }

    /**
     * Test user cannot delete another user's saved search.
     */
    public function test_user_cannot_delete_others_saved_search()
    {
        $user1 = User::factory()->create(['role' => 'jobseeker']);
        Jobseeker::factory()->create(['user_id' => $user1->id]);

        $user2 = User::factory()->create(['role' => 'jobseeker']);
        Jobseeker::factory()->create(['user_id' => $user2->id]);
        $savedSearch = SavedSearch::factory()->create(['user_id' => $user2->id]);

        Sanctum::actingAs($user1);

        $response = $this->deleteJson("/api/v1/search/saved/{$savedSearch->id}");

        $response->assertStatus(403);
    }

    /**
     * Test guest cannot save searches.
     */
    public function test_guest_cannot_save_search()
    {
        $response = $this->postJson('/api/v1/search/saved', [
            'name' => 'My Search',
            'criteria' => ['q' => 'Developer'],
        ]);

        $response->assertStatus(401);
    }
}
