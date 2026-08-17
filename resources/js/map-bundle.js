/**
 * map-bundle.js — chunk khusus halaman yang benar-benar menampilkan peta.
 *
 * Task 5: kelas-kelas MapLibre control (`DlhZoomControl`, `DlhBasemapSwitcher`,
 * `DlhWeatherControl`, dst.) dan `DlhMarkers` dipindahkan Keluar dari app.js
 * (yang dimuat di SEMUA halaman) supaya halaman tabel/dashboard tanpa peta tidak
 * perlu parse ~1.300 baris JS peta.
 *
 * Entry ini di-load dengan dua cara:
 *  1. Eager : `@vite('resources/js/map-bundle.js')` di view yang punya peta.
 *  2. Fallback dinamis : `import('./map-bundle')` dari app.js (ensureMaplibreLoaded)
 *     — dijamin chunk ini selalu ada sebelum map di-create.
 */
// Self-hosted MapLibre GL JS (menggantikan CDN unpkg). Di-bundle oleh Vite
// sehingga tanpa dependency jaringan eksternal & di-cache versinya.
import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

// Ekspos ke global agar kode legacy (map-bundle, sebaran, app.js) yang
// mereferensikan window.maplibregl tetap berfungsi.
window.maplibregl = maplibregl;

import './map-components';
import './dlh-markers';

/* ============================================================================
 * Map helper functions (dlhMapInit, dlhSimpleMap, dlhDraggableMap,
 * dlhMultiMarkerMap, dlhPetaPersampahan, dlhPetaObjekPengawasan, dlhPetaRth,
 * dlhAddLocBtn, dlhMapDrawMarkers) — di-code-split dari app.js (Task 5).
 * Hanya dibutuhkan di halaman peta; tidak membebani bundle admin/settings.
 * ========================================================================== */

/* dlhMapInit, dlhMapDrawMarkers & listener guest-map-vehicles-updated
 * dipindah ke app.js — definisinya harus tersedia tanpa eager-load
 * chunk ini (dipanggil langsung dari blade tracking-armada). */

/* ============================================================
 * Generic MapLibre functions for all map pages
 * ============================================================ */

/** Helper: tambahkan tombol Lokasi Saya ke peta (pojok kiri bawah) */
window.dlhAddLocBtn = function dlhAddLocBtn(map, onLocate) {
    /* Tunggu map siap lalu tempel tombol di dalam container peta */
    function addBtn() {
        var container = map.getContainer();
        var grp = document.createElement('div');
        grp.className = 'maplibregl-ctrl maplibregl-ctrl-group';
        grp.style.cssText = 'position:absolute;bottom:10px;left:10px;z-index:1;border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.12);border:1px solid rgba(0,0,0,.06);background:rgba(255,255,255,.95);backdrop-filter:blur(8px)';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.title = 'Lokasi Saya';
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>';
        btn.style.cssText = 'width:34px;height:34px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:transparent;color:#475569';
        btn.onclick = function () {
            if (!navigator.geolocation) { alert('Geolocation tidak didukung.'); return; }
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>';
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
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>';
                if (onLocate) onLocate(lat, lng);
            }, function () {
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>';
                alert('Gagal mendapatkan lokasi.');
            }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
        };
        grp.appendChild(btn);
        container.appendChild(grp);
    }
    if (map.loaded()) { addBtn(); } else { map.on('load', addBtn); }
}

/** Simple map with single marker (admin show, cek pages) */
window.dlhSimpleMap = function (containerId, cfg) {
    window.ensureMaplibreLoaded(function () {
        var map = new maplibregl.Map({
            container: containerId,
            style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
            center: [cfg.lng, cfg.lat],
            zoom: cfg.zoom || 15,
            attributionControl: false
        });
        map.addControl(new DlhZoomControl(), 'top-right');
        map.addControl(new DlhBasemapSwitcher(), 'bottom-right');
        if (window.DlhFullscreenControl) map.addControl(new DlhFullscreenControl(), 'top-left');
        if (window.DlhWeatherControl) map.addControl(new DlhWeatherControl({ position: 'top-left' }), 'top-left');
        if (cfg.markerHtml) {
            var el = document.createElement('div');
            el.innerHTML = cfg.markerHtml;
            new maplibregl.Marker({ element: el, anchor: 'center' })
                .setLngLat([cfg.lng, cfg.lat])
                .setPopup(new maplibregl.Popup({ offset: [0, -20] }).setHTML(cfg.popup || ''))
                .addTo(map);
        } else {
            new maplibregl.Marker({ anchor: 'center' })
                .setLngLat([cfg.lng, cfg.lat])
                .setPopup(new maplibregl.Popup({ offset: [0, -20] }).setText(cfg.popupText || 'Lokasi'))
                .addTo(map);
        }
        setTimeout(function () { map.resize(); }, 200);
        dlhAddLocBtn(map);
    });
};

/** Map with draggable marker (pengaduan forms) */
window.dlhDraggableMap = function (containerId, cfg) {
    window.ensureMaplibreLoaded(function () {
        var hasInit = cfg.initLat !== null && cfg.initLng !== null;
        var center = hasInit ? [cfg.initLng, cfg.initLat] : [cfg.defLng, cfg.defLat];

        var map = new maplibregl.Map({
            container: containerId,
            style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
            center: center,
            zoom: cfg.zoom || 13,
            attributionControl: false
        });
        map.addControl(new DlhZoomControl(), 'top-right');
        map.addControl(new DlhBasemapSwitcher(), 'bottom-right');
        if (window.DlhFullscreenControl) map.addControl(new DlhFullscreenControl(), 'top-left');
        if (window.DlhWeatherControl) map.addControl(new DlhWeatherControl({ position: 'top-left' }), 'top-left');

        var marker = null;

        function setMarker(lat, lng, writeInputs) {
            if (marker) {
                marker.setLngLat([lng, lat]);
            } else {
                marker = new maplibregl.Marker({ draggable: true, anchor: 'center' })
                    .setLngLat([lng, lat])
                    .addTo(map);
                marker.on('dragend', function () {
                    var p = marker.getLngLat();
                    writeFields(p.lat, p.lng);
                });
            }
            if (writeInputs) writeFields(lat, lng);
        }

        function writeFields(lat, lng) {
            var la = document.getElementById(cfg.latInput) || document.querySelector('[name="' + cfg.latInput + '"]');
            var ln = document.getElementById(cfg.lngInput) || document.querySelector('[name="' + cfg.lngInput + '"]');
            if (la) { la.value = Number(lat).toFixed(6); la.dispatchEvent(new Event('input', { bubbles: true })); }
            if (ln) { ln.value = Number(lng).toFixed(6); ln.dispatchEvent(new Event('input', { bubbles: true })); }
        }

        if (hasInit) setMarker(cfg.initLat, cfg.initLng, false);
        map.on('click', function (e) { setMarker(e.lngLat.lat, e.lngLat.lng, true); });

        setTimeout(function () { map.resize(); }, 200);

        // Lokasi Saya button
        dlhAddLocBtn(map, function (lat, lng) { setMarker(lat, lng, true); });

        // Expose for Livewire events
        window['dlhDragMap_' + containerId] = {
            setMarker: setMarker,
            setCenter: function (lat, lng) { map.flyTo({ center: [lng, lat], zoom: 15 }); setMarker(lat, lng, true); }
        };
    });
};

/** Map with multiple markers from data array */
window.dlhMultiMarkerMap = function (containerId, cfg) {
    window.ensureMaplibreLoaded(function () {
        var map = new maplibregl.Map({
            container: containerId,
            style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
            center: cfg.center || [119.87, -0.9],
            zoom: cfg.zoom || 13,
            attributionControl: false
        });
        map.addControl(new DlhZoomControl(), 'top-right');
        map.addControl(new DlhBasemapSwitcher(), 'bottom-right');
        if (window.DlhFullscreenControl) map.addControl(new DlhFullscreenControl(), 'top-left');
        if (window.DlhWeatherControl) map.addControl(new DlhWeatherControl({ position: 'top-left' }), 'top-left');

        var markers = [];
        (cfg.markers || []).forEach(function (m) {
            var el = document.createElement('div');
            el.style.cssText = 'width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.9);border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.15);cursor:pointer;backdrop-filter:blur(4px)';
            el.innerHTML = '<img src="' + (m.icon || '/assets/tracking/car_acc_on.png') + '" style="width:24px;height:24px;object-fit:contain" />';

            var mk = new maplibregl.Marker({ element: el, anchor: 'center' })
                .setLngLat([m.lng, m.lat])
                .addTo(map);

            if (m.popup) {
                mk.setPopup(new maplibregl.Popup({ offset: [0, -20], maxWidth: '280px' }).setHTML(m.popup));
            }
            markers.push(mk);
        });

        if (cfg.fitBounds && markers.length > 0) {
            var bounds = new maplibregl.LngLatBounds();
            markers.forEach(function (m) { bounds.extend(m.getLngLat()); });
            map.fitBounds(bounds, { padding: 50 });
        }

        setTimeout(function () { map.resize(); }, 200);
        dlhAddLocBtn(map);
    });
};

/** Peta persampahan with GeoJSON layers + armada tracking from API */
window.dlhPetaPersampahan = function (containerId, layers, armada, config) {
    config = config || {};
    var vehicleTypes = config.vehicleTypes || [];
    var defaultType = config.defaultType || (vehicleTypes[0] && vehicleTypes[0].key) || null;

    // Map layerId -> tipe kendaraan & warna tipe
    var typeByLayer = {};
    var typeColor = {};
    vehicleTypes.forEach(function (t) {
        typeColor[t.key] = t.color;
        (t.layerIds || []).forEach(function (id) { typeByLayer[id] = t.key; });
    });

    // State single-select: hanya satu tipe kendaraan aktif pada satu waktu
    var activeType = defaultType;
    var activeKelurahan = null;
    var armadaVisible = true;

    window.ensureMaplibreLoaded(function () {
        var map = new maplibregl.Map({
            container: containerId,
            style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
            center: [119.87, -0.9],
            zoom: 12,
            attributionControl: false
        });
        map.addControl(new DlhZoomControl(), 'top-right');
        if (window.DlhBasemapSwitcher) map.addControl(new DlhBasemapSwitcher(), 'bottom-right');
        if (window.DlhFullscreenControl) map.addControl(new DlhFullscreenControl(), 'top-left');
        if (window.DlhWeatherControl) map.addControl(new DlhWeatherControl({ position: 'top-left' }), 'top-left');

        window._dlhMap = map;
        window._dlhArmadaMarkers = [];
        window._dlhArmadaVisible = true;
        window._dlhArmadaFilter = window._dlhArmadaFilter || {
            types: new Set(['pickup', 'truck']),
            statuses: new Set(['active', 'parked'])
        };

        // Fungsi untuk membuat popup HTML
        function makePopupHtml(props, color, layerName) {
            var details = [];
            if (props.ALAMAT) details.push({ icon: 'lokasi', value: props.ALAMAT });
            if (props.KECAMATAN) details.push({ icon: 'lokasi', value: props.KECAMATAN });
            if (props.KAPASITAS) details.push({ icon: 'volume', value: 'Kapasitas: ' + props.KAPASITAS });
            if (props.VOLUME) details.push({ icon: 'volume', value: 'Volume: ' + props.VOLUME });

            var statusObj = null;
            if (props.STATUS) {
                var sc = props.STATUS === 'Aktif' ? '#22c55e' : props.STATUS === 'Non-aktif' ? '#ef4444' : '#f59e0b';
                statusObj = { text: props.STATUS, color: sc };
            }

            var markerType = DlhMarkers.detectType(layerName, props);

            return DlhMarkers.popup({
                nama: props.NAMA || layerName,
                kategori: layerName,
                type: markerType,
                status: statusObj,
                details: details,
            });
        }

        // Fungsi untuk menambahkan satu layer
        function addSingleLayer(layer) {
            var layerType = typeByLayer[layer.id];
            var color = typeColor[layerType] || (layer.metadata && layer.metadata.color) || '#6b7280';
            var sourceId = 'src-' + layer.id;

            // Hapus source dan layer lama jika ada
            if (map.getSource(sourceId)) {
                map.getStyle().layers.slice().forEach(function (l) {
                    if (l.source === sourceId && map.getLayer(l.id)) {
                        map.removeLayer(l.id);
                    }
                });
                map.removeSource(sourceId);
            }

            // Tambahkan source baru
            // Strip legacy CRS field from GeoJSON FeatureCollection
            var geojsonData = layer.geojson ? JSON.parse(JSON.stringify(layer.geojson)) : { type: 'FeatureCollection', features: [] };
            if (geojsonData.crs) delete geojsonData.crs;
            map.addSource(sourceId, { type: 'geojson', data: geojsonData });

            // Tambahkan layers berdasarkan jenis geometri
            if (layer.jenis_geometri === 'point' || layer.jenis_geometri === 'mixed') {
                // Gunakan DlhMarkers untuk custom SVG markers
                var pointMarkers = [];
                var features = (layer.geojson && layer.geojson.features) || [];
                features.forEach(function (f) {
                    if (!f.geometry || f.geometry.type !== 'Point') return;
                    var coords = f.geometry.coordinates;
                    if (!coords || !coords[0] || !coords[1]) return;
                    var props = f.properties || {};
                    var html = makePopupHtml(props, color, layer.nama_layer);
                    var mk = DlhMarkers.addToMap(map, 'tps', [coords[0], coords[1]], html, { size: 26 });
                    pointMarkers.push(mk);
                });
                // Simpan referensi marker untuk toggle visibility
                layer._pointMarkers = pointMarkers;
            }

            if (layer.jenis_geometri === 'line' || layer.jenis_geometri === 'mixed') {
                var lineId = sourceId + '-line';
                map.addLayer({ id: lineId, type: 'line', source: sourceId, paint: { 'line-color': color, 'line-width': 2 } });
            }

            if (layer.jenis_geometri === 'polygon' || layer.jenis_geometri === 'mixed') {
                var fillId = sourceId + '-fill';
                var outlineId = sourceId + '-outline';
                map.addLayer({ id: fillId, type: 'fill', source: sourceId, paint: { 'fill-color': color, 'fill-opacity': 0.3 } });
                map.addLayer({ id: outlineId, type: 'line', source: sourceId, paint: { 'line-color': color, 'line-width': 1 } });
            }
        }

        // Fungsi untuk menambahkan semua layer persampahan
        function addAllLayers() {
            layers.forEach(function (layer) {
                addSingleLayer(layer);
            });
        }

        // Visibility: hanya layer milik tipe aktif (dan kelurahan terpilih) yang tampil
        function layerVisible(layerId) {
            var t = typeByLayer[layerId];
            // Layer "Tanpa Filter" (tidak terdaftar di tipe kendaraan mana pun)
            // selalu tampil di peta, tidak terpengaruh filter yang aktif.
            if (!t) return true;
            if (!activeType) return false;
            if (t !== activeType) return false;
            if (activeKelurahan && layerId != activeKelurahan) return false;
            return true;
        }

        function applyVisibility() {
            layers.forEach(function (layer) {
                var vis = layerVisible(layer.id);
                var sourceId = 'src-' + layer.id;
                if (map.getSource(sourceId)) {
                    map.getStyle().layers.forEach(function (l) {
                        if (l.source === sourceId && map.getLayer(l.id)) {
                            map.setLayoutProperty(l.id, 'visibility', vis ? 'visible' : 'none');
                        }
                    });
                }
                if (layer._pointMarkers) {
                    layer._pointMarkers.forEach(function (mk) {
                        vis ? mk.addTo(map) : mk.remove();
                    });
                }
            });
        }

        // Fit bounds ke seluruh feature milik satu layer (kelurahan)
        function fitToLayer(layerId) {
            var layer = layers.find(function (l) { return l.id == layerId; });
            if (!layer || !layer.geojson || !layer.geojson.features) return;
            var bounds = new maplibregl.LngLatBounds();
            var any = false;
            function walk(coords) {
                if (typeof coords[0] === 'number') {
                    bounds.extend(coords);
                    any = true;
                    return;
                }
                coords.forEach(walk);
            }
            layer.geojson.features.forEach(function (f) {
                if (f.geometry && f.geometry.coordinates) walk(f.geometry.coordinates);
            });
            if (any) map.fitBounds(bounds, { padding: 60, maxZoom: 15 });
        }

        // API publik untuk filter frontend
        window.dlhPetaPersampahanSelectType = function (key) {
            activeType = key;
            activeKelurahan = null;
            applyVisibility();
        };

        window.dlhPetaPersampahanSelectKelurahan = function (layerId) {
            activeKelurahan = layerId ? String(layerId) : null;
            applyVisibility();
            if (activeKelurahan) fitToLayer(activeKelurahan);
        };

        window.dlhPetaPersampahanSetArmada = function (visible) {
            armadaVisible = !!visible;
            window._dlhArmadaVisible = armadaVisible;
            if (window._dlhArmadaMarkers) {
                window._dlhArmadaMarkers.forEach(function (mk) {
                    armadaVisible ? mk.addTo(map) : mk.remove();
                });
            }
        };

        // Fungsi untuk menggambar marker armada di peta
        window.dlhPetaPersampahanDrawArmada = function (vehicleData) {
            var f = window._dlhArmadaFilter || { types: new Set(['pickup', 'truck']), statuses: new Set(['active', 'parked']) };
            var visible = window._dlhArmadaVisible !== false;

            // Filter hanya berdasarkan jenis (pickup/truck); warna seragam untuk semua status
            var filtered = (vehicleData || []).filter(function (v) {
                var type = (parseInt(v.veh_type) === 4) ? 'truck' : 'pickup';
                return f.types.has(type);
            });

            var markers = window._dlhArmadaMarkers || [];
            var ni = new Set(filtered.map(function (v) { return v.imei; }));
            // Hapus marker yang tidak ada di data baru / ter-filter-out
            window._dlhArmadaMarkers = markers.filter(function (m) {
                if (!ni.has(m._imei)) { m.remove(); return false; }
                return true;
            });
            markers = window._dlhArmadaMarkers;

            filtered.forEach(function (v) {
                var lat = parseFloat(v.latitude), lng = parseFloat(v.longitude);
                if (isNaN(lat) || isNaN(lng)) return;
                var isTruck = (parseInt(v.veh_type) === 4);
                var iu = isTruck ? '/assets/tracking/truck_blue.png' : '/assets/tracking/car_blue.png';
                var el = document.createElement('div');
                el.className = 'custom-vehicle-icon';
                el.innerHTML = '<img src="' + iu + '" alt="" style="width:36px;height:36px;transform:rotate(' + v.angle + 'deg);transition:transform 0.3s ease;filter:drop-shadow(0 2px 6px rgba(0,0,0,0.3));cursor:pointer" onmouseover="this.style.transform=\'rotate(' + v.angle + 'deg) scale(1.15)\'" onmouseout="this.style.transform=\'rotate(' + v.angle + 'deg) scale(1)\'" />';
                var sc = '#8BB2D8';
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
                        .setPopup(new maplibregl.Popup({ offset: [0, -24], closeButton: true, closeOnClick: false, maxWidth: '280px' }).setHTML(ph));
                    if (visible) mk.addTo(map); else mk.remove();
                    mk._imei = v.imei;
                    markers.push(mk);
                }
            });

            window._dlhArmadaMarkers = markers;
        };

        map.on('load', function () {
            addAllLayers();
            applyVisibility();

            // Gambar armada awal
            if (armada && armada.length > 0) {
                window.dlhPetaPersampahanDrawArmada(armada);
            }
        });

        // Ketika basemap berubah, tambahkan ulang semua layer
        map.on('basemap-changed', function () {
            setTimeout(function () {
                addAllLayers();
                applyVisibility();
            }, 150);
        });

        dlhAddLocBtn(map);
    });
};

/** Peta Objek Pengawasan with multiple markers */
window.dlhPetaObjekPengawasan = function (containerId, points) {
    window.ensureMaplibreLoaded(function () {
        var map = new maplibregl.Map({
            container: containerId,
            style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
            center: [119.87, -0.9],
            zoom: 13,
            attributionControl: false
        });
        map.addControl(new DlhZoomControl(), 'top-right');
        map.addControl(new DlhBasemapSwitcher(), 'bottom-right');
        if (window.DlhFullscreenControl) map.addControl(new DlhFullscreenControl(), 'top-left');
        if (window.DlhWeatherControl) map.addControl(new DlhWeatherControl({ position: 'top-left' }), 'top-left');

        var markers = [];

        function addMarkers() {
            // Hapus marker lama
            markers.forEach(function (m) { m.remove(); });
            markers = [];

            points.forEach(function (p) {
                var popupHtml = DlhMarkers.popup({
                    nama: p.nama_perusahaan,
                    kategori: 'Objek Pengawasan',
                    type: 'objek_pengawasan',
                    details: [
                        { icon: 'lokasi', value: p.alamat },
                        { icon: 'doc', value: p.dokumen_summary || 'Belum ada data' },
                    ],
                });

                var mk = DlhMarkers.addToMap(map, 'objek_pengawasan', [p.longitude, p.latitude], popupHtml, { size: 30 });
                markers.push(mk);
            });
        }

        map.on('load', function () {
            addMarkers();

            if (markers.length > 0) {
                var bounds = new maplibregl.LngLatBounds();
                markers.forEach(function (m) { bounds.extend(m.getLngLat()); });
                map.fitBounds(bounds, { padding: 50 });
            }
        });

        // Ketika basemap berubah, tambahkan ulang marker
        map.on('basemap-changed', function () {
            setTimeout(addMarkers, 150);
        });

        dlhAddLocBtn(map);
        setTimeout(function () { map.resize(); }, 200);
    });
};

/** Peta RTH with multiple layers (taman, hutan, jalur, pohon, aset) */
window.dlhPetaRth = function (containerId, mapData) {
    window.ensureMaplibreLoaded(function () {
        var map = new maplibregl.Map({
            container: containerId,
            style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
            center: [119.87, -0.9],
            zoom: 13,
            attributionControl: false
        });
        map.addControl(new DlhZoomControl(), 'top-right');
        map.addControl(new DlhBasemapSwitcher(), 'bottom-right');
        if (window.DlhFullscreenControl) map.addControl(new DlhFullscreenControl(), 'top-left');
        if (window.DlhWeatherControl) map.addControl(new DlhWeatherControl({ position: 'top-left' }), 'top-left');

        // Custom GIS Layers
        var gisLayers = mapData.gis_layers || [];
        var gisVisibility = {};
        gisLayers.forEach(function (layer) { gisVisibility[layer.id] = true; });

        function makeGisPopupHtml(props, color, layerName) {
            var details = [];
            if (props.ALAMAT) details.push({ icon: 'lokasi', value: props.ALAMAT });
            if (props.KECAMATAN) details.push({ icon: 'lokasi', value: props.KECAMATAN });
            if (props.KELURAHAN) details.push({ icon: 'lokasi', value: props.KELURAHAN });
            
            var skipKeys = new Set(['NAMA', 'name', '_record', '_marker_type', 'ALAMAT', 'KECAMATAN', 'KELURAHAN']);
            Object.keys(props).forEach(function (key) {
                if (skipKeys.has(key) || key.indexOf('_') === 0) return;
                if (props[key] === null || props[key] === '') return;
                details.push({ icon: 'doc', value: key + ': ' + props[key] });
            });

            var statusObj = null;
            if (props.STATUS) {
                var sc = props.STATUS === 'Aktif' ? '#22c55e' : props.STATUS === 'Non-aktif' ? '#ef4444' : '#f59e0b';
                statusObj = { text: props.STATUS, color: sc };
            }

            var markerType = DlhMarkers.detectType(layerName, props);

            return DlhMarkers.popup({
                nama: props.NAMA || props.name || layerName,
                kategori: layerName,
                type: markerType,
                status: statusObj,
                details: details,
            });
        }

        function addSingleGisLayer(layer) {
            var color = (layer.metadata && layer.metadata.color) || '#6b7280';
            var sourceId = 'gis-src-' + layer.id;
            var visible = gisVisibility[layer.id] !== false;

            if (map.getSource(sourceId)) {
                map.getStyle().layers.slice().forEach(function (l) {
                    if (l.source === sourceId && map.getLayer(l.id)) {
                        map.removeLayer(l.id);
                    }
                });
                map.removeSource(sourceId);
            }

            // Strip legacy CRS field from GeoJSON FeatureCollection
            var geoJsonClean = layer.geojson ? JSON.parse(JSON.stringify(layer.geojson)) : { type: 'FeatureCollection', features: [] };
            if (geoJsonClean.crs) delete geoJsonClean.crs;
            map.addSource(sourceId, { type: 'geojson', data: geoJsonClean });

            if (layer.jenis_geometri === 'point' || layer.jenis_geometri === 'mixed') {
                var pointMarkers = [];
                var features = (layer.geojson && layer.geojson.features) || [];
                features.forEach(function (f) {
                    if (!f.geometry || f.geometry.type !== 'Point') return;
                    var coords = f.geometry.coordinates;
                    if (!coords || !coords[0] || !coords[1]) return;
                    var props = f.properties || {};
                    var html = makeGisPopupHtml(props, color, layer.nama_layer);
                    var markerType = DlhMarkers.detectType(layer.nama_layer, props);
                    var mk = DlhMarkers.addToMap(map, markerType, [coords[0], coords[1]], html, { size: 26 });
                    if (!visible) mk.remove();
                    pointMarkers.push(mk);
                });
                layer._pointMarkers = pointMarkers;
            }

            if (layer.jenis_geometri === 'line' || layer.jenis_geometri === 'mixed') {
                var lineId = sourceId + '-line';
                map.addLayer({ id: lineId, type: 'line', source: sourceId, paint: { 'line-color': color, 'line-width': 2 } });
                if (!visible) map.setLayoutProperty(lineId, 'visibility', 'none');
            }

            if (layer.jenis_geometri === 'polygon' || layer.jenis_geometri === 'mixed') {
                var fillId = sourceId + '-fill';
                var outlineId = sourceId + '-outline';
                map.addLayer({ id: fillId, type: 'fill', source: sourceId, paint: { 'fill-color': color, 'fill-opacity': 0.3 } });
                map.addLayer({ id: outlineId, type: 'line', source: sourceId, paint: { 'line-color': color, 'line-width': 1 } });
                if (!visible) {
                    map.setLayoutProperty(fillId, 'visibility', 'none');
                    map.setLayoutProperty(outlineId, 'visibility', 'none');
                }
            }
        }

        function addAllGisLayers() {
            gisLayers.forEach(function (layer) {
                addSingleGisLayer(layer);
            });
        }

        map.on('load', function () {
            addAllGisLayers();

            document.querySelectorAll('.gis-layer-toggle').forEach(function (el) {
                el.addEventListener('change', function () {
                    var layerId = el.dataset.layer;
                    if (!layerId) return;
                    var sourceId = 'gis-src-' + layerId;
                    var layer = gisLayers.find(function (l) { return l.id == layerId; });
                    if (!layer) return;

                    gisVisibility[layerId] = el.checked;

                    map.getStyle().layers.forEach(function (l) {
                        if (l.source === sourceId && map.getLayer(l.id)) {
                            map.setLayoutProperty(l.id, 'visibility', el.checked ? 'visible' : 'none');
                        }
                    });

                    if (layer._pointMarkers) {
                        layer._pointMarkers.forEach(function (mk) {
                            el.checked ? mk.addTo(map) : mk.remove();
                        });
                    }
                });
            });
        });

        // Ketika basemap berubah, tambahkan ulang semua GIS layer
        map.on('basemap-changed', function () {
            setTimeout(function () {
                addAllGisLayers();
            }, 150);
        });

        dlhAddLocBtn(map);
        setTimeout(function () { map.resize(); }, 200);
    });
};
