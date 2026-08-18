@props([
    'number' => null,
    'title' => '',
    'subtitle' => null,
    'icon' => null,
])

<section {{ $attributes->merge(['class' => 'relative overflow-visible rounded-2xl border border-slate-200/90 bg-white shadow-[0_8px_24px_-18px_rgba(15,23,42,0.36)] dark:border-slate-800 dark:bg-slate-900']) }}>
    <div class="flex items-start gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:px-6">
        @if($number !== null)
            <div class="grid size-8 shrink-0 place-items-center rounded-lg bg-brand-700 text-xs font-bold text-white shadow-sm">
                {{ $number }}
            </div>
        @elseif($icon)
            <div class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-950/55 dark:text-brand-300">
                <x-admin.icon :name="$icon" :size="20" aria-hidden="true" />
            </div>
        @endif
        <div class="min-w-0 flex-1 pt-0.5">
            <h2 class="text-base font-bold text-slate-950 dark:text-white">{{ $title }}</h2>
            @if($subtitle)
                <p class="mt-0.5 text-sm leading-5 text-slate-600 dark:text-slate-300">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($actions))
            <div class="shrink-0">{{ $actions }}</div>
        @endif
    </div>

    <div class="p-5 sm:p-6">
        {{ $slot }}
    </div>
</section>
