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
      Schema::create('pedido_notificacaos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('empresa_id')->constrained('empresas');
        $table->unsignedBigInteger('pedido_id');
        $table->foreign('pedido_id')->references('id')->on('pedidos');
        $table->string('token_notificacao', 255)->nullable();
        $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::dropIfExists('pedido_notificacao');
    }
};
