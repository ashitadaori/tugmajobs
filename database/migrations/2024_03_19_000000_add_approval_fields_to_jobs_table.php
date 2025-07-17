<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs', 'status')) {
                $table->string('status')->default('pending');
            }
            if (!Schema::hasColumn('jobs', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            if (!Schema::hasColumn('jobs', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (!Schema::hasColumn('jobs', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['status', 'rejection_reason', 'approved_at', 'rejected_at']);
        });
    }
}; 