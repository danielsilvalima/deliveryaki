@extends('layouts/contentNavbarLayout')

@section('title', ' Cadastro de Produto')

@section('content')

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
          <span id="basic-icon-default-company2" class="input-group-text"><i class="mdi mdi-office-building-outline"></i></span>
          <input type="text" id="descricao" name="descricao" value="{{ $produto->descricao }}" class="form-control" placeholder="Descrição" aria-label="Descrição" required aria-describedby="basic-icon-default-company2"
          onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>


        <div class="form-floating form-floating-outline mb-4">
          <select class="form-select" id="categoria_id" name="categoria_id" aria-label="CATEGORIA" required>
            <option  value="">SELECIONAR</option>
            @foreach($categorias as $categoria)
              @if ($categoria->id == $produto->categoria_id)
              <option selected value="{{ $categoria->id }}" >{{ $categoria->descricao }}</option>
              @else
              <option value="{{ $categoria->id }}" >{{ $categoria->descricao }}</option>
              @endif
            @endforeach
            <label for="categoria_id">CATEGORIA</label>
          </select>
        </div>

        <div class="form-floating form-floating-outline mb-4">
          <select class="form-select" id="status" name="status" aria-label="STATUS" required>
            <option selected value="A">ATIVADO</option>
            <option value="D">DESATIVADO</option>
            <label for="status">STATUS</label>
          </select>
        </div>

        <button type="submit" class="btn btn-primary">SALVAR</button>
        <a href="{{ route('produto.index') }}" class="btn btn-secondary">CANCELAR</a>
      </form>
    </div>
  </div>
</div>
@endsection
