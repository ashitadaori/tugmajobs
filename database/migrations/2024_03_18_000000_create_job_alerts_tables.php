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
        Schema::create('job_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('location')->nullable();
            $table->decimal('salary_range', 10, 2)->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'instant'])->default('daily');
            $table->boolean('email_notifications')->default(true);
            $table->timestamps();
        });

        Schema::create('job_alert_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_alert_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['job_alert_id', 'category_id']);
        });

        Schema::create('job_alert_job_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_alert_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['job_alert_id', 'job_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_alert_job_types');
        Schema::dropIfExists('job_alert_categories');
        Schema::dropIfExists('job_alerts');
    }
}; 