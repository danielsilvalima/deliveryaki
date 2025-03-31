<?php

namespace App\Services\Produto;

use App\Models\Produto;
use App\Models\Empresa;
use App\Services\Empresa\EmpresaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdutoService
{
  public function findAllProductCategoryActiveByEmpresaID($id)
  {
    return Empresa::with([
      'categorias' => function ($query) {
        $query->where('status', 'A') // Filtra categorias ativas
          ->whereHas('produtos', function ($query) {
            $query->where('status', 'A'); // Garante que a categoria tem produtos ativos
          })
          ->orderBy('descricao', 'ASC')
          ->with([
            'produtos' => function ($query) {
              $query->where('status', 'A') // Filtra produtos ativos
                ->orderBy('descricao', 'ASC');
            },
          ]);
      },
    ])->where('id', $id)->first();
  }

  public function findAllProductActiveByEmpresaID($id)
  {
    $produto = Empresa::with([
      'produtos' => function ($query) {
        $query->where('status', 'A')
          ->orderBy('descricao', 'ASC');
      },
    ])->where('id', $id)->first();

    return $produto;
  }

  public function update(Request $request, Produto $produto, Empresa $empresa)
  {
    DB::beginTransaction();
    try {
      if ($request->hasFile('imagem')) {
        $this->deleteOldFile($produto->id);

        //$empresa = $empresaService->findByID($produto->empresa_id);

        $directory = "public/logos/produtos/{$empresa->cnpj}";
        $file = $request->file('imagem');
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs($directory, $filename);
        $produto->path = str_replace('public/', '', $filePath);
        $produto->save();
      }

      $produto->update($request->only([
        'descricao',
        'status',
        'vlr_unitario',
        'categoria_id',
        'apresentacao'
      ]));
      DB::commit();

      return $produto;
    } catch (\Exception $e) {
      DB::rollBack();
      //return back()->with('error', 'NÃO FOI POSSÍVEL ATUALIZAR O PRODUTO. ' . $e->getMessage());
      throw new \Exception("Erro ao cadastrar o produto: " . $e->getMessage());
    }
  }

  public function store($produto, $request, $empresa)
  {
    DB::beginTransaction();
    try {
      $produto = Produto::create($produto);

      if ($request->hasFile('imagem')) {
        $file = $request->file('imagem');
        $directory = "public/logos/produtos/{$empresa->cnpj}";
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs($directory, $filename);

        $produto->path = str_replace('public/', '', $filePath);
        $produto->save();
      }

      DB::commit();
      return $produto;
    } catch (\Exception $e) {
      DB::rollBack();
      throw new \Exception("Erro ao cadastrar o produto: " . $e->getMessage());
    }
  }

  public function deleteOldFile($id)
  {
    $produto = Produto::find($id);
    if ($produto && $produto->path) {
      $oldFilePath = storage_path("app/public/{$produto->path}");
      if (file_exists($oldFilePath)) {
        unlink($oldFilePath);
      } else {
        info("Arquivo não encontrado: " . $oldFilePath);
      }
    }
  }
}
