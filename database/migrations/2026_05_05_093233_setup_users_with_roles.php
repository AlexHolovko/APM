<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

return new class extends Migration {

    public function up(): void
    {
        // =========================
        // 1. ДОБАВЛЯЕМ ПОЛЕ ROLE
        // =========================
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->after('email');
        });

        // =========================
        // 2. СОЗДАЁМ РОЛИ
        // =========================
        $roles = ['admin', 'manager', 'specialist', 'accountant'];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        // =========================
        // 3. СОЗДАЁМ ПОЛЬЗОВАТЕЛЕЙ
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
                    'role' => $data['role'],
                ]
            );

            // Spatie связь
            $user->syncRoles([$data['role']]);
        }
    }

    public function down(): void
    {
        // Удаляем пользователей
        User::whereIn('email', [
            'admin@example.com',
            'manager@example.com',
            'specialist@example.com',
            'accountant@example.com',
        ])->delete();

        // Удаляем поле role
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};