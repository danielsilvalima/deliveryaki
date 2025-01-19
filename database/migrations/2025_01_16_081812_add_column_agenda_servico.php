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
      Schema::table('agenda_servicos', function (Blueprint $table) {
          $table->unsignedBigInteger('empresa_id')->after('id');
          $table->foreign('empresa_id')->references('id')->on('agenda_empresas');
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
      Schema::table('agenda_servicos', function (Blueprint $table) {
          $table->dropForeign(['empresa_id']);
          $table->dropColumn('empresa_id');
      });
    }
};
