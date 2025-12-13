<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('event_type'); // login, logout, failed_login, password_change, etc.
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('status')->default('success'); // success, failed, blocked
            $table->text('details')->nullable();
            $table->string('location')->nullable(); // City, Country
            $table->timestamps();
            
            $table->index(['event_type', 'created_at']);
            $table->index('ip_address');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_logs');
    }
};
