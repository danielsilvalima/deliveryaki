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
    Schema::create('pedido_items', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid');
      $table->integer('qtd');
      $table->decimal('vlr_unitario', 10, 2);
      $table->decimal('vlr_total', 10, 2);
      $table->timestamps();
      $table->unsignedBigInteger('pedido_id');
      $table
        ->foreign('pedido_id')
        ->references('id')
        ->on('pedidos');
      $table->unsignedBigInteger('produto_id');
      $table
        ->foreign('produto_id')
        ->references('id')
        ->on('produtos');
      $table->unsignedBigInteger('empresa_id');
      $table
        ->foreign('empresa_id')
        ->references('id')
        ->on('empresas');
      $table->unsignedBigInteger('cliente_id');
      $table
        ->foreign('cliente_id')
        ->references('id')
        ->on('clientes');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('pedido_itens');
  }
};
