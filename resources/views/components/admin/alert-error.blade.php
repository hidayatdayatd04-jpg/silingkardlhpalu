@props([
    'title' => 'Ada yang perlu diperbaiki',
    'errors' => null,
    'icon' => 'alert-triangle',
])

@php
    $messages = [];
    if ($errors instanceof \Illuminate\Support\MessageBag) {
        foreach ($errors->all() as $msg) {
            $messages[] = $msg;
        }
    } elseif (is_iterable($errors)) {
        foreach ($errors as $item) {
            if (is_iterable($item)) {
                foreach ($item as $msg) {
                    $messages[] = $msg;
                }
            } else {
                $messages[] = $item;
            }
        }
    }
@endphp

@if(count($messages))
    <div x-data="{ show: true }" x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-rose-200/80 bg-gradient-to-br from-rose-50 to-red-50 px-5 py-4 shadow-[0_12px_30px_-12px_rgba(225,29,72,0.35)] dark:border-rose-500/30 dark:from-rose-500/10 dark:to-red-500/5']) }}>
        <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-rose-300/20 blur-2xl"></div>
        <div class="relative flex items-start gap-3.5">
            <div class="grid size-9 shrink-0 place-items-center rounded-xl bg-rose-500/15 text-rose-600 ring-1 ring-inset ring-rose-300/40 dark:bg-rose-500/20 dark:text-rose-300">
                <x-admin.icon :name="$icon" :size="20" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-rose-800 dark:text-rose-200">{{ $title }}</p>
                <ul class="mt-1.5 space-y-1 text-sm text-rose-700/90 dark:text-rose-300/90">
                    @foreach($messages as $msg)
                        <li class="flex items-start gap-2">
                            <x-admin.icon name="alert-circle" :size="16" class="mt-0.5 shrink-0 text-rose-400" />
                            <span>{{ $msg }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <button type="button" @click="show = false" class="shrink-0 rounded-lg p-1 text-rose-400 transition hover:bg-rose-500/10 hover:text-rose-600 dark:hover:text-rose-300" aria-label="Tutup">
                <x-admin.icon name="x" :size="16" />
            </button>
        </div>
    </div>
@endif
