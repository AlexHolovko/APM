<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AuditLog;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // Добавляем тестовые данные, если таблица пустая
        if (AuditLog::count() === 0) {
            $admin = User::where('role', 'admin')->first();
            
            $logs = [
                [
                    'user_id' => $admin ? $admin->id : null,
                    'action' => 'login',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (Test Browser)',
                    'details' => json_encode(['message' => 'Успішний вхід в систему', 'time' => now()->toDateTimeString()]),
                    'created_at' => now()->subDays(5),
                ],
                [
                    'user_id' => $admin ? $admin->id : null,
                    'action' => 'create_user',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (Test Browser)',
                    'details' => json_encode(['email' => 'test@example.com', 'name' => 'Test User', 'role' => 'manager']),
                    'created_at' => now()->subDays(4),
                ],
                [
                    'user_id' => $admin ? $admin->id : null,
                    'action' => 'update_policy',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (Test Browser)',
                    'details' => json_encode(['policy_number' => 'POL-001', 'changes' => ['status' => 'active', 'premium' => 1500]]),
                    'created_at' => now()->subDays(3),
                ],
                [
                    'user_id' => null,
                    'action' => 'system',
                    'ip_address' => null,
                    'user_agent' => null,
                    'details' => json_encode(['message' => 'Системний запис аудиту', 'event' => 'cron_job_executed']),
                    'created_at' => now()->subDays(2),
                ],
                [
                    'user_id' => $admin ? $admin->id : null,
                    'action' => 'logout',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (Test Browser)',
                    'details' => json_encode(['message' => 'Вихід з системи']),
                    'created_at' => now()->subDays(1),
                ],
                [
                    'user_id' => $admin ? $admin->id : null,
                    'action' => 'login',
                    'ip_address' => '192.168.1.100',
                    'user_agent' => 'Mozilla/5.0 (Production)',
                    'details' => json_encode(['message' => 'Успішний вхід з нового пристрою']),
                    'created_at' => now(),
                ],
            ];
            
            foreach ($logs as $log) {
                AuditLog::create($log);
            }
        }
    }

    public function down(): void
    {
        // Откат не требуется
    }
};