<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });

        Schema::table('policies', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('application_id')->references('id')->on('applications');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->foreign('policy_id')->references('id')->on('policies')->onDelete('cascade');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
        });

        Schema::table('insurance_cases', function (Blueprint $table) {
            $table->foreign('policy_id')->references('id')->on('policies')->onDelete('cascade');
        });

        Schema::table('client_documents', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        Schema::table('policies', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['application_id']);
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['policy_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['contract_id']);
        });

        Schema::table('insurance_cases', function (Blueprint $table) {
            $table->dropForeign(['policy_id']);
        });

        Schema::table('client_documents', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};
