<?php

namespace App\Livewire;

use Livewire\Component;

class ChatBot extends Component
{
    public bool $isOpen = false;
    public string $message = '';
    public array $messages = [];

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
        session()->forget('chatbot_messages');

        $this->messages[] = [
            'role'      => 'assistant',
            'content'   => "Chat telah dihapus. Ada yang bisa saya bantu?",
            'timestamp' => now()->toIso8601String(),
        ];
        $this->saveMessages();

        $this->dispatch('chatbot-cleared');
    }

    /**
     * Called by JS after the stream completes: saves AI reply to session.
     */
    public function saveAssistantMessage(string $content): void
    {
        if (empty(trim($content))) {
            return;
        }

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
