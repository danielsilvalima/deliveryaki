<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
      Schema::create('agenda_empresa_expedientes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('empresa_id')->constrained('agenda_empresas');
        $table->foreignId('horario_expediente_id')->constrained('agenda_horario_expedientes');
        $table->time('hora_abertura',0)->nullable();
        $table->time('hora_fechamento',0)->nullable();
        $table->time('intervalo_inicio',0)->nullable();
        $table->time('intervalo_fim',0)->nullable();
        $table->timestamps();
      });
    }

    public function down()
    {
      Schema::dropIfExists('agenda_empresa_expedientes');
    }
};
