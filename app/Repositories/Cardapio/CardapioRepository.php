<?php

namespace App\Repositories\Cardapio;

use App\Models\Produto;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Auth;

class CardapioRepository
{
	private $model;

	public function __construct(Produto $model)
	{
		$this->model = $model;
	}

	public function findAllActiveByEmpresaID($empresa_id)
	{
		return $this->model->select('produtos.id as id', 'produtos.descricao', 'produtos.vlr_unitario', 'categorias.descricao as categorias')->where('produtos.empresa_id', $empresa_id)
			->join('categorias', 'produtos.categoria_id', '=', 'categorias.id')->get();
	}
}
