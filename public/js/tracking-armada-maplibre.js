/**
 * MapLibre GL JS - Tracking Armada
 * Dipisah dari Blade agar tidak terpengaruh Livewire/Vite script extraction.
 */
(function () {
    function initTrackingMap(containerId) {
        if (window._trackingMapReady) return;

        window.ensureMaplibreLoaded(function () {
            var map = new maplibregl.Map({
                container: containerId,
                style: {
                    version: 8,
                    sources: {
                        'osm-raster': {
                            type: 'raster',
                            tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
                            tileSize: 256,
                            attribution: 'OpenStreetMap contributors'
                        }
                    },
                    layers: [{
                        id: 'osm-raster-layer',
                        type: 'raster',
                        source: 'osm-raster',
                        minzoom: 0,
                        maxzoom: 19
                    }]
                },
                center: [119.87, -0.9],
                zoom: 13,
                attributionControl: true
            });

            map.addControl(new DlhZoomControl(), 'top-right');
            if (window.DlhFullscreenControl) map.addControl(new DlhFullscreenControl(), 'top-left');
            if (window.DlhWeatherControl) map.addControl(new DlhWeatherControl({ position: 'top-left' }), 'top-left');

            map.addControl(new maplibregl.ScaleControl({
                maxWidth: 150,
                unit: 'metric'
            }), 'bottom-left');

            window._trackingMap = map;
            window._trackingMapReady = true;
            window._trackingMarkers = [];
        });
    }

    function drawTrackingMarkers(vehicleData, fitView) {
        var map = window._trackingMap;
        if (!map) {
            setTimeout(function () { drawTrackingMarkers(vehicleData, fitView); }, 200);
            return;
        }

        var labels = window._trackingLabels || {};
        var markers = window._trackingMarkers;

        var existingImeis = new Set(markers.map(function (m) { return m.imei; }));
        var newImeis = new Set(vehicleData.map(function (v) { return v.imei; }));

        window._trackingMarkers = markers.filter(function (m) {
            if (!newImeis.has(m.imei)) {
                m.marker.remove();
                return false;
            }
            return true;
        });
        markers = window._trackingMarkers;

        vehicleData.forEach(function (v) {
            var lat = parseFloat(v.latitude);
            var lng = parseFloat(v.longitude);
            if (isNaN(lat) || isNaN(lng)) return;

            var isTruck = (parseInt(v.veh_type) === 4);
            var prefix = isTruck ? 'truck' : 'car';
            var state = (parseInt(v.acc) === 1) ? 'acc_on' : 'parking';
            var iconUrl = '/assets/tracking/' + prefix + '_' + state + '.png';

            var el = document.createElement('div');
            el.className = 'custom-vehicle-marker';
            el.innerHTML =
                '<div style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">' +
                '<img src="' + iconUrl + '" alt="" style="transform:rotate(' + v.angle + 'deg);width:32px;height:32px;transition:transform 0.3s ease;" />' +
                '</div>';

            var statusColor = (parseInt(v.acc) === 1) ? '#10b981' : '#64748b';
            var statusText = (parseInt(v.acc) === 1) ? (labels.active || 'Aktif') : (labels.parking || 'Parkir');
            var popupHtml =
                '<div style="min-width:180px;padding:12px;">' +
                '<p style="font-weight:700;font-size:13px;color:#10b981;margin:0 0 8px;">' + v.title + '</p>' +
                '<p style="font-size:11px;color:#64748b;margin:4px 0;">' + (labels.speed || 'Kecepatan:') + ' ' + v.speed + ' km/h</p>' +
                '<p style="font-size:11px;color:#64748b;margin:4px 0;">' + (labels.status || 'Status:') + ' <strong style="color:' + statusColor + '">' + statusText + '</strong></p>' +
                '<p style="font-size:11px;color:#64748b;margin:4px 0;">' + (labels.update || 'Update:') + ' ' + v.server_time + '</p>' +
                '</div>';

            var popup = new maplibregl.Popup({
                offset: [0, -24],
                closeButton: true,
                closeOnClick: false,
                maxWidth: '250px'
            }).setHTML(popupHtml);

            var tooltipEl = document.createElement('div');
            tooltipEl.className = 'vehicle-tooltip-maplibre';
            tooltipEl.textContent = v.title;
            var tooltip = new maplibregl.Popup({
                offset: [0, -45],
                closeButton: false,
                closeOnClick: false
            }).setDOMContent(tooltipEl);

            var existing = markers.find(function (m) { return m.imei === v.imei; });

            if (existing) {
                existing.marker.setLngLat([lng, lat]);
                existing.marker.getElement().querySelector('img').style.transform = 'rotate(' + v.angle + 'deg)';
                existing.marker.setPopup(popup);
            } else {
                var marker = new maplibregl.Marker({ element: el })
                    .setLngLat([lng, lat])
                    .setPopup(popup)
                    .addTo(map);

                el.addEventListener('mouseenter', function () {
                    tooltip.setLngLat([lng, lat]).addTo(map);
                });
                el.addEventListener('mouseleave', function () {
                    tooltip.remove();
                });

                markers.push({
                    imei: v.imei,
                    marker: marker,
                    popup: popup,
                    tooltip: tooltip
                });
            }
        });

        if (fitView && markers.length > 0) {
            var bounds = new maplibregl.LngLatBounds();
            markers.forEach(function (m) { bounds.extend(m.marker.getLngLat()); });
            map.fitBounds(bounds, { padding: 50, maxZoom: 15 });
        }
    }

    window.initTrackingMap = initTrackingMap;
    window.drawTrackingMarkers = drawTrackingMarkers;

    window.addEventListener('guest-map-vehicles-updated', function (e) {
        drawTrackingMarkers(e.detail.vehicles, e.detail.fitBounds);
    });
})();
