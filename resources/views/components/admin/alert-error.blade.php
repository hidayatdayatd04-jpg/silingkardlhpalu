@props([
    'title' => 'Ada yang perlu diperbaiki',
    'errors' => null,
    'icon' => 'alert-triangle',
])

@php
    $messages = [];
    if ($errors instanceof \Illuminate\Support\MessageBag) {
        $messages = $errors->all();
    } elseif (is_iterable($errors)) {
        foreach ($errors as $item) {
            foreach (is_iterable($item) ? $item : [$item] as $message) $messages[] = $message;
        }
    }
@endphp

@if(count($messages))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:enter="transition-[opacity,transform] ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition-[opacity,transform] ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        {{ $attributes->merge(['class' => 'rounded-2xl border border-danger-200 bg-danger-50 px-4 py-3.5 dark:border-danger-900/70 dark:bg-danger-950/35']) }}
        role="alert"
    >
        <div class="flex items-start gap-3">
            <div class="grid size-8 shrink-0 place-items-center rounded-xl bg-danger-100 text-danger-700 dark:bg-danger-900/45 dark:text-danger-300"><x-admin.icon :name="$icon" :size="18" aria-hidden="true" /></div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-danger-800 dark:text-danger-200">{{ $title }}</p>
                <ul class="mt-1.5 space-y-1 text-sm leading-5 text-danger-700 dark:text-danger-300">
                    @foreach($messages as $message)
                        <li class="flex items-start gap-1.5"><x-admin.icon name="alert-circle" :size="14" class="mt-0.5 shrink-0" aria-hidden="true" /><span>{{ $message }}</span></li>
                    @endforeach
                </ul>
            </div>
            <button type="button" x-on:click="show = false" class="grid size-8 shrink-0 place-items-center rounded-lg text-danger-600 outline-none transition-colors duration-150 hover:bg-danger-100 focus-visible:bg-danger-100 dark:text-danger-300 dark:hover:bg-danger-900/45 dark:focus-visible:bg-danger-900/45" aria-label="Tutup ringkasan kesalahan"><x-admin.icon name="x" :size="17" aria-hidden="true" /></button>
        </div>
    </div>
@endif
