<?php

namespace App\Http\Controllers\Produto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StoreProduto;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StoreProdutoController extends Controller
{
  public function store(Request $request)
  {
    $validatedData = $request->validate([
      //'id' => 'nullable|exists:store_produtos,id', // Permite edição se o ID existir
      'descricao' => 'required|string|max:255',
      'url' => 'required|url|max:255',
      'url_afiliado' => 'nullable|url|max:255',
      'valor' => 'required|numeric|min:0',
      //'url_imagem' => 'nullable|file|mimes:jpeg,png,jpg|max:2048'
    ]);

    try {
      $produto = DB::transaction(function () use ($validatedData, $request) {
        if (!empty($request->id)) {
          // Se ID foi passado, busca o produto para atualização
          $produto = StoreProduto::findOrFail($request->id);

          // Remover imagem antiga se uma nova for enviada
          if ($request->hasFile('url_imagem') && $produto->url_imagem) {
            $oldImagePath = storage_path('app/public/' . $produto->url_imagem);
            if (file_exists($oldImagePath)) {
              unlink($oldImagePath);
            }
          }

          // Atualizar os campos (menos ID)
          $produto->update([
            'descricao' => $request['descricao'],
            'url' => $request['url'],
            'url_afiliado' => $request['url_afiliado'] ?? null,
            'texto_personalizado' => $request['texto_personalizado'],
            'categoria' => $request['categoria'],
            'valor' => $request['valor'],
            'valor_promocional' => $request['valor_promocional'],
            'frete_gratis' => filter_var($request->frete_gratis, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'promocao' => filter_var($request->promocao, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'ativo' => filter_var($request->ativo, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
          ]);
        } else {
          // Criar novo produto
          $produto = StoreProduto::create([
            'descricao' => $request['descricao'],
            'url' => $request['url'],
            'url_afiliado' => $request['url_afiliado'] ?? null,
            'texto_personalizado' => $request['texto_personalizado'],
            'categoria' => $request['categoria'],
            'valor' => $request['valor'],
            'valor_promocional' => $request['valor_promocional'],
            'frete_gratis' => filter_var($request->frete_gratis, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'promocao' => filter_var($request->promocao, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'ativo' => filter_var($request->ativo, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
          ]);
        }

        // Gerenciar imagem
        if ($request->hasFile('url_imagem')) {
          $basePath = storage_path('app/public/store/produto');
          $produtoPath = $basePath . '/' . $produto->id;

          if (!file_exists($produtoPath)) {
            mkdir($produtoPath, 0777, true);
          }

          $arquivo = $request->file('url_imagem');
          $nomeArquivo = time() . '_' . $arquivo->getClientOriginalName();
          $arquivo->move($produtoPath, $nomeArquivo);

          $produto->update(['url_imagem' => 'store/produto/' . $produto->id . '/' . $nomeArquivo]);
        }

        return $produto;
      });

      return response()->json($produto, Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }


  public function storeClique(Request $request)
  {
    DB::beginTransaction();
    try {
      $produto = $request->input('produto');

      $produto_db = StoreProduto::find($produto['id']);

      if ($produto_db) {
        $produto_db->cliques += 1;
        $produto_db->save();
      }

      DB::commit();
      return ResponseHelper::success([]);
    } catch (\Exception $e) {
      DB::rollBack();
      return ResponseHelper::error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function delete(Request $request, $id)
  {
    DB::beginTransaction();
    try {
      $produto_db = StoreProduto::find($id);

      if ($produto_db) {
        $produto_db->ativo = false;
        $produto_db->save();
      }

      DB::commit();
      return response()->json(
        Response::HTTP_OK
      );
    } catch (\Exception $e) {
      DB::rollBack();
      return ResponseHelper::error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function getBanner(Request $request)
  {
    try {
      $directory = storage_path('app/public/store/banner');
      if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
      }

      $files = array_diff(scandir($directory), ['.', '..']);


      $banners = array_map(fn($file) => url("/store/banner/$file"), $files);

      return response()->json($banners, Response::HTTP_OK);
    } catch (\Exception $e) {
      return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  public function get(Request $request)
  {
    try {
      $query = StoreProduto::query();
      if (is_null($request->input('ativo'))) {
        $query->where('ativo', true);
      }

      if (!is_null($request->input('descricao'))) {
        $query->where('descricao', 'LIKE', '%' . $request->input('descricao') . '%');
      }

      if (!is_null($request->input('categoria'))) {
        if (
          $request->input('categoria') !== "Relevância" &&
          $request->input('categoria') !== "Todos" &&
          $request->input('categoria') !== "Ofertas"
        ) {
          $query->where('categoria', 'LIKE', '%' . $request->input('categoria') . '%');
        }
      }

      $ordenacoes = [
        'popularidade'   => ['cliques', 'desc'],
        'preco_asc'      => ['valor', 'asc'],
        'preco_desc'     => ['valor', 'desc'],
        'desconto_desc'  => ['desconto', 'desc'],
        'novidades'      => ['created_at', 'desc'],
        'mais_vendidos'  => ['cliques', 'desc'],
        'id_asc'         => ['id', 'asc'],
      ];

      $orderBy = $request->input('order.value', 'novidades');

      if (array_key_exists($orderBy, $ordenacoes)) {
        [$coluna, $direcao] = $ordenacoes[$orderBy];
        $query->orderBy($coluna, $direcao);
      }

      $totalProdutos = $query->count();
      if (!is_null($request->input('limit'))) {
        $limit = $request->input('limit');
      } else {
        $limit = $totalProdutos;
      }
      $page = $request->input('page', 1);

      $produtosPaginados = $query->paginate($limit, ['*'], 'page', $page);

      return response()->json([
        'produtos' => $produtosPaginados->items(),
        'totalPages' => ceil($totalProdutos / $limit),
        'currentPage' => $produtosPaginados->currentPage(),
        'totalProdutos' => $totalProdutos,
      ], Response::HTTP_OK);
    } catch (\Exception $e) {
      return ResponseHelper::error($e->getMessage());
    }
  }

  public function getByID(Request $request, $id)
  {
    try {
      $produto = StoreProduto::find($id);

      return response()->json(
        $produto,
        Response::HTTP_OK
      );
    } catch (\Exception $e) {
      DB::rollBack();
      return ResponseHelper::error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }
}
