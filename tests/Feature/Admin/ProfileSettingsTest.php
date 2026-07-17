<?php

namespace Tests\Feature\Admin;

use App\Enums\AdminRole;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase, CreatesAdminUsers;

    public function test_update_profil_menyimpan_perubahan(): void
    {
        $user = $this->bidangUser(AdminRole::BIDANG_RTH);

        $this->actingAs($user)->put('/admin/profile', [
            'name' => 'Nama Baru',
            'username' => 'username_baru',
            'email' => 'baru@example.com',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru',
            'username' => 'username_baru',
        ]);
    }

    public function test_upload_foto_profil_tersimpan(): void
    {
        Storage::fake('public');
        $user = $this->bidangUser(AdminRole::BIDANG_RTH);

        $this->actingAs($user)->put('/admin/profile', [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'photo' => UploadedFile::fake()->image('avatar.jpg'),
        ])->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->photo_path);
        Storage::disk('public')->assertExists($user->photo_path);
    }

    public function test_ganti_password_salah_current_ditolak(): void
    {
        $user = $this->bidangUser(AdminRole::BIDANG_RTH);
        $user->password = Hash::make('rahasia-lama');
        $user->save();

        $this->actingAs($user)->put('/admin/profile/password', [
            'current_password' => 'password-salah',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ])->assertSessionHasErrors('current_password');
    }

    public function test_ganti_password_benar_berhasil(): void
    {
        $user = $this->bidangUser(AdminRole::BIDANG_RTH);
        $user->password = Hash::make('rahasia-lama');
        $user->save();

        $this->actingAs($user)->put('/admin/profile/password', [
            'current_password' => 'rahasia-lama',
            'password' => 'password-baru-123',
            'password_confirmation' => 'password-baru-123',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('password-baru-123', $user->fresh()->password));
    }

    public function test_settings_menyimpan_preferensi_user(): void
    {
        $user = $this->bidangUser(AdminRole::BIDANG_RTH);

        $this->actingAs($user)->put('/admin/settings', [
            'per_page' => 25,
            'locale' => 'en',
        ])->assertRedirect();

        $this->assertSame(25, $user->fresh()->pref('per_page'));
        $this->assertSame('en', $user->fresh()->pref('locale'));
    }

    public function test_settings_global_hanya_superadmin(): void
    {
        $super = $this->superadmin();

        $this->actingAs($super)->put('/admin/settings', [
            'per_page' => 15,
            'locale' => 'id',
            'app_name' => 'DLH Test App',
            'contact_email' => 'kontak@dlh.test',
        ])->assertRedirect();

        $this->assertSame('DLH Test App', Setting::get('app_name'));
        $this->assertSame('kontak@dlh.test', Setting::get('contact_email'));
    }

    public function test_help_page_render(): void
    {
        $user = $this->bidangUser(AdminRole::BIDANG_RTH);
        $this->actingAs($user)->get('/admin/help')->assertOk()->assertSee('Pusat Bantuan');
    }
}
