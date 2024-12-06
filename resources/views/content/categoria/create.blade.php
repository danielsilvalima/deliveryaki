@extends('layouts/contentNavbarLayout')

@section('title', ' Cadastro de Categoria')

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
      <h5 class="mb-0">Novo Cadastro de Categoria</h5>
      <small class="text-muted float-end"></small>
    </div>
    <div class="card-body">
      <form action="{{ route('categoria.store') }}" method="POST">
        @csrf()

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-company2" class="input-group-text"></span>
          <input type="text" id="descricao" name="descricao" class="form-control" placeholder="DESCRIÇÃO" aria-label="DESCRIÇÃO"
          aria-describedby="basic-icon-default-company2"
          onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="form-floating form-floating-outline mb-4">
          <select class="form-select" id="status" name="status" aria-label="STATUS" >
            <option selected value="A">ATIVADO</option>
            <option value="D">DESATIVADO</option>
          </select>
          <label for="status">STATUS</label>
        </div>

        <button type="submit" class="btn btn-primary">SALVAR</button>
        <a href="{{ route('categoria.index') }}" class="btn btn-secondary">CANCELAR</a>
      </form>
    </div>
  </div>
</div>
@endsection
