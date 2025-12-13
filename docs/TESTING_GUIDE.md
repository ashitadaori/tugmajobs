# Testing Guide

## Overview

This document provides a comprehensive guide to testing the Job Portal application.

## Test Structure

```
tests/
├── Feature/                    # Integration/Feature tests
│   ├── AuthenticationTest.php
│   ├── JobManagementTest.php
│   ├── JobApplicationTest.php
│   └── AdminDashboardTest.php
├── Unit/                       # Unit tests
│   ├── AIJobMatchingServiceTest.php
│   ├── KMeansClusteringServiceTest.php
│   └── JobModelTest.php
├── TestCase.php               # Base test case
└── CreatesApplication.php     # Application bootstrap
```

## Running Tests

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
# Run only feature tests
php artisan test --testsuite=Feature

# Run only unit tests
php artisan test --testsuite=Unit
```

### Run Specific Test File
```bash
php artisan test tests/Feature/AuthenticationTest.php
```

### Run with Coverage
```bash
php artisan test --coverage
```

### Run with Detailed Output
```bash
php artisan test --verbose
```

## Test Database

Tests use an in-memory SQLite database for fast execution:

- **Database**: SQLite (:memory:)
- **Auto-migration**: Yes (via RefreshDatabase trait)
- **Seeding**: Optional (use DatabaseSeeder)

Configuration in `phpunit.xml`:
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

## Test Coverage

### Feature Tests (Integration Tests)

#### 1. AuthenticationTest
Tests user authentication flows:
- ✅ View login/registration pages
- ✅ User registration (jobseeker & employer)
- ✅ User login/logout
- ✅ Invalid credentials handling
- ✅ Form validation

#### 2. JobManagementTest
Tests job CRUD operations:
- ✅ Employer creates/edits/deletes jobs
- ✅ Job listing and detail views
- ✅ Authorization checks
- ✅ Validation rules

#### 3. JobApplicationTest
Tests application workflow:
- ✅ Jobseeker applies to jobs
- ✅ Prevent duplicate applications
- ✅ Employer views applications
- ✅ Status updates with history
- ✅ Application withdrawal

#### 4. AdminDashboardTest
Tests admin functionality:
- ✅ Admin access control
- ✅ Job approval/rejection
- ✅ Category/job type management
- ✅ User management

### Unit Tests

#### 1. AIJobMatchingServiceTest
- Service instantiation
- Matching algorithm structure
- Empty dataset handling

#### 2. KMeansClusteringServiceTest
- Clustering functionality
- Empty dataset handling

#### 3. JobModelTest
- Model relationships
- Soft deletes
- Scopes and attributes

## Writing Tests

### Basic Test Structure

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_example_functionality()
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get('/endpoint');

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('table', ['field' => 'value']);
    }
}
```

### Using Factories

```php
// Create a single model
$user = User::factory()->create();

// Create multiple models
$users = User::factory()->count(10)->create();

// Create with specific attributes
$user = User::factory()->create([
    'role' => 'admin',
    'email' => 'admin@example.com'
]);

// Create related models
$job = Job::factory()->create(['user_id' => $employer->id]);
```

### Testing Authentication

```php
// Act as authenticated user
$this->actingAs($user)->get('/dashboard');

// Test guest access
$this->get('/admin')->assertRedirect('/login');

// Test authorization
$this->actingAs($user)->get('/admin')->assertStatus(403);
```

### Testing Database

```php
// Assert record exists
$this->assertDatabaseHas('users', ['email' => 'test@example.com']);

// Assert record doesn't exist
$this->assertDatabaseMissing('users', ['email' => 'fake@example.com']);

// Assert soft deleted
$this->assertSoftDeleted('jobs', ['id' => $job->id]);
```

### Testing Responses

```php
// Status codes
$response->assertStatus(200);
$response->assertRedirect('/home');

// Content
$response->assertSee('Welcome');
$response->assertDontSee('Error');

// JSON
$response->assertJson(['success' => true]);

// Session
$response->assertSessionHas('message');
$response->assertSessionHasErrors(['email']);
```

## Factories

### Available Factories

- **UserFactory** - Creates users with roles
- **AdminFactory** - Creates admin profiles
- **EmployerFactory** - Creates employer profiles
- **EmployerProfileFactory** - Creates employer extended profiles
- **JobseekerFactory** - Creates jobseeker profiles
- **JobseekerProfileFactory** - Creates jobseeker extended profiles
- **JobFactory** - Creates job postings
- **JobApplicationFactory** - Creates job applications
- **CategoryFactory** - Creates job categories
- **JobTypeFactory** - Creates job types

### Factory Usage Examples

```php
// Create employer with profile
$employer = User::factory()->create(['role' => 'employer']);
$employerProfile = EmployerProfile::factory()->create(['user_id' => $employer->id]);

// Create job with category
$category = Category::factory()->create();
$job = Job::factory()->create(['category_id' => $category->id]);

// Create application
$application = JobApplication::factory()->create([
    'job_id' => $job->id,
    'status' => 'pending'
]);
```

## Best Practices

### 1. Test Independence
- Each test should be independent
- Use `RefreshDatabase` to reset database
- Don't rely on test execution order

### 2. Naming Conventions
- Use descriptive test names
- Format: `test_subject_scenario_expected_behavior`
- Example: `test_user_can_apply_to_job`

### 3. Arrange-Act-Assert
```php
public function test_example()
{
    // Arrange - Set up test data
    $user = User::factory()->create();

    // Act - Perform the action
    $response = $this->actingAs($user)->post('/endpoint', $data);

    // Assert - Verify the outcome
    $response->assertStatus(200);
    $this->assertDatabaseHas('table', ['data']);
}
```

### 4. One Assertion Per Concept
- Test one thing at a time
- Multiple assertions are OK if testing the same concept
- Use separate tests for different scenarios

### 5. Use Meaningful Assertions
```php
// Good
$this->assertEquals(5, $jobs->count());

// Better
$this->assertCount(5, $jobs);
```

## Continuous Integration

Tests should run automatically on:
- Every commit (via GitHub Actions)
- Pull requests
- Before deployment

See `.github/workflows/laravel.yml` for CI configuration.

## Test Coverage Goals

| Component | Target Coverage |
|-----------|----------------|
| Controllers | 80%+ |
| Models | 90%+ |
| Services | 85%+ |
| Overall | 75%+ |

## Troubleshooting

### Tests Running Slow
- Use SQLite in-memory database
- Mock external API calls
- Reduce factory data creation

### Database Errors
- Ensure `RefreshDatabase` trait is used
- Check migrations are up to date
- Verify `.env.testing` configuration

### Factory Errors
- Check model fillable attributes
- Verify relationships are correct
- Ensure required fields have default values

## Next Steps

1. **Increase Coverage**
   - Add tests for KYC verification
   - Add tests for notification system
   - Add tests for resume builder
   - Add tests for review system

2. **Add More Test Types**
   - Browser tests (Laravel Dusk)
   - API tests
   - Performance tests

3. **Automate**
   - Set up CI/CD pipeline
   - Add code coverage reporting
   - Integrate with Codecov/Coveralls

## Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Test Driven Development](https://martinfowler.com/bliki/TestDrivenDevelopment.html)

## Summary

✅ **Phase 1 Complete: Comprehensive Testing Suite**

- Created 4 feature test files (50+ test cases)
- Created 3 unit test files (15+ test cases)
- Created 6 model factories
- Configured SQLite for testing
- Documented testing practices

**Total Test Cases**: 65+
**Test Coverage**: ~30% (baseline)
**Next Goal**: 75%+ coverage
