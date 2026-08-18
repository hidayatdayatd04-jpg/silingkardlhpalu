/**
 * Penyempurnaan ringan untuk shell admin.
 *
 * Navigasi sengaja dibiarkan memakai perilaku browser bawaan: tidak ada lagi
 * overlay/preloader yang menutupi seluruh halaman atau menunda interaksi.
 */
document.addEventListener('DOMContentLoaded', () => {
    initPageTransition();
    initContentReveal();
    initAutoStagger();
    initNavEntrance();
});

function prefersReducedMotion() {
    return window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;
}

/**
 * Backward-compatible hook. Nama ini dipakai oleh skrip lama, tetapi sekarang
 * hanya menandai dokumen siap tanpa membuat overlay atau mencegat tautan.
 */
function initPageTransition() {
    document.documentElement.classList.add('dlh-ready');
}

function initContentReveal() {
    const main = document.querySelector('[data-page-content]');
    if (!main) return;

    Array.from(main.children).forEach((element) => {
        element.classList.add('is-in');
        element.style.opacity = '1';
        element.style.transform = 'none';
    });
}

function initAutoStagger() {
    const elements = Array.from(document.querySelectorAll('[data-animate]'));
    if (!elements.length) return;

    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        elements.forEach((element) => element.classList.add('is-in'));
        return;
    }

    const observer = new IntersectionObserver((entries, activeObserver) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-in');
            activeObserver.unobserve(entry.target);
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.04 });

    elements.forEach((element) => observer.observe(element));
}

function initNavEntrance() {
    if (prefersReducedMotion()) return;

    const candidates = Array.from(document.querySelectorAll('[data-nav-item], .topbar-btn, header .quick-action-btn'));
    const items = [...new Set(candidates)].filter((element) => element.offsetParent !== null);

    items.forEach((element, index) => {
        element.style.setProperty('--nav-delay', `${Math.min(index, 12) * 20}ms`);
        element.classList.add('nav-enter');
    });

    window.setTimeout(() => {
        items.forEach((element) => element.classList.remove('nav-enter'));
    }, 500);
}

window.initPageTransition = initPageTransition;
window.initAutoStagger = initAutoStagger;
