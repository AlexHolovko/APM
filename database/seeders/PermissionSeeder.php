<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Создание разрешений
        $permissions = [
            // Пользователи
            'view_users', 'create_users', 'edit_users', 'delete_users',
            // Роли
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
            // Полисы
            'view_policies', 'create_policies', 'edit_policies', 'delete_policies',
            // Клиенты
            'view_clients', 'create_clients', 'edit_clients', 'delete_clients',
            // Аудит
            'view_audit',
            // Платежи
            'view_payments', 'create_payments', 'edit_payments', 'delete_payments',
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Назначение разрешений для ролей
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());
        
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $managerRole->givePermissionTo([
            'view_policies', 'create_policies', 'edit_policies',
            'view_clients', 'create_clients', 'edit_clients',
            'view_payments', 'create_payments'
        ]);
        
        $specialistRole = Role::firstOrCreate(['name' => 'specialist']);
        $specialistRole->givePermissionTo([
            'view_policies', 'view_clients'
        ]);
        
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);
        $accountantRole->givePermissionTo([
            'view_payments', 'create_payments', 'edit_payments', 'view_policies'
        ]);
    }
}