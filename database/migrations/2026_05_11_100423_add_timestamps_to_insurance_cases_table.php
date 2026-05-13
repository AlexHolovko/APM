<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insurance_cases', function (Blueprint $table) {
            $table->timestamps(); // Добавляет created_at и updated_at
        });
        
        Schema::table('payments', function (Blueprint $table) {
            $table->timestamps();
        });
        
        Schema::table('applications', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('insurance_cases', function (Blueprint $table) {
            $table->dropTimestamps();
        });
        
        Schema::table('payments', function (Blueprint $table) {
            $table->dropTimestamps();
        });
        
        Schema::table('applications', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};