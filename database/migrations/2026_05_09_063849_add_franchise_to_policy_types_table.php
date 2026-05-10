<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->decimal('sum_insured', 12, 2)->nullable()->after('premium')
                  ->comment('Страхова сума (на яку застраховано)');
            $table->decimal('franchise', 10, 2)->nullable()->after('sum_insured')
                  ->comment('Франшиза (сума, яку сплачує клієнт при настанні випадку)');
        });
    }

    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->dropColumn(['sum_insured', 'franchise']);
        });
    }
};
