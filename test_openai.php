<?php

require_once 'vendor/autoload.php';

use OpenAI\Laravel\Facades\OpenAI;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Test OpenAI connection
    $result = OpenAI::chat()->create([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Hello, this is a test message.'
            ]
        ],
        'max_tokens' => 50
    ]);

    echo "OpenAI connection successful!\n";
    echo "Response: " . $result->choices[0]->message->content . "\n";
} catch (Exception $e) {
    echo "OpenAI connection failed: " . $e->getMessage() . "\n";
}