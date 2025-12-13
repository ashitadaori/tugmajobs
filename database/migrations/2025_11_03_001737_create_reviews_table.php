<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Jobseeker who wrote the review
            $table->foreignId('job_id')->constrained()->onDelete('cascade'); // Job being reviewed
            $table->foreignId('employer_id')->constrained('users')->onDelete('cascade'); // Company/Employer
            $table->enum('review_type', ['job', 'company']); // Type of review
            $table->tinyInteger('rating')->unsigned(); // 1-5 stars
            $table->string('title', 200); // Review title
            $table->text('comment'); // Review content
            $table->boolean('is_anonymous')->default(false); // Post anonymously
            $table->boolean('is_verified_hire')->default(false); // Got the job
            $table->integer('helpful_count')->default(0); // Helpful votes
            $table->text('employer_response')->nullable(); // Employer's response
            $table->timestamp('employer_responded_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['job_id', 'review_type']);
            $table->index(['employer_id', 'review_type']);
            $table->index('rating');
            
            // Ensure one review per user per job
            $table->unique(['user_id', 'job_id', 'review_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};
