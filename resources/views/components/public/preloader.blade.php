{{-- ============================================================
     Preloader "Penyambutan" — layar loading bermerek DLH Kota Palu.
     Hanya dipasang di BERANDA (welcome.blade.php), tampil SETIAP refresh,
     durasi tetap ~3 detik, lalu memudar. Menghormati reduced-motion.
     Critical CSS/JS inline agar tampil benar sebelum bundle Vite dimuat.
============================================================ --}}
<div id="dlh-preloader" role="status" aria-live="polite" aria-label="{{ __('Memuat portal DLH Kota Palu') }}">
    <div class="dlh-pre__glow" aria-hidden="true"></div>
    <div class="dlh-pre__inner">
        <div class="dlh-pre__badge">
            <span class="dlh-pre__ring" aria-hidden="true"></span>
            <span class="dlh-pre__ring2" aria-hidden="true"></span>
            <img src="{{ asset('assets/images/logo_kota_palu.png') }}" alt="" width="92" height="92" class="dlh-pre__logo">
        </div>
        <p class="dlh-pre__title">{{ __('Dinas Lingkungan Hidup') }}</p>
        <p class="dlh-pre__subtitle">{{ __('Kota Palu') }}</p>
        <div class="dlh-pre__bar" aria-hidden="true"><span id="dlh-pre-fill"></span></div>
        <p class="dlh-pre__hint">{{ __('Menyiapkan layanan untuk Anda…') }}</p>
    </div>
</div>

{{-- Bila JS mati: sembunyikan preloader & pastikan konten beranimasi tetap tampil penuh. --}}
<noscript><style>
    #dlh-preloader{display:none!important}
    .hero-enter{animation:none!important;opacity:1!important;transform:none!important}
    .reveal{opacity:1!important;transform:none!important}
</style></noscript>

<style>
    #dlh-preloader{position:fixed;inset:0;z-index:100000;display:flex;align-items:center;justify-content:center;
        background:radial-gradient(circle at 30% 25%,#065f46 0%,#064e3b 45%,#082f40 100%);
        opacity:1;transition:opacity .6s ease,visibility .6s ease;overflow:hidden}
    #dlh-preloader.dlh-pre--hide{opacity:0;visibility:hidden;pointer-events:none}
    .dlh-pre__glow{position:absolute;width:520px;height:520px;border-radius:9999px;
        background:radial-gradient(circle,rgba(45,212,191,.35),transparent 60%);filter:blur(40px);
        animation:dlh-glow 3s ease-in-out infinite}
    .dlh-pre__inner{position:relative;display:flex;flex-direction:column;align-items:center;text-align:center;padding:1rem}
    .dlh-pre__badge{position:relative;display:flex;align-items:center;justify-content:center;
        width:132px;height:132px;margin-bottom:1.5rem}
    .dlh-pre__ring{position:absolute;inset:0;border-radius:9999px;
        border:3px solid rgba(255,255,255,.10);border-top-color:#6ee7b7;border-right-color:#28c6e8;
        animation:dlh-spin 1s linear infinite}
    .dlh-pre__ring2{position:absolute;inset:10px;border-radius:9999px;
        border:2px solid rgba(255,255,255,.08);border-bottom-color:#34d399;
        animation:dlh-spin 1.6s linear infinite reverse}
    .dlh-pre__logo{width:92px;height:92px;object-fit:contain;filter:drop-shadow(0 10px 24px rgba(0,0,0,.4));
        animation:dlh-pop .7s cubic-bezier(.16,1,.3,1) both,dlh-float 3s ease-in-out .7s infinite}
    .dlh-pre__title{color:#fff;font-weight:800;letter-spacing:.12em;text-transform:uppercase;font-size:.95rem;
        opacity:0;animation:dlh-rise .6s ease .25s forwards}
    .dlh-pre__subtitle{color:#6ee7b7;font-weight:700;letter-spacing:.28em;text-transform:uppercase;font-size:.72rem;margin-top:.25rem;
        opacity:0;animation:dlh-rise .6s ease .38s forwards}
    .dlh-pre__bar{width:200px;height:4px;border-radius:9999px;background:rgba(255,255,255,.14);overflow:hidden;margin-top:1.5rem}
    .dlh-pre__bar span{display:block;height:100%;width:0%;border-radius:9999px;
        background:linear-gradient(90deg,#6ee7b7,#28c6e8);transition:width .2s linear}
    .dlh-pre__hint{color:rgba(255,255,255,.6);font-size:.72rem;margin-top:1rem;
        opacity:0;animation:dlh-rise .6s ease .5s forwards}
    @keyframes dlh-spin{to{transform:rotate(360deg)}}
    @keyframes dlh-pop{from{transform:scale(.6);opacity:0}to{transform:scale(1);opacity:1}}
    @keyframes dlh-float{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}}
    @keyframes dlh-rise{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
    @keyframes dlh-glow{0%,100%{opacity:.7;transform:scale(1)}50%{opacity:1;transform:scale(1.1)}}
    @media (prefers-reduced-motion:reduce){
        .dlh-pre__ring,.dlh-pre__ring2,.dlh-pre__logo,.dlh-pre__glow{animation:none}
        .dlh-pre__title,.dlh-pre__subtitle,.dlh-pre__hint{animation:none;opacity:1}
    }
</style>

<script>
    (function () {
        var pre = document.getElementById('dlh-preloader');
        if (!pre) return;

        var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var DURATION = reduced ? 500 : 6000; // tampil tetap ~6 detik tiap refresh
        var fill = document.getElementById('dlh-pre-fill');
        var start = Date.now();

        document.documentElement.style.overflow = 'hidden';

        // Progress bar sinkron dengan durasi.
        if (fill && !reduced) {
            (function tickBar() {
                var p = Math.min((Date.now() - start) / DURATION, 1);
                fill.style.width = (p * 100) + '%';
                if (p < 1) requestAnimationFrame(tickBar);
            })();
        } else if (fill) {
            fill.style.width = '100%';
        }

        function hide() {
            pre.classList.add('dlh-pre--hide');
            document.documentElement.style.overflow = '';
            document.documentElement.classList.add('dlh-ready');
            window.dispatchEvent(new CustomEvent('dlh:ready'));
            setTimeout(function () { pre.parentNode && pre.parentNode.removeChild(pre); }, 650);
        }

        setTimeout(hide, DURATION);
        // Failsafe: jangan pernah macet menutupi halaman.
        setTimeout(function () {
            if (document.body.contains(pre)) hide();
        }, DURATION + 3000);
    })();
</script>
