<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insurance_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('insurance_cases', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'rejected'])->default('pending')->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('insurance_cases', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};