<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create job categories table if it doesn't exist
        if (!Schema::hasTable('job_categories')) {
            Schema::create('job_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index('is_active');
                $table->index('sort_order');
            });
        }

        // Create job skills table for skill management
        if (!Schema::hasTable('job_skills')) {
            Schema::create('job_skills', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('category')->nullable(); // 'technical', 'soft', 'language', etc.
                $table->text('description')->nullable();
                $table->integer('popularity_score')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index('category');
                $table->index('is_active');
                $table->index('popularity_score');
            });
        }

        // Create industries table for employer classification
        if (!Schema::hasTable('industries')) {
            Schema::create('industries', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index('is_active');
                $table->index('sort_order');
            });
        }

        // Create locations table for job locations
        if (!Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // City or Region name
                $table->string('type')->default('city'); // 'city', 'region', 'country'
                $table->string('country')->default('Philippines');
                $table->string('state_province')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->integer('job_count')->default(0); // Cached job count
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index(['country', 'type']);
                $table->index('is_active');
                $table->index('job_count');
            });
        }

        // Create company sizes reference table
        if (!Schema::hasTable('company_sizes')) {
            Schema::create('company_sizes', function (Blueprint $table) {
                $table->id();
                $table->string('range'); // '1-10', '11-50', '51-200', etc.
                $table->integer('min_employees');
                $table->integer('max_employees')->nullable(); // null for '1000+'
                $table->string('label'); // 'Startup', 'Small', 'Medium', 'Large', 'Enterprise'
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                
                $table->index('sort_order');
            });
        }

        // Insert default data for company sizes
        DB::table('company_sizes')->insert([
            ['range' => '1-10', 'min_employees' => 1, 'max_employees' => 10, 'label' => 'Startup', 'sort_order' => 1],
            ['range' => '11-50', 'min_employees' => 11, 'max_employees' => 50, 'label' => 'Small', 'sort_order' => 2],
            ['range' => '51-200', 'min_employees' => 51, 'max_employees' => 200, 'label' => 'Medium', 'sort_order' => 3],
            ['range' => '201-500', 'min_employees' => 201, 'max_employees' => 500, 'label' => 'Large', 'sort_order' => 4],
            ['range' => '501-1000', 'min_employees' => 501, 'max_employees' => 1000, 'label' => 'Large', 'sort_order' => 5],
            ['range' => '1000+', 'min_employees' => 1000, 'max_employees' => null, 'label' => 'Enterprise', 'sort_order' => 6],
        ]);

        // Insert default job categories
        DB::table('job_categories')->insert([
            ['name' => 'Information Technology', 'slug' => 'information-technology', 'description' => 'Software development, IT support, cybersecurity, and related tech roles', 'icon' => 'fas fa-laptop-code', 'sort_order' => 1],
            ['name' => 'Marketing & Sales', 'slug' => 'marketing-sales', 'description' => 'Digital marketing, sales, advertising, and business development roles', 'icon' => 'fas fa-bullhorn', 'sort_order' => 2],
            ['name' => 'Finance & Accounting', 'slug' => 'finance-accounting', 'description' => 'Financial analysis, accounting, bookkeeping, and treasury roles', 'icon' => 'fas fa-calculator', 'sort_order' => 3],
            ['name' => 'Human Resources', 'slug' => 'human-resources', 'description' => 'HR management, recruitment, training, and employee relations', 'icon' => 'fas fa-users', 'sort_order' => 4],
            ['name' => 'Customer Service', 'slug' => 'customer-service', 'description' => 'Customer support, call center, and client relations roles', 'icon' => 'fas fa-headset', 'sort_order' => 5],
            ['name' => 'Healthcare', 'slug' => 'healthcare', 'description' => 'Medical, nursing, pharmacy, and healthcare administration roles', 'icon' => 'fas fa-stethoscope', 'sort_order' => 6],
            ['name' => 'Education', 'slug' => 'education', 'description' => 'Teaching, training, curriculum development, and educational administration', 'icon' => 'fas fa-graduation-cap', 'sort_order' => 7],
            ['name' => 'Engineering', 'slug' => 'engineering', 'description' => 'Civil, mechanical, electrical, and other engineering disciplines', 'icon' => 'fas fa-cogs', 'sort_order' => 8],
            ['name' => 'Design & Creative', 'slug' => 'design-creative', 'description' => 'Graphic design, UI/UX, content creation, and creative roles', 'icon' => 'fas fa-palette', 'sort_order' => 9],
            ['name' => 'Operations & Logistics', 'slug' => 'operations-logistics', 'description' => 'Supply chain, logistics, operations management, and related roles', 'icon' => 'fas fa-truck', 'sort_order' => 10],
        ]);

        // Insert popular job skills
        DB::table('job_skills')->insert([
            // Technical Skills
            ['name' => 'PHP', 'slug' => 'php', 'category' => 'technical', 'description' => 'PHP programming language', 'popularity_score' => 100],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'category' => 'technical', 'description' => 'JavaScript programming language', 'popularity_score' => 95],
            ['name' => 'Laravel', 'slug' => 'laravel', 'category' => 'technical', 'description' => 'Laravel PHP framework', 'popularity_score' => 85],
            ['name' => 'React', 'slug' => 'react', 'category' => 'technical', 'description' => 'React JavaScript library', 'popularity_score' => 90],
            ['name' => 'Vue.js', 'slug' => 'vuejs', 'category' => 'technical', 'description' => 'Vue.js JavaScript framework', 'popularity_score' => 80],
            ['name' => 'Python', 'slug' => 'python', 'category' => 'technical', 'description' => 'Python programming language', 'popularity_score' => 88],
            ['name' => 'MySQL', 'slug' => 'mysql', 'category' => 'technical', 'description' => 'MySQL database management', 'popularity_score' => 85],
            ['name' => 'HTML/CSS', 'slug' => 'html-css', 'category' => 'technical', 'description' => 'HTML and CSS web technologies', 'popularity_score' => 95],
            
            // Soft Skills
            ['name' => 'Communication', 'slug' => 'communication', 'category' => 'soft', 'description' => 'Effective verbal and written communication', 'popularity_score' => 100],
            ['name' => 'Leadership', 'slug' => 'leadership', 'category' => 'soft', 'description' => 'Team leadership and management skills', 'popularity_score' => 85],
            ['name' => 'Problem Solving', 'slug' => 'problem-solving', 'category' => 'soft', 'description' => 'Analytical and critical thinking skills', 'popularity_score' => 90],
            ['name' => 'Teamwork', 'slug' => 'teamwork', 'category' => 'soft', 'description' => 'Collaboration and team coordination', 'popularity_score' => 95],
            ['name' => 'Time Management', 'slug' => 'time-management', 'category' => 'soft', 'description' => 'Efficient time and task management', 'popularity_score' => 88],
            
            // Languages
            ['name' => 'English', 'slug' => 'english', 'category' => 'language', 'description' => 'English language proficiency', 'popularity_score' => 100],
            ['name' => 'Filipino', 'slug' => 'filipino', 'category' => 'language', 'description' => 'Filipino language proficiency', 'popularity_score' => 95],
            ['name' => 'Mandarin', 'slug' => 'mandarin', 'category' => 'language', 'description' => 'Mandarin Chinese language proficiency', 'popularity_score' => 70],
            ['name' => 'Japanese', 'slug' => 'japanese', 'category' => 'language', 'description' => 'Japanese language proficiency', 'popularity_score' => 65],
        ]);

        // Insert common industries
        DB::table('industries')->insert([
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Software development, IT services, and tech companies', 'sort_order' => 1],
            ['name' => 'Banking & Finance', 'slug' => 'banking-finance', 'description' => 'Banks, financial institutions, and fintech companies', 'sort_order' => 2],
            ['name' => 'Healthcare & Medical', 'slug' => 'healthcare-medical', 'description' => 'Hospitals, clinics, pharmaceuticals, and medical services', 'sort_order' => 3],
            ['name' => 'Retail & E-commerce', 'slug' => 'retail-ecommerce', 'description' => 'Retail stores, online marketplaces, and consumer goods', 'sort_order' => 4],
            ['name' => 'Manufacturing', 'slug' => 'manufacturing', 'description' => 'Production, assembly, and industrial manufacturing', 'sort_order' => 5],
            ['name' => 'Education', 'slug' => 'education', 'description' => 'Schools, universities, and educational institutions', 'sort_order' => 6],
            ['name' => 'Real Estate', 'slug' => 'real-estate', 'description' => 'Property development, real estate services, and construction', 'sort_order' => 7],
            ['name' => 'Telecommunications', 'slug' => 'telecommunications', 'description' => 'Telecom providers, mobile networks, and communication services', 'sort_order' => 8],
            ['name' => 'Media & Entertainment', 'slug' => 'media-entertainment', 'description' => 'TV, radio, publishing, gaming, and entertainment companies', 'sort_order' => 9],
            ['name' => 'Government', 'slug' => 'government', 'description' => 'Government agencies and public sector organizations', 'sort_order' => 10],
        ]);

        // Insert major Philippine locations
        DB::table('locations')->insert([
            ['name' => 'Manila', 'type' => 'city', 'country' => 'Philippines', 'state_province' => 'Metro Manila', 'latitude' => 14.5995, 'longitude' => 120.9842],
            ['name' => 'Quezon City', 'type' => 'city', 'country' => 'Philippines', 'state_province' => 'Metro Manila', 'latitude' => 14.6760, 'longitude' => 121.0437],
            ['name' => 'Makati', 'type' => 'city', 'country' => 'Philippines', 'state_province' => 'Metro Manila', 'latitude' => 14.5547, 'longitude' => 121.0244],
            ['name' => 'Taguig', 'type' => 'city', 'country' => 'Philippines', 'state_province' => 'Metro Manila', 'latitude' => 14.5176, 'longitude' => 121.0509],
            ['name' => 'Pasig', 'type' => 'city', 'country' => 'Philippines', 'state_province' => 'Metro Manila', 'latitude' => 14.5764, 'longitude' => 121.0851],
            ['name' => 'Cebu City', 'type' => 'city', 'country' => 'Philippines', 'state_province' => 'Cebu', 'latitude' => 10.3157, 'longitude' => 123.8854],
            ['name' => 'Davao City', 'type' => 'city', 'country' => 'Philippines', 'state_province' => 'Davao del Sur', 'latitude' => 7.1907, 'longitude' => 125.4553],
            ['name' => 'Iloilo City', 'type' => 'city', 'country' => 'Philippines', 'state_province' => 'Iloilo', 'latitude' => 10.7202, 'longitude' => 122.5621],
            ['name' => 'Cagayan de Oro', 'type' => 'city', 'country' => 'Philippines', 'state_province' => 'Misamis Oriental', 'latitude' => 8.4542, 'longitude' => 124.6319],
            ['name' => 'Baguio', 'type' => 'city', 'country' => 'Philippines', 'state_province' => 'Benguet', 'latitude' => 16.4023, 'longitude' => 120.5960],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
        Schema::dropIfExists('industries');
        Schema::dropIfExists('company_sizes');
        Schema::dropIfExists('job_skills');
        Schema::dropIfExists('job_categories');
    }
};
