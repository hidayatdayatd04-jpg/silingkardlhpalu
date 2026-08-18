@props([
    'title' => '',
    'icon' => 'folder',
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200/90 bg-white p-5 shadow-[0_8px_24px_-18px_rgba(15,23,42,0.32)] dark:border-slate-800 dark:bg-slate-900 sm:p-6']) }}>
    <div class="mb-5 flex items-start gap-3">
        <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/55 dark:text-brand-300">
            <x-admin.icon :name="$icon" :size="19" aria-hidden="true" />
        </div>
        <div class="min-w-0 flex-1">
            <h2 class="text-base font-bold text-slate-950 dark:text-white">{{ $title }}</h2>
            @if($description)
                <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $description }}</p>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        {{ $slot }}
    </div>
</section>
