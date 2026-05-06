<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    /*Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('login')->unique();
      $table->string('password');
      $table->string('role', 50);
      $table->timestamps();
    });*/

    Schema::create('clients', function (Blueprint $table) {

            $table->id(); 

            // 👤 ПІБ
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();

            // 📅 Дата рождения
            $table->date('birth_date')->nullable();

            // 🆔 ИНН
            $table->string('tax_number')->nullable();

            // 📞 Контакты
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // 🏠 Адрес
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('house')->nullable();
            $table->string('apartment')->nullable();

            // 🪪 Паспорт
            $table->string('passport_series')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('passport_issued_by')->nullable();
            $table->date('passport_issued_at')->nullable();

            $table->timestamps(); 
        });

    Schema::create('applications', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('client_id')->nullable();
      $table->date('date')->nullable();
      $table->string('status', 50)->nullable();
      $table->string('coverage_type', 100)->nullable();
      $table->decimal('amount', 10, 2)->nullable();
    });

    Schema::create('policies', function (Blueprint $table) {
      $table->id();
      $table->string('policy_number', 100);
      $table->date('start_date')->nullable();
      $table->date('end_date')->nullable();
      $table->decimal('premium', 10, 2)->nullable();
      $table->string('status', 50)->nullable();
      $table->unsignedBigInteger('user_id')->nullable();
      $table->unsignedBigInteger('application_id')->nullable();
    });

    Schema::create('contracts', function (Blueprint $table) {
      $table->id();
      $table->string('contract_number', 100);
      $table->date('signed_date')->nullable();
      $table->text('payment_schedule')->nullable();
      $table->unsignedBigInteger('policy_id')->nullable();
    });

    Schema::create('payments', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('contract_id')->nullable();
      $table->decimal('amount', 10, 2)->nullable();
      $table->date('date')->nullable();
      $table->string('status', 50)->nullable();
      $table->string('transaction_id')->nullable();
    });

    Schema::create('insurance_cases', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('policy_id')->nullable();
      $table->date('date')->nullable();
      $table->text('description')->nullable();
      $table->string('status', 50)->nullable();
      $table->decimal('assessed_amount', 10, 2)->nullable();
    });

    Schema::create('client_documents', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('client_id')->nullable();
      $table->string('type', 100)->nullable();
      $table->string('file_path')->nullable();
      $table->timestamp('uploaded_at')->nullable();
    });

    Schema::create('reports', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id')->nullable();
      $table->string('type', 100)->nullable();
      $table->date('date_generated')->nullable();
      $table->string('file_path')->nullable();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('reports');
    Schema::dropIfExists('client_documents');
    Schema::dropIfExists('insurance_cases');
    Schema::dropIfExists('payments');
    Schema::dropIfExists('contracts');
    Schema::dropIfExists('policies');
    Schema::dropIfExists('applications');
    Schema::dropIfExists('clients');
  /*Schema::dropIfExists('users'); */
  }
};
