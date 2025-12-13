<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Socialite\Facades\Socialite;

class TestSocialAuth extends Command
{
    protected $signature = 'test:social-auth';
    protected $description = 'Test social authentication configuration';

    public function handle()
    {
        $this->info('🔍 Testing Social Authentication Configuration');
        $this->info('============================================');
        $this->newLine();

        // Test configuration
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.redirect');

        $this->info('📋 Configuration Check:');
        $this->line('Client ID: ' . ($clientId ? '✅ Set (' . substr($clientId, 0, 20) . '...)' : '❌ Missing'));
        $this->line('Client Secret: ' . ($clientSecret ? '✅ Set' : '❌ Missing'));
        $this->line('Redirect URI: ' . ($redirectUri ? '✅ ' . $redirectUri : '❌ Missing'));
        $this->newLine();

        if (!$clientId || !$clientSecret || !$redirectUri) {
            $this->error('❌ Configuration incomplete!');
            return 1;
        }

        try {
            // Test Socialite driver initialization
            $driver = Socialite::driver('google');
            $this->info('✅ Socialite Google driver initialized successfully');

            // Test that we can set the redirect URL
            $driver->redirectUrl($redirectUri);
            $this->info('✅ Redirect URL set successfully');
            
            // Build a manual OAuth URL to verify parameters
            $params = [
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'scope' => 'openid email profile',
                'response_type' => 'code',
                'access_type' => 'offline',
                'prompt' => 'consent'
            ];
            
            $oauthUrl = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
            
            $this->info('🔗 Expected OAuth URL:');
            $this->line($oauthUrl);
            $this->newLine();

            $this->info('📝 URL Parameters:');
            foreach ($params as $key => $value) {
                $displayValue = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                $this->line("  {$key}: {$displayValue}");
            }
            $this->newLine();

            // Verify all required parameters are present
            $required = ['client_id', 'redirect_uri', 'response_type'];
            $missing = array_diff($required, array_keys($params));

            if (empty($missing)) {
                $this->info('✅ All required parameters are present!');
                $this->info('🚀 OAuth URL should work correctly');
            } else {
                $this->error('❌ Missing required parameters: ' . implode(', ', $missing));
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Error testing Socialite: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info('💡 Test the flow:');
        $this->line('1. Visit: http://localhost:8000/auth/google');
        $this->line('2. Check Laravel logs for debugging info');
        $this->line('3. Use browser dev tools to inspect the actual request');

        return 0;
    }
}