<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Generator;

class OpenRouterService
{
    private string $apiKey;
    private string $baseUrl = 'https://openrouter.ai/api/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key', '');
    }

    /**
     * Send a chat completion request (non-streaming).
     */
    public function chat(array $messages, string $model = 'tencent/hy3:free', int $maxTokens = 2048): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => 'DLH Kota Palu - Chatbot',
            ])->timeout(60)->post($this->baseUrl, [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => $maxTokens,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error('OpenRouter API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('OpenRouter API exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Stream a chat completion request.
     * Yields content chunks as they arrive.
     */
    public function streamChat(array $messages, string $model = 'tencent/hy3:free', int $maxTokens = 2048): Generator
    {
        $url = $this->baseUrl;

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'HTTP-Referer: ' . config('app.url'),
            'X-Title: DLH Kota Palu - Chatbot',
        ];

        $body = json_encode([
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $maxTokens,
            'temperature' => 0.7,
            'stream' => true,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $buffer = '';
        $chunks = [];

        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $data) use (&$buffer, &$chunks) {
            $buffer .= $data;
            
            // Process complete lines
            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);
                
                $line = trim($line);
                
                if ($line === '' || $line === ':' || str_starts_with($line, ': ')) {
                    continue;
                }
                
                if (str_starts_with($line, 'data: ')) {
                    $jsonData = substr($line, 6);
                    
                    if ($jsonData === '[DONE]') {
                        continue;
                    }
                    
                    $json = json_decode($jsonData, true);
                    
                    if (isset($json['choices'][0]['delta']['content'])) {
                        $chunks[] = $json['choices'][0]['delta']['content'];
                    }
                }
            }
            
            return strlen($data);
        });

        try {
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            if ($curlError) {
                Log::error('OpenRouter cURL error', [
                    'error' => $curlError,
                    'errno' => curl_errno($ch),
                ]);
                curl_close($ch);
                throw new \Exception('Connection error: ' . $curlError);
            }

            if ($httpCode !== 200) {
                Log::error('OpenRouter HTTP error', [
                    'status' => $httpCode,
                    'buffer' => $buffer,
                ]);
                curl_close($ch);
                throw new \Exception('API returned status code: ' . $httpCode);
            }

            curl_close($ch);

            // Yield all collected chunks
            foreach ($chunks as $chunk) {
                yield $chunk;
            }

        } catch (\Exception $e) {
            Log::error('OpenRouter stream exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            if (is_resource($ch)) {
                curl_close($ch);
            }
            
            throw $e;
        }
    }
}
