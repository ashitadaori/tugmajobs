<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\KycData;

class ResetKyc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kyc:reset {user_id?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset KYC status for users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $resetAll = $this->option('all');

        if (!$userId && !$resetAll) {
            $this->info('Usage:');
            $this->info('  php artisan kyc:reset [user_id]  - Reset specific user');
            $this->info('  php artisan kyc:reset --all      - Reset all users');
            $this->info('');
            
            $this->info('Current users:');
            $users = User::select('id', 'name', 'email', 'role', 'kyc_status')->get();
            
            foreach ($users as $user) {
                $status = $user->kyc_status ?? 'null';
                $this->line("  ID: {$user->id} | {$user->name} ({$user->email}) | Role: {$user->role} | KYC: {$status}");
            }
            return;
        }

        if ($resetAll) {
            $this->info('Resetting KYC status for ALL users...');
            
            // Delete all KYC data from kyc_data table
            $kycDataCount = KycData::count();
            KycData::truncate();
            $this->info("🗑️ Deleted {$kycDataCount} KYC data records");
            
            $count = User::query()->update([
                'kyc_status' => 'pending',
                'kyc_session_id' => null,
                'kyc_completed_at' => null,
                'kyc_verified_at' => null,
                'kyc_data' => null
            ]);
            
            $this->info("✅ Reset KYC status for {$count} users");
            
        } else {
            $user = User::find($userId);
            
            if (!$user) {
                $this->error("❌ User with ID {$userId} not found");
                return 1;
            }
            
            $this->info("Resetting KYC status for user: {$user->name} ({$user->email})");
            $this->info("Current status: " . ($user->kyc_status ?? 'null'));
            
            // Delete KYC data for this specific user
            $deletedKycData = KycData::where('user_id', $user->id)->delete();
            if ($deletedKycData > 0) {
                $this->info("🗑️ Deleted {$deletedKycData} KYC data record(s) for this user");
            }
            
            $user->update([
                'kyc_status' => 'pending',
                'kyc_session_id' => null,
                'kyc_completed_at' => null,
                'kyc_verified_at' => null,
                'kyc_data' => null
            ]);
            
            $this->info("✅ KYC status reset to 'pending' for {$user->name}");
        }
        
        $this->info('');
        $this->info('Users can now start fresh KYC verification at: ' . config('app.url') . '/kyc/start');
        
        return 0;
    }
}