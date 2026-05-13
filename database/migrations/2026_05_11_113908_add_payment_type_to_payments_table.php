<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'payment_type')) {
                $table->enum('payment_type', ['premium', 'payout'])->default('premium')->after('amount');
            }
            if (!Schema::hasColumn('payments', 'insurance_case_id')) {
                $table->foreignId('insurance_case_id')->nullable()->constrained()->after('policy_id');
            }
            if (!Schema::hasColumn('payments', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('payment_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'insurance_case_id', 'transaction_id']);
        });
    }
};