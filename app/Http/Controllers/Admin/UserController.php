<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\AuditLog;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        $roles = Role::all();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);
        
        // Логирование создания пользователя
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'create_user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => json_encode(['email' => $user->email, 'name' => $user->name, 'role' => $request->role])
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Користувача створено');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
        ]);

        $oldData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->first()->name ?? null
        ];

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role]);
        
        // Логирование обновления пользователя
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => json_encode([
                'user_id' => $user->id,
                'email' => $user->email,
                'changes' => [
                    'name' => ['old' => $oldData['name'], 'new' => $request->name],
                    'email' => ['old' => $oldData['email'], 'new' => $request->email],
                    'role' => ['old' => $oldData['role'], 'new' => $request->role]
                ]
            ])
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Користувача оновлено');
    }

    public function destroy(User $user)
    {
        $email = $user->email;
        $name = $user->name;
        
        // Логирование удаления пользователя
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_user',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => json_encode(['email' => $email, 'name' => $name, 'id' => $user->id])
        ]);
        
        $user->delete();
        
        return redirect()->route('admin.users.index')->with('success', 'Користувача видалено');
    }
    
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }
}