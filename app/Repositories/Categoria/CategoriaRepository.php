<?php

namespace App\Repositories\Categoria;

use App\Models\Categoria;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Auth;

class CategoriaRepository
{
	private $model;

	public function __construct(Categoria $model)
	{
		$this->model = $model;
	}

	public function findAllActiveByEmpresaID($empresa_id)
	{
    return $this->model->where('empresa_id', '=', $empresa_id)->where('status', '=', 'A')->get();
	}


}
