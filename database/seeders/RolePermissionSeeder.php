<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
                'role' => AdminRole::ADMIN->value,
            ],
            [
                'name' => 'Admin Pengendalian',
                'username' => 'pengendalian',
                'email' => 'pengendalian@dlhpalu.go.id',
                'role' => AdminRole::BIDANG_PENGENDALIAN->value,
            ],
            [
                'name' => 'Admin Sampah & LB3',
                'username' => 'sampah-lb3',
                'email' => 'sampah@dlhpalu.go.id',
                'role' => AdminRole::BIDANG_SAMPAH_LB3->value,
            ],
            [
                'name' => 'Admin Tata Penataan',
                'username' => 'tata-penataan',
                'email' => 'tata@dlhpalu.go.id',
                'role' => AdminRole::BIDANG_TATA_PENATAAN->value,
            ],
            [
                'name' => 'Admin RTH',
                'username' => 'rth',
                'email' => 'rth@dlhpalu.go.id',
                'role' => AdminRole::BIDANG_RTH->value,
            ],
        ];

        foreach ($users as $data) {
            $user = User::where('username', $data['username'])->first();

            if ($user) {
                // User sudah ada: jangan pernah menimpa password-nya.
                // Cukup sinkronkan nama, email, status aktif, dan role.
                $user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'is_active' => true,
                ]);
            } else {
                // User baru: generate password acak kuat, tampilkan SEKALI di console.
                $password = Str::password(16);
                $user = User::create([
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'password' => Hash::make($password),
                    'is_active' => true,
                ]);

                $this->command?->warn("Password baru untuk '{$data['username']}' (simpan sekarang, tidak ditampilkan lagi): {$password}");
            }

            $user->syncRoles([$data['role']]);
        }
    }
}
