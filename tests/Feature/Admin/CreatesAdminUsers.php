<?php

namespace Tests\Feature\Admin;

use App\Enums\AdminRole;
use App\Models\User;
use Spatie\Permission\Models\Role;

trait CreatesAdminUsers
{
    protected function createRoles(): void
    {
        foreach (AdminRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }
    }

    protected function superadmin(): User
    {
        $this->createRoles();
        $user = User::factory()->create([
            'username'  => 'super_'.uniqid(),
            'is_active' => true,
        ]);
        $user->syncRoles([AdminRole::SUPERADMIN->value]);

        return $user;
    }

    protected function bidangUser(AdminRole $role): User
    {
        $this->createRoles();
        $user = User::factory()->create([
            'username'  => 'usr_'.uniqid(),
            'is_active' => true,
        ]);
        $user->syncRoles([$role->value]);

        return $user;
    }
}
