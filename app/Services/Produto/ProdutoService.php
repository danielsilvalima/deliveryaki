<?php

namespace App\Services\Produto;

use App\Models\Produto;
use App\Models\Empresa;
use App\Services\Empresa\EmpresaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdutoService
{
  public function findAllProductActiveByEmpresaID($id)
  {
    $produto = Empresa::with([
      'categorias' => function ($query) {
        $query->orderBy('descricao', 'ASC')->with([
          'produtos' => function ($query) {
            $query->orderBy('descricao', 'ASC');
          },
        ]);
      },
      //'empresa_expedientes.horario_expedientes',
    ])
      ->where('id', $id)
      ->first();

    return $produto;
  }

  public function update(Request $request, Produto $produto, EmpresaService $empresaService)
  {
    DB::beginTransaction();
    try {
      if ($request->hasFile('logo')) {
        $this->deleteOldFile($produto->id);

        $empresa = $empresaService->findByID($produto->empresa_id);

        $directory = "public/logos/produtos/{$empresa->cnpj}";
        $file = $request->file('logo');
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs($directory, $filename);
        $produto->path = $recurso['path'] = str_replace('public/', '', $filePath);
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
      return back()->with('error', 'NÃO FOI POSSÍVEL ATUALIZAR O PRODUTO. ' . $e->getMessage());
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
