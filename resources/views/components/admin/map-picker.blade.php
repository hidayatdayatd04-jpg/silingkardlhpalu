@props([
    'latInput' => 'latitude',
    'lngInput' => 'longitude',
    'lat' => null,
    'lng' => null,
    'defaultLat' => -0.8917,
    'defaultLng' => 119.8707,
    'zoom' => 13,
    'height' => '320px',
    'readonly' => false,
])

@php
    $mapId = 'mp-' . Str::random(6);
    $initLat = $lat !== null && $lat !== '' ? (float) $lat : null;
    $initLng = $lng !== null && $lng !== '' ? (float) $lng : null;
@endphp

<div
    class="space-y-3"
    x-data="mapPicker({
        mapId: @js($mapId),
        latInput: @js($latInput),
        lngInput: @js($lngInput),
        initLat: {{ $initLat !== null ? $initLat : 'null' }},
        initLng: {{ $initLng !== null ? $initLng : 'null' }},
        defLat: {{ $defaultLat }},
        defLng: {{ $defaultLng }},
        zoom: {{ (int) $zoom }},
        readonly: {{ $readonly ? 'true' : 'false' }},
    })"
    x-init="init()"
>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
            @if($readonly)
                Lokasi Kejadian (tidak dapat diubah)
            @else
                Pilih Lokasi di Peta
            @endif
        </p>
        @if(!$readonly)
        <button type="button" x-on:click="locate()" class="inline-flex min-h-9 items-center justify-center gap-1.5 self-start rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 outline-none transition-[background-color,border-color,color] duration-150 hover:border-brand-300 hover:bg-brand-50 hover:text-brand-800 focus-visible:ring-2 focus-visible:ring-brand-600/25 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-brand-700 dark:hover:bg-brand-950/45 dark:hover:text-brand-200">
            <x-admin.icon name="map-pin" :size="14" aria-hidden="true" /> Lokasi saya
        </button>
        @endif
    </div>

    <div id="{{ $mapId }}" style="height: {{ $height }};" class="w-full overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-[0_8px_24px_-18px_rgba(15,23,42,0.36)] dark:border-slate-700 dark:bg-slate-800" role="application" aria-label="Peta untuk memilih lokasi"></div>

    @if(!$readonly)
    <p class="text-xs leading-5 text-slate-500 dark:text-slate-400">
        Klik peta untuk memilih lokasi. Titik lokasi akan terisi otomatis.
    </p>
    @endif
</div>

@once
    @push('scripts')
    <script>
        function mapPicker(cfg) {
            return {
                map: null,
                marker: null,
                init: function () {
                    var self = this;
                    window.ensureMaplibreLoaded(function () {
                        var el = document.getElementById(cfg.mapId);
                        if (!el || self.map) return;
                        var hasInit = cfg.initLat !== null && cfg.initLng !== null;
                        var center = hasInit ? [cfg.initLng, cfg.initLat] : [cfg.defLng, cfg.defLat];
                        self.map = new maplibregl.Map({
                            container: cfg.mapId,
                            style: 'https://basemaps.cartocdn.com/gl/voyager-gl-style/style.json',
                            center: center,
                            zoom: cfg.zoom,
                            attributionControl: false
                        });
                        self.map.addControl(new DlhZoomControl(), 'top-left');
if (window.DlhWeatherControl) self.map.addControl(new DlhWeatherControl(), 'top-right');
                        if (window.DlhBasemapSwitcher) self.map.addControl(new DlhBasemapSwitcher(), 'bottom-right');
                        if (window.dlhAddLocBtn) dlhAddLocBtn(self.map);
                        if (hasInit) self.setMarker(cfg.initLat, cfg.initLng, false);
                        // Hanya aktifkan click & drag jika tidak readonly
                        if (!cfg.readonly) {
                            self.map.on('click', function (e) { self.setMarker(e.lngLat.lat, e.lngLat.lng, true); });
                            ['latInput', 'lngInput'].forEach(function (k) {
                                var inp = self.field(cfg[k]);
                                if (inp) inp.addEventListener('change', function () { self.fromInputs(); });
                            });
                        }
                        setTimeout(function () { self.map.resize(); }, 200);
                    });
                },
                field: function (ref) {
                    return document.getElementById(ref) || document.querySelector('[name="' + ref + '"]');
                },
                setMarker: function (lat, lng, writeInputs) {
                    var self = this;
                    if (self.marker) {
                        self.marker.setLngLat([lng, lat]);
                    } else {
                        self.marker = new maplibregl.Marker({ draggable: !cfg.readonly, anchor: 'center' })
                            .setLngLat([lng, lat])
                            .addTo(self.map);
                        if (!cfg.readonly) {
                            self.marker.on('dragend', function () {
                                var p = self.marker.getLngLat();
                                self.writeFields(p.lat, p.lng);
                            });
                        }
                    }
                    if (writeInputs) self.writeFields(lat, lng);
                },
                writeFields: function (lat, lng) {
                    var la = this.field(cfg.latInput), ln = this.field(cfg.lngInput);
                    if (la) { la.value = Number(lat).toFixed(6); la.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (ln) { ln.value = Number(lng).toFixed(6); ln.dispatchEvent(new Event('input', { bubbles: true })); }
                },
                fromInputs: function () {
                    var la = parseFloat(this.field(cfg.latInput).value);
                    var ln = parseFloat(this.field(cfg.lngInput).value);
                    if (!isNaN(la) && !isNaN(ln)) {
                        this.setMarker(la, ln, false);
                        this.map.setCenter([ln, la]);
                    }
                },
                locate: function () {
                    var self = this;
                    if (!navigator.geolocation) return;
                    navigator.geolocation.getCurrentPosition(function (pos) {
                        var lat = pos.coords.latitude, lng = pos.coords.longitude;
                        self.setMarker(lat, lng, true);
                        self.map.flyTo({ center: [lng, lat], zoom: 15 });
                    });
                }
            };
        }
    </script>
    @endpush
@endonce
