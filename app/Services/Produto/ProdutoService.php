<?php

namespace App\Services\Produto;

use App\Models\Produto;

class ProdutoService
{
  public function findAllProductActiveByEmpresaID($empresa_id)
	{
		return Produto::select('produtos.id as id', 'produtos.descricao', 'produtos.vlr_unitario', 'categorias.descricao as categorias', 'produtos.apresentacao')->where('produtos.empresa_id', $empresa_id)
			->join('categorias', 'produtos.categoria_id', '=', 'categorias.id')
      ->orderBy('categorias.descricao', 'ASC')
      ->orderBy('produtos.descricao', 'ASC')->get();
	}
}
