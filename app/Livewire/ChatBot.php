<?php

namespace App\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;

class ChatBot extends Component
{
    public bool $isOpen = false;
    public string $message = '';
    public array $messages = [];

    /**
     * Token sekali pakai untuk memvalidasi saveAssistantMessage. Di-generate
     * saat addUserMessage, divalidasi saat balasan AI disimpan — mencegah
     * penyuntikan pesan "assistant" palsu via panggilan Livewire langsung.
     */
    public string $pendingToken = '';

    public function mount(): void
    {
        $this->messages = session('chatbot_messages', []);
    }

    public function toggleChat(): void
    {
        $this->isOpen = !$this->isOpen;

        if ($this->isOpen && empty($this->messages)) {
            $this->messages[] = [
                'role'      => 'assistant',
                'content'   => "Halo!\n\nSaya adalah **DLH Assistant**, asisten AI untuk Dinas Lingkungan Hidup Kota Palu.\n\nAda yang bisa saya bantu? Silakan ketik pertanyaan Anda tentang layanan DLH Kota Palu.",
                'timestamp' => now()->toIso8601String(),
            ];
            $this->saveMessages();
        }

        $this->dispatch('chatbot-toggled', $this->isOpen);
    }

    public function clearChat(): void
    {
        $this->messages = [];
        $this->pendingToken = '';
        session()->forget('chatbot_messages');

        // Kembalikan ke kondisi "chat pertama kali": simpan pesan sambutan.
        $this->messages[] = [
            'role'      => 'assistant',
            'content'   => "Halo!\n\nSaya adalah **DLH Assistant**, asisten AI untuk Dinas Lingkungan Hidup Kota Palu.\n\nAda yang bisa saya bantu? Silakan ketik pertanyaan Anda tentang layanan DLH Kota Palu.",
            'timestamp' => now()->toIso8601String(),
        ];
        $this->saveMessages();

        $this->dispatch('chatbot-cleared');
    }

    /**
     * Called by JS after the stream completes: saves AI reply to session.
     * Token harus cocok dengan yang diterbitkan addUserMessage — tanpa itu
     * panggilan dianggap spoofing dan balasan tidak disimpan.
     */
    public function saveAssistantMessage(string $content, string $token = ''): void
    {
        if (empty(trim($content))) {
            return;
        }

        if ($this->pendingToken === '' || ! hash_equals($this->pendingToken, $token)) {
            return;
        }

        $this->pendingToken = '';

        $this->messages[] = [
            'role'      => 'assistant',
            'content'   => trim($content),
            'timestamp' => now()->toIso8601String(),
        ];
        $this->saveMessages();
    }

    /**
     * Instantly adds user message to history (called by JS before streaming starts).
     */
    public function addUserMessage(string $content): void
    {
        $content = trim($content);
        if (empty($content)) {
            return;
        }

        $this->messages[] = [
            'role'      => 'user',
            'content'   => $content,
            'timestamp' => now()->toIso8601String(),
        ];
        $this->saveMessages();

        // Terbitkan token untuk balasan AI yang akan disimpan setelah stream.
        $this->pendingToken = Str::random(32);
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

    public function render()
    {
        return view('livewire.chat-bot');
    }
}
