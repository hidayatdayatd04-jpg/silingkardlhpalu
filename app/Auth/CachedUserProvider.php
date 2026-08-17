<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;

/**
 * User provider yang meng-cache hasil retrieveById ke cache (file).
 *
 * Di environment ini DB utama adalah Neon PostgreSQL remote, sehingga setiap
 * request membuka koneksi TLS baru dan me-resolve role/permission (Spatie)
 * dari jauh -> TTFB tinggi. Dengan cache, user (beserta relation role/permission
 * yang sudah ter-load) diambil dari cache sehingga tidak ada query remote per
 * request. TTL 5 menit; invalidasi otomatis saat user disimpan (lihat User).
 */
class CachedUserProvider extends EloquentUserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        if ($identifier === null) {
            return null;
        }

        return Cache::remember(
            'auth.user.' . $identifier,
            now()->addMinutes(5),
            function () use ($identifier) {
                $model = $this->createModel();

                // Eager-load role & permission agar tidak ada query pivot
                // (model_has_roles / model_has_permissions) ke Neon tiap request.
                // Model ter-hydrate ini di-cache, sehingga request berikutnya
                // mengembalikan user lengkap tanpa satu pun query remote.
                return $model->newQuery()
                    ->with(['roles', 'permissions'])
                    ->where($model->getAuthIdentifierName(), $identifier)
                    ->first();
            }
        );
    }
}
