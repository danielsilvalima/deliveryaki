<?php

namespace App\Services\EmpresaExpediente;
use Illuminate\Support\Facades\DB;
use App\Models\EmpresaExpediente;


class EmpresaExpedienteService
{
  public function findAllByEmpresaID($empresa_id)
  {
    return EmpresaExpediente::select('empresa_expedientes.*', 'horario_expedientes.dia_semana', 'horario_expedientes.descricao')
      //->where('empresa_expedientes.empresa_id', $empresa_id)
			->join('horario_expedientes', 'empresa_expedientes.horario_expediente_id', '=', 'horario_expedientes.id')
      ->orderBy('horario_expedientes.id', 'ASC')->get();
  }
}
