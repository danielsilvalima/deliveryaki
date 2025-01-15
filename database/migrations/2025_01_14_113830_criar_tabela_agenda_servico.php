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
      Schema::create('agenda_servicos', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
        $table->string('descricao', 100);
        $table->enum('status', ['A', 'D'])->default('A');
      });
    }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
      Schema::dropIfExists('agenda_servicos');
  }
};
