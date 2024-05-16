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
      $table->decimal('vlr_unitario', total: 6, places: 2);
      $table->enum('status', ['A', 'D']);
      $table
        ->timestamp('created_at')
        ->useCurrent()
        ->nullable();
      $table
        ->timestamp('updated_at')
        ->useCurrent()
        ->nullable();
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
