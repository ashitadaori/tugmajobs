<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('location_name')->nullable()->after('benefits');
            $table->string('location_address')->nullable()->after('location_name');
            $table->decimal('latitude', 10, 8)->nullable()->after('location_address');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['location_name', 'location_address', 'latitude', 'longitude']);
        });
    }
}; 