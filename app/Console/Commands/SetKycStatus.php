<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class SetKycStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kyc:set {user_id} {status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set KYC status for a specific user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $status = $this->argument('status');
        
        $validStatuses = ['pending', 'in_progress', 'verified', 'failed', 'expired'];

        if (!in_array($status, $validStatuses)) {
            $this->error('❌ Invalid status. Valid statuses: ' . implode(', ', $validStatuses));
            return 1;
        }

        $user = User::find($userId);
        
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found");
            
            $this->info('Available users:');
            $users = User::select('id', 'name', 'email', 'role', 'kyc_status')->get();
            
            foreach ($users as $u) {
                $currentStatus = $u->kyc_status ?? 'null';
                $this->line("  ID: {$u->id} | {$u->name} ({$u->email}) | Role: {$u->role} | KYC: {$currentStatus}");
            }
            
            return 1;
        }
        
        $this->info("Setting KYC status for user: {$user->name} ({$user->email})");
        $this->info("Current status: " . ($user->kyc_status ?? 'null'));
        $this->info("New status: {$status}");
        
        $updateData = ['kyc_status' => $status];
        
        // Set additional fields based on status
        switch ($status) {
            case 'pending':
                $updateData['kyc_session_id'] = null;
                $updateData['kyc_completed_at'] = null;
                $updateData['kyc_verified_at'] = null;
                $updateData['kyc_data'] = null;
                break;
                
            case 'in_progress':
                $updateData['kyc_session_id'] = 'test-session-' . time();
                $updateData['kyc_completed_at'] = null;
                $updateData['kyc_verified_at'] = null;
                break;
                
            case 'verified':
                $updateData['kyc_session_id'] = 'test-session-' . time();
                $updateData['kyc_completed_at'] = now();
                $updateData['kyc_verified_at'] = now();
                $updateData['kyc_data'] = [
                    'session_id' => 'test-session-' . time(),
                    'status' => 'completed',
                    'completed_at' => now()->toIso8601String(),
                    'test' => true
                ];
                break;
                
            case 'failed':
            case 'expired':
                $updateData['kyc_session_id'] = 'test-session-' . time();
                $updateData['kyc_completed_at'] = now();
                $updateData['kyc_verified_at'] = null;
                $updateData['kyc_data'] = [
                    'session_id' => 'test-session-' . time(),
                    'status' => $status,
                    'completed_at' => now()->toIso8601String(),
                    'test' => true
                ];
                break;
        }
        
        $user->update($updateData);
        
        $this->info("✅ KYC status updated to '{$status}' for {$user->name}");
        
        // Refresh user data
        $user = $user->fresh();
        
        // Show what the user will see
        $this->info('');
        $this->info('User will see:');
        $this->line("- Status: " . $user->kyc_status_text);
        $this->line("- Can start verification: " . ($user->canStartKycVerification() ? 'Yes' : 'No'));
        $this->line("- Needs verification: " . ($user->needsKycVerification() ? 'Yes' : 'No'));
        $this->line("- Is verified: " . ($user->isKycVerified() ? 'Yes' : 'No'));
        
        return 0;
    }
}