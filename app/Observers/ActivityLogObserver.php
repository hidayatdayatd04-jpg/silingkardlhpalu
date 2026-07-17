<?php

namespace App\Observers;

use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

/**
 * Observer generik pencatat audit log untuk model domain.
 *
 * Didaftarkan di AppServiceProvider untuk daftar model penting.
 * Menangkap created / updated / deleted dan menulis old→new ke activity_logs.
 */
class ActivityLogObserver
{
    public function created(Model $model): void
    {
        ActivityLogger::logModel('created', $model, null, $this->attributes($model));
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        // Buang timestamp otomatis agar update tanpa perubahan nyata tidak tercatat.
        unset($changes['updated_at'], $changes['created_at']);

        $meaningful = array_diff_key($changes, array_flip(ActivityLogger::HIDDEN));
        if (empty($meaningful)) {
            return; // tidak ada perubahan nyata → skip
        }

        $new = $model->getChanges();
        $old = [];
        foreach (array_keys($new) as $key) {
            $old[$key] = $model->getOriginal($key);
        }

        ActivityLogger::logModel('updated', $model, $old, $new);
    }

    public function deleted(Model $model): void
    {
        ActivityLogger::logModel('deleted', $model, $this->attributes($model), null);
    }

    public function restored(Model $model): void
    {
        ActivityLogger::logModel('restored', $model, null, $this->attributes($model));
    }

    /**
     * Ambil atribut model tanpa kolom sensitif.
     */
    protected function attributes(Model $model): array
    {
        return array_diff_key($model->getAttributes(), array_flip(ActivityLogger::HIDDEN));
    }
}
