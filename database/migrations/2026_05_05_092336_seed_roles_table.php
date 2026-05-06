<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
     public function up(): void
    {
        $roles = [
            'admin',
            'manager',
            'specialist',
            'accountant',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }
    }

    public function down(): void
    {
        $roles = [
            'admin',
            'manager',
            'specialist',
            'accountant',
        ];

        foreach ($roles as $role) {
            Role::where('name', $role)->delete();
        }
    }
};
