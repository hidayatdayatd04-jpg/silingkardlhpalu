<?php

namespace Tests\Feature;

use App\Models\StatistikSampah;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\InteractsWithAdminNotifications;
use Tests\TestCase;

class AdminResourceReadOnlyTest extends TestCase
{
    use DatabaseTransactions;
    use InteractsWithAdminNotifications;

    public function test_administrator_utama_opens_non_content_edit_as_read_only_and_put_is_forbidden(): void
    {
        $record = $this->makeStatistik();

        $this->actingAs($this->makeUser('admin'))
            ->get(route('admin.resources.edit', ['statistik-sampah', $record]))
            ->assertOk()
            ->assertSee('Mode baca-saja')
            ->assertDontSee('Perbarui Data');

        $this->put(route('admin.resources.update', ['statistik-sampah', $record]), [
            'tanggal' => '2026-08-27',
            'volume_ton' => '99.50',
            'periode' => 'mingguan',
        ])->assertForbidden();

        $this->assertSame(12.5, (float) $record->fresh()->volume_ton);
    }

    public function test_bidang_keeps_update_access_to_its_resource(): void
    {
        $record = $this->makeStatistik();

        $this->actingAs($this->makeUser('bidang-sampah-lb3'))
            ->put(route('admin.resources.update', ['statistik-sampah', $record]), [
                'tanggal' => '2026-08-27',
                'volume_ton' => '99.50',
                'periode' => 'mingguan',
            ])
            ->assertRedirect();

        $record->refresh();
        $this->assertSame('mingguan', $record->periode->value);
        $this->assertSame(99.5, (float) $record->volume_ton);
    }

    private function makeStatistik(): StatistikSampah
    {
        return StatistikSampah::create([
            'tanggal' => '2026-08-20',
            'volume_ton' => 12.5,
            'periode' => 'harian',
        ]);
    }
}
