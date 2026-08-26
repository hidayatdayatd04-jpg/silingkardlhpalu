#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

//! Panel Admin DLH Kota Palu — shell desktop Tauri 2.
//!
//! Seluruh UI admin tetap hidup di situs produksi (tanpa duplikasi).
//! Aplikasi ini hanyalah "jendela" WebView dengan tambahan:
//! pengarah navigasi (link luar → browser), pelindung koneksi
//! (loading/error/retry), penanganan popup, dan single-instance.

use std::path::PathBuf;
use std::sync::{Mutex, OnceLock};
use std::thread;
use std::time::Duration;

use tauri::{AppHandle, Manager, Url, WebviewUrl, WebviewWindowBuilder};
use tauri_plugin_opener::OpenerExt;

/// URL dasar panel admin produksi (prefix ADMIN_PATH milik Laravel di server).
/// Ganti di sini bila prefix produksi berubah.
const ADMIN_BASE_URL: &str = "https://www.silingkardlhpalu.web.id/ruang-kendali-x7k8p2r6h8j0";

/// Host yang boleh dimuat di dalam aplikasi; selain ini dibuka di browser
/// eksternal Windows. Halaman internal Tauri memakai subdomain `.localhost`.
const ALLOWED_HOSTS: [&str; 2] = ["www.silingkardlhpalu.web.id", "silingkardlhpalu.web.id"];

/// Origin lokal tempat halaman `ui/` dilayani — hanya fallback; nilai sebenarnya
/// dibaca dinamis dari URL awal window (bisa berbeda bila skema HTTPS diaktifkan).
const FALLBACK_LOCAL_ORIGIN: &str = "http://tauri.localhost";

/// Interval pemantauan koneksi saat sesi berjalan.
const MONITOR_INTERVAL: Duration = Duration::from_secs(15);
/// Jeda antar percobaan ulang di dalam satu siklus pemantauan.
const RETRY_GAP: Duration = Duration::from_secs(10);
/// Kegagalan berturut-turut sebelum sesi ditukar ke halaman error
/// (3×15 dtk ≈ 45 dtk down terus-menerus) — blip singkat tak mengganggu form.
const FAILURE_THRESHOLD: u32 = 3;
const PROBE_TIMEOUT: Duration = Duration::from_secs(8);

/// Argumen WebView2 default wry (paritas penuh) — wajib disertakan karena
/// `additional_browser_args` MENGGANTI (bukan menambah) argumen bawaan wry.
/// Ekstra seperti port remote-debugging untuk pengujian bisa ditambahkan lewat
/// env `DLH_WEBVIEW_ARGS`, contoh: DLH_WEBVIEW_ARGS="--remote-debugging-port=9223".
const DEFAULT_WEBVIEW_ARGS: &str = "--disable-features=msWebOOUI,msPdfOOUI,msSmartScreenProtection";

/// Popup webview terpisah tidak didukung shell ini. Dua penanganan berjalan di
/// sisi JS (dijalankan di frame utama, semua halaman):
/// 1. `window.open()` dialihkan menjadi navigasi biasa;
/// 2. anchor `<a target="_blank">` dicegat di fase capture (sebelum script
///    injeksi plugin opener sempat mencegah default-nya): URL internal tetap
///    dimuat DI APLIKASI (sesi cookie ikut), URL eksternal/mailto/tel menjadi
///    navigasi biasa yang nantinya diputuskan pengarah navigasi Rust
///    (dibuka di browser eksternal).
const INIT_SCRIPT: &str = r#"(function () {
  // Kunci judul window ke nama aplikasi: cegah <title> halaman web menimpa
  // title bar shell (get dikunci agar pembacaan tetap konsisten).
  try {
    Object.defineProperty(document, 'title', {
      get: function () { return 'SILINGKAR DLH ADMIN'; },
      set: function () {},
      configurable: true,
    });
  } catch (_) {}
  // URL internal = boleh dimuat di dalam aplikasi (host sama, host *.localhost,
  // atau host produksi). Dipakai pencegat klik & penangan _blank di bawah.
  var skxInternal = function (u) {
    if (u.protocol !== 'http:' && u.protocol !== 'https:') return false;
    var h = u.host.toLowerCase(); var c = location.host.toLowerCase();
    if (h === c) return true;
    if (/\.localhost$/.test(h) && /\.localhost$/.test(c)) return true;
    var prod = ['www.silingkardlhpalu.web.id', 'silingkardlhpalu.web.id'];
    return prod.indexOf(h) !== -1 && prod.indexOf(c) !== -1;
  };
  // Splash tiap pindah halaman untuk halaman web yang TIDAK punya splash
  // sendiri (halaman ber-layout situs sudah menyuntik #silingkar-splash).
  // Desain identik dengan resources/views/components/splash.blade.php.
  // Elemen dipertahankan di DOM agar bisa dimunculkan SEKETIKA saat link
  // internal diklik — bukan menunggu respons server (TTFB) baru tampil.
  // PENTING: DOM belum ada saat script ini dieksekusi WebView2 — pemasangan
  // menunggu <html> tersedia (skxBoot) lalu menyuntik splash secepat mungkin.
  try {
    if (!/\.localhost$/.test(location.host)) {
      // Mode lanjut: halaman sebelumnya sudah menampakkan splash saat link
      // internal diklik (ditandai lewat sessionStorage pada pagehide) —
      // splash halaman ini tampil TANPA animasi intro sehingga terasa
      // sebagai satu splash berkelanjutan, bukan dua splash berturut-turut.
      // Flag hanya dibaca (peek) — konsumsi ditangguhkan ke DOMContentLoaded
      // agar splash milik web (bila ada) juga sempat membacanya.
      var skxResume = false;
      try {
        var skxTs = Number(sessionStorage.getItem('silingkar-splash-resume') || 0);
        if (skxTs && Date.now() - skxTs < 5000) skxResume = true;
      } catch (_) {}
      var skxMounted = false;
      var skxMount = function () {
        // Halaman punya splash sendiri → biarkan milik web.
        if (skxMounted) return;
        if (document.getElementById('silingkar-splash')) { skxMounted = true; return; }
        skxMounted = true;
        var sp = document.createElement('div');
        sp.id = 'silingkar-splash';
        sp.setAttribute('data-shell', '1');
        sp.setAttribute('role', 'status');
        if (skxResume) sp.classList.add('sk-pre--resume');
        sp.innerHTML = '<style>'
          + '#silingkar-splash{position:fixed;inset:0;z-index:100000;display:flex;align-items:center;justify-content:center;'
          + 'background:radial-gradient(circle at 30% 25%,#065f46 0%,#064e3b 45%,#082f40 100%);overflow:hidden;'
          + 'opacity:1;transition:opacity .3s ease,visibility .3s ease}'
          + '#silingkar-splash.sk-pre--hide{opacity:0;visibility:hidden;pointer-events:none}'
          + '.sk-pre__glow{position:absolute;width:520px;height:520px;border-radius:9999px;'
          + 'background:radial-gradient(circle,rgba(45,212,191,.35),transparent 60%);filter:blur(40px);animation:sk-glow 3s ease-in-out infinite}'
          + '.sk-pre__inner{position:relative;display:flex;flex-direction:column;align-items:center;text-align:center;padding:1rem}'
          + '.sk-pre__badge{position:relative;display:flex;align-items:center;justify-content:center;width:156px;height:156px;margin-bottom:1.5rem}'
          + '.sk-pre__ring{position:absolute;inset:0;border-radius:9999px;border:3px solid rgba(255,255,255,.10);'
          + 'border-top-color:#6ee7b7;border-right-color:#28c6e8;animation:sk-spin 1s linear infinite}'
          + '.sk-pre__ring2{position:absolute;inset:10px;border-radius:9999px;border:2px solid rgba(255,255,255,.08);'
          + 'border-bottom-color:#34d399;animation:sk-spin 1.6s linear infinite reverse}'
          + '.sk-pre__logo{width:120px;height:120px;object-fit:contain;filter:drop-shadow(0 10px 24px rgba(0,0,0,.4));'
          + 'animation:sk-pop .7s cubic-bezier(.16,1,.3,1) both,sk-float 3s ease-in-out .7s infinite}'
          + '.sk-pre__title{color:#fff;font-weight:800;letter-spacing:.12em;text-transform:uppercase;font-size:.95rem;'
          + 'opacity:0;animation:sk-rise .6s ease .25s forwards}'
          + '.sk-pre__subtitle{color:#6ee7b7;font-weight:700;letter-spacing:.28em;text-transform:uppercase;font-size:.72rem;'
          + 'margin-top:.25rem;opacity:0;animation:sk-rise .6s ease .38s forwards}'
          + '.sk-pre__bar{width:200px;height:4px;border-radius:9999px;background:rgba(255,255,255,.14);overflow:hidden;margin-top:1.5rem}'
          + '.sk-pre__bar span{display:block;height:100%;width:0%;border-radius:9999px;'
          + 'background:linear-gradient(90deg,#6ee7b7,#28c6e8);transition:width .2s linear}'
          + '.sk-pre__hint{color:rgba(255,255,255,.6);font-size:.72rem;margin-top:1rem;opacity:0;animation:sk-rise .6s ease .5s forwards}'
          + '@keyframes sk-spin{to{transform:rotate(360deg)}}'
          + '@keyframes sk-pop{from{transform:scale(.6);opacity:0}to{transform:scale(1);opacity:1}}'
          + '@keyframes sk-float{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}}'
          + '@keyframes sk-rise{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}'
          + '@keyframes sk-glow{0%,100%{opacity:.7;transform:scale(1)}50%{opacity:1;transform:scale(1.1)}}'
          + '@media (prefers-reduced-motion:reduce){'
          + '.sk-pre__ring,.sk-pre__ring2,.sk-pre__logo,.sk-pre__glow{animation:none}'
          + '.sk-pre__title,.sk-pre__subtitle,.sk-pre__hint{animation:none;opacity:1}}'
          + '.sk-pre--resume .sk-pre__logo{animation:none}'
          + '.sk-pre--resume .sk-pre__title,.sk-pre--resume .sk-pre__subtitle,'
          + '.sk-pre--resume .sk-pre__hint{animation:none;opacity:1}'
          + '</style>'
          + '<div class="sk-pre__glow"></div>'
          + '<div class="sk-pre__inner">'
          + '<div class="sk-pre__badge"><span class="sk-pre__ring"></span><span class="sk-pre__ring2"></span>'
          + '<img src="/assets/images/logo-web.webp" alt="" width="120" height="120" class="sk-pre__logo"></div>'
          + '<p class="sk-pre__title">Dinas Lingkungan Hidup</p>'
          + '<p class="sk-pre__subtitle">Kota Palu</p>'
          + '<div class="sk-pre__bar"><span></span></div>'
          + '<p class="sk-pre__hint">Menyiapkan layanan untuk Anda...</p>'
          + '</div>';
        (document.body || document.documentElement).appendChild(sp);
        window.__skSplash = 1; // penanda uji/debug: splash shell terpasang
        var skxDone = false;
        var skxTimer = null;
        var fill = sp.querySelector('.sk-pre__bar span');
        // Elemen DIPERTAHANKAN di DOM (cukup disembunyikan) agar skxShow()
        // bisa memunculkannya kembali seketika saat link internal diklik.
        var skxHide = function () {
          if (skxDone) return;
          skxDone = true;
          sp.classList.add('sk-pre--hide');
        };
        var skxFill = function () {
          if (!fill) return;
          fill.style.transition = 'none';
          fill.style.width = '0%';
          requestAnimationFrame(function () {
            if (skxDone) return;
            fill.style.transition = 'width 1.2s linear';
            fill.style.width = '100%';
          });
        };
        var skxNavPending = false;
        var skxShow = function () {
          skxNavPending = true; // navigasi internal sedang berlangsung
          if (!skxDone) return;
          skxDone = false;
          if (skxTimer) { clearTimeout(skxTimer); skxTimer = null; }
          sp.classList.remove('sk-pre--hide');
          skxFill();
          // Pengaman: bila navigasi tidak jadi terjadi, splash menutup sendiri;
          // bila jalan, dokumen lama hancur dan timer mati bersamanya.
          skxTimer = setTimeout(skxHide, 4000);
        };
        window.__skxShow = skxShow; // dipakai penangan _blank di window
        if (skxResume) {
          // Mode lanjut: proses loading sudah berjalan sejak halaman
          // sebelumnya — bar langsung penuh, tanpa animasi intro.
          if (fill) { fill.style.transition = 'none'; fill.style.width = '100%'; }
        } else {
          skxFill();
        }
        if (document.readyState === 'complete') {
          setTimeout(skxHide, 400);
        } else {
          window.addEventListener('load', function () { setTimeout(skxHide, 400); });
        }
        // Failsafe: splash tidak boleh menutupi halaman selamanya.
        skxTimer = setTimeout(skxHide, 4000);
        // Navigasi keluar: tandai agar halaman berikutnya MELANJUTKAN splash
        // ini (mode lanjut) alih-alih memutar intro dari awal lagi.
        window.addEventListener('pagehide', function () {
          if (!skxNavPending) return;
          try { sessionStorage.setItem('silingkar-splash-resume', String(Date.now())); } catch (_) {}
        });
        // Splash muncul SEKETIKA saat link internal diklik (bukan menunggu
        // respons server). Anchor _blank ditangani pendengar window di bawah
        // (stopImmediatePropagation mencegah pendengar document ini kena).
        document.addEventListener('click', function (e) {
          if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
          var el = e.target;
          while (el && el.nodeType === 1 && el.nodeName.toUpperCase() !== 'A') el = el.parentElement;
          if (!el || !el.href) return;
          if ((el.getAttribute('target') || '').toLowerCase() === '_blank') return;
          if (el.hasAttribute('download')) return;
          var u;
          try { u = new URL(el.href, location.href); } catch (_) { return; }
          if (!skxInternal(u)) return;
          if (u.origin === location.origin
            && u.pathname + u.search === location.pathname + location.search
            && !u.hash) return; // anchor/tautan tempat sama
          skxShow();
        }, true);
        document.addEventListener('submit', function (e) {
          var f = e.target;
          if (!(f instanceof HTMLFormElement)) return;
          if ((f.getAttribute('method') || 'get').toLowerCase() !== 'get') return;
          if (f.hasAttribute('target') || f.hasAttribute('data-no-splash')) return;
          var u;
          try { u = new URL(f.getAttribute('action') || location.href, location.href); } catch (_) { return; }
          if (skxInternal(u)) skxShow();
        }, true);
        document.addEventListener('livewire:navigate', function () { skxShow(); });
        // Pemasangan dini bisa mendahului splash milik web yang baru ter-parse
        // — serahkan ke splash web bila ternyata ada supaya tidak tampil dua
        // splash dalam satu halaman; bila tidak ada, shell yang konsumsi flag.
        document.addEventListener('DOMContentLoaded', function () {
          var own = document.querySelector('#silingkar-splash:not([data-shell])');
          if (own) {
            if (sp.parentNode) {
              skxDone = true;
              window.__skxShow = (typeof window.__skWebSplashShow === 'function')
                ? window.__skWebSplashShow : null;
              sp.parentNode.removeChild(sp);
            }
          } else {
            try { sessionStorage.removeItem('silingkar-splash-resume'); } catch (_) {}
          }
        });
      };
      // Pasang SEEARLY mungkin — begitu <html> tersedia — supaya splash halaman
      // baru menyambung tanpa celah kosong dari splash halaman sebelumnya.
      var skxTicks = 0;
      var skxBoot = function () {
        if (skxMounted) return;
        if (!document.documentElement) {
          if (++skxTicks < 500) setTimeout(skxBoot, 0);
          return;
        }
        skxMount();
      };
      skxBoot();
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { skxBoot(); });
      }
    }
  } catch (_) {}
  window.open = function (url) {
    if (url) { window.location.href = String(url); }
    return null;
  };
  window.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var el = e.target;
    while (el && el.nodeType === 1 && el.nodeName.toUpperCase() !== 'A') el = el.parentElement;
    if (!el || !el.href) return;
    if ((el.getAttribute('target') || '').toLowerCase() !== '_blank') return;
    var u;
    try { u = new URL(el.href, location.href); } catch (_) { return; }
    if (u.protocol !== 'http:' && u.protocol !== 'https:' && u.protocol !== 'mailto:' && u.protocol !== 'tel:') return;
    e.preventDefault();
    e.stopImmediatePropagation();
    // Internal → tampilkan splash seketika sebelum pindah halaman.
    if (window.__skxShow && skxInternal(u)) window.__skxShow();
    window.location.href = u.href;
  }, true);

  // Title bar desktop kustom. Dijalankan di setiap dokumen WebView, termasuk
  // halaman admin yang dinavigasi, sehingga kontrol zoom tetap ada sepanjang
  // sesi tanpa mengubah kode Laravel di server produksi.
  var skxDesktopBar = function () {
    if (document.getElementById('dlh-desktop-titlebar')) return;
    var mount = function () {
      if (document.getElementById('dlh-desktop-titlebar')) return;
      var root = document.documentElement;
      if (!root) return;
      var style = document.createElement('style');
      style.id = 'dlh-desktop-titlebar-style';
      style.textContent = ''
        + ':root{box-sizing:border-box!important;padding-top:40px!important}'
        + '#dlh-desktop-titlebar{position:fixed!important;inset:0 0 auto 0!important;height:40px!important;z-index:2147483647!important;'
        + 'display:flex!important;align-items:center!important;background:linear-gradient(90deg,#063f3b,#075a52)!important;'
        + 'color:#ecfdf5!important;font:600 12px/1 "Segoe UI",Arial,sans-serif!important;box-shadow:0 1px 0 rgba(255,255,255,.12)!important;user-select:none!important}'
        + '#dlh-desktop-titlebar .dlh-title{height:40px!important;display:flex!important;align-items:center!important;gap:9px!important;padding:0 14px!important;flex:1!important;min-width:90px!important;letter-spacing:.04em!important;cursor:move!important}'
        + '#dlh-desktop-titlebar .dlh-title::before{content:""!important;width:8px!important;height:8px!important;border-radius:999px!important;background:#6ee7b7!important;box-shadow:0 0 0 3px rgba(110,231,183,.15)!important}'
        + '#dlh-desktop-titlebar .dlh-controls{height:100%!important;display:flex!important;align-items:center!important;margin-left:auto!important}'
        + '#dlh-desktop-titlebar button{appearance:none!important;border:0!important;background:transparent!important;color:inherit!important;height:40px!important;min-width:40px!important;padding:0 10px!important;cursor:pointer!important;font:600 15px/1 "Segoe UI Symbol","Segoe UI",sans-serif!important;display:inline-flex!important;align-items:center!important;justify-content:center!important}'
        + '#dlh-desktop-titlebar button:hover{background:rgba(255,255,255,.14)!important}#dlh-desktop-titlebar button:focus-visible{outline:2px solid #a7f3d0!important;outline-offset:-3px!important}'
        + '#dlh-desktop-titlebar .dlh-zoom-value{min-width:49px!important;font:700 11px/1 "Segoe UI",Arial,sans-serif!important;letter-spacing:.02em!important}'
        + '#dlh-desktop-titlebar .dlh-separator{width:1px!important;height:22px!important;background:rgba(255,255,255,.22)!important;margin:0 3px!important}'
        + '#dlh-desktop-titlebar .dlh-close:hover{background:#dc2626!important;color:#fff!important}'
        + '@media (max-width:620px){#dlh-desktop-titlebar .dlh-title{padding-left:10px!important;font-size:10px!important}#dlh-desktop-titlebar button{min-width:34px!important;padding:0 7px!important}}';
      (document.head || root).appendChild(style);

      var bar = document.createElement('div');
      bar.id = 'dlh-desktop-titlebar';
      bar.setAttribute('role', 'toolbar');
      bar.setAttribute('aria-label', 'Kontrol jendela aplikasi desktop');
      bar.innerHTML = ''
        + '<div class="dlh-title" data-dlh-drag="1" title="Geser untuk memindahkan jendela">SILINGKAR DLH ADMIN</div>'
        + '<div class="dlh-controls">'
        + '<button type="button" data-dlh-action="zoom_out" aria-label="Perkecil tampilan web" title="Perkecil tampilan web">−</button>'
        + '<button type="button" class="dlh-zoom-value" data-dlh-action="zoom_reset" aria-label="Kembalikan ukuran tampilan ke 100 persen" title="Reset ukuran tampilan">100%</button>'
        + '<button type="button" data-dlh-action="zoom_in" aria-label="Perbesar tampilan web" title="Perbesar tampilan web">+</button>'
        + '<span class="dlh-separator" aria-hidden="true"></span>'
        + '<button type="button" data-dlh-action="minimize" aria-label="Minimalkan jendela" title="Minimalkan">—</button>'
        + '<button type="button" data-dlh-action="toggle_maximize" aria-label="Maksimalkan atau pulihkan jendela" title="Maksimalkan / Pulihkan">□</button>'
        + '<button type="button" class="dlh-close" data-dlh-action="close" aria-label="Tutup aplikasi" title="Tutup">×</button>'
        + '</div>';
      (document.body || root).appendChild(bar);

      var invoke = function (action) {
        var api = window.__TAURI__ && window.__TAURI__.core;
        if (!api || typeof api.invoke !== 'function') return Promise.reject(new Error('Tauri API tidak tersedia'));
        return api.invoke('desktop_window_control', { action: action });
      };
      var zoomLabel = bar.querySelector('.dlh-zoom-value');
      var setZoomLabel = function (zoom) {
        if (zoomLabel && typeof zoom === 'number') zoomLabel.textContent = Math.round(zoom * 100) + '%';
      };
      bar.addEventListener('click', function (event) {
        var button = event.target.closest('button[data-dlh-action]');
        if (!button) return;
        event.preventDefault();
        event.stopPropagation();
        invoke(button.getAttribute('data-dlh-action')).then(setZoomLabel).catch(function (error) {
          console.warn('[dlh] kontrol jendela gagal', error);
        });
      });
      var drag = bar.querySelector('[data-dlh-drag]');
      if (drag) {
        drag.addEventListener('mousedown', function (event) {
          if (event.button !== 0) return;
          invoke('start_dragging').catch(function () {});
        });
        drag.addEventListener('dblclick', function (event) {
          event.preventDefault();
          invoke('toggle_maximize').catch(function () {});
        });
      }
      invoke('zoom_state').then(setZoomLabel).catch(function () {});
    };
    if (document.body) mount(); else document.addEventListener('DOMContentLoaded', mount, { once: true });
  };
  skxDesktopBar();
})();"#;

#[derive(Clone, Copy, Default, PartialEq)]
enum Phase {
    /// Belum ada probe yang dijalankan (kondisi awal).
    #[default]
    Idle,
    Online,
    Error,
}

struct AppState {
    phase: Mutex<Phase>,
    /// Zoom WebView berlaku untuk seluruh navigasi selama proses aplikasi hidup.
    zoom: Mutex<f64>,
    /// Origin halaman lokal (http(s)://tauri.localhost atau serupa), ditentukan
    /// sekali setelah window dibuat agar navigasi ke error.html selalu tepat.
    local_origin: OnceLock<String>,
}

impl Default for AppState {
    fn default() -> Self {
        Self {
            phase: Mutex::new(Phase::Idle),
            zoom: Mutex::new(1.0),
            local_origin: OnceLock::new(),
        }
    }
}

const MIN_ZOOM: f64 = 0.75;
const MAX_ZOOM: f64 = 1.25;
const ZOOM_STEP: f64 = 0.05;

/// Semua aksi title bar divalidasi di native side. Halaman web tidak diberi
/// akses IPC umum; ia hanya dapat meminta aksi jendela yang tercantum di sini.
#[tauri::command]
fn desktop_window_control(app: AppHandle, action: String) -> Result<f64, String> {
    let window = app
        .get_webview_window("main")
        .ok_or_else(|| "Jendela utama tidak ditemukan.".to_string())?;
    let state = app.state::<AppState>();

    let mut zoom = state.zoom.lock().unwrap_or_else(|e| e.into_inner());

    match action.as_str() {
        "zoom_in" => {
            *zoom = ((*zoom + ZOOM_STEP) * 100.0).round() / 100.0;
            *zoom = zoom.clamp(MIN_ZOOM, MAX_ZOOM);
            window.set_zoom(*zoom).map_err(|e| e.to_string())?;
        }
        "zoom_out" => {
            *zoom = ((*zoom - ZOOM_STEP) * 100.0).round() / 100.0;
            *zoom = zoom.clamp(MIN_ZOOM, MAX_ZOOM);
            window.set_zoom(*zoom).map_err(|e| e.to_string())?;
        }
        "zoom_reset" => {
            *zoom = 1.0;
            window.set_zoom(*zoom).map_err(|e| e.to_string())?;
        }
        "minimize" => window.minimize().map_err(|e| e.to_string())?,
        "toggle_maximize" => {
            if window.is_maximized().map_err(|e| e.to_string())? {
                window.unmaximize().map_err(|e| e.to_string())?;
            } else {
                window.maximize().map_err(|e| e.to_string())?;
            }
        }
        "close" => window.close().map_err(|e| e.to_string())?,
        "start_dragging" => window.start_dragging().map_err(|e| e.to_string())?,
        "zoom_state" => {}
        _ => return Err("Aksi jendela tidak diizinkan.".to_string()),
    }

    Ok(*zoom)
}

/// Target admin dapat dioverride untuk dev lokal / uji offline:
/// `DLH_ADMIN_URL=http://127.0.0.1:8000/admin`.
fn admin_target_url() -> String {
    std::env::var("DLH_ADMIN_URL").unwrap_or_else(|_| ADMIN_BASE_URL.to_string())
}

fn probe_origin(target: &str) -> Option<String> {
    Url::parse(target)
        .ok()
        .map(|u| u.origin().ascii_serialization())
}

/// true = server terjangkau (status respons apa pun dianggap online — yang
/// penting bukan gangguan jaringan), false = DNS/gagal koneksi/timeout.
fn server_reachable(origin: &str) -> bool {
    reqwest::blocking::Client::builder()
        .connect_timeout(PROBE_TIMEOUT)
        .timeout(PROBE_TIMEOUT)
        .user_agent(concat!("DLHAdminDesktop/", env!("CARGO_PKG_VERSION")))
        .build()
        .map(|client| client.get(format!("{origin}/up")).send().is_ok())
        .unwrap_or(false)
}

/// true bila perintah navigasi ke window utama berhasil dikirim.
fn navigate_main(app: &AppHandle, url: Url) -> bool {
    if let Some(win) = app.get_webview_window("main") {
        return match win.navigate(url) {
            Ok(()) => true,
            Err(e) => {
                eprintln!("[dlh] gagal navigasi: {e}");
                false
            }
        };
    }
    eprintln!("[dlh] window utama tidak ditemukan");
    false
}

fn go_to_error(app: &AppHandle) {
    let origin = app
        .state::<AppState>()
        .local_origin
        .get()
        .map(String::as_str)
        .unwrap_or(FALLBACK_LOCAL_ORIGIN)
        .to_string();
    match Url::parse(&format!("{origin}/error.html")) {
        Ok(url) => {
            if !navigate_main(app, url) {
                eprintln!("[dlh] gagal menampilkan halaman error");
            }
        }
        Err(e) => eprintln!("[dlh] gagal menyusun URL halaman error: {e}"),
    }
}

/// Probe koneksi (bisa memblokir ±17 dtk saat server mati) → "online"/"offline".
/// Probe blocking dijalankan di thread pool `spawn_blocking` — `reqwest::blocking`
/// PANIK bila dipanggil dari worker async runtime. Keputusan navigasi diambil
/// sisi JS (`location.href`) karena `win.navigate()` dari Rust terbukti tidak
/// andal di WebView2.
#[tauri::command]
async fn check_connection(app: AppHandle) -> String {
    tauri::async_runtime::spawn_blocking(move || {
        let target = admin_target_url();
        let ok = match probe_origin(&target) {
            Some(origin) => {
                // Dua percobaan: blip DNS/jaringan sesaat saat boot sering terjadi.
                server_reachable(&origin) || {
                    thread::sleep(Duration::from_millis(1500));
                    server_reachable(&origin)
                }
            }
            None => false,
        };
        *app.state::<AppState>()
            .phase
            .lock()
            .unwrap_or_else(|e| e.into_inner()) = if ok { Phase::Online } else { Phase::Error };
        eprintln!(
            "[dlh] probe {target}: {}",
            if ok { "online" } else { "offline" }
        );
        if ok {
            "online".into()
        } else {
            "offline".into()
        }
    })
    .await
    .unwrap_or_else(|_| "offline".into())
}

/// URL admin aktif — dipakai sisi JS untuk berpindah halaman (location.href).
#[tauri::command]
fn admin_target() -> String {
    admin_target_url()
}

fn open_externally(app: &AppHandle, url: &str) {
    eprintln!("[dlh] buka di browser eksternal: {url}");
    if let Err(e) = app.opener().open_url(url, None::<&str>) {
        eprintln!("[dlh] gagal membuka browser eksternal: {e}");
    }
}

/// Pengarah navigasi top-frame: hanya host produksi dan halaman internal yang
/// boleh dimuat di webview; sisanya dibuka di browser default Windows.
fn navigation_guard(app: &AppHandle, url: &Url) -> bool {
    match url.scheme() {
        "http" | "https" => {
            if let Some(host) = url.host_str() {
                let host = host.to_ascii_lowercase();
                if ALLOWED_HOSTS.contains(&host.as_str()) || host.ends_with(".localhost") {
                    return true;
                }
            }
            open_externally(app, url.as_str());
            false
        }
        "about" | "blob" | "data" => true,
        "mailto" | "tel" => {
            open_externally(app, url.as_str());
            false
        }
        _ => {
            eprintln!("[dlh] navigasi diblokir: {url}");
            false
        }
    }
}

/// Satu siklus pemantauan: true bila gagal `FAILURE_THRESHOLD` kali berturut.
fn outage_confirmed(origin: &str) -> bool {
    for attempt in 0..FAILURE_THRESHOLD {
        if attempt > 0 {
            thread::sleep(RETRY_GAP);
        }
        if server_reachable(origin) {
            return false; // cuma blip — biarkan sesi apa adanya
        }
    }
    true
}

/// Pantau server selagi sesi berjalan. Pemulihan SELALU manual lewat tombol
/// "Coba Lagi" agar halaman/form pengguna tidak dimuat ulang tiba-tiba.
fn monitor_loop(app: AppHandle) {
    loop {
        thread::sleep(MONITOR_INTERVAL);
        if *app
            .state::<AppState>()
            .phase
            .lock()
            .unwrap_or_else(|e| e.into_inner())
            != Phase::Online
        {
            continue;
        }
        let Some(origin) = probe_origin(&admin_target_url()) else {
            continue;
        };
        if outage_confirmed(&origin) {
            eprintln!("[dlh] koneksi ke server hilang saat sesi berjalan");
            *app.state::<AppState>()
                .phase
                .lock()
                .unwrap_or_else(|e| e.into_inner()) = Phase::Error;
            go_to_error(&app);
        }
    }
}

fn main() {
    tauri::Builder::default()
        // Single-instance WAJIB plugin pertama.
        .plugin(tauri_plugin_single_instance::init(|app, _args, _cwd| {
            eprintln!("[dlh] instans kedua ditolak — fokuskan window utama");
            if let Some(win) = app.get_webview_window("main") {
                let _ = win.unminimize();
                let _ = win.set_focus();
            }
        }))
        .plugin(tauri_plugin_opener::init())
        .manage(AppState::default())
        .invoke_handler(tauri::generate_handler![
            check_connection,
            admin_target,
            desktop_window_control
        ])
        .setup(|app| {
            let handle = app.handle().clone();

            // Gabungkan argumen default wry dengan ekstra dari env (uji/debug).
            let webview_args = match std::env::var("DLH_WEBVIEW_ARGS") {
                Ok(extra) => format!("{DEFAULT_WEBVIEW_ARGS} {extra}"),
                Err(_) => DEFAULT_WEBVIEW_ARGS.to_string(),
            };

            let win = WebviewWindowBuilder::new(
                app,
                "main",
                WebviewUrl::App(PathBuf::from("index.html")),
            )
            .title("SILINGKAR DLH ADMIN")
            .inner_size(1366.0, 900.0)
            .min_inner_size(960.0, 640.0)
            .center()
            // Windows title bar bawaan diganti title bar kustom yang memuat
            // kontrol zoom serta minimize/maximize/close.
            .decorations(false)
            // Matikan penangan drag-drop bawaan wry agar drag&drop HTML5
            // (upload berkas di form admin) berperilaku seperti browser.
            .disable_drag_drop_handler()
            .initialization_script(INIT_SCRIPT)
            .additional_browser_args(&webview_args)
            .on_navigation({
                let handle = handle.clone();
                move |url| navigation_guard(&handle, url)
            })
            .build()?;

            // Catat origin halaman lokal sesungguhnya untuk navigasi error.
            // Validasi agar origin kosong/opaque (mis. belum siap) jatuh ke fallback.
            let local_origin = win
                .url()
                .ok()
                .map(|u| u.origin().ascii_serialization())
                .filter(|o| o.starts_with("http"))
                .unwrap_or_else(|| FALLBACK_LOCAL_ORIGIN.to_string());
            eprintln!("[dlh] origin halaman lokal: {local_origin}");
            let _ = app.state::<AppState>().local_origin.set(local_origin);

            // Probe koneksi dipicu dari ui/index.html lewat invoke
            // `check_connection`; navigasi dilakukan sisi JS (location.href).
            thread::spawn(move || monitor_loop(handle));

            Ok(())
        })
        .run(tauri::generate_context!())
        .expect("gagal menjalankan aplikasi SILINGKAR DLH ADMIN");
}
