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
    Schema::table('categorias', function (Blueprint $table) {
      $table->boolean('qrcode_mesa')->nullable()->default(false);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('categorias', function (Blueprint $table) {
      $table->dropColumn('qrcode_mesa');
    });
  }
};
