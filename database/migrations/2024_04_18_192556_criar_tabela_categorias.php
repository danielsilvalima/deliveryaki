<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('categorias', function (Blueprint $table) {
      $table->id('id');
      $table->string('descricao');
      $table->enum('status', ['A', 'D']);
      $table
        ->timestamp('created_at')
        ->useCurrent()
        ->nullable();
      $table
        ->timestamp('updated_at')
        ->useCurrent()
        ->nullable();
      //$table->integer('empresa_id')->unsigned();
      $table->unsignedBigInteger('empresa_id');
      $table->foreign('empresa_id')->references('id')->on('empresas');
      //$table->unsignedBigInteger('empresa_id'); // Alterado

      /*Schema::table('categorias', function (Blueprint $table) {
        $table->foreign('empresa_id')->references('id')->on('empresas');*/
    //});
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
      Schema::dropIfExists('categorias');
  }
};
