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
      Schema::create('agenda_empresa_servicos', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
        $table->string('descricao', 100);
        $table->time('duracao',0);
        $table->decimal('vlr', 10, 2)->default(0.00); // Valor monetário
        $table->enum('status', ['A', 'D'])->default('A');
        $table->foreignId('empresa_id')->constrained('agenda_empresas');
      });
    }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
      Schema::dropIfExists('agenda_empresa_servicos');
  }
};
