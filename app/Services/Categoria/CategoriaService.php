<?php

namespace App\Services\Categoria;

use App\Models\Categoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CategoriaService
{
  public function findAllActiveByEmpresaID($empresa_id)
	{
    return Categoria::where('empresa_id', '=', $empresa_id)->where('status', '=', 'A')->orderBy('descricao', 'ASC')->get();
	}
}
