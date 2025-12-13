<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can view login page.
     */
    public function test_user_can_view_login_page()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Test user can view registration page.
     */
    public function test_user_can_view_registration_page()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /**
     * Test user can register as job seeker.
     */
    public function test_user_can_register_as_jobseeker()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'jobseeker',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'user_type' => 'jobseeker',
        ]);

        $response->assertRedirect('/account/jobseeker');
    }

    /**
     * Test user can register as employer.
     */
    public function test_user_can_register_as_employer()
    {
        $response = $this->post('/register', [
            'name' => 'Jane Employer',
            'email' => 'jane@company.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'employer',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@company.com',
            'user_type' => 'employer',
        ]);

        $response->assertRedirect('/account/employer');
    }

    /**
     * Test registered user can login.
     */
    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'jobseeker',
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    /**
     * Test user cannot login with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    /**
     * Test authenticated user can logout.
     */
    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /**
     * Test validation errors on registration.
     */
    public function test_registration_requires_valid_data()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => '123',
            'password_confirmation' => '456',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }
}
