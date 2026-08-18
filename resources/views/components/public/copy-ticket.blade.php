@props([
    'ticket' => null,
    'class' => 'text-2xl font-bold font-mono text-slate-900 dark:text-slate-100',
])

@if ($ticket)
<span x-data="{ copied: false }" class="inline-flex items-center gap-2 align-middle">
    <span x-ref="ticketText" class="{{ $class }} select-all">{{ $ticket }}</span>
    <button
        type="button"
        @click="navigator.clipboard.writeText($refs.ticketText.textContent.trim()).then(() => { copied = true; setTimeout(() => copied = false, 1500); }).catch(() => {})"
        :class="copied ? 'text-emerald-600 border-emerald-300 dark:border-emerald-700' : 'text-slate-400 hover:text-slate-600 dark:text-slate-500 dark:hover:text-slate-300 border-slate-200 dark:border-slate-700'"
        class="inline-flex items-center justify-center h-7 w-7 rounded-md border transition-colors shrink-0"
        :title="copied ? '{{ __('Tersalin') }}' : '{{ __('Salin nomor tiket') }}'"
        aria-label="{{ __('Salin nomor tiket') }}">
        <x-icons.ui name="copy" class="h-4 w-4" x-show="!copied" />
        <x-icons.ui name="check" class="h-4 w-4" x-show="copied" />
    </button>
</span>
@endif
