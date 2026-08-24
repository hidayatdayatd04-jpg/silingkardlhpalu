{{-- ============================================================
     Splash "SILINGKAR" — layar loading bermerek yang tampil
     SETIAP pindah halaman (publik & admin).
     - Tampil seketika saat dokumen mulai dimuat (CSS/JS inline,
       tanpa menunggu bundle Vite).
     - Terangkat ±400 ms setelah DOM siap (150 ms bila
       prefers-reduced-motion), failsafe 4 dtk.
     - Klik link internal / submit form GET internal memunculkan
       splash lagi sebelum navigasi → transisi antarhalaman kontinu.
     Desain mengikuti preloader beranda (components/public/preloader).
============================================================ --}}
<div id="silingkar-splash" role="status" aria-live="polite" aria-label="{{ __('Memuat SILINGKAR DLH Kota Palu') }}">
    <div class="sk-pre__glow" aria-hidden="true"></div>
    <div class="sk-pre__inner">
        <div class="sk-pre__badge">
            <span class="sk-pre__ring" aria-hidden="true"></span>
            <span class="sk-pre__ring2" aria-hidden="true"></span>
            <img src="{{ asset('assets/images/logo-web.webp') }}" alt="" width="120" height="120" class="sk-pre__logo">
        </div>
        <p class="sk-pre__title">{{ __('Dinas Lingkungan Hidup') }}</p>
        <p class="sk-pre__subtitle">{{ __('Kota Palu') }}</p>
        <div class="sk-pre__bar" aria-hidden="true"><span id="sk-pre-fill"></span></div>
        <p class="sk-pre__hint">{{ __('Menyiapkan layanan untuk Anda...') }}</p>
    </div>
</div>

{{-- Bila JS mati: sembunyikan splash agar konten tetap terbaca. --}}
<noscript><style>#silingkar-splash{display:none!important}</style></noscript>

<style>
    #silingkar-splash{position:fixed;inset:0;z-index:100000;display:flex;align-items:center;justify-content:center;
        background:radial-gradient(circle at 30% 25%,#065f46 0%,#064e3b 45%,#082f40 100%);
        opacity:1;transition:opacity .3s ease,visibility .3s ease;overflow:hidden}
    #silingkar-splash.sk-pre--hide{opacity:0;visibility:hidden;pointer-events:none}
    .sk-pre__glow{position:absolute;width:520px;height:520px;border-radius:9999px;
        background:radial-gradient(circle,rgba(45,212,191,.35),transparent 60%);filter:blur(40px);
        animation:sk-glow 3s ease-in-out infinite}
    .sk-pre__inner{position:relative;display:flex;flex-direction:column;align-items:center;text-align:center;padding:1rem}
    .sk-pre__badge{position:relative;display:flex;align-items:center;justify-content:center;
        width:156px;height:156px;margin-bottom:1.5rem}
    .sk-pre__ring{position:absolute;inset:0;border-radius:9999px;
        border:3px solid rgba(255,255,255,.10);border-top-color:#6ee7b7;border-right-color:#28c6e8;
        animation:sk-spin 1s linear infinite}
    .sk-pre__ring2{position:absolute;inset:10px;border-radius:9999px;
        border:2px solid rgba(255,255,255,.08);border-bottom-color:#34d399;
        animation:sk-spin 1.6s linear infinite reverse}
    .sk-pre__logo{width:120px;height:120px;object-fit:contain;filter:drop-shadow(0 10px 24px rgba(0,0,0,.4));
        animation:sk-pop .7s cubic-bezier(.16,1,.3,1) both,sk-float 3s ease-in-out .7s infinite}
    .sk-pre__title{color:#fff;font-weight:800;letter-spacing:.12em;text-transform:uppercase;font-size:.95rem;
        opacity:0;animation:sk-rise .6s ease .25s forwards}
    .sk-pre__subtitle{color:#6ee7b7;font-weight:700;letter-spacing:.28em;text-transform:uppercase;font-size:.72rem;margin-top:.25rem;
        opacity:0;animation:sk-rise .6s ease .38s forwards}
    .sk-pre__bar{width:200px;height:4px;border-radius:9999px;background:rgba(255,255,255,.14);overflow:hidden;margin-top:1.5rem}
    .sk-pre__bar span{display:block;height:100%;width:0%;border-radius:9999px;
        background:linear-gradient(90deg,#6ee7b7,#28c6e8);transition:width .2s linear}
    .sk-pre__hint{color:rgba(255,255,255,.6);font-size:.72rem;margin-top:1rem;
        opacity:0;animation:sk-rise .6s ease .5s forwards}
    @keyframes sk-spin{to{transform:rotate(360deg)}}
    @keyframes sk-pop{from{transform:scale(.6);opacity:0}to{transform:scale(1);opacity:1}}
    @keyframes sk-float{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}}
    @keyframes sk-rise{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
    @keyframes sk-glow{0%,100%{opacity:.7;transform:scale(1)}50%{opacity:1;transform:scale(1.1)}}
    @media (prefers-reduced-motion:reduce){
        .sk-pre__ring,.sk-pre__ring2,.sk-pre__logo,.sk-pre__glow{animation:none}
        .sk-pre__title,.sk-pre__subtitle,.sk-pre__hint{animation:none;opacity:1}
    }
    /* Mode lanjut (hasil navigasi antarhalaman): tanpa animasi intro agar
       splash terasa satu kesatuan dengan splash halaman sebelumnya. */
    .sk-pre--resume .sk-pre__logo{animation:none}
    .sk-pre--resume .sk-pre__title,.sk-pre--resume .sk-pre__subtitle,
    .sk-pre--resume .sk-pre__hint{animation:none;opacity:1}
</style>

<script>
    (function () {
        // Pengaman penyertaan ganda: sisakan #silingkar-splash pertama saja
        // (id harus unik — duplikat tidak akan pernah terikat & terangkat).
        var all = document.querySelectorAll('#silingkar-splash');
        for (var d = 1; d < all.length; d++) {
            if (all[d].parentNode) all[d].parentNode.removeChild(all[d]);
        }
        var pre = document.getElementById('silingkar-splash');
        if (!pre || pre.dataset.bound === '1') return;
        pre.dataset.bound = '1';

        var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var MIN_SHOW = reduced ? 150 : 400;   // durasi minimum splash tiap halaman
        var fill = document.getElementById('sk-pre-fill');
        var start = Date.now();
        var hidden = false;

        // Mode lanjut: halaman sebelumnya sudah menampakkan splash saat link
        // diklik (flag dari pagehide) — lanjutkan tanpa animasi intro supaya
        // terasa satu splash berkelanjutan, bukan dua tampilan berturut-turut.
        var resume = false;
        try {
            var rts = Number(sessionStorage.getItem('silingkar-splash-resume') || 0);
            if (rts && Date.now() - rts < 5000) resume = true;
            sessionStorage.removeItem('silingkar-splash-resume');
        } catch (e) {}
        if (resume) pre.classList.add('sk-pre--resume');

        document.documentElement.style.overflow = 'hidden';

        if (resume) {
            if (fill) { fill.style.transition = 'none'; fill.style.width = '100%'; }
        } else if (fill && !reduced) {
            (function tickBar() {
                if (hidden) return;
                var p = Math.min((Date.now() - start) / MIN_SHOW, 1);
                fill.style.width = (p * 100) + '%';
                if (p < 1) requestAnimationFrame(tickBar);
            })();
        } else if (fill) {
            fill.style.width = '100%';
        }

        function hide() {
            if (hidden) return;
            hidden = true;
            pre.classList.add('sk-pre--hide');
            document.documentElement.style.overflow = '';
            // Elemen sengaja TIDAK dihapus dari DOM: splash dipakai ulang
            // seketika saat link internal diklik (showForNavigation).
        }

        // Terangkat setelah durasi minimum tercapai sejak DOM siap.
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(hide, Math.max(0, MIN_SHOW - (Date.now() - start)));
            });
        } else {
            setTimeout(hide, Math.max(0, MIN_SHOW - (Date.now() - start)));
        }
        // Failsafe: jangan pernah macet menutupi halaman.
        var failsafe = setTimeout(hide, 4000);

        // Kembali dari bfcache (tombol back) → splash pasti disembunyikan.
        window.addEventListener('pageshow', function (e) { if (e.persisted) hide(); });

        // Navigasi keluar setelah splash dimunculkan → tandai halaman
        // berikutnya untuk MELANJUTKAN splash (mode lanjut) via sessionStorage.
        var navPending = false;
        window.addEventListener('pagehide', function () {
            if (!navPending) return;
            try { sessionStorage.setItem('silingkar-splash-resume', String(Date.now())); } catch (e) {}
        });

        // Pindah halaman: tampilkan splash sebelum browser meninggalkan
        // halaman ini agar transisi antarhalaman terasa kontinu.
        function sameDestination(url) {
            return url.origin === location.origin
                && url.pathname + url.search === location.pathname + location.search;
        }
        document.addEventListener('click', function (e) {
            if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            var el = e.target;
            while (el && el.nodeType === 1 && el.nodeName.toUpperCase() !== 'A') el = el.parentElement;
            if (!el || !el.href) return;
            if ((el.getAttribute('target') || '').toLowerCase() === '_blank') return;
            if (el.hasAttribute('download')) return;
            var u;
            try { u = new URL(el.href, location.href); } catch (err) { return; }
            if (u.protocol !== 'http:' && u.protocol !== 'https:') return;
            if (sameDestination(u) && !u.hash) return; // anchor/tautan tempat sama
            showForNavigation();
        }, true);
        document.addEventListener('submit', function (e) {
            var f = e.target;
            if (!(f instanceof HTMLFormElement)) return;
            if ((f.getAttribute('method') || 'get').toLowerCase() !== 'get') return;
            if (f.hasAttribute('target') || f.hasAttribute('data-no-splash')) return;
            var u;
            try { u = new URL(f.getAttribute('action') || location.href, location.href); } catch (err) { return; }
            if (u.origin !== location.origin) return;
            showForNavigation();
        }, true);

        // Navigasi Livewire (bila dipakai) juga memunculkan splash.
        document.addEventListener('livewire:navigate', function () { showForNavigation(); });

        function showForNavigation() {
            navPending = true;
            if (!hidden) return;
            // Splash sudah terangkat — paksa tampil kembali SEKETIKA saat
            // link internal diklik, jangan menunggu respons server (TTFB).
            hidden = false;
            pre.classList.remove('sk-pre--hide');
            document.documentElement.style.overflow = 'hidden';
            start = Date.now();
            if (failsafe) clearTimeout(failsafe);
            if (fill && !reduced) {
                fill.style.width = '0%';
                (function tickBar() {
                    if (hidden) return;
                    var p = Math.min((Date.now() - start) / MIN_SHOW, 1);
                    fill.style.width = (p * 100) + '%';
                    if (p < 1) requestAnimationFrame(tickBar);
                })();
            } else if (fill) {
                fill.style.width = '100%';
            }
            // Pengaman: bila navigasi tidak jadi terjadi (link dicegah handler
            // lain), splash menutup sendiri; bila jalan, dokumen lama hancur
            // dan timer mati bersamanya.
            failsafe = setTimeout(hide, 4000);
        }
        // Terekspos ke shell desktop: penangan _blank shell memakai ini bila
        // shell menyerahkan splash ke halaman web (lihat __skxShow di main.rs).
        window.__skWebSplashShow = showForNavigation;
    })();
</script>
