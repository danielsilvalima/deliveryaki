@extends('layouts/contentNavbarLayout')

@section('title', 'Pedidos')

@section('content')

<div class="toast-container position-fixed bottom-0 end-0 p-3">
  @if(session('success'))
  <div class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        {{ session('success') }}
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
  @endif

  @if(session('error'))
  <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        {{ session('error') }}
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
  @endif
</div>


<script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>

<div class="card">
  <div class="card-body">
    <form id="form" action="{{ route('pedido.postPedido') }}" method="POST">
      @csrf()
      <div class="row g-2">
        <div class="col-md-2">
          <input class="form-control" type="date" id="data_inicio" name="data_inicio" value="{{ $data_inicio ?? '' }}" required placeholder="DATA INICIAL" />
        </div>

        <div class="col-md-2">
          <input class="form-control" type="date" id="data_fim" name="data_fim" value="{{ $data_fim ?? '' }}" required placeholder="DATA FINAL" />
        </div>

        <div class="col-md-2">
          <div class="form-floating-outline ">
            <select class="form-select" id="tipo_entrega" name="tipo_entrega" aria-label="TIPO ENTREGA">
              <option value="E" {{ $tipo_entrega == "E" ? "selected" : '' }}>ENTREGA</option>
              <option value="R" {{ $tipo_entrega == "R" ? "selected" : '' }}>RETIRA</option>
            </select>
          </div>
        </div>

        <div class="col-md-2">
          <div class="form-floating-outline mb-3">
            <select class="form-select" id="status" name="status" aria-label="STATUS">
              <option value="A" {{ $status == "A" ? "selected" : '' }}>ATIVADO</option>
              <option value="C" {{ $status == "C" ? "selected" : '' }}>CANCELADO</option>
              <option value="P" {{ $status == "P" ? "selected" : '' }}>PENDENTE</option>
              <option value="S" {{ $status == "S" ? "selected" : '' }}>SAIU P/ ENTREGA</option>
              <option value="E" {{ $status == "E" ? "selected" : '' }}>ENTREGUE</option>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <button type="submit" id="submitButton" class="btn btn-outline-primary w-100" style="width: 120px;">
            <span id="buttonText">PESQUISAR</span>
            <span id="spinner" class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true" style="display: none;"></span>
          </button>
        </div>
      </div>
    </form>
    <script>
      document.getElementById('form').addEventListener('submit', function() {
        var submitButton = document.getElementById('submitButton');
        var buttonText = document.getElementById('buttonText');
        var spinner = document.getElementById('spinner');

        buttonText.style.display = 'none'; // Esconde o texto
        spinner.style.display = 'inline-block'; // Mostra o spinner
        submitButton.disabled = true;
      });
    </script>
  </div>
</div>

<div class="card mt-4">
  <h5 class="card-header">PEDIDOS</h5>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead class="table-dark">
        <tr>
          <th style="width: 1%;"></th>
          <th style="width: 5%;">NÚMERO</th>
          <th style="width: 20%;">CLIENTE</th>
          <th style="width: 50%;">ENDEREÇO</th>
          <th style="width: 5%;">DATA</th>
          <th style="width: 5%;">VLR TAXA</th>
          <th style="width: 5%;">VLR TOTAL</th>
          <th style="width: 10%;">TIPO ENTREGA</th>
          <th style="width: 10%;">STATUS</th>
          <th style="width: 10%;">AÇÕES</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        @foreach($pedidos as $pedido)
        <tr>
          <td>
            <button type="button" class="btn btn-link p-0" onclick="toggleDetails({{ $pedido->id }})">
              <i class="mdi mdi-eye-outline"></i>
            </button>
          </td>
          <td>{{ $pedido->id }}</td>
          <td>{{ $pedido->cliente->nome_completo }}</td>
          <td>{{ $pedido->cliente->ceps->logradouro }}, {{ $pedido->cliente->numero }} - {{ $pedido->cliente->ceps->bairro }}
          </td>
          <td>{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
          <td style="text-align: right;">{{ number_format($pedido->vlr_taxa , 2, ',', '.')}}</td>
          <td style="text-align: right;">{{ number_format($pedido->vlr_total, 2, ',', '.') }}</td>
          <td style="text-align: center;">{!! $pedido->tipo_entrega == "E"
            ? '<span class="badge rounded-pill bg-label-warning me-1">ENTREGA</span>'
            : ($pedido->tipo_entrega == "R" ? '<span class="badge rounded-pill bg-label-danger me-1">RETIRA</span>' : '') !!}</td>
          <td>
            <span class="badge rounded-pill
              {{ $pedido->status == 'C' ? 'bg-label-danger' :
                ($pedido->status == 'A' ? 'bg-label-primary' :
                ($pedido->status == 'P' ? 'bg-label-warning' :
                ($pedido->status == 'S' ? 'bg-label-warning' :
                ($pedido->status == 'E' ? 'bg-label-success' : '')))) }} me-1">
              {{ $pedido->status == 'C' ? 'CANCELADO' :
                ($pedido->status == 'A' ? 'ATIVADO' :
                ($pedido->status == 'P' ? 'PENDENTE' :
                ($pedido->status == 'S' ? 'SAIU P/ ENTREGA' :
                ($pedido->status == 'E' ? 'ENTREGUE' : '')))) }}
            </span>
          </td>

          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="javascript:void(0);"
                  onclick="openEditModal({{ $pedido->id }}, {{ json_encode($pedido->pedido_items) }}, '{{ $pedido->status }}', '{{ $pedido->tipo_entrega }}', '{{ $pedido->tipo_pagamento }}')">
                  <i class="mdi mdi-pencil-outline me-1"></i> EDITAR
                </a>
                <a class="dropdown-item" href="{{ route('pedido.show', $pedido->id) }}"><i class="mdi mdi-eye-outline me-1"></i> VER</a>

              </div>
            </div>
          </td>
        </tr>
        <tr id="details-{{ $pedido->id }}" style="display: none;">
          <td colspan="7">
            <div class="details-content">
              <!-- Coloque aqui os detalhes do pedido -->
              <p><strong>Detalhes:</strong> </p>
              <ul>
                @foreach($pedido->pedido_items as $item)
                <li>{{ $item->descricao }} / VLR. UNITÁRIO R$ {{ number_format($item->vlr_unitario, 2, ',', '.') }} X {{ $item->qtd }} QTD = R$ {{ number_format($item->vlr_total, 2, ',', '.') }}</li>
                @endforeach
              </ul>
            </div>
          </td>
        </tr>
        @endforeach
        <script>
          function toggleDetails(id) {
            var row = document.getElementById('details-' + id);
            if (row.style.display === 'none') {
              row.style.display = 'table-row';
            } else {
              row.style.display = 'none';
            }
          }
        </script>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="basicModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="editPedidoForm" method="POST" data-route="{{ route('pedido.updateStatus', ['id' => '__ID__']) }}">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title" id="exampleModalLabel1">EDITAR STATUS DO PEDIDO</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p><strong>NÚMERO DO PEDIDO: </strong> <span id="pedido-id"></span></p>
          <p><strong>ITENS DO PEDIDO: </strong></p>
          <ul id="pedido-itens"></ul>
          <p><strong>TIPO DE PAGAMENTO:</strong> <span id="tipo-pagamento"></span></p>
          <p><strong>TIPO DE ENTREGA:</strong> <span id="tipo-entrega"></span></p>
          <p><strong>STATUS DO PEDIDO: </strong></p>
          <div id="status-container"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">CANCELAR</button>
          <button id="submitButton" type="submit" class="btn btn-primary">SALVAR ALTERAÇÕES</button>
        </div>
      </div>
    </form>
    <script>
      document.getElementById('editPedidoForm').addEventListener('submit', function() {
        var submitButton = document.getElementById('submitButton');
        submitButton.innerHTML = '<span class="spinner-border spinner-border-lg text-primary" role="status" aria-hidden="true"></span>';
        submitButton.disabled = true;
      });
    </script>
  </div>
</div>
<script src="{{ asset('assets/js/modal-pedido.js') }}"></script>


@endsection