<?php

namespace Tests\Feature;

use App\Models\StatistikSampah;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PetaPersampahanStatisticsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_chart_receives_every_statistic_and_keeps_periods_separate(): void
    {
        foreach (range(1, 13) as $day) {
            StatistikSampah::create([
                'tanggal' => sprintf('2026-08-%02d', $day),
                'volume_ton' => $day + 0.5,
                'periode' => 'harian',
            ]);
        }

        StatistikSampah::create([
            'tanggal' => '2026-08-31',
            'volume_ton' => 180,
            'periode' => 'mingguan',
        ]);

        $this->get('/peta-persampahan')
            ->assertOk()
            ->assertViewHas('chartSeries', function (array $series): bool {
                $harian = $series['harian'] ?? [];
                $mingguan = $series['mingguan'] ?? [];

                return collect($harian)->contains(fn (array $record) => $record['date'] === '2026-08-13' && $record['value'] === 13.5)
                    && collect($mingguan)->contains(fn (array $record) => $record['date'] === '2026-08-31' && $record['value'] === 180.0);
            });
    }
}
