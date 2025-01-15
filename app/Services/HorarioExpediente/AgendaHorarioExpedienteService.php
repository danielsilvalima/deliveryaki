<?php

namespace App\Services\HorarioExpediente;
use Illuminate\Support\Facades\DB;
use App\Models\AgendaHorarioExpediente;


class AgendaHorarioExpedienteService
{
  public function findAll()
  {
    return AgendaHorarioExpediente::select('id', 'dia_semana', 'descricao')->orderBy('id', 'ASC')->get();
  }
}
