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
        Schema::table('jobs', function (Blueprint $table) {
            // Add only the missing location fields
            if (!Schema::hasColumn('jobs', 'address')) {
                $table->string('address')->nullable()->after('description');
            }
            if (!Schema::hasColumn('jobs', 'barangay')) {
                $table->string('barangay')->nullable()->after('location_address');
            }
            if (!Schema::hasColumn('jobs', 'city')) {
                $table->string('city')->default('Sta. Cruz')->after('barangay');
            }
            if (!Schema::hasColumn('jobs', 'province')) {
                $table->string('province')->default('Davao del Sur')->after('city');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['address', 'barangay', 'city', 'province']);
        });
    }
};
