@extends('layouts.app')

@section('title', 'Peta TPA - Dinas Lingkungan Hidup Kota Palu')
@section('description', 'Peta interaktif titik fasilitas dan sebaran data spasial Tempat Pemrosesan Akhir (TPA) Dinas Lingkungan Hidup Kota Palu.')

@push('styles')
<style>
    .tpa-page {
        position: relative;
        overflow-x: clip;
    }
    .tpa-page::before {
        content: '';
        position: absolute;
        z-index: -1;
        top: 12rem;
        right: -10rem;
        width: 24rem;
        height: 24rem;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(16, 185, 129, .1), transparent 68%);
        filter: blur(5px);
        pointer-events: none;
    }
    .persampahan-map-frame {
        position: relative;
        border-radius: 1.5rem;
    }
    .persampahan-map-frame::before {
        content: '';
        position: absolute;
        z-index: 1;
        inset: 1px 1px auto;
        height: 4.5rem;
        border-radius: 1.45rem 1.45rem 0 0;
        background: linear-gradient(180deg, rgba(255, 255, 255, .22), transparent);
        pointer-events: none;
    }
    .tpa-page .map-container {
        position: relative;
        width: 100%;
        height: clamp(32rem, 70vw, 50rem);
        border-radius: 1.5rem;
        overflow: hidden;
        isolation: isolate;
        border: 1px solid rgba(16, 76, 51, .14);
        background: #e7f2eb;
        box-shadow: 0 1px 2px rgba(10, 48, 30, .05), 0 24px 48px -30px rgba(10, 48, 30, .38);
    }
    .dark .tpa-page .map-container {
        border-color: rgba(110, 231, 183, .2);
        background: #0b241a;
    }
    .tpa-page .map-container .maplibregl-map {
        width: 100%;
        height: 100%;
    }
    .tpa-page .map-container .maplibregl-ctrl-group {
        border: 1px solid rgba(16, 76, 51, .12) !important;
        border-radius: 12px !important;
        overflow: hidden;
        box-shadow: 0 10px 24px -12px rgba(10, 48, 30, .35) !important;
    }
    .tpa-page .map-container .maplibregl-ctrl:not(.dlh-tools-ctrl):not(.dlh-tools-ctrl__item) {
        margin: 12px !important;
    }
    .tpa-page .map-container .maplibregl-ctrl-top-left {
        top: 12px !important;
        left: 12px !important;
    }
    .tpa-page .map-container .maplibregl-ctrl-bottom-right {
        bottom: 12px !important;
        right: 12px !important;
    }
    .tpa-page .map-container .maplibregl-popup-content {
        border: 1px solid rgba(16, 76, 51, .12) !important;
        border-radius: 16px !important;
        box-shadow: 0 18px 40px -16px rgba(10, 48, 30, .34) !important;
        padding: 0 !important;
        overflow: hidden;
    }

    @media (max-width: 640px) {
        .tpa-page::before { right: -13rem; }
        .tpa-page .map-container { height: 28rem; border-radius: 1.25rem; }
    }
</style>
@endpush

@section('content')
<div class="tpa-page space-y-8">
    <x-public.page-hero
        badge="{{ __('Sampah & LB3') }}"
        icon="map-pin"
        title="{{ __('Peta TPA') }}"
        description="{{ __('Peta interaktif titik fasilitas dan sebaran data spasial Tempat Pemrosesan Akhir (TPA) Dinas Lingkungan Hidup Kota Palu.') }}"
    />

    {{-- Map Container --}}
    <div class="space-y-6">
        <div class="persampahan-map-frame reveal reveal-scale">
            <div class="map-container" role="region" aria-label="Peta fasilitas TPA Dinas Lingkungan Hidup Kota Palu">
                <div id="tpa-map" style="width:100%;height:100%"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@vite('resources/js/map-bundle.js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var mapLayers = @json($layers);

        window.ensureMaplibreLoaded(function () {
            var defaultCenter = [119.8707, -0.9003];
            var mapInstance = new maplibregl.Map({
                container: 'tpa-map',
                style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
                center: defaultCenter,
                zoom: 13,
                attributionControl: false
            });

            mapInstance.addControl(new DlhZoomControl(), 'top-right');
            if (window.DlhToolsControl && window.dlhToolsDropdown) mapInstance.addControl(window.dlhToolsDropdown(), 'top-left');
            if (window.DlhBasemapSwitcher) mapInstance.addControl(new DlhBasemapSwitcher(), 'bottom-right');

            mapInstance.on('load', function () {
                var bounds = new maplibregl.LngLatBounds();
                var hasFeatures = false;

                mapLayers.forEach(function (layer) {
                    var sourceId = 'tpa-layer-' + layer.id;
                    var color = (layer.metadata && layer.metadata.color) ? layer.metadata.color : '#10b981';
                    var geojson = layer.geojson || { type: 'FeatureCollection', features: [] };
                    var features = geojson.features || [];

                    if (layer.jenis_geometri === 'point') {
                        // Render titik dengan DlhMarkers (SVG Icon Badge persis seperti di Admin)
                        features.forEach(function (f) {
                            if (!f.geometry || !f.geometry.coordinates) return;
                            var coords = f.geometry.coordinates;
                            var props = f.properties || {};

                            var LAYER_MARKER_MAP = {
                                'taman kota': 'taman', 'taman': 'taman',
                                'hutan kota': 'hutan', 'hutan': 'hutan',
                                'pohon pelindung': 'pohon', 'pohon': 'pohon',
                                'jalur hijau': 'jalur_hijau', 'jalur': 'jalur_hijau',
                                'aset rth': 'aset_rth', 'aset': 'aset_rth',
                                'bank sampah': 'bank_sampah',
                                'tpst': 'tpst', 'tpa': 'tpa', 'tps': 'tps',
                                'armada': 'armada',
                                'objek pengawasan': 'objek_pengawasan',
                                'pengaduan': 'pengaduan',
                            };
                            function getMarkerType(layerName) {
                                var name = (layerName || '').toLowerCase().trim();
                                for (var key in LAYER_MARKER_MAP) {
                                    if (name === key || name.indexOf(key) !== -1) return LAYER_MARKER_MAP[key];
                                }
                                return 'default';
                            }

                            var layerDefault = (layer.metadata && layer.metadata.marker_type) || getMarkerType(layer.nama_layer);
                            var markerType = props._marker_type || props.marker_type || layerDefault;

                            var detailRows = [];
                            var skip = new Set(['NAMA', 'nama', 'Name', 'NAME', '_marker_type', '_record', 'id', 'fid']);
                            Object.keys(props).forEach(function (key) {
                                if (skip.has(key) || key.startsWith('_')) return;
                                if (props[key] === null || props[key] === '') return;
                                var ic = 'doc';
                                if (key.toUpperCase().indexOf('ALAMAT') !== -1 || key.toUpperCase().indexOf('LOKASI') !== -1) ic = 'lokasi';
                                else if (key.toUpperCase().indexOf('STATUS') !== -1) ic = 'status';
                                else if (key.toUpperCase().indexOf('LUAS') !== -1 || key.toUpperCase().indexOf('AREA') !== -1) ic = 'area';
                                else if (key.toUpperCase().indexOf('KAPASITAS') !== -1 || key.toUpperCase().indexOf('VOLUME') !== -1) ic = 'volume';
                                detailRows.push({ icon: ic, value: key + ': ' + props[key] });
                            });

                            var popHtml = (typeof window.DlhMarkers !== 'undefined' && window.DlhMarkers.popup)
                                ? window.DlhMarkers.popup({
                                    nama: props.NAMA || props.nama || props.Name || props.NAME || layer.nama_layer,
                                    kategori: layer.nama_layer,
                                    type: markerType,
                                    details: detailRows
                                })
                                : '<div style="padding:10px;font-weight:bold">' + (props.NAMA || layer.nama_layer) + '</div>';

                            if (typeof window.DlhMarkers !== 'undefined' && window.DlhMarkers.addToMap) {
                                window.DlhMarkers.addToMap(mapInstance, markerType, [coords[0], coords[1]], popHtml, { size: 30 });
                            } else {
                                new maplibregl.Marker({ color: color })
                                    .setLngLat([coords[0], coords[1]])
                                    .setPopup(new maplibregl.Popup({ offset: [0, -20] }).setHTML(popHtml))
                                    .addTo(mapInstance);
                            }

                            bounds.extend([coords[0], coords[1]]);
                            hasFeatures = true;
                        });
                    } else {
                        // Polygon & Line layers
                        if (!mapInstance.getSource(sourceId)) {
                            mapInstance.addSource(sourceId, {
                                type: 'geojson',
                                data: geojson
                            });
                        }

                        if (layer.jenis_geometri === 'polygon') {
                            mapInstance.addLayer({
                                id: sourceId + '-fill',
                                type: 'fill',
                                source: sourceId,
                                paint: {
                                    'fill-color': color,
                                    'fill-opacity': 0.4
                                }
                            });
                            mapInstance.addLayer({
                                id: sourceId + '-line',
                                type: 'line',
                                source: sourceId,
                                paint: {
                                    'line-color': color,
                                    'line-width': 2.5
                                }
                            });
                        } else if (layer.jenis_geometri === 'line') {
                            mapInstance.addLayer({
                                id: sourceId + '-line',
                                type: 'line',
                                source: sourceId,
                                paint: {
                                    'line-color': color,
                                    'line-width': 3.5
                                }
                            });
                        }

                        var layerIds = [sourceId + '-fill', sourceId + '-line'].filter(function (id) {
                            return mapInstance.getLayer(id);
                        });

                        layerIds.forEach(function (lyId) {
                            mapInstance.on('click', lyId, function (e) {
                                if (!e.features || !e.features.length) return;
                                var f = e.features[0];
                                var props = f.properties || {};
                                var name = props.NAMA || props.nama || props.Name || props.NAME || layer.nama_layer;
                                var desc = props.DESKRIPSI || props.deskripsi || props.KETERANGAN || props.keterangan || '';

                                var popHtml = '<div style="min-width:200px;padding:12px;font-family:system-ui,-apple-system,sans-serif">'
                                    + '<h4 style="font-weight:700;font-size:13px;color:#0f172a;margin:0 0 4px">' + name + '</h4>'
                                    + '<p style="font-size:11px;color:#64748b;margin:0">' + (desc || ('Layer: ' + layer.nama_layer)) + '</p>'
                                    + '</div>';

                                new maplibregl.Popup({ offset: [0, -10], closeButton: true })
                                    .setLngLat(e.lngLat)
                                    .setHTML(popHtml)
                                    .addTo(mapInstance);
                            });

                            mapInstance.on('mouseenter', lyId, function () {
                                mapInstance.getCanvas().style.cursor = 'pointer';
                            });
                            mapInstance.on('mouseleave', lyId, function () {
                                mapInstance.getCanvas().style.cursor = '';
                            });
                        });

                        if (features.length) {
                            features.forEach(function (f) {
                                if (f.geometry && f.geometry.coordinates) {
                                    var coords = f.geometry.coordinates;
                                    function extendCoords(c) {
                                        if (typeof c[0] === 'number') bounds.extend(c);
                                        else if (Array.isArray(c)) c.forEach(extendCoords);
                                    }
                                    extendCoords(coords);
                                    hasFeatures = true;
                                }
                            });
                        }
                    }
                });

                if (hasFeatures && !bounds.isEmpty()) {
                    mapInstance.fitBounds(bounds, { padding: 60, maxZoom: 16.5 });
                }
            });
        });
    });
</script>
@endpush
@endsection
