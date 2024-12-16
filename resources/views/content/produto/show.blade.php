@extends('layouts/contentNavbarLayout')

@section('title', ' Cadastro de Produto')

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

<div class="col-xl">
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Editar Cadastro de Produto</h5>
      <small class="text-muted float-end"></small>
    </div>
    <div class="card-body">
      <form action="{{ route('produto.edit', $produto->id) }}" method="POST">
        @csrf()
        @method('PUT')
        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-company2" class="input-group-text"></span>
          <input type="text" id="descricao" name="descricao" value="{{ $produto->descricao }}" class="form-control" placeholder="DESCRIÇÃO" aria-label="DESCRIÇÃO" required aria-describedby="basic-icon-default-company2"
          onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-company2" class="input-group-text"></span>
          <input type="text" id="apresentacao" name="apresentacao" value="{{ $produto->apresentacao }}" class="form-control" placeholder="APRESENTAÇÃO" aria-label="APRESENTAÇÃO" required aria-describedby="basic-icon-default-company2"
          onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-company2" class="input-group-text"></span>
          <input type="number" min="0" max="1000" step="any" id="vlr_unitario" name="vlr_unitario" value="{{ $produto->vlr_unitario }}" class="form-control" placeholder="VALOR UNITÁRIO" aria-label="VALOR UNITÁRIO" required aria-describedby="basic-icon-default-company2" />
        </div>

        <div class="form-floating form-floating-outline mb-4">
          <select class="form-select" id="categoria_id" name="categoria_id" aria-label="CATEGORIA" required>
            <option value="">SELECIONAR</option>
            @foreach($categorias as $categoria)
            @if ($categoria->id == $produto->categoria_id)
            <option selected value="{{ $categoria->id }}">{{ $categoria->descricao }}</option>
            @else
            <option value="{{ $categoria->id }}">{{ $categoria->descricao }}</option>
            @endif
            @endforeach
          </select>
          <label for="categoria_id">CATEGORIA</label>
        </div>

        <div class="form-floating form-floating-outline mb-4">
          <select class="form-select" id="status" name="status" aria-label="STATUS" required>
            <option selected value="A">ATIVADO</option>
            <option value="D">DESATIVADO</option>
          </select>
          <label for="status">STATUS</label>
        </div>

        <button type="submit" class="btn btn-primary">SALVAR</button>
        <a href="{{ route('produto.index') }}" class="btn btn-secondary">CANCELAR</a>
      </form>
    </div>
  </div>
</div>
@endsection
