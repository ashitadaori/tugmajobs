<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\KycVerificationService;

class MigrateKycData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kyc:migrate-data {user_id?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing KYC data to the new verification system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $migrateAll = $this->option('all');

        if (!$userId && !$migrateAll) {
            $this->info('Usage:');
            $this->info('  php artisan kyc:migrate-data [user_id]  - Migrate data for specific user');
            $this->info('  php artisan kyc:migrate-data --all      - Migrate data for all users with KYC data');
            $this->info('');
            
            $this->info('Users with KYC data:');
            $users = User::whereNotNull('kyc_data')->orWhereNotNull('kyc_session_id')->get();
            
            foreach ($users as $user) {
                $this->line("  ID: {$user->id} | {$user->name} ({$user->email}) | Status: {$user->kyc_status}");
            }
            return;
        }

        $kycService = app(KycVerificationService::class);

        if ($migrateAll) {
            $users = User::whereNotNull('kyc_data')->orWhereNotNull('kyc_session_id')->get();
            
            $this->info("Migrating KYC data for " . $users->count() . " users...");
            
            foreach ($users as $user) {
                $this->migrateUserData($user, $kycService);
            }
        } else {
            $user = User::find($userId);
            
            if (!$user) {
                $this->error("❌ User with ID {$userId} not found");
                return 1;
            }
            
            $this->migrateUserData($user, $kycService);
        }
        
        return 0;
    }

    private function migrateUserData(User $user, KycVerificationService $kycService)
    {
        $this->info("📡 Migrating data for: {$user->name} ({$user->email})");
        
        if (!$user->kyc_session_id && !$user->kyc_data) {
            $this->warn("⚠️  No KYC data to migrate for this user");
            return;
        }
        
        // Check if verification already exists
        $existingVerification = $user->kycVerifications()->where('session_id', $user->kyc_session_id)->first();
        
        if ($existingVerification) {
            $this->warn("⚠️  Verification record already exists for session: {$user->kyc_session_id}");
            return;
        }
        
        try {
            // Prepare migration data
            $migrationData = [
                'session_id' => $user->kyc_session_id ?? 'migrated-' . time(),
                'status' => $this->mapKycStatus($user->kyc_status),
                'raw_data' => $user->kyc_data ?? [],
                'vendor_data' => $user->id, // Use user ID as vendor data
                'metadata' => [
                    'user_id' => $user->id,
                    'user_type' => $user->role,
                    'name' => $user->name,
                    'email' => $user->email,
                    'migrated' => true,
                    'migration_date' => now()->toIso8601String()
                ]
            ];
            
            // Add extracted data if available from kyc_data
            if ($user->kyc_data) {
                $kycData = $user->kyc_data;
                
                // Try to extract any personal information that might be in the data
                if (isset($kycData['extracted_data'])) {
                    $migrationData['extracted_data'] = $kycData['extracted_data'];
                }
                
                if (isset($kycData['verification_data'])) {
                    $migrationData['verification_data'] = $kycData['verification_data'];
                }
                
                if (isset($kycData['detailed_verification_data'])) {
                    $migrationData['verification_data'] = $kycData['detailed_verification_data'];
                }
            }
            
            $verification = $kycService->processVerificationData($migrationData);
            
            if ($verification) {
                // Update timestamps to match original data
                if ($user->kyc_verified_at) {
                    $verification->verified_at = $user->kyc_verified_at;
                }
                
                if ($user->kyc_completed_at) {
                    $verification->completed_at = $user->kyc_completed_at;
                } elseif ($user->kyc_verified_at) {
                    $verification->completed_at = $user->kyc_verified_at;
                }
                
                $verification->save();
                
                $this->info("✅ Migration successful!");
                $this->line("  - Verification ID: {$verification->id}");
                $this->line("  - Session ID: {$verification->session_id}");
                $this->line("  - Status: {$verification->status}");
                
                if ($verification->full_name) {
                    $this->line("  - Name: {$verification->full_name}");
                }
                
                if ($verification->document_type) {
                    $this->line("  - Document: {$verification->document_type}");
                }
            } else {
                $this->error("❌ Failed to create verification record");
            }
            
        } catch (Exception $e) {
            $this->error("❌ Migration failed: " . $e->getMessage());
        }
        
        $this->line('');
    }
    
    private function mapKycStatus(string $status): string
    {
        $statusMap = [
            'verified' => 'verified',
            'pending' => 'pending',
            'in_progress' => 'in_progress',
            'failed' => 'failed',
            'expired' => 'expired',
            'not_started' => 'pending'
        ];
        
        return $statusMap[$status] ?? $status;
    }
}