<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MesaSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run()
  {
    $conexao = DB::connection('mysql');

    // Gera 50 mesas: Mesa 01, Mesa 02, ..., Mesa 50
    $mesas = [];
    for ($i = 1; $i <= 50; $i++) {
      $descricao = 'Mesa ' . str_pad($i, 2, '0', STR_PAD_LEFT);
      $mesas[] = ['descricao' => $descricao];
    }

    // Recupera descrições já existentes na conexão mysql
    $descricoesExistentes = $conexao->table('mesas')->pluck('descricao')->toArray();

    // Filtra apenas as que ainda não existem
    $novasMesas = array_filter($mesas, function ($mesa) use ($descricoesExistentes) {
      return !in_array($mesa['descricao'], $descricoesExistentes);
    });

    // Insere apenas as novas
    if (!empty($novasMesas)) {
      $conexao->table('mesas')->insert($novasMesas);
    }
  }
}
