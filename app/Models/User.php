<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

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
     * URL foto profil (avatar) bila ada.
     */
    public function photoUrl(): ?string
    {
        return $this->photo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->photo_path) : null;
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
     * Get all groups that user can access (role default + additional)
     */
    public function allowedGroups(): array
    {
        $role = \App\Enums\AdminRole::tryFrom($this->primaryRoleName());
        
        if (!$role) {
            return [];
        }

        $defaultGroups = $role->allowedGroups();
        $additionalGroups = $this->additional_access ?? [];

        return array_unique(array_merge($defaultGroups, $additionalGroups));
    }

    /**
     * Check if user can access a specific group
     */
    public function canAccessGroup(string $groupKey): bool
    {
        return in_array($groupKey, $this->allowedGroups());
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
     * Get user's primary admin role enum
     */
    public function adminRole(): ?\App\Enums\AdminRole
    {
        return \App\Enums\AdminRole::tryFrom($this->primaryRoleName());
    }
}
