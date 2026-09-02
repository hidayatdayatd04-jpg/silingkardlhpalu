<?php

namespace App\Http\Controllers;

use App\Enums\StatistikSampahPeriode;
use App\Models\StatistikSampah;

class StatistikSampahPublicController extends Controller
{
    public function index()
    {
        // Semua catatan statistik dikirim ke publik dan dipisah per periode.
        $stats = StatistikSampah::query()
            ->select(['tanggal', 'volume_ton', 'periode'])
            ->orderBy('tanggal')
            ->orderBy('created_at')
            ->get();

        $chartSeries = collect(StatistikSampahPeriode::cases())
            ->mapWithKeys(function (StatistikSampahPeriode $periode) use ($stats): array {
                $records = $stats
                    ->filter(fn (StatistikSampah $stat) => $stat->periode === $periode)
                    ->values()
                    ->map(fn (StatistikSampah $stat): array => [
                        'date' => $stat->tanggal->toDateString(),
                        'label' => $stat->tanggal->translatedFormat('d M Y'),
                        'value' => (float) $stat->volume_ton,
                    ])
                    ->all();

                return [$periode->value => $records];
            })
            ->all();

        $chartPeriodLabels = collect(StatistikSampahPeriode::cases())
            ->mapWithKeys(fn (StatistikSampahPeriode $periode) => [$periode->value => $periode->label()])
            ->all();

        $chartDefaultPeriod = collect(StatistikSampahPeriode::cases())
            ->map(fn (StatistikSampahPeriode $periode) => $periode->value)
            ->first(fn (string $periode) => ! empty($chartSeries[$periode]))
            ?? StatistikSampahPeriode::HARIAN->value;

        return view('public.statistik-timbulan-sampah', [
            'chartSeries' => $chartSeries,
            'chartPeriodLabels' => $chartPeriodLabels,
            'chartDefaultPeriod' => $chartDefaultPeriod,
        ]);
    }
}
