<?php

namespace App\Services\HorarioExpediente;
use Illuminate\Support\Facades\DB;
use App\Models\HorarioExpediente;


class HorarioExpedienteService
{
  public function findAll()
  {
    return HorarioExpediente::select('*')->orderBy('id', 'ASC')->get();
  }
}
