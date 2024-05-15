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
    Schema::create('pedido_items', function (Blueprint $table) {
      $table->id('id');
      $table->uuid('uuid');
      $table->integer('qtd');
      $table->decimal('vlr_unitario', total: 6, places: 2);
      $table->decimal('vlr_total', total: 6, places: 2);
      $table
        ->timestamp('created_at')
        ->useCurrent()
        ->nullable();
      $table
        ->timestamp('updated_at')
        ->useCurrent()
        ->nullable();
      $table->integer('pedido_id')->unsigned();
      $table->foreign('pedido_id')->references('id')->on('pedidos');
      $table->integer('produto_id')->unsigned();
      $table->foreign('produto_id')->references('id')->on('produtos');
      $table->integer('empresa_id')->unsigned();
      $table->foreign('empresa_id')->references('id')->on('empresas');
      $table->integer('cliente_id')->unsigned();
      $table->foreign('cliente_id')->references('id')->on('clientes');
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
