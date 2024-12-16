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
      Schema::create('horario_expedientes', function (Blueprint $table) {
          $table->id();
          $table->integer('dia_semana')->comment('0=domingo, 1=segunda, ..., 6=sábado');
          $table->string('descricao', 50)->nullable();
          $table->timestamps();
      });
    }

    public function down()
    {
      Schema::dropIfExists('horario_expedientes');
    }
};
