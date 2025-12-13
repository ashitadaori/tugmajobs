<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->boolean('is_active')->default(false);
            $table->text('message')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('maintenance_settings')->insert([
            [
                'key' => 'jobseeker_maintenance',
                'is_active' => false,
                'message' => 'We are currently under maintenance. Please wait for a moment, we\'ll be back as soon as possible.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'employer_maintenance',
                'is_active' => false,
                'message' => 'We are currently under maintenance. Please wait for a moment, we\'ll be back as soon as possible.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_settings');
    }
};
