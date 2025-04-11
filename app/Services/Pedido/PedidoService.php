<?php

namespace App\Services\Pedido;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Cep;
use App\Models\PedidoNotificacao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PedidoService
{
  public function validateCliente(array $cliente): void
  {
    if (empty($cliente['celular'])) {
      throw new \Exception('O campo "celular" é obrigatório.');
    }

    if (!preg_match('/^\d{10,11}$/', $cliente['celular'])) {
      throw new \Exception('O número de celular deve conter entre 10 e 11 dígitos.');
    }
  }

  public function validatePedido(array $entrega, array $itens): void
  {
    if (
      array_key_exists('tipo_pagamento', $entrega) &&
      $entrega['tipo_pagamento'] !== null &&
      !in_array($entrega['tipo_pagamento'], ['cr', 'de', 'pi', 'di'])
    ) {
      throw new \Exception('O tipo de pagamento informado é inválido.');
    }

    if (empty($entrega['vlr_total']) || $entrega['vlr_total'] <= 0) {
      throw new \Exception('O "vlr_total" deve ser maior que zero.');
    }

    if (empty($itens)) {
      throw new \Exception('O pedido deve conter ao menos um item.');
    }

    /*foreach ($itens as $index => $item) {
          if (empty($item['id']) || empty($item['qtd']) || empty($item['valor'])) {
              throw new InvalidArgumentException("O item #{$index} está incompleto.");
          }
      }*/
  }

  public function createPedido(array $clienteData, array $entregaData, array $itensData)
  {
    DB::beginTransaction();

    try {
      // Validações
      //Necessário setar null para o modulo qrcode de mesas
      if (!isset($entregaData['tipo_pagamento']) || $entregaData['tipo_pagamento'] === '' || $entregaData['tipo_pagamento'] === false) {
        $entregaData['tipo_pagamento'] = null;
      }
      $this->validateCliente($clienteData);
      $this->validatePedido($entregaData, $itensData);

      // Consulta ou criação do CEP
      $cep = Cep::firstOrCreate(
        ['cep' => $clienteData['cep']],
        [
          'logradouro' => $clienteData['logradouro'],
          'bairro' => $clienteData['bairro'],
          'complemento' => $clienteData['complemento'],
          'cidade' => $clienteData['cidade'],
          'uf' => $clienteData['uf'],
        ]
      );

      // Criação ou atualização do cliente
      $cliente = Cliente::where('celular', $clienteData['celular'])
        ->where('empresa_id', $clienteData['empresa_id'])
        ->first();
      if ($cliente) {
        $clienteData['cep_id'] = $cep->id;
        $cliente->update([
          'nome_completo' => $clienteData['nome_completo'],
          'cep' => $clienteData['cep'],
          'numero' => $clienteData['numero'],
          'celular' => $clienteData['celular'],
          'empresa_id' => $clienteData['empresa_id'],
          'cep_id' => $cep->id,
        ]);
      } else {
        $cliente = Cliente::create([
          'nome_completo' => $clienteData['nome_completo'],
          'cep' => $clienteData['cep'],
          'numero' => $clienteData['numero'],
          'celular' => $clienteData['celular'],
          'empresa_id' => $clienteData['empresa_id'],
          'cep_id' => $cep->id,
        ]);
      }

      // Criação do pedido
      $pedido = new Pedido([
        'status' => 'A',
        'tipo_pagamento' => $entregaData['tipo_pagamento'] !== null ? strtoupper($entregaData['tipo_pagamento']) : null,
        'tipo_entrega' => strtoupper($entregaData['tipo_entrega']),
        'vlr_taxa' => floatval($entregaData['vlr_taxa']),
        'vlr_total' => floatval($entregaData['vlr_total']),
        //'deliver_at' => $entregaData['horario_entrega'],
        'cliente_id' => $cliente->id,
        'empresa_id' => $entregaData['empresa_id'],
      ]);
      $pedido->save();

      //Inserir token da notificacao
      $pedido_notificacao = new PedidoNotificacao([
        'token_notificacao' => $entregaData['token_notificacao'],
        'pedido_id' => $pedido->id,
        'empresa_id' => $entregaData['empresa_id'],
      ]);
      $pedido_notificacao->save();

      // Preparar os itens para inserção em massa
      $itens = array_map(function ($itemData) use ($pedido, $cliente, $entregaData) {
        return [
          'produto_id' => $itemData['id'],
          'qtd' => intval($itemData['qtd']),
          'vlr_unitario' => floatval($itemData['vlr_unitario']),
          'vlr_total' => floatval($itemData['vlr_total']),
          'pedido_id' => $pedido->id,
          'cliente_id' => $cliente->id,
          'empresa_id' => $entregaData['empresa_id'],
        ];
      }, $itensData);

      // Inserir os itens em massa
      $pedido->pedido_items()->createMany($itens);

      DB::commit();

      return $pedido;
    } catch (\Exception $e) {
      DB::rollBack();
      throw new \Exception('Erro ao criar o pedido: ' . $e->getMessage());
    }
  }

  public function buscaPedidosPorData($data_inicio = null, $data_fim = null, $tipo_entrega = null, $status = 'A')
  {
    try {

      $query = Pedido::with([
        'cliente', // Relacionamento com clientes
        'cliente.ceps', // Relacionamento com ceps
        'pedido_items' // Relacionamento com pedido_items
      ])
        ->where('empresa_id', Auth::user()->empresa_id)
        ->where('status', $status)
        ->orderBy('id', 'ASC');

      if (!empty($tipo_entrega)) {
        $query->where('tipo_entrega', $tipo_entrega);
      }

      // Se as datas forem preenchidas, aplica o filtro
      if (!empty($data_inicio) && !empty($data_fim)) {
        $query->whereBetween('created_at', [$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59']);
      }

      $pedidos = $query->get();

      return $pedidos;
    } catch (\Exception $e) {

      return back()->with('error', 'ERRO AO BUSCAR OS PEDIDOS. ' . $e->getMessage());
    }
  }

  public function buscaPedidosPorID($id)
  {
    try {

      $pedidos = Pedido::with([
        'cliente', // Relacionamento com clientes
        'cliente.ceps', // Relacionamento com ceps
        'pedido_items.produto' // Relacionamento com pedido_items
      ])
        ->where('empresa_id', Auth::user()->empresa_id)
        ->where('id', $id)->first();

      if (!$pedidos) {
        return back()->with('error', 'PEDIDO NÃO ENCONTRADO.');
      }

      return $pedidos;
    } catch (\Exception $e) {

      return back()->with('error', 'ERRO AO BUSCAR OS PEDIDOS. ' . $e->getMessage());
    }
  }
}
