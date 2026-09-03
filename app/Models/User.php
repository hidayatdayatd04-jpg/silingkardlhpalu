<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, Notifiable;

    protected $table = 'user';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_active',
        'additional_access',
        'photo_path',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'additional_access' => 'array',
            'preferences' => 'array',
        ];
    }

    /**
     * Invalidate cache user ter-cache (Auth\CachedUserProvider) saat disimpan,
     * agar perubahan role/profil tercermin paling lambat dalam TTL cache.
     */
    protected static function booted(): void
    {
        static::saved(function (User $user) {
            \Illuminate\Support\Facades\Cache::forget('auth.user.' . $user->id);
        });
    }

    /**
     * Notifikasi database — override bawaan framework agar memakai model
     * App\Models\DatabaseNotification (tabel "notification", bukan "notifications").
     */
    public function notifications()
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')
            ->latest();
    }

    /**
     * URL foto profil (avatar) bila ada.
     */
    public function photoUrl(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        // Bucket B2 bersifat private -> gunakan signed URL yang berlaku 24 jam.
        try {
            return $disk->temporaryUrl($this->photo_path, now()->addHours(24));
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Ambil satu preferensi user dengan fallback.
     */
    public function pref(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences ?? [], $key, $default);
    }

    public function primaryRoleName(): ?string
    {
        return $this->roles->first()?->name;
    }

    /**
     * Label peran yang ramah pengguna (contoh: "Kepala Bidang", bukan slug).
     */
    public function roleLabel(): string
    {
        return \App\Support\AdminAccess::roleLabel($this->primaryRoleName());
    }

    /**
     * Warna tema peran (danger|info|warning|gray|success).
     */
    public function roleColor(): string
    {
        return \App\Support\AdminAccess::roleColor($this->primaryRoleName());
    }

    /**
     * Get all groups that user can access (role default + additional)
     *
     * @deprecated Gunakan accessibleGroups() untuk mencakup akses per-menu.
     */
    public function allowedGroups(): array
    {
        $role = \App\Enums\AdminRole::tryFrom($this->primaryRoleName());

        if (!$role) {
            return [];
        }

        $defaultGroups = $role->allowedGroups();

        return array_unique($defaultGroups);
    }

    /**
     * Slug menu spesifik yang diberikan sebagai akses tambahan
     * (menyimpan slug, bukan key grup, sehingga bisa memilih sub-menu tertentu).
     */
    public function allowedSlugs(): array
    {
        return array_values(array_unique(array_filter(
            $this->additional_access ?? [],
            fn ($value) => is_string($value) && $value !== ''
        )));
    }

    /**
     * Grup yang bisa diakses: default role + grup yang memuat slug akses tambahan.
     */
    public function accessibleGroups(): array
    {
        $groups = $this->allowedGroups();
        $slugs = $this->allowedSlugs();

        if (! empty($slugs)) {
            $extra = collect(\App\Support\Admin\AdminRegistry::all())
                ->filter(fn ($group) => collect($group['items'])
                    ->contains(fn ($item) => in_array($item['slug'] ?? null, $slugs, true)))
                ->keys()
                ->all();

            $groups = array_unique(array_merge($groups, $extra));
        }

        return $groups;
    }

    /**
     * Check if user can access a specific group
     */
    public function canAccessGroup(string $groupKey): bool
    {
        return in_array($groupKey, $this->accessibleGroups());
    }

    /**
     * Cek akses ke satu resource admin (berdasar grup ATAU slug menu spesifik).
     */
    public function canAccessResource(array $meta): bool
    {
        return in_array($meta['group'] ?? null, $this->allowedGroups(), true)
            || in_array($meta['slug'] ?? null, $this->allowedSlugs(), true);
    }

    /**
     * Check if user is superadmin
     */
    public function isSuperadmin(): bool
    {
        $role = \App\Enums\AdminRole::tryFrom($this->primaryRoleName());
        return $role?->isSuperadmin() ?? false;
    }

    /**
     * Check if user has admin panel access
     */
    public function hasAdminAccess(): bool
    {
        return \App\Support\AdminAccess::hasAnyPanelRole($this);
    }

    /**
     * Get user's primary admin role enum
     */
    public function adminRole(): ?\App\Enums\AdminRole
    {
        return \App\Enums\AdminRole::tryFrom($this->primaryRoleName());
    }
}
