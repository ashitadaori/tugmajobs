<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CacheStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display cache statistics and performance metrics';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cacheService = app(\App\Services\CacheService::class);
        $stats = $cacheService->getStats();

        $this->info('Cache Statistics');
        $this->line(str_repeat('=', 50));

        $this->table(
            ['Metric', 'Value'],
            [
                ['Cache Driver', $stats['driver']],
                ['Status', $stats['enabled'] ? 'Enabled' : 'Disabled'],
            ]
        );

        if (isset($stats['redis'])) {
            $this->newLine();
            $this->info('Redis Metrics');
            $this->line(str_repeat('=', 50));

            if (isset($stats['redis']['error'])) {
                $this->error('Redis Error: ' . $stats['redis']['error']);
            } else {
                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Connected Clients', $stats['redis']['connected_clients']],
                        ['Memory Used', $stats['redis']['used_memory_human']],
                        ['Commands Processed', number_format($stats['redis']['total_commands_processed'])],
                        ['Cache Hits', number_format($stats['redis']['keyspace_hits'])],
                        ['Cache Misses', number_format($stats['redis']['keyspace_misses'])],
                        ['Hit Rate', $stats['redis']['hit_rate'] . '%'],
                    ]
                );

                // Evaluate performance
                $hitRate = $stats['redis']['hit_rate'];
                $this->newLine();

                if ($hitRate >= 90) {
                    $this->info('✓ Excellent cache performance!');
                } elseif ($hitRate >= 75) {
                    $this->info('✓ Good cache performance');
                } elseif ($hitRate >= 50) {
                    $this->warn('⚠ Moderate cache performance - consider warming up cache');
                } else {
                    $this->error('✗ Poor cache performance - cache may need optimization');
                }
            }
        }

        return Command::SUCCESS;
    }
}
