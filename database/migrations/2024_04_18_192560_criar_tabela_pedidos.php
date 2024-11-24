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
    Schema::create('pedidos', function (Blueprint $table) {
      $table->id();
        $table->uuid('uuid');
        $table->enum('status', ['A', 'C', 'P', 'E']);
        $table->enum('tipo_pagamento', ['CR', 'DE', 'PI', 'DI']);
        $table->enum('tipo_entrega', ['E', 'R']);
        $table->decimal('vlr_taxa', 10, 2);
        $table->decimal('vlr_total', 10, 2);
        $table->timestamps();
        $table->timestamp('delivered_at')->nullable();
        $table->timestamp('deliver_at')->nullable();
        $table->unsignedBigInteger('empresa_id');
        $table->foreign('empresa_id')->references('id')->on('empresas');
        $table->unsignedBigInteger('cliente_id');
        $table->foreign('cliente_id')->references('id')->on('clientes');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('pedidos');
  }
};
