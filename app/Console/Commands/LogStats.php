<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LogStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:stats
                            {--channel= : Specific log channel to analyze}
                            {--json : Output as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display log file statistics';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $channel = $this->option('channel');
        $outputJson = $this->option('json');

        $stats = $this->getLogStats($channel);

        if ($outputJson) {
            $this->line(json_encode($stats, JSON_PRETTY_PRINT));
        } else {
            $this->displayStats($stats);
        }

        return Command::SUCCESS;
    }

    /**
     * Get log statistics
     *
     * @param string|null $channel
     * @return array
     */
    protected function getLogStats(?string $channel): array
    {
        $logsPath = storage_path('logs');

        if ($channel) {
            $pattern = $logsPath . '/' . $channel . '*.log';
            $files = glob($pattern);
        } else {
            $files = File::files($logsPath);
            $files = array_map(fn($file) => $file->getPathname(), $files);
        }

        // Filter out .gitignore
        $files = array_filter($files, fn($file) => basename($file) !== '.gitignore');

        $stats = [
            'total_files' => count($files),
            'total_size' => 0,
            'total_size_mb' => 0,
            'files' => [],
            'by_channel' => [],
        ];

        foreach ($files as $file) {
            $size = filesize($file);
            $stats['total_size'] += $size;

            $fileName = basename($file);
            $channel = $this->extractChannel($fileName);

            $fileStats = [
                'name' => $fileName,
                'size' => $size,
                'size_mb' => round($size / 1024 / 1024, 2),
                'size_human' => $this->formatBytes($size),
                'modified' => date('Y-m-d H:i:s', filemtime($file)),
                'age_days' => round((time() - filemtime($file)) / 86400, 1),
                'channel' => $channel,
            ];

            $stats['files'][] = $fileStats;

            // Group by channel
            if (!isset($stats['by_channel'][$channel])) {
                $stats['by_channel'][$channel] = [
                    'count' => 0,
                    'total_size' => 0,
                ];
            }

            $stats['by_channel'][$channel]['count']++;
            $stats['by_channel'][$channel]['total_size'] += $size;
        }

        $stats['total_size_mb'] = round($stats['total_size'] / 1024 / 1024, 2);
        $stats['total_size_human'] = $this->formatBytes($stats['total_size']);

        // Format by_channel sizes
        foreach ($stats['by_channel'] as $channel => &$channelStats) {
            $channelStats['total_size_mb'] = round($channelStats['total_size'] / 1024 / 1024, 2);
            $channelStats['total_size_human'] = $this->formatBytes($channelStats['total_size']);
        }

        return $stats;
    }

    /**
     * Display statistics in table format
     *
     * @param array $stats
     * @return void
     */
    protected function displayStats(array $stats): void
    {
        $this->info('=== Log File Statistics ===');
        $this->newLine();

        $this->info("Total Files: {$stats['total_files']}");
        $this->info("Total Size: {$stats['total_size_human']} ({$stats['total_size_mb']} MB)");
        $this->newLine();

        // Display by channel
        if (!empty($stats['by_channel'])) {
            $this->info('=== By Channel ===');
            $channelData = [];
            foreach ($stats['by_channel'] as $channel => $channelStats) {
                $channelData[] = [
                    'channel' => $channel,
                    'count' => $channelStats['count'],
                    'size' => $channelStats['total_size_human'],
                ];
            }

            $this->table(['Channel', 'Files', 'Total Size'], array_map(function($row) {
                return [
                    $row['channel'],
                    $row['count'],
                    $row['size'],
                ];
            }, $channelData));

            $this->newLine();
        }

        // Display individual files
        $this->info('=== Individual Files ===');
        $filesData = array_map(function($file) {
            return [
                $file['name'],
                $file['channel'],
                $file['size_human'],
                $file['age_days'] . ' days',
            ];
        }, $stats['files']);

        $this->table(['File', 'Channel', 'Size', 'Age'], $filesData);
    }

    /**
     * Extract channel name from log file name
     *
     * @param string $fileName
     * @return string
     */
    protected function extractChannel(string $fileName): string
    {
        // Remove date suffixes and .log extension
        $name = preg_replace('/-\d{4}-\d{2}-\d{2}\.log$/', '', $fileName);
        $name = preg_replace('/\.log$/', '', $name);

        return $name;
    }

    /**
     * Format bytes to human-readable format
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
