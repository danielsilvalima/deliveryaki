<?php

namespace App\Repositories\Produto;

use App\Models\Produto;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Auth;

class ProdutoRepository
{
	private $model;

	public function __construct(Produto $model)
	{
		$this->model = $model;
	}

	public function findAllActiveByEmpresaID($empresa_id)
	{
    return $this->model->select('descricao')->where('empresa_id', '=', $empresa_id)->where('status', '=', 'A')->get();
	}

}
