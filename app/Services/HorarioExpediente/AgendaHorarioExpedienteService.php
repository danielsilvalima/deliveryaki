<?php

namespace App\Services\HorarioExpediente;
use Illuminate\Support\Facades\DB;
use App\Models\AgendaHorarioExpediente;


class AgendaHorarioExpedienteService
{
  public function findByIDEmpresaResource()
  {
    return AgendaHorarioExpediente::select('id', 'dia_semana', 'descricao')->orderBy('id', 'ASC')->get();
  }
}
