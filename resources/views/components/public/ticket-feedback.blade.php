@props(['ticket'])

@php
    $feedback = $ticket->feedback;
    $ticketNumber = $ticket->nomor_tiket ?? $ticket->nomor_pengajuan ?? $ticket->nomor_registrasi ?? '';
    $feedbackSent = session('feedback_sent') && session('feedback_ticket') === $ticketNumber;
@endphp

@if ($feedbackSent || $feedback)
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg p-4 mt-4">
        <div class="flex items-center gap-2 mb-2">
            <x-icons.ui name="check" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
            <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ __('Terima kasih atas penilaian Anda!') }}</span>
        </div>
        <div class="flex items-center gap-1">
            @for ($i = 1; $i <= 5; $i++)
                <x-icons.ui name="star" class="h-5 w-5 {{ $i <= ($feedback->rating ?? 0) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}" />
            @endfor
        </div>
        @if ($feedback->komentar ?? null)
            <p class="text-sm text-emerald-700 dark:text-emerald-300 mt-2">{{ $feedback->komentar }}</p>
        @endif
    </div>
@else
    <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg p-4 mt-4" x-data="{ rating: 0, hover: 0 }">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">{{ __('Beri Penilaian') }}</p>
        <form method="POST" action="{{ route('feedback.store', $ticketNumber) }}">
            @csrf
            <div class="flex items-center gap-1 mb-3">
                @for ($i = 1; $i <= 5; $i++)
                    <button type="button" x-on:mouseenter="hover = {{ $i }}" x-on:mouseleave="hover = 0" x-on:click="rating = {{ $i }}"
                        class="focus:outline-none transition">
                        <x-icons.ui name="star" class="h-7 w-7 transition" x-bind:class="hover >= {{ $i }} || rating >= {{ $i }} ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600'" />
                    </button>
                @endfor
                <input type="hidden" name="rating" :value="rating" x-model="rating" />
            </div>
            @error('rating')
                <p class="text-xs text-red-500 mb-2">{{ $message }}</p>
            @enderror
            <textarea name="komentar" rows="2" placeholder="{{ __('Komentar (opsional)') }}"
                class="w-full rounded-md border border-slate-200 dark:border-slate-700 bg-transparent px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:text-slate-200 mb-3"></textarea>
            <button type="submit" x-bind:disabled="rating === 0"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-slate-900 text-slate-50 hover:bg-slate-900/90 h-9 px-4 dark:bg-slate-50 dark:text-slate-900 disabled:opacity-50 disabled:cursor-not-allowed">
                {{ __('Kirim Penilaian') }}
            </button>
        </form>
    </div>
@endif
