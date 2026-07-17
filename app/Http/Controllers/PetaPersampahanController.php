<?php

namespace App\Http\Controllers;

use App\Models\GisDataLayer;
use App\Models\GpsVehicleCache;
use App\Models\JadwalArmada;
use App\Models\StatistikSampah;

class PetaPersampahanController extends Controller
{
    public function index()
    {
        $layers = GisDataLayer::where('bidang', 'sampah-lb3')
            ->visible()
            ->public()
            ->orderBy('z_index')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($layer) => [
                'id' => $layer->id,
                'nama_layer' => $layer->nama_layer,
                'deskripsi' => $layer->deskripsi,
                'jenis_geometri' => $layer->jenis_geometri,
                'metadata' => $layer->metadata ?? ['color' => GisDataLayer::defaultColor($layer->bidang)],
                'geojson' => $layer->toGeoJson(),
            ]);

        $jadwal = JadwalArmada::orderBy('hari')->orderBy('jam')->get();
        $stats = StatistikSampah::query()->orderBy('tanggal')->limit(12)->get();
        $armada = GpsVehicleCache::all();

        return view('public.peta-persampahan', [
            'layers' => $layers,
            'jadwal' => $jadwal,
            'armada' => $armada,
            'chartLabels' => $stats->map(fn ($r) => $r->tanggal->format('d M Y'))->all(),
            'chartValues' => $stats->pluck('volume_ton')->map(fn ($v) => (float) $v)->all(),
        ]);
    }

    public function layers()
    {
        $layers = GisDataLayer::where('bidang', 'sampah-lb3')
            ->visible()
            ->public()
            ->orderBy('z_index')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($layer) => [
                'id' => $layer->id,
                'nama_layer' => $layer->nama_layer,
                'deskripsi' => $layer->deskripsi,
                'jenis_geometri' => $layer->jenis_geometri,
                'metadata' => $layer->metadata ?? ['color' => GisDataLayer::defaultColor($layer->bidang)],
                'geojson' => $layer->toGeoJson(),
            ]);

        return response()->json($layers);
    }
}
