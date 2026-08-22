@extends('layouts.app')

@section('title', $artikel->judul.' - DLH Kota Palu')
@section('description', \Illuminate\Support\Str::limit(strip_tags($artikel->konten), 160))
@if ($artikel->thumbnail)
    @section('og_image', url('/file/og?path='.urlencode($artikel->thumbnail)))
@endif

@php
    // Bersihkan konten mentah dari editor (Jodit) yang kerap menyisipkan
    // paragraf/baris kosong (<p></p>, <p><br></p>, &nbsp;) sehingga tampilan
    // detail tidak berantakan dan tidak banyak spasi kosong.
    $kontenBersih = \App\Support\HtmlSanitizer::clean((string) $artikel->konten);
    do {
        $kontenPrev = $kontenBersih;
        $kontenBersih = preg_replace('/<(p|div|span)[^>]*>(?:\s|&nbsp;|<br\s*\/?>)*<\/\1>/i', '', $kontenBersih);
    } while ($kontenBersih !== $kontenPrev);
    $kontenBersih = trim($kontenBersih);
    $kontenKosong = trim(strip_tags($kontenBersih)) === '' && ! preg_match('/<(img|video|table|iframe)\b/i', $kontenBersih);
@endphp

@section('content')
<article class="max-w-3xl mx-auto">
    {{-- Kembali --}}
    @php
        $dariBeranda = request('dari') === 'beranda';
        $shareUrl = url()->current();
        $whatsAppShareUrl = 'https://wa.me/?text='.urlencode($artikel->judul."\n".$shareUrl);
    @endphp
    <a href="{{ $dariBeranda ? '/' : '/berita' }}" class="reveal is-revealed group inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 transition-[gap,color] dark:text-brand-400">
        <x-icons.ui name="arrow-right" class="size-4 -rotate-180 transition-transform duration-200 group-hover:-translate-x-0.5" />
        {{ $dariBeranda ? __('Kembali ke Beranda') : __('Kembali ke Berita') }}
    </a>

    {{-- Header --}}
    <header class="reveal mt-6 text-center">
        <h1 class="mt-5 text-3xl sm:text-4xl lg:text-[2.75rem] font-bold leading-[1.1] tracking-tight text-slate-900 dark:text-white">{{ $artikel->judul }}</h1>
        <div class="mt-5 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-sm text-slate-500 dark:text-slate-400">
            <span class="inline-flex items-center gap-1.5">
                <x-icons.ui name="calendar" class="size-4 text-brand-500" />
                {{ $artikel->tanggal_publish?->translatedFormat('d F Y') }}
            </span>
            <span class="inline-flex items-center gap-1.5">
                <x-icons.ui name="user" class="size-4 text-brand-500" />
                {{ $artikel->user?->name ?? 'Admin DLH' }}
            </span>
        </div>
    </header>

    {{-- Gambar utama --}}
    @if ($artikel->thumbnail)
        <figure class="reveal reveal-scale mt-9 overflow-hidden rounded-3xl shadow-[0_28px_70px_-30px_rgba(15,23,42,0.4)] ring-1 ring-slate-900/5 dark:ring-white/10">
            <img src="{{ Storage::disk('public')->temporaryUrl($artikel->thumbnail, now()->addHours(24)) }}" alt="{{ $artikel->judul }}" class="w-full object-cover max-h-[30rem]">
        </figure>
    @else
        <div class="reveal mt-9 h-56 rounded-3xl bg-gradient-to-br from-brand-600 via-brand-500 to-bay-400"></div>
    @endif

    {{-- Konten --}}
    <div class="reveal mt-10 text-[1.05rem] leading-8 text-slate-700 dark:text-slate-300
                [&_p]:mb-5 [&_p]:empty:hidden [&_h1]:mt-9 [&_h1]:mb-3 [&_h1]:text-3xl [&_h1]:font-bold [&_h1]:text-slate-900 dark:[&_h1]:text-white [&_h1]:tracking-tight
                [&_h2]:mt-9 [&_h2]:mb-3 [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:text-slate-900 dark:[&_h2]:text-white [&_h2]:tracking-tight
                [&_h3]:mt-7 [&_h3]:mb-2 [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-slate-900 dark:[&_h3]:text-white
                [&_h4]:mt-6 [&_h4]:mb-2 [&_h4]:text-lg [&_h4]:font-bold [&_h4]:text-slate-900 dark:[&_h4]:text-white
                [&_ul]:my-5 [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:space-y-2 [&_ol]:my-5 [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:space-y-2
                [&_li]:mb-1
                [&_a]:font-semibold [&_a]:text-brand-600 dark:[&_a]:text-brand-400 [&_a]:underline [&_a]:decoration-brand-500/30 [&_a]:underline-offset-2
                [&_img]:mx-auto [&_img]:my-6 [&_img]:rounded-2xl [&_img]:shadow-lg [&_img]:max-w-full [&_img]:h-auto
                [&_blockquote]:my-6 [&_blockquote]:border-l-4 [&_blockquote]:border-brand-500 [&_blockquote]:bg-brand-50/50 dark:[&_blockquote]:bg-brand-900/15 [&_blockquote]:rounded-r-xl [&_blockquote]:px-5 [&_blockquote]:py-3 [&_blockquote]:italic
                [&_strong]:text-slate-900 dark:[&_strong]:text-white [&_em]:italic
                [&_pre]:my-6 [&_pre]:rounded-xl [&_pre]:bg-slate-900 [&_pre]:p-5 [&_pre]:overflow-x-auto [&_pre]:text-sm [&_pre]:text-slate-100
                [&_code]:font-mono [&_code]:text-sm [&_code]:bg-slate-100 dark:[&_code]:bg-slate-800 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded
                [&_pre_code]:bg-transparent [&_pre_code]:p-0 [&_pre_code]:text-inherit
                [&_table]:my-6 [&_table]:w-full [&_table]:overflow-hidden [&_table]:rounded-xl [&_table]:border [&_table]:border-slate-200 dark:[&_table]:border-slate-700
                [&_thead]:bg-slate-50 dark:[&_thead]:bg-slate-800
                [&_th]:px-4 [&_th]:py-3 [&_th]:text-left [&_th]:text-sm [&_th]:font-bold [&_th]:text-slate-900 dark:[&_th]:text-white [&_th]:border-b [&_th]:border-slate-200 dark:[&_th]:border-slate-700
                [&_td]:px-4 [&_td]:py-3 [&_td]:text-sm [&_td]:border-b [&_td]:border-slate-100 dark:[&_td]:border-slate-800
                [&_tbody_tr:last-child_td]:border-b-0
                [&_hr]:my-8 [&_hr]:border-0 [&_hr]:h-px [&_hr]:bg-slate-200 dark:[&_hr]:bg-slate-700
                [&_figure]:my-6 [&_figure]:rounded-2xl [&_figure]:overflow-hidden
                [&_figcaption]:mt-2 [&_figcaption]:text-center [&_figcaption]:text-sm [&_figcaption]:text-slate-500 dark:[&_figcaption]:text-slate-400
                [&_sub]:text-xs [&_sub]:align-sub [&_sup]:text-xs [&_sup]:align-super">
        @if ($kontenKosong)
            <div class="flex flex-col items-center gap-2 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 px-6 py-10 text-center">
                <x-icons.ui name="document" class="size-8 text-slate-300 dark:text-slate-600" />
                <p class="text-sm text-slate-400 dark:text-slate-500">Konten artikel belum diisi.</p>
            </div>
        @else
            {!! $kontenBersih !!}
        @endif
    </div>

    {{-- Bagikan + kembali --}}
    <div class="reveal mt-12 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-200 dark:border-slate-800 pt-6">
        <div class="flex items-center gap-2.5">
            <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ __('Bagikan:') }}</span>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener noreferrer" class="size-9 inline-flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-white hover:bg-blue-600 transition-all hover:-translate-y-0.5" title="Facebook">
                <x-icons.social.facebook class="size-4" />
            </a>
            <a href="{{ $whatsAppShareUrl }}" target="_blank" rel="noopener noreferrer" class="size-9 inline-flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-white hover:bg-emerald-500 transition-all hover:-translate-y-0.5" title="WhatsApp" aria-label="{{ __('Bagikan ke WhatsApp') }}">
                <x-icons.social.whatsapp class="size-4" />
            </a>
            <button type="button" id="instagram-share-btn" class="relative size-9 inline-flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 transition-all hover:-translate-y-0.5 hover:bg-gradient-to-br hover:from-pink-500 hover:to-purple-600 hover:text-white" title="{{ __('Bagikan ke Instagram') }}" aria-label="{{ __('Bagikan ke Instagram') }}">
                <x-icons.social.instagram class="size-4" />
                <span class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-slate-900 px-2 py-0.5 text-[10px] text-white opacity-0"></span>
            </button>
            <button type="button" id="copy-link-btn" class="size-9 inline-flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-white hover:bg-slate-700 transition-all hover:-translate-y-0.5 relative" title="{{ __('Salin tautan') }}">
                <x-icons.ui name="copy" class="size-4" />
                <span class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-slate-900 px-2 py-0.5 text-[10px] text-white opacity-0"></span>
            </button>
        </div>
        <a href="{{ $dariBeranda ? '/' : '/berita' }}" class="group inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-brand-600 transition-[background-color,border-color,transform] duration-200 hover:border-brand-300 hover:bg-brand-50 dark:border-slate-700 dark:bg-slate-900 dark:text-brand-400 dark:hover:bg-brand-900/20">
            <x-icons.ui name="arrow-right" class="size-4 -rotate-180 transition-transform duration-200 group-hover:-translate-x-0.5" />
            {{ $dariBeranda ? __('Kembali ke Beranda') : __('Semua Berita') }}
        </a>
    </div>

    {{-- Komentar — UI selalu tampil; saat dinonaktifkan popup menampilkan pemberitahuan --}}
    <x-public.komentar-artikel :artikel="$artikel" />
</article>

@push('scripts')
<script>
    (function () {
        var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var els = document.querySelectorAll('.reveal');
        if (reduced || !('IntersectionObserver' in window)) {
            els.forEach(function (el) { el.classList.add('is-revealed'); });
        } else {
            var obs = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('is-revealed'); obs.unobserve(e.target); } });
            }, { rootMargin: '0px 0px -6% 0px', threshold: 0.08 });
            els.forEach(function (el) { obs.observe(el); });
        }

        function showShareFeedback(button, message) {
            var tip = button.querySelector('span');
            if (!tip) return;
            tip.textContent = message;
            tip.style.opacity = '1';
            setTimeout(function () { tip.style.opacity = '0'; }, 1800);
        }

        // CSP: tombol salin tautan dipasang via addEventListener, bukan onclick inline.
        var copyBtn = document.getElementById('copy-link-btn');
        if (copyBtn) {
            copyBtn.addEventListener('click', function () {
                if (!navigator.clipboard) return;
                navigator.clipboard.writeText(location.href).then(function () {
                    showShareFeedback(copyBtn, @json(__('Tersalin!')));
                });
            });
        }

        // Instagram tidak menyediakan URL share web. Gunakan native share sheet
        // agar Instagram dapat dipilih di perangkat yang mendukungnya; di desktop
        // tautan disalin sebagai fallback.
        var instagramBtn = document.getElementById('instagram-share-btn');
        if (instagramBtn) {
            instagramBtn.addEventListener('click', async function () {
                var shareData = {
                    title: @json($artikel->judul),
                    text: @json(__('Bagikan berita ini ke Instagram.')),
                    url: @json($shareUrl),
                };

                if (navigator.share) {
                    try {
                        await navigator.share(shareData);
                        return;
                    } catch (error) {
                        if (error && error.name === 'AbortError') return;
                    }
                }

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(location.href).then(function () {
                        showShareFeedback(instagramBtn, @json(__('Tautan disalin')));
                    });
                }
            });
        }
    })();
</script>
@endpush
@endsection
