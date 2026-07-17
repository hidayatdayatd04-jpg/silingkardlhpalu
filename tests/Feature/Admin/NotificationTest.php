<?php

namespace Tests\Feature\Admin;

use App\Enums\AdminRole;
use App\Models\PermohonanRekomendasi;
use App\Support\AdminNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase, CreatesAdminUsers;

    public function test_notifier_mengirim_ke_user_grup(): void
    {
        $rth = $this->bidangUser(AdminRole::BIDANG_RTH);
        $sampah = $this->bidangUser(AdminRole::BIDANG_SAMPAH_LB3);

        AdminNotifier::toGroup('rth', [
            'title' => 'Uji', 'message' => 'Pesan uji', 'module' => 'test',
        ]);

        $this->assertSame(1, $rth->fresh()->unreadNotifications()->count());
        $this->assertSame(0, $sampah->fresh()->unreadNotifications()->count());
    }

    public function test_mark_as_read_mengisi_read_at(): void
    {
        $user = $this->bidangUser(AdminRole::BIDANG_RTH);
        AdminNotifier::toGroup('rth', ['title' => 'A', 'message' => 'B']);

        $notif = $user->fresh()->notifications()->first();
        $this->assertNull($notif->read_at);

        $this->actingAs($user->fresh())
            ->post("/admin/notifications/{$notif->id}/read")
            ->assertOk();

        $this->assertNotNull($user->fresh()->notifications()->first()->read_at);
    }

    public function test_mark_all_read(): void
    {
        $user = $this->bidangUser(AdminRole::BIDANG_RTH);
        AdminNotifier::toGroup('rth', ['title' => 'A', 'message' => 'B']);
        AdminNotifier::toGroup('rth', ['title' => 'C', 'message' => 'D']);

        $this->assertSame(2, $user->fresh()->unreadNotifications()->count());

        $this->actingAs($user->fresh())->post('/admin/notifications/read-all')->assertOk();

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_poll_endpoint_mengembalikan_json(): void
    {
        $user = $this->bidangUser(AdminRole::BIDANG_RTH);
        AdminNotifier::toGroup('rth', ['title' => 'Halo', 'message' => 'Dunia']);

        $this->actingAs($user->fresh())
            ->getJson('/admin/notifications/poll')
            ->assertOk()
            ->assertJsonStructure(['unread', 'notifications' => [['id', 'title', 'message', 'read']]])
            ->assertJsonPath('unread', 1);
    }

    public function test_membuat_permohonan_memicu_notifikasi_ke_rth(): void
    {
        $rth = $this->bidangUser(AdminRole::BIDANG_RTH);

        PermohonanRekomendasi::create([
            'nama_perusahaan' => 'PT Uji',
            'nama_pemilik' => 'Budi',
            'npwp' => '123',
            'jenis_usaha' => 'Manufaktur',
            'alamat_lengkap' => 'Jl. Test',
            'nomor_telepon' => '0812',
            'email' => 'uji@example.com',
            'jenis_pengajuan' => 'Rekomendasi',
            'surat_permohonan' => 'surat-uji.pdf',
            'status' => 'Belum Ditindaklanjuti',
        ]);

        $this->assertGreaterThanOrEqual(1, $rth->fresh()->unreadNotifications()->count());
    }
}
