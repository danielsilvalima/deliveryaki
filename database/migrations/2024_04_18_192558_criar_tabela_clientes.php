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
    Schema::create('clientes', function (Blueprint $table) {
      $table->id('id');
      $table->uuid('uuid');
      $table->string('nome_completo');
      $table->string('cep')->nullable();
      $table->string('logradouro')->nullable();
      $table->string('numero')->nullable();
      $table->string('complemento')->nullable();
      $table->string('bairro')->nullable();
      $table->string('cidade')->nullable();
      $table->string('celular')->nullable();
      $table->enum('status', ['A', 'D']);
      $table->unsignedBigInteger('empresa_id');
      $table->foreign('empresa_id')->references('id')->on('empresas');
      $table
        ->timestamp('created_at')
        ->useCurrent()
        ->nullable();
      $table
        ->timestamp('updated_at')
        ->useCurrent()
        ->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('clientes');
  }
};
