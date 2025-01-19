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
      Schema::create('agenda_cliente_agendamentos', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
        $table->time('duracao',0);
        $table->decimal('vlr', 10, 2)->default(0.00);
        $table->timestamp('start_scheduling_at')->nullable();
        $table->timestamp('end_scheduling_at')->nullable();
        $table->foreignId('empresa_id')->constrained('agenda_empresas');
        $table->foreignId('cliente_id')->constrained('agenda_clientes');
        $table->foreignId('empresa_servico_id')->constrained('agenda_empresa_servicos');
        $table->foreignId('servico_id')->constrained('agenda_servicos');
      });
    }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
      Schema::dropIfExists('agenda_cliente_agendamentos');
  }
};
