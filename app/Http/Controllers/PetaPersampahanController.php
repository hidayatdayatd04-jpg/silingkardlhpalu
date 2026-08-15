<?php

namespace App\Http\Controllers;

use App\Models\GisDataLayer;
use App\Models\GpsVehicleCache;
use App\Models\StatistikSampah;

class PetaPersampahanController extends Controller
{
    public function index()
    {
        // Ambil SELURUH layer bidang sampah-lb3 (tanpa filter public di sini),
        // karena kita perlu mengetahui induk/root untuk menerapkan aturan visibilitas.
        $allLayers = GisDataLayer::where('bidang', 'sampah-lb3')
            ->orderBy('z_index')
            ->orderBy('created_at')
            ->get();

        $byId = $allLayers->keyBy('id');

        // Tentukan layer akar (root) untuk tiap layer.
        $rootOf = [];
        foreach ($allLayers as $l) {
            $root = $l;
            $pid = $l->parent_id;
            $guard = 0;
            while ($pid && isset($byId[$pid]) && $guard++ < 20) {
                $root = $byId[$pid];
                $pid = $root->parent_id;
            }
            $rootOf[$l->id] = $root;
        }

        // Aturan visibilitas publik: sebuah layer tampil di publik hanya jika
        // dirinya visible & public DAN layer utamanya (root) juga visible & public.
        // Jika layer utama disembunyikan dari publik, maka seluruh sub-layer
        // (jalur per kelurahan) ikut disembunyikan beserta datanya di peta.
        $visibleLayers = $allLayers->filter(function ($l) use ($rootOf) {
            $root = $rootOf[$l->id] ?? $l;
            return $l->is_visible && $l->is_public
                && $root->is_visible && $root->is_public;
        });

        // Flat array (untuk inisialisasi peta di JS) — hanya layer yang lolos aturan visibilitas.
        $layers = $visibleLayers->map(fn ($layer) => [
            'id' => $layer->id,
            'nama_layer' => $layer->nama_layer,
            'deskripsi' => $layer->deskripsi,
            'jenis_geometri' => $layer->jenis_geometri,
            'metadata' => $layer->metadata ?? ['color' => GisDataLayer::defaultColor($layer->bidang)],
            'geojson' => $layer->toGeoJson(),
        ])->values()->all();

        // Kategorisasi tipe kendaraan DIAMBIL DARI DATA: setiap layer akar (root)
        // adalah satu tipe kendaraan (LABEL diambil dari nama layer utama, mis.
        // "Jalur Armada Pickup"), dan sub-layer-nya adalah jalur per kelurahan
        // (LABEL kelurahan diambil dari nama sub-layer). Key/warna/icon hanya
        // untuk styling, sedangkan teks yang tampil di publik murni dari data.
        $typeInfo = function (string $nama): array {
            $n = strtolower($nama);
            if (str_contains($n, 'pick')) {
                return ['key' => 'pickup', 'label' => $nama, 'color' => '#ef4444'];
            }
            if (str_contains($n, 'kaisar')) {
                return ['key' => 'kaisar', 'label' => $nama, 'color' => '#3b82f6'];
            }
            if (str_contains($n, 'r6')) {
                return ['key' => 'r6', 'label' => $nama, 'color' => '#22c55e'];
            }
            $key = \Illuminate\Support\Str::slug($n) ?: 'tipe';
            return ['key' => $key, 'label' => $nama, 'color' => GisDataLayer::defaultColor('sampah-lb3')];
        };

        $vehicleTypes = [];
        foreach ($visibleLayers as $l) {
            // Hanya layer akar yang menjadi entri tipe kendaraan.
            if (!is_null($l->parent_id)) {
                continue;
            }
            $info = $typeInfo($l->nama_layer);
            $key = $info['key'];
            if (isset($vehicleTypes[$key])) {
                continue;
            }

            // Layer milik tipe ini = root itu sendiri + seluruh sub-layer langsung.
            $typeLayers = $visibleLayers->filter(function ($c) use ($l) {
                return $c->id === $l->id || $c->parent_id === $l->id;
            });

            $kelurahans = $typeLayers
                ->whereNotNull('parent_id')
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'nama' => $c->nama_layer,
                    'count' => count($c->geojson_features ?? []),
                ])
                ->values()
                ->all();

            $total = $typeLayers->sum(fn ($c) => count($c->geojson_features ?? []));

            $vehicleTypes[$key] = [
                'key' => $key,
                'label' => $info['label'],
                'color' => $info['color'],
                'total' => $total,
                'kelurahans' => $kelurahans,
                'layerIds' => $typeLayers->pluck('id')->all(),
            ];
        }

        // Tipe default: tipe pertama yang punya data jalur.
        $defaultType = null;
        foreach ($vehicleTypes as $key => $vt) {
            if ($vt['total'] > 0) {
                $defaultType = $key;
                break;
            }
        }
        if ($defaultType === null) {
            $defaultType = array_key_first($vehicleTypes) ?? 'pickup';
        }

        $stats = StatistikSampah::query()->orderBy('tanggal')->limit(12)->get();
        $armada = GpsVehicleCache::all();

        return view('public.peta-persampahan', [
            'layers' => $layers,
            'vehicleTypes' => $vehicleTypes,
            'defaultType' => $defaultType,
            'armada' => $armada,
            'chartLabels' => $stats->map(fn ($r) => $r->tanggal->format('d M Y'))->all(),
            'chartValues' => $stats->pluck('volume_ton')->map(fn ($v) => (float) $v)->all(),
        ]);
    }

    public function layers()
    {
        $allLayers = GisDataLayer::where('bidang', 'sampah-lb3')
            ->orderBy('z_index')
            ->orderBy('created_at')
            ->get();

        $byId = $allLayers->keyBy('id');
        $rootOf = [];
        foreach ($allLayers as $l) {
            $root = $l;
            $pid = $l->parent_id;
            $guard = 0;
            while ($pid && isset($byId[$pid]) && $guard++ < 20) {
                $root = $byId[$pid];
                $pid = $root->parent_id;
            }
            $rootOf[$l->id] = $root;
        }

        // Sama seperti index(): sembunyikan layer beserta sub-layer-nya jika
        // layer utama (root) tidak visible/public.
        $layers = $allLayers->filter(function ($l) use ($rootOf) {
            $root = $rootOf[$l->id] ?? $l;
            return $l->is_visible && $l->is_public
                && $root->is_visible && $root->is_public;
        })->map(fn ($layer) => [
            'id' => $layer->id,
            'nama_layer' => $layer->nama_layer,
            'deskripsi' => $layer->deskripsi,
            'jenis_geometri' => $layer->jenis_geometri,
            'metadata' => $layer->metadata ?? ['color' => GisDataLayer::defaultColor($layer->bidang)],
            'geojson' => $layer->toGeoJson(),
        ])->values()->all();

        return response()->json($layers);
    }
}
