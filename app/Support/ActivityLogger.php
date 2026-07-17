<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Pencatat aktivitas terpusat (audit log).
 *
 * Semua penulisan ke tabel activity_logs melewati kelas ini agar konsisten
 * (siapa, kapan, IP, UA) dan tahan-error (kegagalan log tidak boleh menggagalkan
 * aksi bisnis utama).
 */
class ActivityLogger
{
    /**
     * Guard anti-recursion — mencegah pencatatan model ActivityLog itu sendiri
     * memicu pencatatan berikutnya.
     */
    protected static bool $enabled = true;

    /**
     * Kolom sensitif yang tidak boleh masuk ke diff old/new.
     */
    public const HIDDEN = ['password', 'remember_token', 'additional_access', 'email_verified_at'];

    public static function disable(): void
    {
        static::$enabled = false;
    }

    public static function enable(): void
    {
        static::$enabled = true;
    }

    /**
     * Catat satu baris aktivitas.
     *
     * @param  string       $event    created|updated|deleted|login|logout|exported|imported|backup|restore|...
     * @param  string       $subject  label subjek (mis. "Pengaduan #123")
     * @param  string       $module   slug resource / 'auth' / 'system'
     * @param  array|null   $old      nilai sebelum
     * @param  array|null   $new      nilai sesudah
     * @param  Model|null   $auditable model terkait (opsional)
     */
    public static function log(
        string $event,
        string $subject,
        string $module,
        ?array $old = null,
        ?array $new = null,
        ?Model $auditable = null,
    ): ?ActivityLog {
        if (! static::$enabled) {
            return null;
        }

        try {
            $user = auth()->user();
            $request = request();

            $properties = [];
            if (! empty($old)) {
                $properties['old'] = static::clean($old);
            }
            if (! empty($new)) {
                $properties['new'] = static::clean($new);
            }

            return ActivityLog::create([
                'user_id'        => $user?->getKey(),
                'user_name'      => $user?->name ?? 'System',
                'event'          => $event,
                'auditable_type' => $auditable ? $auditable::class : null,
                'auditable_id'   => $auditable?->getKey(),
                'subject_label'  => mb_substr($subject, 0, 255),
                'module'         => $module,
                'properties'     => $properties ?: null,
                'ip_address'     => $request?->ip(),
                'user_agent'     => $request ? mb_substr((string) $request->userAgent(), 0, 500) : null,
            ]);
        } catch (Throwable $e) {
            // Jangan pernah menggagalkan aksi utama gara-gara audit log.
            report($e);

            return null;
        }
    }

    /**
     * Catat perubahan model (dipakai trait LogsActivity).
     */
    public static function logModel(string $event, Model $model, ?array $old = null, ?array $new = null): ?ActivityLog
    {
        if ($model instanceof ActivityLog) {
            return null; // anti-recursion
        }

        return static::log(
            $event,
            static::subjectFor($model),
            static::moduleFor($model),
            $old,
            $new,
            $model,
        );
    }

    /**
     * Bersihkan array: buang kolom sensitif, ubah nilai jadi terbaca.
     */
    protected static function clean(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if (in_array($key, static::HIDDEN, true)) {
                continue;
            }
            $out[$key] = static::readable($value);
        }

        return $out;
    }

    protected static function readable(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            return method_exists($value, 'label') ? $value->label() : $value->value;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }
        if (is_array($value)) {
            return $value;
        }

        return $value;
    }

    /**
     * Tebak modul (slug resource) dari class model via AdminRegistry.
     */
    public static function moduleFor(Model $model): string
    {
        foreach (\App\Support\Admin\AdminRegistry::flat() as $slug => $meta) {
            if (($meta['model'] ?? null) === $model::class) {
                return $slug;
            }
        }

        return str(class_basename($model))->kebab()->toString();
    }

    /**
     * Buat label subjek terbaca dari model.
     */
    public static function subjectFor(Model $model): string
    {
        $label = null;
        foreach (\App\Support\Admin\AdminRegistry::flat() as $meta) {
            if (($meta['model'] ?? null) === $model::class) {
                $label = $meta['label'];
                break;
            }
        }
        $label ??= class_basename($model);

        foreach (['judul', 'nama', 'name', 'nama_perusahaan', 'nama_usaha', 'nama_pelapor', 'nama_pemohon', 'nomor_tiket', 'nomor_pengajuan', 'nomor_registrasi', 'tema', 'email'] as $field) {
            if (filled($model->{$field} ?? null)) {
                return $label.' — '.$model->{$field};
            }
        }

        return $label.' #'.$model->getKey();
    }
}
