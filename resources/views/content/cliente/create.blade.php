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
      <h5 class="mb-0">Novo Cadastro de Cliente</h5>
      <small class="text-muted float-end"></small>
    </div>
    <div class="card-body">
      <form action="{{ route('cliente.store') }}" method="POST">
        @csrf()
        <div class="row">
          <div class="col-md-4">
            <div class="form-floating form-floating-outline mb-3">
              <input type="text" id="nome_completo" name="nome_completo" class="form-control" placeholder="NOME COMPLETO" aria-label="NOME COMPLETO"
              aria-describedby="basic-icon-default-company2"
              onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
              <label for="nome_completo">NOME COMPLETO</label>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline mb-3">
              <input type="text" id="celular" name="celular" class="form-control phone-mask" placeholder="WHATSAPP" aria-label="WHATSAPP"
                aria-describedby="basic-icon-default-phone2"
                onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
              <label for="celular">WHATSAPP</label>
            </div>
          </div>

          <div class="col-md-2">
            <div class="form-floating form-floating-outline mb-3">
              <input type="text" class="form-control" id="cep" name="cep" placeholder="CEP" aria-label="CEP"
                aria-describedby="basic-icon-default-fullname2"
                onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);"
                onblur="getCEP()" />
              <label for="cep">CEP</label>
            </div>
          </div>

          <div class="col-md-2">
            <div class="form-floating form-floating-outline mb-3">
              <input type="text" id="numero" name="numero" class="form-control" placeholder="NÚMERO" aria-label="NÚMERO" aria-describedby="basic-icon-default-email2"
              onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
              <label for="numero">NÚMERO</label>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline mb-3">
              <input type="text" id="logradouro" name="logradouro" readonly class="form-control" placeholder="LOGRADOURO" aria-label="LOGRADOURO"
                aria-describedby="basic-icon-default-phone2"
                onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
              <label for="logradouro">LOGRADOURO</label>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline mb-3">
              <input type="text" id="complemento" name="complemento" readonly class="form-control" placeholder="COMPLEMENTO" aria-label="COMPLEMENTO" aria-describedby="basic-icon-default-email2"
                onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
              <label for="complemento">COMPLEMENTO</label>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline mb-3">
              <input type="text" id="bairro" name="bairro" readonly class="form-control" placeholder="BAIRRO" aria-label="BAIRRO" aria-describedby="basic-icon-default-email2"
                onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
              <label for="bairro">BAIRRO</label>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline mb-3">
              <input type="text" id="cidade" name="cidade" readonly class="form-control" placeholder="CIDADE" aria-label="CIDADE" aria-describedby="basic-icon-default-email2"
                onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
              <label for="cidade">CIDADE</label>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline mb-3">
              <input type="text" id="uf" name="uf" maxlength="2" readonly class="form-control" placeholder="UF" aria-label="UF" aria-describedby="basic-icon-default-email2"
                onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
              <label for="uf">UF</label>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-floating form-floating-outline mb-3">
              <select class="form-select" id="status" name="status" aria-label="STATUS" >
                <option selected value="A">ATIVADO</option>
                <option value="D">DESATIVADO</option>
                <label for="status">STATUS</label>
              </select>
              <label for="nome_completo">NOME COMPLETO</label>
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">SALVAR</button>
        <a href="{{ route('cliente.index') }}" class="btn btn-secondary">CANCELAR</a>
      </form>
    </div>
  </div>
</div>
@endsection
