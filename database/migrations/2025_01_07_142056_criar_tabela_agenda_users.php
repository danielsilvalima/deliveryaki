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
    Schema::create('agenda_users', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->string('nome_completo', 100);
      $table->string('email', 255);
      $table->string('celular', 20);
      $table->enum('status', ['A', 'D'])->default('A');
      $table->foreignId('empresa_id');
      $table->foreign('empresa_id')->references('id')->on('agenda_empresas');
      $table->string('token_notificacao', 255)->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
      Schema::dropIfExists('agenda_users');
  }
};
