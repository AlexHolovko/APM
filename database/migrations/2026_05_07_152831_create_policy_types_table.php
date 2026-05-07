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
        Schema::create('policy_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Назва: "Автострахування"
            $table->string('code')->unique(); // Код: "auto"
            $table->text('description')->nullable(); // Опис
            $table->decimal('default_premium', 10, 2); // Стандартна вартість
            $table->integer('duration_months'); // Тривалість в місяцях
            $table->json('conditions')->nullable(); // Умови у JSON форматі
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('policy_types');
    }
};
