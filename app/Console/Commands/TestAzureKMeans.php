<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Job;
use App\Models\Category;
use App\Services\AzureMLClusteringService;
use Illuminate\Console\Command;

class TestAzureKMeans extends Command
{
    protected $signature = 'test:azure-kmeans';
    protected $description = 'Test Azure ML K-Means clustering integration';

    private $clusteringService;

    public function __construct()
    {
        parent::__construct();
        $this->clusteringService = new AzureMLClusteringService();
    }

    public function handle()
    {
        $this->info('☁️  Starting Azure ML K-Means Clustering Tests');
        $this->line(str_repeat('=', 50));

        try {
            // Test 1: Check Configuration
            $this->checkConfiguration();

            // Test 2: Job Clustering
            $this->testJobClustering();

            // Test 3: User Clustering
            $this->testUserClustering();

            $this->info("\n✅ Azure ML tests completed!");

        } catch (\Exception $e) {
            $this->error("\n❌ Test failed with error: " . $e->getMessage());
            $this->line("Stack trace:\n" . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    private function checkConfiguration()
    {
        $this->line("\n⚙️  Checking Configuration");
        $this->line(str_repeat('-', 40));

        $config = config('azure-ml');

        $this->line("Endpoint URL: " . ($config['endpoint_url'] ? '✅ Configured' : '❌ Missing'));
        $this->line("Endpoint Key: " . ($config['endpoint_key'] ? '✅ Configured' : '❌ Missing'));
        $this->line("Fallback Enabled: " . ($config['fallback']['enabled'] ? 'Yes' : 'No'));

        if (!$config['endpoint_url'] || !$config['endpoint_key']) {
            $this->warn("⚠️  Azure ML credentials are missing. Service will likely use fallback.");
        }
    }

    private function testJobClustering()
    {
        $this->line("\n🎯 Test: Job Clustering");
        $this->line(str_repeat('-', 40));

        $startTime = microtime(true);
        $result = $this->clusteringService->runJobClustering();
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        if (empty($result)) {
            $this->warn("No jobs available for clustering");
            return;
        }

        $source = $result['source'] ?? 'unknown';
        $sourceIcon = $source === 'azure_ml' ? '☁️' : '💻';

        $this->info("Clustering completed in {$duration}ms");
        $this->info("Source: {$sourceIcon} " . strtoupper($source));
        $this->info("Clusters created: " . count($result['clusters'] ?? []));

        if ($source === 'azure_ml') {
            $this->info("✅ Successfully connected to Azure ML!");
        } else {
            $this->warn("⚠️  Used local fallback. Check Azure logs if this was unexpected.");
        }

        // Show sample
        if (!empty($result['clusters'])) {
            $this->line("\nCluster Breakdown:");
            foreach ($result['clusters'] as $i => $cluster) {
                $this->line("  Cluster {$i}: " . count($cluster) . " jobs");
            }
        }
    }

    private function testUserClustering()
    {
        $this->line("\n👥 Test: User Clustering");
        $this->line(str_repeat('-', 40));

        $startTime = microtime(true);
        $result = $this->clusteringService->runUserClustering();
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        $source = $result['source'] ?? 'unknown';
        $sourceIcon = $source === 'azure_ml' ? '☁️' : '💻';

        $this->info("Clustering completed in {$duration}ms");
        $this->info("Source: {$sourceIcon} " . strtoupper($source));

        if ($source === 'azure_ml') {
            $this->info("✅ Successfully connected to Azure ML for users!");
        }
    }
}
