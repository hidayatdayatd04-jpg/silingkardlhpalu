<?php

namespace App\Console\Commands;

use App\Services\OpenRouterService;
use App\Services\ChatKnowledgeBase;
use Illuminate\Console\Command;

class TestChatbot extends Command
{
    protected $signature = 'test:chatbot {message=Halo}';
    protected $description = 'Test chatbot connection';

    public function handle(OpenRouterService $openRouter, ChatKnowledgeBase $kb)
    {
        $message = $this->argument('message');
        
        $this->info('Testing OpenRouter connection...');
        $this->info('Message: ' . $message);
        $this->info('API Key: ' . substr(config('services.openrouter.api_key'), 0, 20) . '...');
        
        $messages = [
            ['role' => 'system', 'content' => $kb->getSystemPrompt()],
            ['role' => 'user', 'content' => $message],
        ];
        
        try {
            $this->info('Sending request...');
            
            $response = '';
            foreach ($openRouter->streamChat($messages) as $chunk) {
                $response .= $chunk;
                $this->info('Chunk: ' . $chunk);
            }
            
            $this->info("\nFull Response:");
            $this->line($response);
            
            $this->info("\n✅ Test successful!");
            
        } catch (\Exception $e) {
            $this->error('❌ Test failed!');
            $this->error('Error: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
        }
    }
}
