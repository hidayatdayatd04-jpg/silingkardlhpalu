<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (AdminRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }

        // Bersihkan user & role superadmin lama (sudah digabung ke admin)
        $oldSuperadmin = User::where('username', 'superadmin')->first();
        if ($oldSuperadmin) {
            $oldSuperadmin->syncRoles([]);
            $oldSuperadmin->delete();
        }
        Role::where('name', 'superadmin')->delete();

        $users = [
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@dlhpalu.go.id',
                'password' => 'admin123',
                'role' => AdminRole::ADMIN->value,
            ],
            [
                'name' => 'Admin Pengendalian',
                'username' => 'pengendalian',
                'email' => 'pengendalian@dlhpalu.go.id',
                'password' => 'pengendalian123',
                'role' => AdminRole::BIDANG_PENGENDALIAN->value,
            ],
            [
                'name' => 'Admin Sampah & LB3',
                'username' => 'sampah-lb3',
                'email' => 'sampah@dlhpalu.go.id',
                'password' => 'sampah123',
                'role' => AdminRole::BIDANG_SAMPAH_LB3->value,
            ],
            [
                'name' => 'Admin Tata Penataan',
                'username' => 'tata-penataan',
                'email' => 'tata@dlhpalu.go.id',
                'password' => 'tata123',
                'role' => AdminRole::BIDANG_TATA_PENATAAN->value,
            ],
            [
                'name' => 'Admin RTH',
                'username' => 'rth',
                'email' => 'rth@dlhpalu.go.id',
                'password' => 'rth123',
                'role' => AdminRole::BIDANG_RTH->value,
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['username' => $data['username']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'is_active' => true,
                ]
            );

            $user->syncRoles([$data['role']]);
        }
    }
}
