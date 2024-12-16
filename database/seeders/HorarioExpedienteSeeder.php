<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HorarioExpedienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
      $diasSemana = [
          ['dia_semana' => 0, 'descricao' => 'Domingo'],
          ['dia_semana' => 1, 'descricao' => 'Segunda-feira'],
          ['dia_semana' => 2, 'descricao' => 'Terca-feira'],
          ['dia_semana' => 3, 'descricao' => 'Quarta-feira'],
          ['dia_semana' => 4, 'descricao' => 'Quinta-feira'],
          ['dia_semana' => 5, 'descricao' => 'Sexta-feira'],
          ['dia_semana' => 6, 'descricao' => 'Sabado'],
      ];

      DB::table('horario_expedientes')->insert($diasSemana);
    }
}
