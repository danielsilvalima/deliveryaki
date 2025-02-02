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
      Schema::create('agenda_empresa_recursos', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
        $table->string('descricao', 100);
        $table->enum('status', ['A', 'D'])->default('A');
        $table->foreignId('empresa_id')->constrained('agenda_empresas');
      });

      Schema::table('agenda_empresa_servicos', function (Blueprint $table) {
        $table->foreignId('empresa_recurso_id')->nullable()->constrained('agenda_empresa_recursos');
      });

      Schema::table('agenda_cliente_agendamentos', function (Blueprint $table) {
          $table->foreignId('empresa_recurso_id')->nullable()->constrained('agenda_empresa_recursos');
      });

      Schema::table('agenda_empresa_expedientes', function (Blueprint $table) {
        $table->foreignId('empresa_recurso_id')->nullable()->constrained('agenda_empresa_recursos');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Removendo primeiro as colunas que referenciam agenda_empresa_recursos
        Schema::table('agenda_empresa_servicos', function (Blueprint $table) {
            $table->dropForeign(['empresa_recurso_id']);
            $table->dropColumn('empresa_recurso_id');
        });

        Schema::table('agenda_cliente_agendamentos', function (Blueprint $table) {
            $table->dropForeign(['empresa_recurso_id']);
            $table->dropColumn('empresa_recurso_id');
        });

        Schema::table('agenda_empresa_expedientes', function (Blueprint $table) {
          $table->dropForeign(['empresa_recurso_id']);
          $table->dropColumn('empresa_recurso_id');
        });

        // Agora podemos excluir a tabela principal
        Schema::dropIfExists('agenda_empresa_recursos');
    }
};
