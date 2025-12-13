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
        Schema::create('job_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "NBI Clearance", "Medical Certificate"
            $table->text('description')->nullable(); // Optional details about the requirement
            $table->boolean('is_required')->default(true); // Whether it's mandatory
            $table->integer('sort_order')->default(0); // For ordering requirements
            $table->timestamps();

            // Index for faster queries
            $table->index(['job_id', 'is_required']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_requirements');
    }
};
