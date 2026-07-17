<?php

namespace Tests\Feature;

use App\Models\Laporan;
use App\Models\TicketFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function feedback_route_get_returns_405(): void
    {
        $this->get('/feedback/test-ticket')->assertStatus(405);
    }

    /** @test */
    public function feedback_post_with_invalid_ticket_returns_error(): void
    {
        $this->post('/feedback/INVALID-TICKET', [
            'rating' => 5,
            'komentar' => 'Test feedback',
        ])->assertRedirect(); // Redirects back with error
    }

    /** @test */
    public function feedback_post_with_valid_ticket_works(): void
    {
        $laporan = Laporan::create([
            'nomor_hp' => '081234567890',
            'kategori' => 'sampah',
            'deskripsi' => 'Test pengaduan',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        $this->post("/feedback/{$laporan->nomor_tiket}", [
            'rating' => 5,
            'komentar' => 'Pelayanan sangat baik',
        ])->assertRedirect();

        $this->assertDatabaseHas('ticket_feedbacks', [
            'feedbackable_type' => Laporan::class,
            'feedbackable_id' => $laporan->id,
            'rating' => 5,
            'komentar' => 'Pelayanan sangat baik',
        ]);
    }

    /** @test */
    public function feedback_validates_rating_required(): void
    {
        $laporan = Laporan::create([
            'nomor_hp' => '081234567890',
            'kategori' => 'sampah',
            'deskripsi' => 'Test pengaduan',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        $this->post("/feedback/{$laporan->nomor_tiket}", [
            'rating' => '',
            'komentar' => 'Test',
        ])->assertSessionHasErrors(['rating']);
    }

    /** @test */
    public function feedback_validates_rating_range(): void
    {
        $laporan = Laporan::create([
            'nomor_hp' => '081234567890',
            'kategori' => 'sampah',
            'deskripsi' => 'Test pengaduan',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        $this->post("/feedback/{$laporan->nomor_tiket}", [
            'rating' => 6,
            'komentar' => 'Test',
        ])->assertSessionHasErrors(['rating']);

        $this->post("/feedback/{$laporan->nomor_tiket}", [
            'rating' => 0,
            'komentar' => 'Test',
        ])->assertSessionHasErrors(['rating']);
    }

    /** @test */
    public function feedback_komentar_is_optional(): void
    {
        $laporan = Laporan::create([
            'nomor_hp' => '081234567890',
            'kategori' => 'sampah',
            'deskripsi' => 'Test pengaduan',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        $this->post("/feedback/{$laporan->nomor_tiket}", [
            'rating' => 4,
        ])->assertRedirect();

        $this->assertDatabaseHas('ticket_feedbacks', [
            'feedbackable_type' => Laporan::class,
            'feedbackable_id' => $laporan->id,
            'rating' => 4,
        ]);
    }

    /** @test */
    public function feedback_prevents_duplicate(): void
    {
        $laporan = Laporan::create([
            'nomor_hp' => '081234567890',
            'kategori' => 'sampah',
            'deskripsi' => 'Test pengaduan',
            'latitude' => -0.9,
            'longitude' => 119.87,
            'status' => 'Belum Ditinjau',
        ]);

        // First feedback
        $this->post("/feedback/{$laporan->nomor_tiket}", [
            'rating' => 5,
            'komentar' => 'First feedback',
        ])->assertRedirect();

        // Second feedback should fail
        $this->post("/feedback/{$laporan->nomor_tiket}", [
            'rating' => 3,
            'komentar' => 'Second feedback',
        ])->assertSessionHasErrors();

        // Only one feedback should exist
        $this->assertCount(1, TicketFeedback::where('feedbackable_id', $laporan->id)
            ->where('feedbackable_type', Laporan::class)
            ->get());
    }
}
