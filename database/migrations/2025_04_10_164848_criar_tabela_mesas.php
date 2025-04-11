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
    // Cria a tabela de mesas
    Schema::create('mesas', function (Blueprint $table) {
      $table->id();
      $table->timestamps();
      $table->string('descricao', 255);
    });

    // Adiciona coluna mesa_id na tabela empresas
    Schema::table('pedidos', function (Blueprint $table) {
      $table->unsignedBigInteger('mesa_id')->nullable();
      $table->foreign('mesa_id')->references('id')->on('mesas')->onDelete('set null');
    });

    // Adiciona coluna observacao e altera tipo_pagamento para nullable na tabela pedidos
    Schema::table('pedidos', function (Blueprint $table) {
      $table->text('observacao')->nullable();

      // Modificação de tipo_pagamento precisa ser feita separadamente com Doctrine (se já existir a coluna),
      // mas se estiver criando agora, pode definir diretamente como nullable:
      $table->enum('tipo_pagamento', ['CR', 'DE', 'PI', 'DI'])->nullable()->change();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('pedidos', function (Blueprint $table) {
      $table->dropForeign(['mesa_id']);
      $table->dropColumn('mesa_id');
    });

    Schema::table('pedidos', function (Blueprint $table) {
      $table->dropColumn('observacao');
    });

    Schema::dropIfExists('mesas');
  }
};
