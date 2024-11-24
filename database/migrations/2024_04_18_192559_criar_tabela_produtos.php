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
    Schema::create('produtos', function (Blueprint $table) {
      $table->id('id');
      $table->uuid('uuid');
      $table->string('descricao');
      $table->decimal('vlr_unitario', 10, 2);
      $table->enum('status', ['A', 'D']);
      $table->timestamps();
      $table->unsignedBigInteger('empresa_id');
      $table->foreign('empresa_id')->references('id')->on('empresas');
      $table->unsignedBigInteger('categoria_id');
      $table->foreign('categoria_id')->references('id')->on('categorias');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('produtos');
  }
};
