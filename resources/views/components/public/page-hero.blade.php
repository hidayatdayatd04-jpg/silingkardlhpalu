@props([
    'title',
    'description' => null,
    'badge' => null,
    'icon' => 'leaf',
])

@php
    // String attributes rendered with {{ $value }} are HTML-escaped before they
    // reach a Blade component. Decode that one attribute layer here, then let
    // the normal Blade output below escape the final text safely.
    $decodeHeroText = static fn ($value) => is_string($value)
        ? html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8')
        : $value;

    $title = $decodeHeroText($title);
    $description = $decodeHeroText($description);
    $badge = $decodeHeroText($badge);
@endphp

<section {{ $attributes->merge(['class' => 'page-hero relative isolate overflow-hidden rounded-[1.75rem] border border-brand-200/70 px-6 py-8 text-white shadow-[0_20px_54px_-30px_rgba(6,78,59,0.55)] dark:border-brand-800/60 sm:px-9 sm:py-10 lg:px-12 lg:py-12']) }}>
    <div class="page-hero__orb page-hero__orb--top" aria-hidden="true"></div>
    <div class="page-hero__orb page-hero__orb--bottom" aria-hidden="true"></div>
    <div class="page-hero__contour" aria-hidden="true"></div>

    <div class="relative z-10 max-w-3xl">
        @if ($badge)
            <span class="page-hero-enter inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold tracking-[0.08em] text-white backdrop-blur-sm" style="--hero-delay:0ms">
                <span class="grid size-5 place-items-center rounded-full bg-brand-300/20 text-brand-100" aria-hidden="true">
                    <x-icons.ui :name="$icon" class="size-3.5" />
                </span>
                <span>{{ $badge }}</span>
            </span>
        @endif

        <h1 class="page-hero-enter mt-5 max-w-2xl min-w-0 text-[1.7rem] leading-tight font-extrabold tracking-[-0.035em] text-white break-words sm:text-4xl sm:leading-[1.15] lg:text-[2.75rem] lg:leading-[1.08]" style="--hero-delay:90ms">{{ $title }}</h1>

        @if ($description)
            <p class="page-hero-enter mt-4 max-w-2xl text-sm leading-7 text-brand-50/95 sm:text-base" style="--hero-delay:180ms">{{ $description }}</p>
        @endif
    </div>
</section>
