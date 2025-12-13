<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Gemini\Laravel\Facades\Gemini;

class TestGemini extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:gemini';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Gemini AI API connection';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $this->info('Testing Gemini AI connection...');

            // Test basic chat completion
            $result = Gemini::generativeModel(model: 'gemini-1.5-flash')
                ->generateContent('Hello, this is a test message. Please respond with "Gemini is working!"');

            $this->info('✅ Gemini connection successful!');
            $this->info('Response: ' . $result->text());

            // Test embeddings
            $this->info('Testing embeddings...');
            $embedding = Gemini::embeddingModel(model: 'text-embedding-004')
                ->embedContent('This is a test text for embedding');

            $this->info('✅ Embeddings working! Vector length: ' . count($embedding->embedding->values));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Gemini connection failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
