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
      Schema::create('ceps', function (Blueprint $table) {
        $table->id();
        $table->string('cep', 50)->notNullable();
        $table->string('logradouro', 255)->nullable();
        $table->string('complemento', 255)->nullable();
        $table->string('bairro', 255)->nullable();
        $table->string('cidade', 255)->nullable();
        $table->string('uf', 255)->nullable();
        $table->timestamps();
      });

      Schema::table('clientes', function (Blueprint $table) {
        $table->unsignedBigInteger('cep_id')->nullable();
        $table->foreign('cep_id')->references('id')->on('ceps')->onDelete('set null');
        $table->string('lat', 20)->nullable();
        $table->string('lng', 20)->nullable();

        $table->dropColumn(['logradouro', 'complemento', 'bairro', 'cidade']);
      });

      Schema::table('empresas', function (Blueprint $table) {
        $table->decimal('vlr_km', 10, 2)->nullable();
        $table->enum('tipo_taxa', ['F', 'D'])->default('F');
        $table->string('inicio_distancia', 10)->nullable();
        $table->string('cep', 50)->nullable();
        $table->string('logradouro', 255)->nullable();
        $table->string('numero', 100)->nullable();
        $table->string('complemento', 255)->nullable();
        $table->string('bairro', 255)->nullable();
        $table->string('cidade', 255)->nullable();
        $table->string('uf', 255)->nullable();
        $table->string('lat', 20)->nullable();
        $table->string('lng', 20)->nullable();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('clientes', function (Blueprint $table) {
        $table->dropForeign(['cep_id']);
        $table->dropColumn('cep_id');
        $table->dropColumn('lat');
        $table->dropColumn('lng');

        $table->string('logradouro', 255)->nullable();
        $table->string('complemento', 255)->nullable();
        $table->string('bairro', 255)->nullable();
        $table->string('cidade', 255)->nullable();
      });

      Schema::table('empresas', function (Blueprint $table) {
        $table->dropColumn('vlr_km');
        $table->dropColumn('tipo_taxa');
        $table->dropColumn('inicio_distancia');
        $table->dropColumn('cep');
        $table->dropColumn('logradouro');
        $table->dropColumn('numero');
        $table->dropColumn('complemento');
        $table->dropColumn('bairro');
        $table->dropColumn('cidade');
        $table->dropColumn('uf');
        $table->dropColumn('lat');
        $table->dropColumn('lng');
      });

      Schema::dropIfExists('ceps');
    }
};
