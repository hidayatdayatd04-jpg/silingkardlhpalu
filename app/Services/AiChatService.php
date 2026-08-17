<?php

namespace App\Services;

use App\Models\AiProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Layanan chat AI OpenAI-compatible dengan failover antar provider.
 *
 * Provider dicoba berurutan sesuai prioritas (terkecil lebih dulu).
 * Provider yang gagal ditandai "down" selama 5 menit sehingga request
 * berikutnya langsung loncat ke provider berikutnya.
 */
class AiChatService
{
    /** Lama provider dianggap down setelah gagal (detik). */
    private const DOWN_TTL = 300;

    /**
     * Kirim pesan ke provider pertama yang sehat.
     * Return konten balasan, atau null bila semua provider gagal.
     */
    public function chat(array $messages, int $maxTokens = 2048): ?string
    {
        $providers = AiProvider::active()->get();

        if ($providers->isEmpty()) {
            Log::warning('AI chat: tidak ada provider yang dikonfigurasi.');

            return null;
        }

        // Lewati provider yang sedang ditandai down.
        $candidates = $providers->reject(fn (AiProvider $p) => $this->isDown($p));

        // Bila semuanya ditandai down, tetap coba semuanya sebagai upaya terakhir.
        if ($candidates->isEmpty()) {
            $candidates = $providers;
        }

        foreach ($candidates as $provider) {
            $content = $this->request($provider, $messages, $maxTokens);

            if ($content !== null) {
                $this->markUp($provider);

                return $content;
            }

            $this->markDown($provider);
        }

        return null;
    }

    /**
     * Satu percobaan request ke satu provider.
     */
    private function request(AiProvider $provider, array $messages, int $maxTokens): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $provider->api_key,
            ])->timeout(60)->post($provider->endpoint(), [
                'model'       => $provider->model,
                'messages'    => $messages,
                'max_tokens'  => $maxTokens,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $content = trim((string) $response->json('choices.0.message.content', ''));

                return $content !== '' ? $content : null;
            }

            Log::error('AI provider error', [
                'provider' => $provider->name,
                'status'   => $response->status(),
                'body'     => mb_substr($response->body(), 0, 500),
            ]);

            return null;
        } catch (\Throwable $e) {
            Log::error('AI provider exception', [
                'provider' => $provider->name,
                'message'  => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function isDown(AiProvider $provider): bool
    {
        return (bool) Cache::get($this->downKey($provider));
    }

    private function markDown(AiProvider $provider): void
    {
        Cache::put($this->downKey($provider), true, self::DOWN_TTL);
    }

    private function markUp(AiProvider $provider): void
    {
        Cache::forget($this->downKey($provider));
    }

    private function downKey(AiProvider $provider): string
    {
        return 'ai-provider-down:' . $provider->id;
    }
}
