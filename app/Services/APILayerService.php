<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class APILayerService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.apilayer.com/resume_parser';

    public function __construct()
    {
        // Using the key provided by the user
        $this->apiKey = '10uZpWhln1sCU0QtiIiUaAmC99C7vl9J';
    }

    /**
     * Parse a resume file using APILayer
     *
     * @param UploadedFile $file
     * @return array|null
     */
    public function parseResume(UploadedFile $file)
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post($this->baseUrl . '/upload');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('APILayer Parse Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('APILayer Service Exception: ' . $e->getMessage());
            return null;
        }
    }
}
