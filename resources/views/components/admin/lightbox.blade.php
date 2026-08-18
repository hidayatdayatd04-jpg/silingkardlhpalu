@props([
    'images' => [],   // array of url string, atau [['url'=>..,'caption'=>..], ...]
    'columns' => 3,
])

@php
    $items = collect($images)->map(function ($img) {
        if (is_array($img)) return ['url' => $img['url'] ?? '', 'caption' => $img['caption'] ?? ''];
        return ['url' => $img, 'caption' => ''];
    })->filter(fn ($i) => $i['url'] !== '')->values();

    $colCls = [2 => 'grid-cols-2', 3 => 'grid-cols-2 sm:grid-cols-3', 4 => 'grid-cols-2 sm:grid-cols-4'][$columns] ?? 'grid-cols-2 sm:grid-cols-3';
@endphp

<div
    x-data="{
        open: false,
        idx: 0,
        images: @js($items),
        show(i) { this.idx = i; this.open = true; },
        next() { this.idx = (this.idx + 1) % this.images.length; },
        prev() { this.idx = (this.idx - 1 + this.images.length) % this.images.length; },
    }"
    x-on:keydown.window.arrow-right="if (open) next()"
    x-on:keydown.window.arrow-left="if (open) prev()"
    x-on:keydown.escape.window="open = false"
    {{ $attributes }}
>
    @if($items->isEmpty())
        <p class="text-sm text-slate-400">Belum ada foto.</p>
    @else
        <div class="grid {{ $colCls }} gap-3">
            @foreach($items as $i => $img)
                <button
                    type="button"
                    x-on:click="show({{ $i }})"
                    class="group relative aspect-square overflow-hidden rounded-xl border border-slate-200 bg-slate-100 outline-none transition-[border-color,box-shadow] duration-150 hover:border-brand-300 focus-visible:ring-2 focus-visible:ring-brand-600/30 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-brand-700"
                >
                    <img src="{{ $img['url'] }}" alt="{{ $img['caption'] ?: 'Foto '.($i+1) }}" loading="lazy" class="size-full object-cover transition-transform duration-200 group-hover:scale-[1.03] motion-reduce:transition-none">
                    <div class="absolute inset-0 grid place-items-center bg-slate-950/0 transition-[background-color] duration-150 group-hover:bg-slate-950/35">
                        <span class="translate-y-1 text-white opacity-0 transition-[opacity,transform] duration-150 group-hover:translate-y-0 group-hover:opacity-100 motion-reduce:transition-none">
                            <x-admin.icon name="eye" :size="22" />
                        </span>
                    </div>
                </button>
            @endforeach
        </div>
    @endif

    {{-- Lightbox overlay --}}
    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition-[opacity] ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-[opacity] ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[130] flex items-center justify-center bg-ink-950/90 p-4"
            x-on:click.self="open = false"
            role="dialog"
            aria-modal="true"
            aria-label="Pratinjau foto"
        >
            <button type="button" x-on:click="open = false" class="absolute right-4 top-4 grid size-11 place-items-center rounded-full bg-white/10 text-white outline-none transition-[background-color,color] duration-150 hover:bg-white/20 focus-visible:ring-2 focus-visible:ring-white/70" aria-label="Tutup">
                <x-admin.icon name="x" :size="22" />
            </button>

            <button type="button" x-show="images.length > 1" x-on:click.stop="prev()" class="absolute left-4 grid size-11 place-items-center rounded-full bg-white/10 text-white outline-none transition-[background-color,color] duration-150 hover:bg-white/20 focus-visible:ring-2 focus-visible:ring-white/70" aria-label="Sebelumnya">
                <x-admin.icon name="chevron-left" :size="24" />
            </button>

            <figure class="max-h-[85vh] max-w-4xl">
                <img :src="images[idx]?.url" :alt="images[idx]?.caption" class="mx-auto max-h-[80vh] w-auto rounded-lg object-contain shadow-2xl">
                <figcaption x-show="images[idx]?.caption" x-text="images[idx]?.caption" class="mt-3 text-center text-sm text-white/80"></figcaption>
                <p class="mt-2 text-center text-xs font-semibold text-white/50"><span x-text="idx + 1"></span> / <span x-text="images.length"></span></p>
                <a
                    x-show="images[idx]?.url"
                    x-bind:href="images[idx]?.url"
                    download
                    class="mx-auto mt-4 inline-flex min-h-10 items-center gap-2 rounded-xl bg-white/15 px-4 text-sm font-semibold text-white outline-none backdrop-blur transition-[background-color] duration-150 hover:bg-white/25 focus-visible:ring-2 focus-visible:ring-white/70"
                    aria-label="Unduh foto"
                >
                    <x-admin.icon name="download" :size="16" /> Unduh
                </a>
            </figure>

            <button type="button" x-show="images.length > 1" x-on:click.stop="next()" class="absolute right-4 grid size-11 place-items-center rounded-full bg-white/10 text-white outline-none transition-[background-color,color] duration-150 hover:bg-white/20 focus-visible:ring-2 focus-visible:ring-white/70" aria-label="Berikutnya">
                <x-admin.icon name="chevron-right" :size="24" />
            </button>
        </div>
    </template>
</div>
