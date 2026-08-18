@props([
    'icon' => 'folder',
    'title' => 'Belum ada data',
    'description' => 'Data akan muncul di sini ketika sudah ditambahkan.',
    'actionText' => null,
    'actionUrl' => null,
    'actionIcon' => 'plus',
])

<div {{ $attributes->merge(['class' => 'px-5 py-14 text-center sm:px-8']) }}>
    <div class="mx-auto max-w-md">
        <div class="mx-auto grid size-16 place-items-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
            <x-admin.icon :name="$icon" :size="32" :stroke="1.7" aria-hidden="true" />
        </div>
        <div class="mt-5">
            <h2 class="text-base font-bold text-slate-950 dark:text-white">{{ $title }}</h2>
            <p class="mt-1.5 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $description }}</p>
        </div>
        @if($actionText && $actionUrl)
            <div class="mt-5"><x-admin.button variant="primary" :icon="$actionIcon" :href="$actionUrl">{{ $actionText }}</x-admin.button></div>
        @endif
        @if($slot->isNotEmpty())
            <div class="mt-5">{{ $slot }}</div>
        @endif
    </div>
</div>
