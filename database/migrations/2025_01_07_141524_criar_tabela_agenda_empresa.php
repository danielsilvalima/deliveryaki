<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
  {
    Schema::create('agenda_empresas', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->uuid('uuid');
      $table->string('razao_social', 20);
      $table->string('cnpj', 20);
      $table->enum('status', ['A', 'D'])->default('A');
      $table->string('hash', 8)->unique();
      $table->timestamp('expiration_at')->nullable();
      $table->string('token_notificacao', 255)->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
      Schema::dropIfExists('agenda_empresas');
  }
};
