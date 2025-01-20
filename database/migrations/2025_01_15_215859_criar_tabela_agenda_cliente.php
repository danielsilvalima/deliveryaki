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
      Schema::create('agenda_clientes', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
        $table->string('email',100)->notNullable()->unique();
        $table->string('nome_completo',255)->notNullable();
        $table->string('cnpj',20)->nullable();
        $table->string('celular', 20)->nullable();
        $table->foreignId('empresa_id')->constrained('agenda_empresas');
      });
    }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
      Schema::dropIfExists('agenda_clientes');
  }
};
