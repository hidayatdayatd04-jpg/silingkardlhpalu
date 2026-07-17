@props([
    'title' => '',
    'subtitle' => null,
    'breadcrumbs' => [],   // [['label' => 'Dashboard', 'url' => '...'], ['label' => 'Pengaduan']]
    'icon' => null,
    'hero' => false,       // gaya gradient hero gelap
])

@php
    $isHero = filter_var($hero, FILTER_VALIDATE_BOOL);
    $wrap = $isHero
        ? 'relative overflow-hidden rounded-xl border border-forest-800/40 p-6 text-white shadow-[var(--shadow-lift)]'
        : 'relative';
@endphp

<div {{ $attributes->merge(['class' => $wrap]) }} @if($isHero) style="background: var(--gradient-header-hero);" @endif>
    @if($isHero)
        <div class="bg-grain pointer-events-none absolute inset-0 opacity-[0.04]"></div>
    @endif

    <div class="relative">
        {{-- Breadcrumb --}}
        @if(!empty($breadcrumbs))
            <nav aria-label="Breadcrumb" class="mb-2">
                <ol class="flex flex-wrap items-center gap-1.5 text-xs font-semibold {{ $isHero ? 'text-white/70' : 'text-slate-500' }}">
                    @foreach($breadcrumbs as $i => $crumb)
                        <li class="flex items-center gap-1.5">
                            @if(!empty($crumb['url']) && !$loop->last)
                                <a href="{{ $crumb['url'] }}" class="transition hover:{{ $isHero ? 'text-white' : 'text-brand-600' }}">{{ $crumb['label'] }}</a>
                            @else
                                <span class="{{ $isHero ? 'text-white' : 'text-slate-700' }}">{{ $crumb['label'] }}</span>
                            @endif
                            @unless($loop->last)
                                <x-admin.icon name="chevron-right" :size="14" class="opacity-50" />
                            @endunless
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                @if($icon)
                    <div class="grid size-11 shrink-0 place-items-center rounded-xl {{ $isHero ? 'bg-white/10 text-white' : 'bg-brand-50 text-brand-600' }}">
                        <x-admin.icon :name="$icon" :size="22" />
                    </div>
                @endif
                <div class="min-w-0">
                    <h1 class="truncate text-h1 font-bold {{ $isHero ? 'text-white' : 'text-ink-900' }}">{{ $title }}</h1>
                    @if($subtitle)
                        <p class="mt-1 text-sm {{ $isHero ? 'text-white/75' : 'text-slate-500' }}">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>

            @if(isset($actions))
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>

        {{ $slot }}
    </div>
</div>
