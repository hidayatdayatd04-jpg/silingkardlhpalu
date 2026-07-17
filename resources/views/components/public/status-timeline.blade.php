@props(['timeline' => []])

@if(count($timeline) > 1)
    <div class="mt-4">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">{{ __('Riwayat Status') }}</p>
        <div class="relative ml-3">
            {{-- Vertical line --}}
            <div class="absolute left-0 top-2 bottom-2 w-0.5 bg-slate-200 dark:bg-slate-700"></div>

            @foreach ($timeline as $index => $entry)
                @php
                    $colorMap = [
                        'gray' => 'bg-slate-400',
                        'amber' => 'bg-amber-400',
                        'emerald' => 'bg-emerald-400',
                        'red' => 'bg-red-400',
                        'brand' => 'bg-brand-500',
                        'sky' => 'bg-sky-400',
                    ];
                    $dotColor = $colorMap[$entry['color']] ?? 'bg-slate-400';
                    $isLast = $index === count($timeline) - 1;
                @endphp
                <div class="relative flex items-start gap-3 pb-4 {{ $isLast ? 'pb-0' : '' }}">
                    {{-- Dot --}}
                    <div class="absolute left-0 top-1.5 -translate-x-1/2 z-10">
                        <div class="h-3 w-3 rounded-full {{ $dotColor }} {{ $isLast ? 'ring-2 ring-white dark:ring-slate-950 ring-offset-2' : '' }}"></div>
                    </div>
                    {{-- Content --}}
                    <div class="ml-4 {{ $isLast ? '' : '' }}">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $entry['label'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $entry['time']->diffForHumans() }} &middot; {{ $entry['time']->format('d M Y H:i') }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
