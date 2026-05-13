<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Статистика
        $totalUsers = User::count();
        $totalRoles = Role::count();
        $totalAuditLogs = AuditLog::count();
        
        // Останні дії
        $recentLogs = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Статистика по ролям для графіка
        $roleLabels = [];
        $roleData = [];
        
        $roles = Role::withCount('users')->get();
        foreach ($roles as $role) {
            if ($role->users_count > 0) {
                $roleLabels[] = $role->name;
                $roleData[] = $role->users_count;
            }
        }
        
        return view('admin.dashboard', compact(
            'totalUsers', 'totalRoles', 'totalAuditLogs',
            'recentLogs', 'roleLabels', 'roleData'
        ));
    }
}