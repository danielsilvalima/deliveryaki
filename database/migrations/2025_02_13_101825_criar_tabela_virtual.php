<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('virtual_users', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table
        ->string('username', 255)
        ->notNullable()
        ->unique();
      $table->decimal('balance', 10, 2)->default(0.0);
    });

    Schema::create('virtual_transacoes', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->string('qrcode', 255)->nullable();
      $table->decimal('balance', 10, 2)->default(0.0);
      $table->enum('status', ['A', 'C'])->default('A');
      $table->foreignId('virtual_user_id')->constrained('virtual_users');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('virtual_transacoes');
    Schema::dropIfExists('virtual_users');
  }
};
