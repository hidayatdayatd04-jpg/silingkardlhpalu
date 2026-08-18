@props([
    'title' => '',
    'subtitle' => null,
    'breadcrumbs' => [],
    'icon' => null,
    'hero' => false,
])

@php
    $isHero = filter_var($hero, FILTER_VALIDATE_BOOL);
    $wrap = $isHero
        ? 'relative overflow-hidden rounded-2xl border border-brand-900/35 bg-brand-800 px-5 py-5 text-white shadow-[0_14px_32px_-20px_rgba(21,128,61,0.65)] sm:px-6'
        : 'relative py-1';
@endphp

<header {{ $attributes->merge(['class' => $wrap]) }}>
    @if($isHero)
        <div aria-hidden="true" class="pointer-events-none absolute inset-y-0 right-0 w-1/3 bg-brand-700/35"></div>
    @endif

    <div class="relative">
        @if(!empty($breadcrumbs))
            <nav aria-label="Jejak navigasi" class="mb-3">
                <ol class="flex flex-wrap items-center gap-1.5 text-xs font-medium {{ $isHero ? 'text-white/75' : 'text-slate-500 dark:text-slate-400' }}">
                    @foreach($breadcrumbs as $crumb)
                        <li class="flex min-w-0 items-center gap-1.5">
                            @if(!empty($crumb['url']) && !$loop->last)
                                <a href="{{ $crumb['url'] }}" class="truncate transition-colors duration-150 {{ $isHero ? 'hover:text-white' : 'hover:text-brand-700 dark:hover:text-brand-300' }}">{{ $crumb['label'] }}</a>
                            @else
                                <span class="truncate {{ $isHero ? 'text-white' : 'text-slate-700 dark:text-slate-200' }}" @if($loop->last) aria-current="page" @endif>{{ $crumb['label'] }}</span>
                            @endif
                            @unless($loop->last)
                                <x-admin.icon name="chevron-right" :size="14" class="shrink-0 opacity-55" aria-hidden="true" />
                            @endunless
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex min-w-0 items-start gap-3">
                @if($icon)
                    <div class="grid size-10 shrink-0 place-items-center rounded-xl {{ $isHero ? 'bg-white/12 text-white' : 'bg-brand-50 text-brand-700 dark:bg-brand-950/55 dark:text-brand-300' }}">
                        <x-admin.icon :name="$icon" :size="20" aria-hidden="true" />
                    </div>
                @endif
                <div class="min-w-0">
                    <h1 class="text-xl font-bold tracking-tight {{ $isHero ? 'text-white' : 'text-slate-950 dark:text-white' }} sm:text-2xl">{{ $title }}</h1>
                    @if($subtitle)
                        <p class="mt-1 max-w-3xl text-sm leading-6 {{ $isHero ? 'text-white/80' : 'text-slate-600 dark:text-slate-300' }}">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>

            @if(isset($actions))
                <div class="flex shrink-0 flex-wrap items-center gap-2 sm:justify-end">
                    {{ $actions }}
                </div>
            @endif
        </div>

        {{ $slot }}
    </div>
</header>
