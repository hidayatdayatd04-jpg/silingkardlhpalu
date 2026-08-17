import './bootstrap';
import './admin';
import './alpine';
import { initMotion } from './motion';

// Scroll-reveal + count-up terpusat untuk semua halaman publik (idempoten,
// aman berdampingan dengan observer inline di welcome/berita dsb.).
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMotion, { once: true });
} else {
    initMotion();
}

/**
 * Task 5 — code-split peta.
 * Kelas MapLibre control (DlhZoomControl, DlhBasemapSwitcher, ...) dan DlhMarkers
 * TIDAK lagi di-import di sini (app.js dimuat di SEMUA halaman). Keduanya dipindah
 * ke entry terpisah `resources/js/map-bundle.js` yang di-load via:
 *   1. `@vite('resources/js/map-bundle.js')` di view yang menampilkan peta, atau
 *   2. dynamic import di bawah ini sebagai fallback (dijamin tersedia sebelum map dibuat).
 */
let _mapComponentsPromise = null;

function ensureMapComponents() {
    if (window.DlhZoomControl && window.DlhMarkers) {
        return Promise.resolve();
    }
    if (!_mapComponentsPromise) {
        _mapComponentsPromise = import('./map-bundle').catch((err) => {
            _mapComponentsPromise = null; // izinkan retry sekali lagi
            console.error('[DLH] Gagal memuat map-bundle:', err);
            throw err;
        });
    }
    return _mapComponentsPromise;
}

window.ensureMapComponents = ensureMapComponents;

/**
 * Helper untuk memastikan MapLibre GL JS sudah di-load sebelum callback dieksekusi.
 * Digunakan oleh komponen yang membutuhkan peta modern dengan vector tiles.
 *
 * @param {Function} callback - Fungsi yang dipanggil setelah MapLibre siap
 */
window.ensureMaplibreLoaded = function (callback) {
    // Pastikan chunk peta (DlhZoomControl, DlhMarkers, maplibre-gl) siap.
    // maplibre-gl kini di-bundle (self-host) lewat map-bundle.js → tidak ada
    // lagi request CDN yang mem-block render/LCP.
    ensureMapComponents().then(function () {
        if (window.maplibregl) {
            callback();
            return;
        }

        if (window._maplibreLoading) {
            window._maplibreCallbacks = window._maplibreCallbacks || [];
            window._maplibreCallbacks.push(callback);
            return;
        }

        window._maplibreLoading = true;
        window._maplibreCallbacks = [callback];

        // Fallback polling (jarang terpakai): tunggu window.maplibregl
        // ter-set oleh map-bundle, lalu jalankan semua callback tertunda.
        const iv = setInterval(function () {
            if (window.maplibregl) {
                clearInterval(iv);
                window._maplibreLoading = false;
                (window._maplibreCallbacks || []).forEach(fn => fn());
                window._maplibreCallbacks = [];
            }
        }, 50);
        setTimeout(function () { clearInterval(iv); window._maplibreLoading = false; }, 8000);
    }).catch(function (err) {
        console.error('[DLH] ensureMaplibreLoaded gagal memuat map-bundle:', err);
    });
};

/* ============================================================
 * DLH Tracking Map — MapLibre GL JS
 * Custom basemap + vehicle markers + geolocation.
 *
 * dlhMapInit & dlhMapDrawMarkers dipanggil langsung dari blade
 * (tracking-armada, dsb.), jadi definisinya harus tersedia tanpa
 * menunggu map-bundle. Tubuhnya memakai maplibregl + DlhZoomControl
 * dsb., tapi hanya dieksekusi di dalam ensureMaplibreLoaded →
 * chunk peta di-load pas saat dibutuhkan (lazy).
 * ============================================================ */
window.dlhMapInit = function (containerId) {
    if (window._dlhMapReady) return;
    window._dlhMapReady = true;
    var el = document.getElementById(containerId);
    if (!el) return;
    window.ensureMaplibreLoaded(function () {
        var map = new maplibregl.Map({
            container: containerId,
            style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
            center: [119.87, -0.9], zoom: 13,
            attributionControl: false, maxPitch: 0
        });
        map.addControl(new DlhZoomControl(), 'top-right');
        if (window.DlhScaleControl) map.addControl(new DlhScaleControl(), 'bottom-left');
        if (window.DlhBasemapSwitcher) map.addControl(new DlhBasemapSwitcher(), 'bottom-right');
        if (window.DlhFullscreenControl) map.addControl(new DlhFullscreenControl(), 'top-left');
        if (window.DlhWeatherControl) map.addControl(new DlhWeatherControl({ position: 'top-left' }), 'top-left');

        var locGrp = document.createElement('div');
        locGrp.className = 'maplibregl-ctrl maplibregl-ctrl-group';
        locGrp.style.cssText = 'position:absolute;top:120px;right:10px;z-index:1';
        var locBtn = document.createElement('button');
        locBtn.type = 'button'; locBtn.title = 'Lokasi Saya';
        locBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>';
        locBtn.style.cssText = 'width:34px;height:34px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:transparent;color:#4a5568';
        locBtn.onclick = function () {
            if (!navigator.geolocation) { alert('Geolocation tidak didukung.'); return; }
            navigator.geolocation.getCurrentPosition(function (p) {
                var lng = p.coords.longitude, lat = p.coords.latitude;
                map.flyTo({ center: [lng, lat], zoom: 15, duration: 1500 });
                if (window._dlhLocMarker) window._dlhLocMarker.remove();
                var dot = document.createElement('div');
                dot.innerHTML = '<div style="width:20px;height:20px;background:#3b82f6;border:3px solid #fff;border-radius:50%;box-shadow:0 0 0 3px rgba(59,130,246,0.3),0 2px 8px rgba(0,0,0,0.2)"></div>';
                window._dlhLocMarker = new maplibregl.Marker({ element: dot, anchor: 'center' })
                    .setLngLat([lng, lat])
                    .setPopup(new maplibregl.Popup({ offset: [0, -14], closeButton: false, maxWidth: '200px' })
                        .setHTML('<div style="padding:8px 12px;font-family:system-ui;text-align:center"><p style="font-size:11px;font-weight:600;color:#1e293b;margin:0">Lokasi Anda</p><p style="font-size:10px;color:#94a3b8;margin:2px 0 0">' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</p></div>'))
                    .addTo(map);
                window._dlhLocMarker.togglePopup();
            }, function () { alert('Gagal mendapatkan lokasi.'); }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
        };
        locGrp.appendChild(locBtn);
        map._container.appendChild(locGrp);

        window._dlhMap = map;
        window._dlhMarkers = [];

        map.on('load', function () {
            var layers = map.getStyle().layers;
            function sid(id, p, v) { if (map.getLayer(id)) try { map.setPaintProperty(id, p, v); } catch(e){} }
            function s2(id, p1, v1, p2, v2) { if (map.getLayer(id)) try { map.setPaintProperty(id, p1, v1); map.setPaintProperty(id, p2, v2); } catch(e){} }
            sid('background', 'background-color', '#f6f7f9');
            s2('water', 'fill-color', '#aad4f0', 'fill-opacity', 1);
            s2('waterway', 'line-color', '#aad4f0', 'line-width', 1.5);
            s2('park', 'fill-color', '#c8e6b8', 'fill-opacity', 0.75);
            s2('landcover', 'fill-color', '#c8e6b8', 'fill-opacity', 0.5);
            s2('landuse', 'fill-color', '#f0efec', 'fill-opacity', 0.2);
            s2('building', 'fill-color', '#eae8e4', 'fill-opacity', 0.55);
            sid('building', 'fill-outline-color', '#dddad5');
            layers.forEach(function (l) {
                if (l['source-layer'] !== 'transportation' || l.type !== 'line') return;
                var c = (l.layout && l.layout['class']) || '', id = l.id;
                var ic = id.indexOf('case') > -1 || id.indexOf('casing') > -1 || id.indexOf('outline') > -1;
                if (c === 'motorway') s2(id, 'line-color', ic ? '#e0b840' : '#f0cc5c', 'line-width', ic ? 7 : 6);
                else if (c === 'trunk') s2(id, 'line-color', ic ? '#e0b840' : '#f0cc5c', 'line-width', ic ? 6 : 5);
                else if (c === 'primary') s2(id, 'line-color', ic ? '#ecd898' : '#f5dea0', 'line-width', ic ? 5.5 : 4);
                else if (c === 'secondary') s2(id, 'line-color', ic ? '#d8d5d0' : '#fff', 'line-width', ic ? 5 : 3.5);
                else if (c === 'tertiary') s2(id, 'line-color', ic ? '#e0ddd8' : '#fff', 'line-width', ic ? 4 : 2.8);
                else if (c === 'path') s2(id, 'line-color', '#c8c5c0', 'line-width', 1.2);
                else if (c === '' || c === 'minor' || c === 'service' || c === 'residential')
                    s2(id, 'line-color', ic ? '#e8e6e3' : '#fff', 'line-width', ic ? 2.5 : 2);
            });
            s2('rail', 'line-color', '#d0ccc5', 'line-width', 1.5);
            layers.forEach(function (l) {
                if (l.type !== 'symbol' || !map.getLayer(l.id)) return;
                try {
                    map.setPaintProperty(l.id, 'text-halo-width', 3.5);
                    map.setPaintProperty(l.id, 'text-halo-color', '#ffffff');
                    map.setPaintProperty(l.id, 'text-halo-blur', 0.6);
                    map.setPaintProperty(l.id, 'text-color', l['source-layer'] === 'place' ? '#2d3748' : '#4a5568');
                    var op = map.getPaintProperty(l.id, 'text-opacity');
                    if (Array.isArray(op) && op[0] === 'interpolate') {
                        var n = ['interpolate', ['linear'], ['zoom']];
                        for (var j = 2; j < op.length - 1; j += 2) { n.push(op[j]); n.push(Math.min(1, op[j + 1] + 0.4)); }
                        map.setPaintProperty(l.id, 'text-opacity', n);
                    } else if (typeof op === 'number' && op < 1) {
                        map.setPaintProperty(l.id, 'text-opacity', Math.min(1, op + 0.4));
                    }
                } catch (e) {}
            });
        });
    });
};

window.dlhMapDrawMarkers = function (vd, fv) {
    var map = window._dlhMap;
    if (!map) { setTimeout(function () { window.dlhMapDrawMarkers(vd, fv); }, 200); return; }
    var markers = window._dlhMarkers;
    var ni = new Set(vd.map(function (v) { return v.imei; }));
    window._dlhMarkers = markers.filter(function (m) {
        if (!ni.has(m._imei)) { m.remove(); return false; }
        return true;
    });
    markers = window._dlhMarkers;

    vd.forEach(function (v) {
        var lat = parseFloat(v.latitude), lng = parseFloat(v.longitude);
        if (isNaN(lat) || isNaN(lng)) return;
        var isTruck = (parseInt(v.veh_type) === 4);
        var pfx = isTruck ? 'truck' : 'car';
        var st = (parseInt(v.acc) === 1) ? 'acc_on' : 'parking';
        var iu = '/assets/tracking/' + pfx + '_' + st + '.png';
        var el = document.createElement('div');
        el.className = 'custom-vehicle-icon';
        el.innerHTML = '<div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.9);border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.15),0 0 0 1px rgba(0,0,0,0.05);backdrop-filter:blur(4px)" onmouseover="this.style.transform=\'scale(1.1)\'" onmouseout="this.style.transform=\'scale(1)\'"><img src="' + iu + '" alt="" style="transform:rotate(' + v.angle + 'deg);width:30px;height:30px;transition:transform 0.3s ease;border-radius:8px" /></div>';
        var sc = (parseInt(v.acc) === 1) ? '#10b981' : '#64748b';
        var stx = (parseInt(v.acc) === 1) ? 'Aktif Melayani' : 'Parkir / Mesin Mati';
        var ph = '<div style="min-width:200px;padding:14px;font-family:system-ui,-apple-system,sans-serif"><div style="display:flex;align-items:center;gap:8px;margin-bottom:10px"><div style="width:8px;height:8px;border-radius:50%;background:' + sc + ';box-shadow:0 0 0 3px ' + sc + '33;flex-shrink:0"></div><p style="font-weight:700;font-size:13px;color:#1e293b;margin:0;letter-spacing:-0.3px;line-height:1.3">' + v.title + '</p></div><div style="display:grid;grid-template-columns:1fr 1fr;gap:6px 12px"><div><p style="font-size:10px;color:#94a3b8;margin:0;text-transform:uppercase;letter-spacing:0.5px;font-weight:600">Kecepatan</p><p style="font-size:12px;color:#334155;margin:2px 0 0;font-weight:600">' + v.speed + ' <span style="font-weight:400;color:#94a3b8">km/h</span></p></div><div><p style="font-size:10px;color:#94a3b8;margin:0;text-transform:uppercase;letter-spacing:0.5px;font-weight:600">Status</p><p style="font-size:12px;color:' + sc + ';margin:2px 0 0;font-weight:600">' + stx + '</p></div></div><div style="margin-top:10px;padding-top:8px;border-top:1px solid #f1f5f9"><p style="font-size:10px;color:#94a3b8;margin:0">Update: ' + v.server_time + '</p></div></div>';

        var ex = markers.find(function (m) { return m._imei === v.imei; });
        if (ex) {
            ex.setLngLat([lng, lat]);
            ex.getElement().querySelector('img').style.transform = 'rotate(' + v.angle + 'deg)';
            ex.setPopup(new maplibregl.Popup({ offset: [0, -24], closeButton: true, closeOnClick: false, maxWidth: '280px' }).setHTML(ph));
        } else {
            var mk = new maplibregl.Marker({ element: el, anchor: 'center' })
                .setLngLat([lng, lat])
                .setPopup(new maplibregl.Popup({ offset: [0, -24], closeButton: true, closeOnClick: false, maxWidth: '280px' }).setHTML(ph))
                .addTo(map);
            mk._imei = v.imei;
            markers.push(mk);
        }
    });

    if (fv && markers.length > 0) {
        var b = new maplibregl.LngLatBounds();
        markers.forEach(function (m) { b.extend(m.getLngLat()); });
        map.fitBounds(b, { padding: 50, maxZoom: 15 });
    }
};

window.addEventListener('guest-map-vehicles-updated', function (e) {
    window.dlhMapDrawMarkers(e.detail.vehicles, e.detail.fitBounds);
});
