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
            ->assertSee('Mode Baca')
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

    public function test_statistik_sampah_rejects_invalid_or_duplicate_period_data(): void
    {
        $this->makeStatistik();
        $countBefore = StatistikSampah::count();

        $this->actingAs($this->makeUser('bidang-sampah-lb3'))
            ->post(route('admin.resources.store', 'statistik-sampah'), [
                'tanggal' => '2026-08-20',
                'volume_ton' => '-1.25',
                'periode' => 'harian',
            ])
            ->assertSessionHasErrors(['tanggal', 'volume_ton']);

        $this->assertSame($countBefore, StatistikSampah::count());

        $this->actingAs($this->makeUser('bidang-sampah-lb3'))
            ->post(route('admin.resources.store', 'statistik-sampah'), [
                'tanggal' => '2026-08-21',
                'volume_ton' => '1.234',
                'periode' => 'bukan-periode',
            ])
            ->assertSessionHasErrors(['volume_ton', 'periode']);

        $this->assertSame($countBefore, StatistikSampah::count());
    }

    public function test_administrator_utama_cannot_see_tambah_button_or_create_operational_resources(): void
    {
        $admin = $this->makeUser('admin');
        $operationalSlugs = ['statistik-sampah', 'sosialisasi', 'data-tanam-pohon', 'data-tpu', 'pelanggaran'];

        foreach ($operationalSlugs as $slug) {
            $this->actingAs($admin)
                ->get(route('admin.resources.index', $slug))
                ->assertOk()
                ->assertDontSee(route('admin.resources.create', $slug));

            $this->actingAs($admin)
                ->get(route('admin.resources.create', $slug))
                ->assertForbidden();

            $this->actingAs($admin)
                ->post(route('admin.resources.store', $slug), [])
                ->assertForbidden();
        }
    }

    public function test_bidang_admin_can_see_tambah_button_and_access_create(): void
    {
        $bidangSampah = $this->makeUser('bidang-sampah-lb3');
        $this->actingAs($bidangSampah)
            ->get(route('admin.resources.index', 'statistik-sampah'))
            ->assertOk()
            ->assertSee(route('admin.resources.create', 'statistik-sampah'));

        $this->actingAs($bidangSampah)
            ->get(route('admin.resources.create', 'statistik-sampah'))
            ->assertOk();

        $bidangTata = $this->makeUser('bidang-tata-penataan');
        $this->actingAs($bidangTata)
            ->get(route('admin.resources.index', 'sosialisasi'))
            ->assertOk()
            ->assertSee(route('admin.resources.create', 'sosialisasi'));

        $this->actingAs($bidangTata)
            ->get(route('admin.resources.create', 'sosialisasi'))
            ->assertOk();

        $this->actingAs($bidangTata)
            ->get(route('admin.resources.index', 'pelanggaran'))
            ->assertOk()
            ->assertSee(route('admin.resources.create', 'pelanggaran'));

        $this->actingAs($bidangTata)
            ->get(route('admin.resources.create', 'pelanggaran'))
            ->assertOk();

        $bidangRth = $this->makeUser('bidang-rth');
        $this->actingAs($bidangRth)
            ->get(route('admin.resources.index', 'data-tanam-pohon'))
            ->assertOk()
            ->assertSee(route('admin.resources.create', 'data-tanam-pohon'));

        $this->actingAs($bidangRth)
            ->get(route('admin.resources.create', 'data-tanam-pohon'))
            ->assertOk();

        $this->actingAs($bidangRth)
            ->get(route('admin.resources.index', 'data-tpu'))
            ->assertOk()
            ->assertSee(route('admin.resources.create', 'data-tpu'));

        $this->actingAs($bidangRth)
            ->get(route('admin.resources.create', 'data-tpu'))
            ->assertOk();
    }

    public function test_administrator_utama_peta_is_read_only_and_write_is_forbidden(): void
    {
        $admin = $this->makeUser('admin');

        // Admin Utama can view peta page and layers
        $this->actingAs($admin)
            ->get(route('admin.peta.index'))
            ->assertOk()
            ->assertSee('Panel Terkunci (Mode Baca)');

        $this->actingAs($admin)
            ->get(route('admin.peta.layers'))
            ->assertOk();

        // Mutating operations are forbidden for Admin Utama
        $this->actingAs($admin)
            ->post(route('admin.peta.layers.store'), [
                'bidang' => 'sampah-lb3',
                'nama_layer' => 'Layer Uji',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.peta.layers.bulk-visibility'), [
                'visible' => true,
            ])
            ->assertForbidden();
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
