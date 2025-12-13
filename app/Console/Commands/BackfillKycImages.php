<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\KycData;

class BackfillKycImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kyc:backfill-images
                            {--limit=50 : Number of records to process}
                            {--force : Process all records even if they already have local paths}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download and permanently store KYC verification images from Didit temporary URLs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $force = $this->option('force');

        $this->info('Starting KYC images backfill process...');
        $this->info("Processing up to {$limit} records.");

        // Query KYC data records that have external image URLs
        $query = KycData::whereNotNull('front_image_url')
            ->orWhereNotNull('back_image_url')
            ->orWhereNotNull('portrait_image_url');

        // If not forcing, only process records with external URLs (not already stored locally)
        if (!$force) {
            $query->where(function($q) {
                $q->where('front_image_url', 'LIKE', 'http%')
                  ->orWhere('back_image_url', 'LIKE', 'http%')
                  ->orWhere('portrait_image_url', 'LIKE', 'http%');
            });
        }

        $kycRecords = $query->limit($limit)->get();

        $this->info("Found {$kycRecords->count()} KYC records to process.");

        if ($kycRecords->isEmpty()) {
            $this->info('No records to process. All images are already stored locally or no records found.');
            return Command::SUCCESS;
        }

        $progressBar = $this->output->createProgressBar($kycRecords->count());
        $progressBar->start();

        $successCount = 0;
        $failedCount = 0;
        $skippedCount = 0;

        foreach ($kycRecords as $kycData) {
            try {
                $updated = false;

                // Process front image
                if ($kycData->front_image_url && $this->isExternalUrl($kycData->front_image_url)) {
                    $localPath = $this->downloadAndStoreImage(
                        $kycData->front_image_url,
                        $kycData->user_id,
                        $kycData->session_id,
                        'front'
                    );

                    if ($localPath && $localPath !== $kycData->front_image_url) {
                        $kycData->front_image_url = $localPath;
                        $updated = true;
                    }
                }

                // Process back image
                if ($kycData->back_image_url && $this->isExternalUrl($kycData->back_image_url)) {
                    $localPath = $this->downloadAndStoreImage(
                        $kycData->back_image_url,
                        $kycData->user_id,
                        $kycData->session_id,
                        'back'
                    );

                    if ($localPath && $localPath !== $kycData->back_image_url) {
                        $kycData->back_image_url = $localPath;
                        $updated = true;
                    }
                }

                // Process portrait/selfie image
                if ($kycData->portrait_image_url && $this->isExternalUrl($kycData->portrait_image_url)) {
                    $localPath = $this->downloadAndStoreImage(
                        $kycData->portrait_image_url,
                        $kycData->user_id,
                        $kycData->session_id,
                        'portrait'
                    );

                    if ($localPath && $localPath !== $kycData->portrait_image_url) {
                        $kycData->portrait_image_url = $localPath;
                        $updated = true;
                    }
                }

                // Process liveness video if exists
                if ($kycData->liveness_video_url && $this->isExternalUrl($kycData->liveness_video_url)) {
                    $localPath = $this->downloadAndStoreImage(
                        $kycData->liveness_video_url,
                        $kycData->user_id,
                        $kycData->session_id,
                        'video',
                        'mp4'
                    );

                    if ($localPath && $localPath !== $kycData->liveness_video_url) {
                        $kycData->liveness_video_url = $localPath;
                        $updated = true;
                    }
                }

                if ($updated) {
                    $kycData->save();
                    $successCount++;
                } else {
                    $skippedCount++;
                }

            } catch (\Exception $e) {
                $failedCount++;
                Log::error('Failed to backfill KYC images', [
                    'kyc_data_id' => $kycData->id,
                    'user_id' => $kycData->user_id,
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display summary
        $this->info('KYC images backfill completed!');
        $this->table(
            ['Status', 'Count'],
            [
                ['Successfully updated', $successCount],
                ['Skipped (already local)', $skippedCount],
                ['Failed', $failedCount],
                ['Total processed', $kycRecords->count()]
            ]
        );

        if ($failedCount > 0) {
            $this->warn("Some images failed to download. Check the logs for details.");
        }

        return Command::SUCCESS;
    }

    /**
     * Check if a URL is an external URL (not already stored locally)
     *
     * @param string $url
     * @return bool
     */
    private function isExternalUrl(string $url): bool
    {
        // Check if it's an HTTP/HTTPS URL (not a local storage path)
        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
    }

    /**
     * Download an image from a URL and store it permanently in Laravel storage
     *
     * @param string $url The URL of the image to download
     * @param int $userId The user ID
     * @param string $sessionId The KYC session ID
     * @param string $type The type of image (front, back, portrait, video)
     * @param string $extension The file extension (default: jpg)
     * @return string|null The storage path of the downloaded image or null on failure
     */
    private function downloadAndStoreImage(string $url, int $userId, string $sessionId, string $type, string $extension = 'jpg'): ?string
    {
        try {
            // Download the image from the URL
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                $this->warn("Failed to download {$type} image for user {$userId}: HTTP {$response->status()}");
                return $url; // Return original URL as fallback
            }

            // Get the image content
            $imageContent = $response->body();

            if (empty($imageContent)) {
                $this->warn("Downloaded {$type} image is empty for user {$userId}");
                return $url; // Return original URL as fallback
            }

            // Create a unique filename
            $filename = sprintf(
                'kyc/%d/%s/%s_%s.%s',
                $userId,
                $sessionId,
                $type,
                time(),
                $extension
            );

            // Store the image in the public disk so it's accessible via URL
            Storage::disk('public')->put($filename, $imageContent);

            // Generate the public URL for the stored image
            $storedPath = Storage::disk('public')->url($filename);

            return $storedPath;

        } catch (\Exception $e) {
            $this->error("Exception while downloading {$type} image for user {$userId}: {$e->getMessage()}");
            Log::error('Exception while downloading KYC image', [
                'url' => $url,
                'user_id' => $userId,
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            // Return the original URL as fallback
            return $url;
        }
    }
}
