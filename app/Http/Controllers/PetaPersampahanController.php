<?php

namespace App\Http\Controllers;

use App\Models\GisDataLayer;

class PetaPersampahanController extends Controller
{
    public function index()
    {
        // Ambil SELURUH layer bidang sampah-lb3
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

        // Aturan visibilitas publik: sebuah layer tampil di publik jika
        // dirinya visible & public DAN layer utamanya (root) juga visible & public.
        // Baik layer jalur-angkut maupun TPA/fasilitas digabung dalam satu peta terpadu.
        $visibleLayers = $allLayers->filter(function ($l) use ($rootOf) {
            $root = $rootOf[$l->id] ?? $l;
            return $l->is_visible
                && ($l->is_public || $root->is_public)
                && $root->is_visible;
        });

        // Flat array (untuk inisialisasi peta di JS)
        $layers = $visibleLayers->map(fn ($layer) => [
            'id' => $layer->id,
            'nama_layer' => $layer->nama_layer,
            'deskripsi' => $layer->deskripsi,
            'jenis_geometri' => $layer->jenis_geometri,
            'metadata' => $layer->metadata ?? ['color' => GisDataLayer::defaultColor($layer->bidang)],
            'geojson' => $layer->toGeoJson(),
            'tampilkan_di' => $layer->tampilkan_di ?? 'jalur-angkut',
            'is_facility' => ($layer->tampilkan_di === 'tpa' || ($rootOf[$layer->id]->tampilkan_di ?? null) === 'tpa'),
        ])->values()->all();

        // Data fasilitas TPA & TPS3R untuk toggle dan informasi di peta
        $facilityLayers = $visibleLayers->filter(function ($l) use ($rootOf) {
            $root = $rootOf[$l->id] ?? $l;
            return $root->tampilkan_di === 'tpa';
        });
        $totalFasilitas = $facilityLayers->sum(fn ($l) => count($l->geojson_features ?? []));

        // Kategorisasi tipe kendaraan (hanya untuk root layer armada, bukan fasilitas TPA)
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
            if (!is_null($l->parent_id)) {
                continue;
            }
            if (!$l->show_in_filter) {
                continue;
            }
            // Fasilitas TPA bukan kategori kendaraan
            if ($l->tampilkan_di === 'tpa') {
                continue;
            }

            $info = $typeInfo($l->nama_layer);
            $key = $info['key'];
            if (isset($vehicleTypes[$key])) {
                continue;
            }

            $typeLayers = $visibleLayers->filter(function ($c) use ($l) {
                if ($c->id === $l->id) {
                    return true;
                }
                return $c->parent_id === $l->id && $c->show_in_filter;
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
                'color' => ($l->metadata['color'] ?? null) ?: $info['color'],
                'total' => $total,
                'kelurahans' => $kelurahans,
                'layerIds' => $typeLayers->pluck('id')->all(),
            ];
        }

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

        return view('public.peta-persampahan', [
            'layers' => $layers,
            'vehicleTypes' => $vehicleTypes,
            'defaultType' => $defaultType,
            'totalFasilitas' => $totalFasilitas,
            'facilityLayerIds' => $facilityLayers->pluck('id')->values()->all(),
        ]);
    }

    public function jalurAngkut()
    {
        return redirect()->route('peta-persampahan');
    }

    public function tpa()
    {
        return redirect()->route('peta-persampahan');
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

        $layers = $allLayers->filter(function ($l) use ($rootOf) {
            $root = $rootOf[$l->id] ?? $l;
            return $l->is_visible
                && ($l->is_public || $root->is_public)
                && $root->is_visible;
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

