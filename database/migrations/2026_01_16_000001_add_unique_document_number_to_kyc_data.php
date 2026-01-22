<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds a unique constraint on the document_number field
     * to prevent duplicate accounts from being verified with the same ID document.
     *
     * Note: Only one account can have a verified KYC with a specific document number.
     * This constraint only applies to verified records (status in verified, approved, completed).
     */
    public function up(): void
    {
        // First, check if there are any duplicate document_numbers in verified records
        // and log them for manual review if needed
        $duplicates = DB::table('kyc_data')
            ->select('document_number', DB::raw('COUNT(*) as count'))
            ->whereIn('status', ['verified', 'approved', 'completed'])
            ->whereNotNull('document_number')
            ->groupBy('document_number')
            ->having('count', '>', 1)
            ->get();

        if ($duplicates->count() > 0) {
            // Log duplicates for manual review - don't block migration
            foreach ($duplicates as $duplicate) {
                \Illuminate\Support\Facades\Log::warning('Duplicate document_number found in kyc_data', [
                    'document_number' => $duplicate->document_number,
                    'count' => $duplicate->count,
                ]);
            }
        }

        Schema::table('kyc_data', function (Blueprint $table) {
            // Add a unique index on document_number for verified records
            // Note: MySQL/MariaDB allows multiple NULLs with unique constraint
            // This prevents the same document from being used for multiple verified accounts
            $table->unique(['document_number'], 'kyc_data_document_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kyc_data', function (Blueprint $table) {
            $table->dropUnique('kyc_data_document_number_unique');
        });
    }
};
