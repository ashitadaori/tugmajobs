<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jobseeker_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('viewer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('viewer_type')->default('employer'); // employer, admin, guest
            $table->string('viewer_ip')->nullable();
            $table->string('viewer_user_agent')->nullable();
            $table->string('source')->nullable(); // application, profile_page, search_results
            $table->foreignId('job_application_id')->nullable()->constrained('job_applications')->onDelete('set null');
            $table->timestamp('viewed_at');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('jobseeker_id');
            $table->index('viewer_id');
            $table->index('viewed_at');
            $table->index(['jobseeker_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_views');
    }
};
