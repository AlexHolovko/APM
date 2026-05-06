<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RolesUsersSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // 1. РОЛИ
        // =========================
        $roles = ['admin', 'manager', 'specialist', 'accountant'];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        // =========================
        // 2. ПОЛЬЗОВАТЕЛИ + СВЯЗЬ
        // =========================
        $users = [
            [
                'name' => 'Головко Олександр',
                'email' => 'admin@example.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Коваленко Андрій',
                'email' => 'manager@example.com',
                'role' => 'manager',
            ],
            [
                'name' => 'Мельник Ольга',
                'email' => 'specialist@example.com',
                'role' => 'specialist',
            ],
            [
                'name' => 'Левченко Вікторія',
                'email' => 'accountant@example.com',
                'role' => 'accountant',
            ],
        ];

        foreach ($users as $data) {

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                ]
            );

            // 💣 ВАЖНО: это создаёт связь model_has_roles
            $user->syncRoles([$data['role']]);
        }
    }
}