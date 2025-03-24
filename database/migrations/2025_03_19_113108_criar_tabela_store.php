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
    Schema::connection('mysql2')->create('produto', function (Blueprint $table) {
      $table->id();
      $table->string('descricao', 255);
      $table->string('url', 255);
      $table->string('url_afiliado', 255);
      $table->string('url_imagem', 255)->nullable();
      $table->string('texto_personalizado', 255);
      $table->string('categoria', 255);
      $table->decimal('valor', 10, 2)->default(0.0);
      $table->decimal('valor_promocional', 10, 2)->default(0.0);
      $table->decimal('valor_desconto', 10, 2)->default(0.0);
      $table->boolean('promocao')->nullable()->default(false);
      $table->boolean('ativo')->nullable()->default(true);
      $table->boolean('frete_gratis')->nullable()->default(false);
      $table->integer('cliques')->notNullable()->default(0);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::connection('mysql2')->dropIfExists('produto');
  }
};
