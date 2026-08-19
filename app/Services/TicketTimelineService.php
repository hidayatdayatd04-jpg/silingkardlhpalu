<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class TicketTimelineService
{
    private const STATUS_LABELS = [
        'Belum Ditinjau' => ['label' => 'Belum Ditinjau', 'color' => 'gray'],
        'Ditinjau' => ['label' => 'Ditinjau', 'color' => 'amber'],
        'Selesai' => ['label' => 'Selesai', 'color' => 'emerald'],
        'Ditolak' => ['label' => 'Ditolak', 'color' => 'red'],
        'Belum Ditindaklanjuti' => ['label' => 'Belum Ditindaklanjuti', 'color' => 'warning'],
        'Ditindaklanjuti' => ['label' => 'Ditindaklanjuti', 'color' => 'success'],
        'Disetujui' => ['label' => 'Disetujui', 'color' => 'emerald'],
        'Diajukan' => ['label' => 'Diajukan', 'color' => 'gray'],
        'Diverifikasi' => ['label' => 'Diverifikasi', 'color' => 'amber'],
        'menunggu' => ['label' => 'Menunggu', 'color' => 'gray'],
        'ditugaskan' => ['label' => 'Ditugaskan', 'color' => 'amber'],
        'selesai' => ['label' => 'Selesai', 'color' => 'emerald'],
        'diberikan' => ['label' => 'Diberikan', 'color' => 'emerald'],
        'banding' => ['label' => 'Banding', 'color' => 'amber'],
        'belum' => ['label' => 'Belum', 'color' => 'gray'],
        'proses' => ['label' => 'Proses', 'color' => 'amber'],
        'DIPROSES' => ['label' => 'Sedang Diproses', 'color' => 'amber'],
        'DIPERBARUI' => ['label' => 'Diperbarui', 'color' => 'sky'],
    ];

    /**
     * Get timeline of status changes for a ticket, extracted from activity_logs.
     */
    public static function forTicket(Model $ticket): array
    {
        $logs = ActivityLog::query()
            ->where('auditable_type', $ticket::class)
            ->where('auditable_id', $ticket->getKey())
            ->where('event', 'updated')
            ->orderBy('created_at')
            ->get();

        $timeline = [];

        // Add creation event
        $statusValue = $ticket->status instanceof \BackedEnum ? $ticket->status->value : ($ticket->status ?? 'Dibuat');
        $timeline[] = [
            'status' => $statusValue,
            'label' => static::statusLabel($statusValue),
            'color' => 'brand',
            'time' => $ticket->created_at,
        ];

        foreach ($logs as $log) {
            $properties = $log->properties ?? [];
            $new = $properties['new'] ?? [];

            if (! isset($new['status'])) {
                continue;
            }

            $newStatus = $new['status'];

            // Skip if same as last entry
            if (! empty($timeline) && $timeline[array_key_last($timeline)]['status'] === $newStatus) {
                continue;
            }

            $meta = static::STATUS_LABELS[$newStatus] ?? ['label' => $newStatus, 'color' => 'gray'];

            $timeline[] = [
                'status' => $newStatus,
                'label' => $meta['label'],
                'color' => $meta['color'],
                'time' => $log->created_at,
            ];
        }

        return $timeline;
    }

    private static function statusLabel(string|\BackedEnum $status): string
    {
        $key = $status instanceof \BackedEnum ? $status->value : $status;
        return (static::STATUS_LABELS[$key] ?? null)['label'] ?? $key;
    }
}
