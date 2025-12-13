<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\DiditService;

class FetchKycDetails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kyc:fetch-details {user_id?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch detailed KYC verification data from Didit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $fetchAll = $this->option('all');

        if (!$userId && !$fetchAll) {
            $this->info('Usage:');
            $this->info('  php artisan kyc:fetch-details [user_id]  - Fetch details for specific user');
            $this->info('  php artisan kyc:fetch-details --all      - Fetch details for all verified users');
            $this->info('');
            
            $this->info('Verified users:');
            $users = User::where('kyc_status', 'verified')->get();
            
            foreach ($users as $user) {
                $this->line("  ID: {$user->id} | {$user->name} ({$user->email}) | Session: {$user->kyc_session_id}");
            }
            return;
        }

        $diditService = new DiditService();

        if ($fetchAll) {
            $users = User::where('kyc_status', 'verified')
                         ->whereNotNull('kyc_session_id')
                         ->get();
            
            $this->info("Fetching details for " . $users->count() . " verified users...");
            
            foreach ($users as $user) {
                $this->fetchUserDetails($user, $diditService);
            }
        } else {
            $user = User::find($userId);
            
            if (!$user) {
                $this->error("❌ User with ID {$userId} not found");
                return 1;
            }
            
            if ($user->kyc_status !== 'verified') {
                $this->error("❌ User is not verified (status: {$user->kyc_status})");
                return 1;
            }
            
            if (!$user->kyc_session_id) {
                $this->error("❌ User has no KYC session ID");
                return 1;
            }
            
            $this->fetchUserDetails($user, $diditService);
        }
        
        return 0;
    }

    private function fetchUserDetails(User $user, DiditService $diditService)
    {
        $this->info("📡 Fetching details for: {$user->name} ({$user->email})");
        $this->info("🔑 Session ID: {$user->kyc_session_id}");
        
        try {
            $detailedData = $diditService->getDetailedVerificationData($user->kyc_session_id);
            
            if (empty($detailedData)) {
                $this->warn("⚠️  No additional data available for this session");
                return;
            }
            
            $this->info("✅ Found detailed data with " . count($detailedData) . " endpoints");
            
            // Show what data was found
            foreach ($detailedData as $endpoint => $data) {
                $this->line("  📋 {$endpoint}: " . count($data) . " fields");
                
                // Show key information if available
                if (isset($data['documents'])) {
                    $this->line("    📄 Documents: " . count($data['documents']));
                }
                if (isset($data['biometric'])) {
                    $this->line("    🤳 Biometric data: Available");
                }
                if (isset($data['extracted_data'])) {
                    $this->line("    📝 Extracted data: Available");
                }
                if (isset($data['verification_result'])) {
                    $this->line("    ✅ Verification result: Available");
                }
            }
            
            // Ask if user wants to update the database
            if ($this->confirm('💾 Update database with detailed verification data?', true)) {
                $currentData = $user->kyc_data ?? [];
                
                $updatedData = array_merge($currentData, [
                    'detailed_verification_data' => $detailedData,
                    'data_fetched_at' => now()->toIso8601String(),
                    'data_source' => 'didit_api_detailed_fetch'
                ]);
                
                $user->update(['kyc_data' => $updatedData]);
                
                $this->info("✅ Database updated with detailed verification data!");
            } else {
                $this->info("⏭️  Skipping database update.");
            }
            
        } catch (Exception $e) {
            $this->error("❌ Error fetching details: " . $e->getMessage());
        }
        
        $this->line('');
    }
}