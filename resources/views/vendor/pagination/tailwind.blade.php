@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between gap-4">
        {{-- Mobile --}}
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex cursor-default items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-400">
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-ink-700 transition hover:border-brand-300 hover:bg-brand-50">
                    Sebelumnya
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-ink-700 transition hover:border-brand-300 hover:bg-brand-50">
                    Berikutnya
                </a>
            @else
                <span class="inline-flex cursor-default items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-400">
                    Berikutnya
                </span>
            @endif
        </div>

        {{-- Desktop --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <p class="text-sm text-slate-500">
                Menampilkan
                <span class="font-bold text-ink-800">{{ $paginator->firstItem() }}</span>
                –
                <span class="font-bold text-ink-800">{{ $paginator->lastItem() }}</span>
                dari
                <span class="font-bold text-ink-800">{{ $paginator->total() }}</span>
                data
            </p>

            <span class="inline-flex items-center gap-1">
                {{-- Prev --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-slate-50 text-slate-300">
                        <x-admin.icon name="chevron-left" :size="16" />
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya" class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700">
                        <x-admin.icon name="chevron-left" :size="16" />
                    </a>
                @endif

                {{-- Numbers --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="grid size-9 place-items-center text-sm font-semibold text-slate-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="grid size-9 place-items-center rounded-lg bg-brand-600 text-sm font-bold text-white shadow-[var(--shadow-brand-glow)]">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-white text-sm font-semibold text-ink-700 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya" class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700">
                        <x-admin.icon name="chevron-right" :size="16" />
                    </a>
                @else
                    <span aria-disabled="true" class="grid size-9 place-items-center rounded-lg border border-slate-200 bg-slate-50 text-slate-300">
                        <x-admin.icon name="chevron-right" :size="16" />
                    </span>
                @endif
            </span>
        </div>
    </nav>
@endif
