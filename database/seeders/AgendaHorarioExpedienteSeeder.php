<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgendaHorarioExpedienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
      $diasSemanas = [
          ['dia_semana' => 0, 'descricao' => 'Domingo'],
          ['dia_semana' => 1, 'descricao' => 'Segunda-feira'],
          ['dia_semana' => 2, 'descricao' => 'Terca-feira'],
          ['dia_semana' => 3, 'descricao' => 'Quarta-feira'],
          ['dia_semana' => 4, 'descricao' => 'Quinta-feira'],
          ['dia_semana' => 5, 'descricao' => 'Sexta-feira'],
          ['dia_semana' => 6, 'descricao' => 'Sabado'],
      ];

      $existingDiaSemana = DB::table('agenda_horario_expedientes')->pluck('dia_semana')->toArray();

      // Filtrar os serviços que ainda não estão cadastrados
      $newDiaSemana = array_filter($diasSemanas, function ($diasSemana) use ($existingDiaSemana) {
          return !in_array(strtoupper($diasSemana['dia_semana']), $existingDiaSemana);
      });

      // Inserir os novos serviços que não foram encontrados no banco
      if (!empty($newDiaSemana)) {
          DB::table('agenda_horario_expedientes')->insert($newDiaSemana);
      }
    }
}
