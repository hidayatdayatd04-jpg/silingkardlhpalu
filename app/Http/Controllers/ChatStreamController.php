<?php

namespace App\Http\Controllers;

use App\Services\AiChatService;
use App\Services\ChatKnowledgeBase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChatStreamController extends Controller
{
    public function stream(Request $request, AiChatService $aiChat, ChatKnowledgeBase $knowledgeBase)
    {
        $userMessage = trim($request->input('message', ''));
        $history     = $request->input('history', []);

        if ($userMessage === '') {
            return response()->json(['error' => 'Pesan tidak boleh kosong.'], 422);
        }

        // Batasi panjang input agar hemat token & mencegah abuse.
        if (mb_strlen($userMessage) > 1000) {
            $userMessage = mb_substr($userMessage, 0, 1000);
        }

        // Throttle: max 20 messages per minute per IP
        $ip         = $request->ip();
        $limiterKey = 'chatbot-stream:' . $ip;

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($limiterKey, 20)) {
            return response()->json(['error' => 'Terlalu banyak permintaan. Coba lagi dalam 1 menit.'], 429);
        }

        \Illuminate\Support\Facades\RateLimiter::hit($limiterKey, 60);

        $systemPrompt = $knowledgeBase->getSystemPrompt();

        // Build messages for the API call
        $apiMessages = [['role' => 'system', 'content' => $systemPrompt]];

        // Append history (already-sent messages) — batasi agar konteks tidak membengkak.
        $history = is_array($history) ? array_slice($history, -12) : [];
        foreach ($history as $msg) {
            if (isset($msg['role'], $msg['content']) && in_array($msg['role'], ['user', 'assistant'], true)) {
                $apiMessages[] = [
                    'role'    => $msg['role'],
                    'content' => (string) $msg['content'],
                ];
            }
        }

        // Append current user message
        $apiMessages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            \Illuminate\Support\Facades\Log::info('ChatBot: Starting request', [
                'user_message' => $userMessage,
            ]);

            // AiChatService mencoba provider aktif sesuai prioritas dan
            // otomatis failover bila provider sebelumnya gagal/down.
            $fullResponse = trim((string) $aiChat->chat($apiMessages));

            // Fallback cerdas bila AI mengembalikan respons kosong.
            if ($fullResponse === '') {
                $fullResponse = $knowledgeBase->localAnswer($userMessage)
                    ?? 'Maaf, saya belum dapat memproses pertanyaan itu. Silakan coba susun ulang, atau hubungi call center kami di **0851-9151-2076** (WhatsApp).';
            }

            \Illuminate\Support\Facades\Log::info('ChatBot: Got response', [
                'length' => strlen($fullResponse),
            ]);

            return response()->json([
                'success' => true,
                'content' => $fullResponse,
            ]);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ChatBot error', [
                'message' => $e->getMessage(),
            ]);

            // Bila layanan AI gagal total, tetap coba jawab lewat basis pengetahuan lokal.
            $fallback = $knowledgeBase->localAnswer($userMessage);

            if ($fallback !== null) {
                return response()->json([
                    'success'  => true,
                    'content'  => $fallback,
                    'fallback' => true,
                ]);
            }

            return response()->json([
                'error' => 'Maaf, asisten sedang sibuk. Silakan coba lagi sebentar lagi atau hubungi call center kami di **0851-9151-2076** (WhatsApp).',
            ], 503);
        }
    }
}
