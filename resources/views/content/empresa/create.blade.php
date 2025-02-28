@extends('layouts/contentNavbarLayout')

@section('title', ' Cadastro de Empresa')

@section('content')

<div class="toast-container position-fixed bottom-0 end-0 p-3">
  @if(session('success'))
  <div class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
    <div class="d-flex">
      <div class="toast-body">
        {{ session('success') }}
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
  @endif

  @if(session('error'))
  <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
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
      <h5 class="mb-0">Novo Cadastro de Empresa</h5>
      <small class="text-muted float-end"></small>
    </div>
    <div class="card-body">
      <form id="createEmpresaForm" action="{{ route('empresa.store') }}" method="POST">
        @csrf()

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-company2" class="input-group-text"><i class="mdi mdi-office-building-outline"></i></span>
          <input type="text" id="cnpj" name="cnpj" class="form-control" placeholder="CNPJ" aria-label="CNPJ" aria-describedby="basic-icon-default-company2"
            onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-fullname2" class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
          <input type="text" class="form-control" id="razao_social" name="razao_social" placeholder="RAZÃO SOCIAL" aria-label="RAZÃO SOCIAL" aria-describedby="basic-icon-default-fullname2"
            onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"><i class="mdi mdi-phone"></i></span>
          <input type="text" id="telefone" name="telefone" class="form-control phone-mask" placeholder="TELEFONE" aria-label="TELEFONE" aria-describedby="basic-icon-default-phone2"
            onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"><i class="mdi mdi-whatsapp"></i></span>
          <input type="text" id="celular" name="celular" class="form-control phone-mask" placeholder="WHATSAPP" aria-label="WHATSAPP" aria-describedby="basic-icon-default-phone2"
            onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" id="cep" name="cep" class="form-control phone-mask" placeholder="CEP" aria-label="CEP" aria-describedby="basic-icon-default-phone2"
            onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" id="logradouro" name="logradouro" class="form-control phone-mask" placeholder="LOGRADOURO" aria-label="LOGRADOURO" aria-describedby="basic-icon-default-phone2"
            onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" id="numero" name="numero" class="form-control phone-mask" placeholder="NUMERO" aria-label="NUMERO" aria-describedby="basic-icon-default-phone2"
            onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" id="bairro" name="bairro" class="form-control phone-mask" placeholder="BAIRRO" aria-label="BAIRRO" aria-describedby="basic-icon-default-phone2"
            onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" id="complemento" name="complemento" class="form-control phone-mask" placeholder="COMPLEMENTO" aria-label="COMPLEMENTO" aria-describedby="basic-icon-default-phone2"
            onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" id="cidade" name="cidade" class="form-control phone-mask" placeholder="CIDADE" aria-label="CIDADE" aria-describedby="basic-icon-default-phone2"
            onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" id="uf" name="uf" class="form-control phone-mask" placeholder="UF" aria-label="UF" aria-describedby="basic-icon-default-phone2"
            onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="mb-4">
          <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="mdi mdi-email-outline"></i></span>
            <input type="text" id="email" name="email" class="form-control" placeholder="E-MAIL" aria-label="E-MAIL" aria-describedby="basic-icon-default-email2"
              onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
            <span id="email" class="input-group-text">exemplo@exemplo.com</span>
          </div>
          <!--<div class="form-text"> Você pode usar letras, números e pontos </div>-->
        </div>

        <div class="form-floating form-floating-outline mb-4">
          <select class="form-select" id="status" name="status" aria-label="STATUS">
            <option selected value="A">ATIVADO</option>
            <option value="D">DESATIVADO</option>
          </select>
          <label for="status">STATUS</label>
        </div>

        <button id="submitButton" type="submit" class="btn btn-primary">SALVAR</button>
        <a href="{{ route('empresa.index') }}" class="btn btn-secondary">CANCELAR</a>
      </form>
      <script>
        document.getElementById('createEmpresaForm').addEventListener('submit', function() {
          var submitButton = document.getElementById('submitButton');
          submitButton.innerHTML = '<span class="spinner-border spinner-border-lg text-primary" role="status" aria-hidden="true"></span>';
          submitButton.disabled = true;
        });
      </script>
    </div>
  </div>
</div>
@endsection