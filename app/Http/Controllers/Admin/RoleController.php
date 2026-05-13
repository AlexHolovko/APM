<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->get();
        $permissions = Permission::all();
        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles|max:255',
            'description' => 'nullable|string'
        ]);

        $role = Role::create(['name' => $request->name]);
        
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_role',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => json_encode(['role' => $role->name, 'id' => $role->id])
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Роль створено');
    }

    public function update(Request $request, Role $role)
    {
        // Защита от изменения системных ролей
        $systemRoles = ['admin', 'manager', 'specialist', 'accountant'];
        if (in_array($role->name, $systemRoles)) {
            return redirect()->route('admin.roles.index')->with('error', 'Системну роль не можна змінювати');
        }

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id . '|max:255',
        ]);

        $oldName = $role->name;
        $role->update(['name' => $request->name]);
        
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_role',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => json_encode(['old_name' => $oldName, 'new_name' => $request->name])
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Роль оновлено');
    }

    public function destroy(Role $role)
    {
        // Защита от удаления системных ролей
        $systemRoles = ['admin', 'manager', 'specialist', 'accountant'];
        if (in_array($role->name, $systemRoles)) {
            return redirect()->route('admin.roles.index')->with('error', 'Системну роль не можна видалити');
        }

        // Проверка, есть ли пользователи с этой ролью
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')->with('error', 'Неможливо видалити роль, оскільки є користувачі з цією роллю');
        }

        $roleName = $role->name;
        $role->delete();
        
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_role',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => json_encode(['role' => $roleName])
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Роль видалено');
    }

    public function getPermissions(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return response()->json([
            'role_name' => $role->name,
            'permissions' => $permissions,
            'role_permissions' => $rolePermissions,
            'is_system_role' => in_array($role->name, ['admin', 'manager', 'specialist', 'accountant'])
        ]);
    }

    public function syncPermissions(Request $request, Role $role)
    {
        // Защита для системных ролей
        $systemRoles = ['admin', 'manager', 'specialist', 'accountant'];
        if (in_array($role->name, $systemRoles)) {
            return response()->json(['success' => false, 'message' => 'Системну роль не можна змінювати'], 403);
        }

        $role->permissions()->sync($request->permissions);
        
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'sync_role_permissions',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => json_encode([
                'role' => $role->name,
                'permissions_count' => count($request->permissions ?? [])
            ])
        ]);
        
        return response()->json(['success' => true]);
    }
    public function edit(Role $role)
{
    return response()->json([
        'id' => $role->id,
        'name' => $role->name
    ]);
}
}