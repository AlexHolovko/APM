<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuditLog;
use App\Models\User;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем тестовые записи
        $admin = User::where('email', 'admin@example.com')->first();
        
        if ($admin) {
            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'login',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Test)',
                'details' => ['message' => 'Успішний вхід в систему']
            ]);
            
            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'create',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Test)',
                'details' => ['model' => 'User', 'name' => 'Новий користувач']
            ]);
            
            AuditLog::create([
                'user_id' => $admin->id,
                'action' => 'update',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Test)',
                'details' => ['model' => 'Policy', 'changes' => ['status' => 'active']]
            ]);
        }
        
        // Создаем системную запись
        AuditLog::create([
            'user_id' => null,
            'action' => 'system',
            'ip_address' => null,
            'user_agent' => null,
            'details' => ['message' => 'Системний запис аудиту']
        ]);
    }
}