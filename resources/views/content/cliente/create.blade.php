@extends('layouts/contentNavbarLayout')

@section('title', ' Cadastro de Produto')

@section('content')

<div class="col-xl">
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Novo Cadastro de Cliente</h5>
      <small class="text-muted float-end"></small>
    </div>
    <div class="card-body">
      <form action="{{ route('cliente.store') }}" method="POST">
        @csrf()

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-company2" class="input-group-text"></span>
          <input type="text" id="nome_completo" name="nome_completo" class="form-control" placeholder="NOME COMPLETO" aria-label="NOME COMPLETO"
          aria-describedby="basic-icon-default-company2"
          onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-fullname2" class="input-group-text"></i></span>
          <input type="text" class="form-control" id="cep" name="cep" placeholder="CEP" aria-label="CEP"
          aria-describedby="basic-icon-default-fullname2"
          onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></i></span>
          <input type="text" id="logradouro" name="logradouro" class="form-control" placeholder="LOGRADOURO" aria-label="LOGRADOURO"
          aria-describedby="basic-icon-default-phone2"
          onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>


        <div class="input-group input-group-merge mb-4">
          <span class="input-group-text"></span>
          <input type="text" id="numero" name="numero" class="form-control" placeholder="NUMERO" aria-label="NUMERO" aria-describedby="basic-icon-default-email2"
          onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span class="input-group-text"></span>
          <input type="text" id="complemento" name="complemento" class="form-control" placeholder="COMPLEMENTO" aria-label="COMPLEMENTO" aria-describedby="basic-icon-default-email2"
          onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span class="input-group-text"></span>
          <input type="text" id="bairro" name="bairro" class="form-control" placeholder="BAIRRO" aria-label="BAIRRO" aria-describedby="basic-icon-default-email2"
          onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span class="input-group-text"></span>
          <input type="text" id="cidade" name="cidade" class="form-control" placeholder="CIDADE" aria-label="CIDADE" aria-describedby="basic-icon-default-email2"
          onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"><i class="mdi mdi-whatsapp"></i></span>
          <input type="text" id="celular" name="celular" class="form-control phone-mask" placeholder="WHATSAPP" aria-label="WHATSAPP"
          aria-describedby="basic-icon-default-phone2"
          onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="form-floating form-floating-outline mb-4">
          <select class="form-select" id="status" name="status" aria-label="STATUS" >
            <option selected value="A">ATIVADO</option>
            <option value="D">DESATIVADO</option>
            <label for="status">STATUS</label>
          </select>
        </div>

        <button type="submit" class="btn btn-primary">SALVAR</button>
        <a href="{{ route('cliente.index') }}" class="btn btn-secondary">CANCELAR</a>
      </form>
    </div>
  </div>
</div>
@endsection
