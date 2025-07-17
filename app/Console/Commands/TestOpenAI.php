<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI;

class TestOpenAI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:openai';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test OpenAI API connection';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $this->info('Testing OpenAI connection...');
            
            // Test basic chat completion
            $result = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Hello, this is a test message. Please respond with "OpenAI is working!"'
                    ]
                ],
                'max_tokens' => 50
            ]);

            $this->info('✅ OpenAI connection successful!');
            $this->info('Response: ' . $result->choices[0]->message->content);
            
            // Test embeddings
            $this->info('Testing embeddings...');
            $embedding = OpenAI::embeddings()->create([
                'model' => 'text-embedding-3-small',
                'input' => 'This is a test text for embedding',
            ]);
            
            $this->info('✅ Embeddings working! Vector length: ' . count($embedding->embeddings[0]->embedding));
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ OpenAI connection failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
