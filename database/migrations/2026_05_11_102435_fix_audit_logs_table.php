<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Изменяем структуру если нужно
            if (!Schema::hasColumn('audit_logs', 'action')) {
                $table->string('action')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'details')) {
                $table->json('details')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Откат
    }
};