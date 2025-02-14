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
    Schema::table('virtual_transacaos', function (Blueprint $table) {
      $table->string('chat_id', 255)->nullable();
      $table->string('message_id', 255)->nullable();
    });

    Schema::table('virtual_users', function (Blueprint $table) {
      $table->dropColumn('chat_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('virtual_transacaos', function (Blueprint $table) {
      $table->dropColumn('chat_id');
      $table->dropColumn('message_id');
    });

    Schema::table('virtual_users', function (Blueprint $table) {
      $table->string('chat_id');
    });
  }
};
