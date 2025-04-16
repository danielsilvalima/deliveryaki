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
    Schema::create('faturas', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('empresa_id');
      $table->enum('tipo_app', ['deliveryaki', 'agendaadmin']); // Origem da empresa
      $table->string('referencia', 7)->comment('04/2025'); // Ex: 04/2025
      $table->decimal('valor_total', 10, 2);
      $table->decimal('valor_a_pagar', 10, 2);
      $table->enum('status', ['pendente', 'paga', 'vencida', 'cancelado'])->default('pendente');
      $table->date('vencimento');
      $table->timestamp('pago_em')->nullable();
      $table->string('metodo_pagamento')->nullable();
      $table->text('url_pagamento')->nullable();
      $table->timestamps();

      // Index para evitar faturas duplicadas por mês
      $table->unique(['empresa_id', 'tipo_app', 'referencia']);

      // Não fazemos foreign key direta porque temos duas tabelas diferentes
      // Podemos validar no código com base em `tipo_app`
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('faturas');
  }
};
