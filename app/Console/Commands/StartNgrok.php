<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class StartNgrok extends Command
{
    protected $signature = 'ngrok:start {--port=8000 : The port to expose via ngrok}';
    protected $description = 'Start ngrok and automatically update environment URLs';

    public function handle()
    {
        $port = $this->option('port');
        
        $this->info("🚀 Starting ngrok and updating environment URLs...");
        
        // Check if Laravel is running
        $this->checkLaravelStatus($port);
        
        // Stop existing ngrok processes
        $this->stopExistingNgrok();
        
        // Start ngrok
        $ngrokUrl = $this->startNgrok($port);
        
        if ($ngrokUrl) {
            // Update environment files
            $this->updateEnvironmentFiles($ngrokUrl);
            
            // Clear config cache
            $this->call('config:clear');
            
            $this->displaySuccess($ngrokUrl);
            
            // Keep the command running
            $this->keepAlive();
        } else {
            $this->error('❌ Failed to start ngrok or retrieve URL');
            return 1;
        }
        
        return 0;
    }
    
    private function checkLaravelStatus($port)
    {
        $this->info("🔍 Checking if Laravel is running on port {$port}...");
        
        try {
            $response = Http::timeout(5)->get("http://localhost:{$port}");
            $this->info("   ✅ Laravel is running on port {$port}");
        } catch (\Exception $e) {
            $this->warn("   ⚠️  Laravel doesn't seem to be running on port {$port}");
            $this->warn("   💡 Make sure to run 'php artisan serve' first");
        }
    }
    
    private function stopExistingNgrok()
    {
        $this->info("🔄 Stopping any existing ngrok processes...");
        
        if (PHP_OS_FAMILY === 'Windows') {
            $process = new Process(['taskkill', '/F', '/IM', 'ngrok.exe']);
        } else {
            $process = new Process(['pkill', '-f', 'ngrok']);
        }
        
        $process->run();
        sleep(2);
    }
    
    private function startNgrok($port)
    {
        $this->info("🌐 Starting ngrok tunnel on port {$port}...");
        
        // Start ngrok process
        $process = new Process(['ngrok', 'http', $port]);
        $process->start();
        
        // Wait for ngrok to initialize
        $this->info("⏳ Waiting for ngrok to initialize...");
        sleep(5);
        
        // Get ngrok URL from API
        try {
            $response = Http::get('http://localhost:4040/api/tunnels');
            $tunnels = $response->json();
            
            foreach ($tunnels['tunnels'] as $tunnel) {
                if ($tunnel['proto'] === 'https') {
                    return $tunnel['public_url'];
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ Error connecting to ngrok API: " . $e->getMessage());
        }
        
        return null;
    }
    
    private function updateEnvironmentFiles($ngrokUrl)
    {
        $this->info("📝 Updating environment files...");
        
        // Update .env
        $this->updateEnvFile('.env', $ngrokUrl);
        
        // Update .env.local if it exists
        if (File::exists('.env.local')) {
            $this->updateEnvFile('.env.local', $ngrokUrl);
        }
    }
    
    private function updateEnvFile($filePath, $ngrokUrl)
    {
        if (!File::exists($filePath)) {
            $this->warn("   ⚠️  File {$filePath} not found");
            return;
        }
        
        $this->info("   📝 Updating {$filePath}...");
        
        $content = File::get($filePath);
        $lines = explode("\n", $content);
        $updatedLines = [];
        
        foreach ($lines as $line) {
            // Update APP_URL
            if (preg_match('/^APP_URL=/', $line)) {
                $updatedLines[] = "APP_URL={$ngrokUrl}";
                $this->info("      ✅ Updated APP_URL");
            }
            // Update DIDIT_CALLBACK_URL
            elseif (preg_match('/^DIDIT_CALLBACK_URL=/', $line)) {
                $updatedLines[] = "DIDIT_CALLBACK_URL={$ngrokUrl}/kyc/webhook";
                $this->info("      ✅ Updated DIDIT_CALLBACK_URL");
            }
            // Update DIDIT_REDIRECT_URL
            elseif (preg_match('/^DIDIT_REDIRECT_URL=/', $line)) {
                $updatedLines[] = "DIDIT_REDIRECT_URL={$ngrokUrl}/kyc/success";
                $this->info("      ✅ Updated DIDIT_REDIRECT_URL");
            }
            // Update GOOGLE_REDIRECT_URI
            elseif (preg_match('/^GOOGLE_REDIRECT_URI=/', $line)) {
                $updatedLines[] = "GOOGLE_REDIRECT_URI={$ngrokUrl}/auth/google/callback";
                $this->info("      ✅ Updated GOOGLE_REDIRECT_URI");
            }
            else {
                $updatedLines[] = $line;
            }
        }
        
        File::put($filePath, implode("\n", $updatedLines));
        $this->info("      💾 Saved changes to {$filePath}");
    }
    
    private function displaySuccess($ngrokUrl)
    {
        $this->info("");
        $this->info("🎉 Setup Complete!");
        $this->info("   🌐 Your app is now accessible at: {$ngrokUrl}");
        $this->info("   🔧 ngrok web interface: http://localhost:4040");
        $this->info("   📋 All environment URLs have been updated automatically");
        $this->info("");
        $this->comment("💡 Tips:");
        $this->comment("   - Keep this terminal open to maintain the tunnel");
        $this->comment("   - Visit http://localhost:4040 to inspect requests");
        $this->comment("   - Press Ctrl+C to stop ngrok");
        $this->info("");
        
        // Try to open ngrok web interface
        if (PHP_OS_FAMILY === 'Windows') {
            exec('start http://localhost:4040');
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            exec('open http://localhost:4040');
        } else {
            exec('xdg-open http://localhost:4040 2>/dev/null &');
        }
    }
    
    private function keepAlive()
    {
        $this->info("Press Ctrl+C to stop ngrok and exit...");
        
        // Register signal handler for graceful shutdown
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGINT, function() {
                $this->info("\n🛑 Stopping ngrok...");
                $this->stopExistingNgrok();
                exit(0);
            });
        }
        
        // Keep the command running
        while (true) {
            if (function_exists('pcntl_signal_dispatch')) {
                pcntl_signal_dispatch();
            }
            sleep(1);
        }
    }
}