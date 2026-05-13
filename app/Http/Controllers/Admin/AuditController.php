<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->action) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }
        
        $logs = $query->latest()->paginate(20);
        $users = User::all();
        
        return view('admin.audit.index', compact('logs', 'users'));
    }
    
    public function show($id)
    {
        $log = AuditLog::with('user')->find($id);
        
        if (!$log) {
            return response()->json([
                'error' => 'Запис не знайдено'
            ], 404);
        }
        
        // Декодируем details если это JSON
        $details = $log->details;
        if (is_string($details) && $this->isJson($details)) {
            $details = json_decode($details, true);
        }
        
        return response()->json([
            'id' => $log->id,
            'user_name' => $log->user ? $log->user->name : 'Система',
            'user_email' => $log->user ? $log->user->email : '-',
            'action' => $this->getActionName($log->action),
            'action_code' => $log->action,
            'ip_address' => $log->ip_address ?? '-',
            'user_agent' => $log->user_agent ?? '-',
            'created_at' => $log->created_at ? $log->created_at->format('d.m.Y H:i:s') : date('d.m.Y H:i:s', strtotime($log->created_at)),
            'details' => $details,
            'model_type' => $log->model_type ?? '-',
            'model_id' => $log->model_id ?? '-',
        ]);
    }
    
    private function getActionName($action)
    {
        $actions = [
            'create_user' => 'Створення користувача',
            'update_user' => 'Оновлення користувача',
            'delete_user' => 'Видалення користувача',
            'create_role' => 'Створення ролі',
            'update_role' => 'Оновлення ролі',
            'delete_role' => 'Видалення ролі',
            'login' => 'Вхід в систему',
            'login_failed' => 'Невдалий вхід',
            'logout' => 'Вихід з системи',
            'create_policy' => 'Створення полісу',
            'update_policy' => 'Оновлення полісу',
            'delete_policy' => 'Видалення полісу',
            'create_client' => 'Створення клієнта',
            'update_client' => 'Оновлення клієнта',
            'delete_client' => 'Видалення клієнта',
            'system' => 'Системна дія',
        ];
        
        return $actions[$action] ?? $action ?? 'Невідома дія';
    }
    
    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}