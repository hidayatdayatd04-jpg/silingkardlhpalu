@props([
    'title',
    'description' => null,
    'badge' => null,
])

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl border border-brand-200/60 dark:border-brand-800/40 bg-gradient-to-br from-brand-600 via-brand-700 to-emerald-800 px-6 py-8 sm:px-10 sm:py-10 text-white shadow-lg shadow-brand-900/10']) }}>
    <div class="absolute -top-16 -right-16 size-48 rounded-full bg-white/10 blur-2xl"></div>
    <div class="absolute -bottom-20 -left-10 size-40 rounded-full bg-emerald-400/20 blur-2xl"></div>

    <div class="relative z-10 max-w-3xl">
        @if ($badge)
            <span class="page-hero-enter inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold tracking-wide uppercase backdrop-blur-sm mb-4">
                {!! $badge !!}
            </span>
        @endif
        <h1 class="page-hero-enter text-2xl sm:text-3xl font-bold tracking-tight" style="--hero-delay:90ms">{!! $title !!}</h1>
        @if ($description)
            <p class="page-hero-enter mt-2 text-sm sm:text-base text-brand-50/90 leading-relaxed max-w-2xl" style="--hero-delay:180ms">{!! $description !!}</p>
        @endif
    </div>
</div>
