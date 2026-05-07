<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('policies', function (Blueprint $table) {
            if (!Schema::hasColumn('policies', 'policy_type_id')) {
                $table->foreignId('policy_type_id')->nullable()->constrained()->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->dropForeign(['policy_type_id']);
            $table->dropColumn('policy_type_id');
        });
    }
};
