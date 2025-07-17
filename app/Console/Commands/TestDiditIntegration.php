<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DiditService;
use Illuminate\Support\Facades\Log;

class TestDiditIntegration extends Command
{
    protected $signature = 'didit:test';
    protected $description = 'Test Didit KYC integration';

    protected $diditService;

    public function __construct(DiditService $diditService)
    {
        parent::__construct();
        $this->diditService = $diditService;
    }

    public function handle()
    {
        $this->info('Testing Didit KYC Integration...');
        
        try {
            // Test 1: Check configuration
            $this->info('1. Checking configuration...');
            $this->checkConfiguration();
            
            // Test 2: Test authentication (if credentials are provided)
            if (config('services.didit.client_id') && config('services.didit.client_secret')) {
                $this->info('2. Testing authentication...');
                $this->testAuthentication();
            } else {
                $this->warn('2. Skipping authentication test - credentials not configured');
            }
            
            // Test 3: Test session creation (if API key is provided)
            if (config('services.didit.api_key')) {
                $this->info('3. Testing session creation...');
                $this->testSessionCreation();
            } else {
                $this->warn('3. Skipping session creation test - API key not configured');
            }
            
            $this->info('✅ Didit integration test completed!');
            
        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            Log::error('Didit test failed', ['error' => $e->getMessage()]);
        }
    }

    private function checkConfiguration()
    {
        $requiredConfigs = [
            'services.didit.auth_url',
            'services.didit.base_url',
            'services.didit.api_key',
            'services.didit.client_id',
            'services.didit.client_secret',
            'services.didit.callback_url',
            'services.didit.redirect_url',
        ];

        $optionalConfigs = [
            'services.didit.workflow_id',
            'services.didit.webhook_secret',
        ];

        foreach ($requiredConfigs as $config) {
            $value = config($config);
            if (empty($value) || $value === 'your_didit_api_key_here' || strpos($value, 'your_') === 0) {
                $this->warn("   ⚠️  {$config} is not configured");
            } else {
                $this->info("   ✅ {$config} is configured");
            }
        }

        foreach ($optionalConfigs as $config) {
            $value = config($config);
            if (empty($value) || strpos($value, 'your_') === 0) {
                if ($config === 'services.didit.workflow_id') {
                    $this->warn("   ⚠️  {$config} is not configured (optional - will use default workflow)");
                    $this->info("      💡 To find your workflow ID:");
                    $this->info("         1. Login to business.didit.me");
                    $this->info("         2. Go to Workflows or Verification Templates");
                    $this->info("         3. Copy the workflow ID (UUID format)");
                    $this->info("         4. Or contact Didit support for your default workflow ID");
                } else {
                    $this->warn("   ⚠️  {$config} is not configured (optional)");
                }
            } else {
                $this->info("   ✅ {$config} is configured");
            }
        }
    }

    private function testAuthentication()
    {
        try {
            $token = $this->diditService->fetchAccessToken();
            if ($token) {
                $this->info('   ✅ Authentication successful');
                $this->info('   📝 Token: ' . substr($token, 0, 20) . '...');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Authentication failed: ' . $e->getMessage());
        }
    }

    private function testSessionCreation()
    {
        try {
            $sessionData = [
                'vendor_data' => 'test-user-' . time(),
                'metadata' => [
                    'test' => true,
                    'user_id' => 'test-123',
                ],
                'contact_details' => [
                    'email' => 'test@example.com',
                    'email_lang' => 'en',
                ]
            ];

            $response = $this->diditService->createSession($sessionData);
            
            if (isset($response['session_id'])) {
                $this->info('   ✅ Session creation successful');
                $this->info('   📝 Session ID: ' . $response['session_id']);
                if (isset($response['url'])) {
                    $this->info('   🔗 Verification URL: ' . $response['url']);
                }
            } else {
                $this->warn('   ⚠️  Session created but no session_id in response');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Session creation failed: ' . $e->getMessage());
        }
    }
}