@extends('layouts/contentNavbarLayout')

@section('title', 'Pedido')

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


<div src="https://cdn.datatables.net/2.0.7/js/dataTables.js">
  </script>

  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-8">
          <div class="form-floating form-floating-outline mb-3">
            <input type="text" id="nome_completo" name="nome_completo" disabled class="form-control" placeholder="NOME COMPLETO" aria-label="NOME COMPLETO"
              aria-describedby="basic-icon-default-company2" value="{{ $pedido->cliente->nome_completo }}"
              onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
            <label for="nome_completo">NOME COMPLETO</label>
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-floating form-floating-outline mb-3">
            <input type="text" id="celular" name="celular" disabled value="{{ $pedido->cliente->celular }}" class="form-control phone-mask" placeholder="WHATSAPP" aria-label="WHATSAPP"
              aria-describedby="basic-icon-default-phone2"
              onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
            <label for="celular">WHATSAPP</label>
          </div>
        </div>

        <div class="col-md-8">
          <div class="form-floating form-floating-outline mb-3">
            <select class="form-select" id="produto" name="produto" aria-label="PRODUTOS">
              <option value="">SELECIONE UM PRODUTO</option>
              @foreach($produtos as $produto)
              <option value="{{ $produto->id }}">{{ $produto->descricao }} - R$ {{ str_replace('.', ',', $produto->vlr_unitario)}}</option>
              @endforeach
            </select>
            <label for="produto">PRODUTO</label>
          </div>
        </div>
      </div>


      <button type="button" id="submitButton" class="btn btn-outline-primary" style="width: 120px;">
        <span type="button" id="add-product">ADICIONAR</span>
        <span id="spinner" class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true" style="display: none;"></span>
      </button>

    </div>
  </div>

  <div class="card mt-4">
    <h5 class="card-header">PRODUTOS</h5>
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead class="table-dark">
          <tr>
            <th style="width: 1%;">ID</th>
            <th style="width: 20%;">DESCRIÇÃO</th>
            <th style="width: 20%;">APRESENTAÇÃO</th>
            <th style="width: 10%;">VLR UNIT</th>
            <th style="width: 5%;">QTD</th>
            <th style="width: 10%;">VLR TOTAL</th>
            <th style="width: 10%;">AÇÕES</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0" id="table-body-pedido-items">
        </tbody>
      </table>
    </div>
    <div id="existingPedidoData" style="display: none;">
      @json($pedido)
    </div>
    <div id="existingProdutosData" style="display: none;">
      @json($produtos)
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-body d-flex justify-content-end gap-2">
      <form id="salvarForm" action="{{ route('pedido.update', $pedido->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div id="listaProdutos" type="hidden"></div>
        <button type="submit" id="submitButton" class="btn btn-primary" style="width: 120px; ">
          <span id="buttonText">SALVAR</span>
          <span id="spinner" class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true" style="display: none;"></span>
        </button>
      </form>

      <a href="{{ route('pedido.index') }}" class="btn btn-secondary " style="width: 150px; ">CANCELAR</a>
    </div>
  </div>

  <script src="{{ asset('assets/js/pedido.js') }}"></script>



  @endsection