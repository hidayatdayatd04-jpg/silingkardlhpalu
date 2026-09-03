<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithAdminNotifications;
use Tests\TestCase;

class AdminPreviewFileTest extends TestCase
{
    use DatabaseTransactions;
    use InteractsWithAdminNotifications;

    public function test_user_has_admin_access_method_works(): void
    {
        $admin = $this->makeUser('admin');
        $this->assertTrue($admin->hasAdminAccess());

        $rth = $this->makeUser('bidang-rth');
        $this->assertTrue($rth->hasAdminAccess());
    }

    public function test_admin_can_preview_file_without_500_error(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('pengaduan-pengendalian/test.webp', 'dummy content');

        $admin = $this->makeUser('admin');

        $response = $this->actingAs($admin)
            ->get('/pengaduan-pengendalian/test.webp');

        $this->assertNotEquals(500, $response->getStatusCode(), 'Preview file should not trigger 500 error');
        $response->assertOk();
    }
}
