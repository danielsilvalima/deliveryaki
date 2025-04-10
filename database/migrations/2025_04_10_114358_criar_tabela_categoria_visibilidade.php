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
    Schema::create('categoria_visibilidades', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->unsignedBigInteger('categoria_id');
      $table->unsignedBigInteger('horario_expediente_id');


      $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');
      $table->foreign('horario_expediente_id')->references('id')->on('horario_expedientes')->onDelete('cascade');
      $table->unique(['categoria_id', 'horario_expediente_id'], 'categoria_dia_unica');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('categoria_visibilidades');
  }
};
