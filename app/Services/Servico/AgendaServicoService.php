<?php

namespace App\Services\Servico;
use Illuminate\Support\Facades\DB;
use App\Models\AgendaServico;


class AgendaServicoService
{
  public function findAll()
  {
    return AgendaServico::select('id', 'descricao')->orderBy('descricao', 'ASC')->get();
  }
}
