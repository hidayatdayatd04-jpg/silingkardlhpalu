/**
 * DLH Tracking Map - MapLibre GL JS
 * Custom basemap + vehicle markers + geolocation
 * No Blade directives — reads data from data-* attributes.
 */
(function () {
    function initDLHMap(containerId) {
        if (window._dlhMapReady) return;
        window._dlhMapReady = true;

        var container = document.getElementById(containerId);
        if (!container) return;

        var labels = window._dlhLabels || {};
        var vehicleData = window._dlhVehicles || [];

        window.ensureMaplibreLoaded(function () {
            var map = new maplibregl.Map({
                container: containerId,
                style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
                center: [119.87, -0.9],
                zoom: 13,
                attributionControl: false,
                maxPitch: 0
            });

            map.addControl(new DlhZoomControl(), 'top-right');
            if (window.DlhFullscreenControl) map.addControl(new DlhFullscreenControl(), 'top-left');
            if (window.DlhWeatherControl) map.addControl(new DlhWeatherControl({ position: 'top-left' }), 'top-left');
            map.addControl(new maplibregl.ScaleControl({ maxWidth: 150, unit: 'metric' }), 'bottom-left');
            map.addControl(new maplibregl.AttributionControl({ customAttribution: 'Maps DLH - Palu Dev Custom', compact: true }), 'bottom-left');

            // My Location control
            var locBtn = document.createElement('button');
            locBtn.className = 'maplibregl-ctrl-icon';
            locBtn.type = 'button';
            locBtn.title = 'Lokasi Saya';
            locBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>';
            locBtn.style.cssText = 'width:34px;height:34px;display:flex;align-items:center;justify-content:center;cursor:pointer;border:none;background:transparent;color:#4a5568;';
            locBtn.addEventListener('click', function () {
                if (!navigator.geolocation) { alert('Geolocation tidak didukung browser ini.'); return; }
                locBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>';
                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        var lng = pos.coords.longitude;
                        var lat = pos.coords.latitude;
                        map.flyTo({ center: [lng, lat], zoom: 15, duration: 1500 });

                        if (window._myLocMarker) window._myLocMarker.remove();

                        var el = document.createElement('div');
                        el.innerHTML = '<div style="width:20px;height:20px;background:#3b82f6;border:3px solid #fff;border-radius:50%;box-shadow:0 0 0 3px rgba(59,130,246,0.3),0 2px 8px rgba(0,0,0,0.2);"></div>';

                        window._myLocMarker = new maplibregl.Marker({ element: el, anchor: 'center' })
                            .setLngLat([lng, lat])
                            .setPopup(new maplibregl.Popup({ offset: [0, -14], closeButton: false, closeOnClick: false, maxWidth: '200px' }).setHTML(
                                '<div style="padding:8px 12px;font-family:system-ui,-apple-system,sans-serif;text-align:center;">' +
                                '<p style="font-size:11px;font-weight:600;color:#1e293b;margin:0;">Lokasi Anda</p>' +
                                '<p style="font-size:10px;color:#94a3b8;margin:2px 0 0;">' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</p>' +
                                '</div>'
                            ))
                            .addTo(map);

                        window._myLocMarker.togglePopup();
                        locBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>';
                    },
                    function () {
                        locBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>';
                        alert('Gagal mendapatkan lokasi. Pastikan Izin Lokasi diaktifkan.');
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            });

            var locCtrl = document.createElement('div');
            locCtrl.className = 'maplibregl-ctrl maplibregl-ctrl-group';
            locCtrl.style.cssText = 'border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.1);border:1px solid rgba(0,0,0,0.06);background:rgba(255,255,255,0.95);backdrop-filter:blur(8px);';
            locCtrl.appendChild(locBtn);
            map._container.appendChild(locCtrl);
            locCtrl.style.position = 'absolute';
            locCtrl.style.top = '120px';
            locCtrl.style.right = '10px';
            locCtrl.style.zIndex = '1';

            window._dlhMap = map;
            window._dlhMarkers = [];

            // Style customization on load
            map.on('load', function () {
                var layers = map.getStyle().layers;
                var m = map;

                function set(id, prop, val) {
                    if (m.getLayer(id)) try { m.setPaintProperty(id, prop, val); } catch (e) {}
                }
                function set2(id, p1, v1, p2, v2) {
                    if (m.getLayer(id)) try { m.setPaintProperty(id, p1, v1); m.setPaintProperty(id, p2, v2); } catch (e) {}
                }

                set('background', 'background-color', '#f6f7f9');
                set2('water', 'fill-color', '#aad4f0', 'fill-opacity', 1);
                set2('waterway', 'line-color', '#aad4f0', 'line-width', 1.5);
                set2('park', 'fill-color', '#c8e6b8', 'fill-opacity', 0.75);
                set2('park_outline', 'line-color', '#b8d8a8', 'line-width', 0.6);
                set2('landcover', 'fill-color', '#c8e6b8', 'fill-opacity', 0.5);
                set2('landuse', 'fill-color', '#f0efec', 'fill-opacity', 0.2);
                set2('building', 'fill-color', '#eae8e4', 'fill-opacity', 0.55);
                set('building', 'fill-outline-color', '#dddad5');

                // Roads
                layers.forEach(function (layer) {
                    if (layer['source-layer'] !== 'transportation' || layer.type !== 'line') return;
                    var cls = (layer.layout && layer.layout['class']) || '';
                    var id = layer.id;
                    var isCase = id.indexOf('case') !== -1 || id.indexOf('casing') !== -1 || id.indexOf('outline') !== -1;
                    if (cls === 'motorway') set2(id, 'line-color', isCase ? '#e0b840' : '#f0cc5c', 'line-width', isCase ? 7 : 6);
                    else if (cls === 'trunk') set2(id, 'line-color', isCase ? '#e0b840' : '#f0cc5c', 'line-width', isCase ? 6 : 5);
                    else if (cls === 'primary') set2(id, 'line-color', isCase ? '#ecd898' : '#f5dea0', 'line-width', isCase ? 5.5 : 4);
                    else if (cls === 'secondary') set2(id, 'line-color', isCase ? '#d8d5d0' : '#ffffff', 'line-width', isCase ? 5 : 3.5);
                    else if (cls === 'tertiary') set2(id, 'line-color', isCase ? '#e0ddd8' : '#ffffff', 'line-width', isCase ? 4 : 2.8);
                    else if (cls === 'path') set2(id, 'line-color', '#c8c5c0', 'line-width', 1.2);
                    else if (cls === '' || cls === 'minor' || cls === 'service' || cls === 'residential')
                        set2(id, 'line-color', isCase ? '#e8e6e3' : '#ffffff', 'line-width', isCase ? 2.5 : 2);
                });

                set2('rail', 'line-color', '#d0ccc5', 'line-width', 1.5);

                // Labels
                layers.forEach(function (layer) {
                    if (layer.type !== 'symbol' || !m.getLayer(layer.id)) return;
                    try {
                        m.setPaintProperty(layer.id, 'text-halo-width', 3.5);
                        m.setPaintProperty(layer.id, 'text-halo-color', '#ffffff');
                        m.setPaintProperty(layer.id, 'text-halo-blur', 0.6);
                        var src = layer['source-layer'] || '';
                        m.setPaintProperty(layer.id, 'text-color', src === 'place' ? '#2d3748' : '#4a5568');
                        var op = m.getPaintProperty(layer.id, 'text-opacity');
                        if (Array.isArray(op) && op[0] === 'interpolate') {
                            var n = ['interpolate', ['linear'], ['zoom']];
                            for (var j = 2; j < op.length - 1; j += 2) { n.push(op[j]); n.push(Math.min(1, op[j + 1] + 0.4)); }
                            m.setPaintProperty(layer.id, 'text-opacity', n);
                        } else if (typeof op === 'number' && op < 1) {
                            m.setPaintProperty(layer.id, 'text-opacity', Math.min(1, op + 0.4));
                        }
                    } catch (e) {}
                });

                // Draw initial vehicles
                window.drawDLHMarkers(vehicleData, true);
            });
        });
    }

    function drawDLHMarkers(vehicleData, fitView) {
        var map = window._dlhMap;
        if (!map) { setTimeout(function () { drawDLHMarkers(vehicleData, fitView); }, 200); return; }

        var labels = window._dlhLabels || {};
        var markers = window._dlhMarkers;

        var newImeis = new Set(vehicleData.map(function (v) { return v.imei; }));
        window._dlhMarkers = markers.filter(function (m) {
            if (!newImeis.has(m._imei)) { m.remove(); return false; }
            return true;
        });
        markers = window._dlhMarkers;

        vehicleData.forEach(function (v) {
            var lat = parseFloat(v.latitude);
            var lng = parseFloat(v.longitude);
            if (isNaN(lat) || isNaN(lng)) return;

            var isTruck = (parseInt(v.veh_type) === 4);
            var prefix = isTruck ? 'truck' : 'car';
            var state = (parseInt(v.acc) === 1) ? 'acc_on' : 'parking';
            var iconUrl = '/assets/tracking/' + prefix + '_' + state + '.png';

            var el = document.createElement('div');
            el.className = 'custom-vehicle-icon';
            el.innerHTML = '<div style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.9);border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.15),0 0 0 1px rgba(0,0,0,0.05);backdrop-filter:blur(4px);transition:transform 0.3s ease,box-shadow 0.2s ease;" onmouseover="this.style.transform=\'scale(1.1)\';this.style.boxShadow=\'0 4px 16px rgba(0,0,0,0.25),0 0 0 1px rgba(0,0,0,0.08)\'" onmouseout="this.style.transform=\'scale(1)\';this.style.boxShadow=\'0 2px 8px rgba(0,0,0,0.15),0 0 0 1px rgba(0,0,0,0.05)\'"><img src="' + iconUrl + '" style="transform:rotate(' + v.angle + 'deg);width:30px;height:30px;transition:transform 0.3s ease;" /></div>';

            var statusColor = (parseInt(v.acc) === 1) ? '#10b981' : '#64748b';
            var statusText = (parseInt(v.acc) === 1) ? (labels.active || 'Aktif') : (labels.parking || 'Parkir');
            var popupHtml = '<div style="min-width:200px;padding:16px;font-family:system-ui,-apple-system,sans-serif;">'
                + '<div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">'
                + '<div style="width:8px;height:8px;border-radius:50%;background:' + statusColor + ';box-shadow:0 0 0 3px ' + statusColor + '33;flex-shrink:0;"></div>'
                + '<p style="font-weight:700;font-size:13px;color:#1e293b;margin:0;letter-spacing:-0.3px;line-height:1.3;">' + v.title + '</p>'
                + '</div>'
                + '<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px 12px;">'
                + '<div><p style="font-size:10px;color:#94a3b8;margin:0;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">' + (labels.speed || 'Kecepatan') + '</p><p style="font-size:12px;color:#334155;margin:2px 0 0;font-weight:600;">' + v.speed + ' <span style="font-weight:400;color:#94a3b8;">km/h</span></p></div>'
                + '<div><p style="font-size:10px;color:#94a3b8;margin:0;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">' + (labels.status || 'Status') + '</p><p style="font-size:12px;color:' + statusColor + ';margin:2px 0 0;font-weight:600;">' + statusText + '</p></div>'
                + '</div>'
                + '<div style="margin-top:10px;padding-top:8px;border-top:1px solid #f1f5f9;">'
                + '<p style="font-size:10px;color:#94a3b8;margin:0;">' + (labels.update || 'Update') + ': ' + v.server_time + '</p>'
                + '</div>'
                + '</div>';

            var existing = markers.find(function (m) { return m._imei === v.imei; });
            if (existing) {
                existing.setLngLat([lng, lat]);
                existing.getElement().querySelector('img').style.transform = 'rotate(' + v.angle + 'deg)';
            } else {
                var marker = new maplibregl.Marker({ element: el, anchor: 'center' })
                    .setLngLat([lng, lat])
                    .setPopup(new maplibregl.Popup({ offset: [0, -24], closeButton: true, closeOnClick: false, maxWidth: '280px' }).setHTML(popupHtml))
                    .addTo(map);
                marker._imei = v.imei;
                markers.push(marker);
            }
        });

        if (fitView && markers.length > 0) {
            var bounds = new maplibregl.LngLatBounds();
            markers.forEach(function (m) { bounds.extend(m.getLngLat()); });
            map.fitBounds(bounds, { padding: 50, maxZoom: 15 });
        }
    }

    window.initDLHMap = initDLHMap;
    window.drawDLHMarkers = drawDLHMarkers;

    window.addEventListener('guest-map-vehicles-updated', function (e) {
        drawDLHMarkers(e.detail.vehicles, e.detail.fitBounds);
    });
})();
