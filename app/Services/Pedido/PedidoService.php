<?php

namespace App\Services\Pedido;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Cep;
use App\Models\PedidoNotificacao;
use Illuminate\Support\Facades\DB;

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
      if (empty($entrega['tipo_pagamento'])) {
          throw new \Exception('O campo "tipo_pagamento" é obrigatório.');
      }

      if (empty($entrega['valor_total']) || $entrega['valor_total'] <= 0) {
          throw new \Exception('O "valor_total" deve ser maior que zero.');
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
          $cliente = Cliente::where('celular', $clienteData['celular'])->where('empresa_id', $clienteData['empresa_id'])->first();
          if ($cliente) {
            $clienteData['cep_id'] = $cep->id;
            $cliente->update([
              'nome_completo' => $clienteData['nome_completo'],
                'cep' => $clienteData['cep'],
                'numero' => $clienteData['numero'],
                'celular' => $clienteData['celular'],
                'empresa_id' => $clienteData['empresa_id'],
                'cep_id' => $cep->id
            ]);
          } else {
              $cliente = Cliente::create([
                'nome_completo' => $clienteData['nome_completo'],
                'cep' => $clienteData['cep'],
                'numero' => $clienteData['numero'],
                'celular' => $clienteData['celular'],
                'empresa_id' => $clienteData['empresa_id'],
                'cep_id' => $cep->id
              ]);
          }

          // Criação do pedido
          $pedido = new Pedido([
            'status' => 'A',
            'tipo_pagamento' => strtoupper($entregaData['tipo_pagamento']),
            'tipo_entrega' => strtoupper($entregaData['tipo_entrega']),
            'vlr_taxa' => floatval($entregaData['valor_taxa']),
            'vlr_total' => floatval($entregaData['valor_total']),
            //'deliver_at' => $entregaData['horario_entrega'],
            'cliente_id' => $cliente->id,
            'empresa_id' => $entregaData['empresa_id']
          ]);
          $pedido->save();

          //Inserir token da notificacao
          $pedido_notificacao = new PedidoNotificacao([
            'token_notificacao' => $entregaData['token_notificacao'],
            'pedido_id' => $pedido->id,
            'empresa_id' => $entregaData['empresa_id']
          ]);
          $pedido_notificacao->save();

          // Preparar os itens para inserção em massa
          $itens = array_map(function ($itemData) use ($pedido, $cliente, $entregaData) {
            return [
                'produto_id' => $itemData['id'],
                'qtd' => intval($itemData['qtd']),
                'vlr_unitario' => floatval($itemData['valor_unitario']),
                'vlr_total' => floatval($itemData['valor']),
                'pedido_id' => $pedido->id,
                'cliente_id' => $cliente->id,
                'empresa_id' => $entregaData['empresa_id']
            ];
        }, $itensData);

        // Inserir os itens em massa
        $pedido->pedido_items()->createMany($itens);

          DB::commit();

          return $pedido;
      } catch (\Exception $e) {
          DB::rollBack();
          throw new \Exception('ERRO AO CRIAR O PEDIDO: ' . $e->getMessage());
      }
  }
}