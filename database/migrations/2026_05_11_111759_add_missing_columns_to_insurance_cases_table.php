<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insurance_cases', function (Blueprint $table) {
            // Добавляем колонку claim_amount если её нет
            if (!Schema::hasColumn('insurance_cases', 'claim_amount')) {
                $table->decimal('claim_amount', 12, 2)->default(0)->after('description');
            }
            
            // Добавляем колонку status если её нет
            if (!Schema::hasColumn('insurance_cases', 'status')) {
                $table->enum('status', ['pending', 'in_review', 'approved', 'rejected'])->default('pending')->after('claim_amount');
            }
            
            // Добавляем колонку decision_date если её нет
            if (!Schema::hasColumn('insurance_cases', 'decision_date')) {
                $table->date('decision_date')->nullable()->after('status');
            }
            
            // Добавляем колонку decision_notes если её нет
            if (!Schema::hasColumn('insurance_cases', 'decision_notes')) {
                $table->text('decision_notes')->nullable()->after('decision_date');
            }
            
            // Добавляем колонку approved_amount если её нет
            if (!Schema::hasColumn('insurance_cases', 'approved_amount')) {
                $table->decimal('approved_amount', 12, 2)->nullable()->after('decision_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('insurance_cases', function (Blueprint $table) {
            $table->dropColumn([
                'claim_amount', 
                'status', 
                'decision_date', 
                'decision_notes',
                'approved_amount'
            ]);
        });
    }
};