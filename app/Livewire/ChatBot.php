<?php

namespace App\Livewire;

use App\Models\ChatMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

class ChatBot extends Component
{
    public bool $isOpen = false;
    public string $message = '';
    public array $messages = [];

    /**
     * ID percakapan aktif — dipakai sebagai conversation_id ketika pesan
     * disimpan permanen ke tabel chat_message. Diganti baru setiap clear.
     */
    public string $conversationId = '';

    /**
     * Token sekali pakai untuk memvalidasi saveAssistantMessage. Di-generate
     * saat addUserMessage, divalidasi saat balasan AI disimpan — mencegah
     * penyuntikan pesan "assistant" palsu via panggilan Livewire langsung.
     */
    public string $pendingToken = '';

    public function mount(): void
    {
        $this->messages = session('chatbot_messages', []);
        $this->conversationId = session('chatbot_conversation_id', '');
    }

    /**
     * Return timestamp (ISO8601 WITA) sambutan pembuka agar frontend memakai
     * waktu asli dari server untuk tampilan & fitur copy.
     */
    public function toggleChat(): ?string
    {
        $this->isOpen = !$this->isOpen;

        if ($this->isOpen && empty($this->messages)) {
            $welcome = $this->welcomeMessage();
            $this->messages = [$welcome];
            $this->saveMessages();
            $this->storeInDatabase('assistant', 'DLH Assistant', $welcome['content'], $welcome['timestamp']);
        }

        $this->dispatch('chatbot-toggled', $this->isOpen);

        return $this->messages[0]['timestamp'] ?? null;
    }

    public function clearChat(): string
    {
        // Reset ke kondisi awal: seluruh riwayat lama hilang dari tampilan &
        // session, sambutan baru ditulis dengan conversation id baru —
        // riwayat lama tetap tersimpan permanen di database.
        $this->conversationId = '';
        session(['chatbot_conversation_id' => '']);
        $this->pendingToken = '';

        $welcome = $this->welcomeMessage();
        $this->messages = [$welcome];
        $this->saveMessages();
        $this->storeInDatabase('assistant', 'DLH Assistant', $welcome['content'], $welcome['timestamp']);

        $this->dispatch('chatbot-cleared');

        return $welcome['timestamp'];
    }

    /**
     * Called by JS after the stream completes: saves AI reply to session.
     * Token harus cocok dengan yang diterbitkan addUserMessage — tanpa itu
     * panggilan dianggap spoofing dan balasan tidak disimpan.
     * Return timestamp balasan yang tersimpan (atau null bila ditolak).
     */
    public function saveAssistantMessage(string $content, string $token = ''): ?string
    {
        if (empty(trim($content))) {
            return null;
        }

        if ($this->pendingToken === '' || ! hash_equals($this->pendingToken, $token)) {
            return null;
        }

        $this->pendingToken = '';

        $message = [
            'role'      => 'assistant',
            'content'   => trim($content),
            'timestamp' => now()->toIso8601String(),
        ];
        $this->messages[] = $message;
        $this->saveMessages();
        $this->storeInDatabase('assistant', 'DLH Assistant', $message['content'], $message['timestamp']);

        return $message['timestamp'];
    }

    /**
     * Instantly adds user message to history (called by JS before streaming starts).
     * Return timestamp pesan yang tersimpan (atau null bila kosong).
     */
    public function addUserMessage(string $content): ?string
    {
        $content = trim($content);
        if (empty($content)) {
            return null;
        }

        $message = [
            'role'      => 'user',
            'content'   => $content,
            'timestamp' => now()->toIso8601String(),
        ];
        $this->messages[] = $message;
        $this->saveMessages();
        $this->storeInDatabase('user', 'Pengguna', $content, $message['timestamp']);

        // Terbitkan token untuk balasan AI yang akan disimpan setelah stream.
        $this->pendingToken = Str::random(32);

        return $message['timestamp'];
    }

    public function sendSuggestion(string $text): void
    {
        $this->message = $text;
        $this->dispatch('chatbot-send-suggestion', text: $text);
    }

    protected function saveMessages(): void
    {
        $trimmed = array_slice($this->messages, -50);
        session(['chatbot_messages' => $trimmed]);
    }

    private function welcomeMessage(): array
    {
        return [
            'role'      => 'assistant',
            'content'   => "Halo, selamat datang 👋\nSaya dari DLH Kota Palu. Ada yang bisa saya bantu?\n\nKalau mau tanya soal sampah, lingkungan, taman, atau mau cek laporan pengaduan, langsung chat saja ya.",
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Simpan pesan secara permanen ke tabel chat_message (write-through
     * dari session). Timestamp memakai waktu server (WITA) sehingga hasil
     * copy tetap konsisten walau halaman di-refresh atau dibuka kembali.
     * Kegagalan DB tidak boleh memutus jalannya chat — cukup dicatat di log.
     */
    private function storeInDatabase(string $senderType, string $senderName, string $content, string $timestamp): void
    {
        try {
            ChatMessage::create([
                'conversation_id' => $this->ensureConversationId(),
                'sender_type'     => $senderType,
                'sender_name'     => $senderName,
                'message'         => $content,
                'created_at'      => Carbon::parse($timestamp),
            ]);
        } catch (\Throwable $e) {
            Log::warning('ChatBot: gagal menyimpan pesan ke database', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function ensureConversationId(): string
    {
        if ($this->conversationId === '') {
            $this->conversationId = (string) Str::uuid();
            session(['chatbot_conversation_id' => $this->conversationId]);
        }

        return $this->conversationId;
    }

    public function render()
    {
        return view('livewire.chat-bot');
    }
}
