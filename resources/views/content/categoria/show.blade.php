@extends('layouts/contentNavbarLayout')

@section('title', ' Cadastro de Categoria')

@section('content')

<div class="col-xl">
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Editar Cadastro de Categoria</h5>
      <small class="text-muted float-end"></small>
    </div>
    <div class="card-body">
      <form action="{{ route('categoria.edit', $categoria->id) }}" method="POST">
        @csrf()
        @method('PUT')

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-company2" class="input-group-text"></span>
          <input type="text" id="descricao" name="descricao" class="form-control" placeholder="DESCRIÇÃO" aria-label="DESCRIÇÃO"
          aria-describedby="basic-icon-default-company2" value="{{ $categoria->descricao }}"
          onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="form-floating form-floating-outline mb-4">
          <select class="form-select" id="status" name="status" aria-label="STATUS" >
            <option value="A" {{ $categoria->status == "A" ? "selected" : '' }}">ATIVADO</option>
            <option value="D" {{ $categoria->status == "D" ? "selected" : '' }}>DESATIVADO</option>
            <label for="status">STATUS</label>
          </select>
        </div>

        <button type="submit" class="btn btn-primary">SALVAR</button>
        <a href="{{ route('categoria.index') }}" class="btn btn-secondary">CANCELAR</a>
      </form>
    </div>
  </div>
</div>
@endsection
