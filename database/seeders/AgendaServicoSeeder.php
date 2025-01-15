<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AgendaServicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Sugestões de serviços para a tabela agenda_servicos
      $services = [
          ['descricao' => 'CORTE DE CABELO', 'status' => 'A'],
          ['descricao' => 'MANICURE', 'status' => 'A'],
          ['descricao' => 'PEDICURE', 'status' => 'A'],
          ['descricao' => 'CORTE DE BARBA', 'status' => 'A'],
          ['descricao' => 'BARBA + SOMBRANCELHA', 'status' => 'A'],
          ['descricao' => 'HIDRATAÇÃO CAPILAR', 'status' => 'A'],
          ['descricao' => 'ESCOVA PROGRESSIVA', 'status' => 'A'],
          ['descricao' => 'COLORAÇÃO DE CABELO', 'status' => 'A'],
          ['descricao' => 'ALISAMENTO CAPILAR', 'status' => 'A'],
          ['descricao' => 'DESIGN DE SOBRANCELHAS', 'status' => 'A'],
      ];

      $existingService = DB::table('agenda_servicos')->pluck('descricao')->toArray();

      // Filtrar os serviços que ainda não estão cadastrados
      $newService = array_filter($services, function ($service) use ($existingService) {
          return !in_array(strtoupper($service['descricao']), $existingService);
      });

      // Inserir os novos serviços que não foram encontrados no banco
      if (!empty($newService)) {
          DB::table('agenda_servicos')->insert($newService);
      }
    }
}
