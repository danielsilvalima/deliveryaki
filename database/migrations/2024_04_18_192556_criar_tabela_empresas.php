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
    Schema::create('empresas', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid');
      $table->string('razao_social');
      $table->string('cnpj');
      $table->string('telefone')->nullable();
      $table->string('celular');
      $table->enum('status', ['A', 'D'])->default('A');
      $table->string('hash', 8)->unique;
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
      Schema::dropIfExists('empresas');
  }
};
