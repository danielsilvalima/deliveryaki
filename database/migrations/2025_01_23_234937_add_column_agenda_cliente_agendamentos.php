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
        Schema::table('agenda_cliente_agendamentos', function (Blueprint $table) {
          $table->boolean('notificado')->nullable()->default(false);
          $table->enum('tipo_notificacao', ['A', 'C', 'E'])->default('A');
          $table->enum('status', ['A', 'C'])->default('A');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agenda_cliente_agendamentos', function (Blueprint $table) {
          $table->dropColumn('notificado');
          $table->dropColumn('tipo_notificacao');
          $table->dropColumn('status');
        });
    }
};
