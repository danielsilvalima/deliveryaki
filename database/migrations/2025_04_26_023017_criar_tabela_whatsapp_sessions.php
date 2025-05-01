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
    Schema::create('whatsapp_sessions', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('empresa_id')->index();
      $table->string('session_name')->unique();
      $table->enum('status', ['pendente', 'ativo', 'desconectado'])->default('pendente');
      $table->longText('qr_code_base64')->nullable();
      $table->timestamp('last_connected_at')->nullable();
      $table->timestamps();

      $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('whatsapp_sessions');
  }
};
