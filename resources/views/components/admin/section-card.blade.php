@props([
    'number' => null,       // badge angka wizard (1..5)
    'title' => '',
    'subtitle' => null,
    'icon' => null,
])

<section {{ $attributes->merge(['class' => 'relative card-lift rounded-xl border border-slate-200/80 bg-white shadow-[var(--shadow-soft)]']) }}>
    <div class="flex items-start gap-4 border-b border-slate-100 p-6">
        @if($number !== null)
            <div class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-600 text-sm font-bold text-white shadow-[var(--shadow-brand-glow)]">
                {{ $number }}
            </div>
        @elseif($icon)
            <div class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600">
                <x-admin.icon :name="$icon" :size="22" />
            </div>
        @endif
        <div class="min-w-0 flex-1 pt-1">
            <h2 class="text-h4 font-bold text-ink-900">{{ $title }}</h2>
            @if($subtitle)
                <p class="mt-0.5 text-sm text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($actions))
            <div class="shrink-0">{{ $actions }}</div>
        @endif
    </div>

    <div class="p-6">
        {{ $slot }}
    </div>
</section>
