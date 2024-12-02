@extends('layouts/contentNavbarLayout')

@section('title', ' Cadastro de Empresa')

@section('content')

<div class="col-xl">
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Editar Cadastro de Empresa</h5>
      <small class="text-muted float-end"></small>
    </div>
    <div class="card-body">
      <form action="{{ route('empresa.edit', $empresa->id) }}" method="POST">
        @csrf()
        @method('PUT')
        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-company2" class="input-group-text"></i></span>
          <input type="text" id="cnpj" name="cnpj" disabled value="{{ $empresa->cnpj }}" class="form-control" placeholder="CNPJ" aria-label="CNPJ" required aria-describedby="basic-icon-default-company2" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-fullname2" class="input-group-text"></span>
          <input type="text" class="form-control" value="{{ $empresa->razao_social }}" id="razao_social" name="razao_social" placeholder="RAZÃO SOCIAL" required aria-label="RAZÃO SOCIAL" aria-describedby="basic-icon-default-fullname2" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" id="telefone" name="telefone" value="{{ $empresa->telefone }}" class="form-control phone-mask" placeholder="TELEFONE" aria-label="TELEFONE" aria-describedby="basic-icon-default-phone2" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"><i class="mdi mdi-whatsapp"></i></span>
          <input type="text" id="celular" name="celular" value="{{ $empresa->celular }}" class="form-control phone-mask" placeholder="WHATSAPP" required aria-label="WHATSAPP" aria-describedby="basic-icon-default-phone2" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" id="cep" name="cep" value="{{ $empresa->cep }}" class="form-control phone-mask" placeholder="CEP" required aria-label="CEP" aria-describedby="basic-icon-default-phone2" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" id="logradouro" name="logradouro" value="{{ $empresa->logradouro }}" class="form-control phone-mask" placeholder="LOGRADOURO" required aria-label="LOGRADOURO" aria-describedby="basic-icon-default-phone2" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" id="numero" name="numero" value="{{ $empresa->numero }}" class="form-control phone-mask" placeholder="NÚMERO" required aria-label="NÚMERO" aria-describedby="basic-icon-default-phone2" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" id="bairro" name="bairro" value="{{ $empresa->bairro }}" class="form-control phone-mask" placeholder="BAIRRO" required aria-label="BAIRRO" aria-describedby="basic-icon-default-phone2" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" id="complemento" name="complemento" value="{{ $empresa->complemento }}" class="form-control phone-mask" placeholder="COMPLEMENTO"  aria-label="COMPLEMENTO" aria-describedby="basic-icon-default-phone2" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" id="cidade" name="cidade" value="{{ $empresa->cidade }}" class="form-control phone-mask" placeholder="CIDADE" required aria-label="CIDADE" aria-describedby="basic-icon-default-phone2" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" id="uf" name="uf" value="{{ $empresa->uf }}" class="form-control phone-mask" placeholder="UF" required aria-label="UF" aria-describedby="basic-icon-default-phone2" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-phone2" class="input-group-text"></span>
          <input type="text" disabled id="hash" name="hash" value="{{ config('app.url_pedido') }}{{ $empresa->hash }}" class="form-control phone-mask" placeholder="LINK" required aria-label="LINK" aria-describedby="basic-icon-default-phone2" />
        </div>

        <!--<div class="mb-4">
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="mdi mdi-email-outline"></i></span>
              <input type="text" id="email" name="email" value="{{ $empresa->email }}" class="form-control" placeholder="E-MAIL" aria-label="E-MAIL" aria-describedby="basic-icon-default-email2" />
              <span id="email" class="input-group-text">exemplo@exemplo.com</span>
            </div>
            <!--<div class="form-text"> Você pode usar letras, números e pontos </div>-->
        <!--</div>-->

        <div class="form-floating form-floating-outline mb-4">
          <select class="form-select" id="status" disabled name="status" aria-label="STATUS" required>
            <option value="A" {{ $empresa->status == "A" ? "selected" : '' }}">ATIVADO</option>
            <option value="D" {{ $empresa->status == "D" ? "selected" : '' }}>DESATIVADO</option>
            <label for="status">STATUS</label>
          </select>
        </div>

        <button type="submit" class="btn btn-primary">SALVAR</button>
        <a href="{{ route('empresa.index') }}" class="btn btn-secondary">CANCELAR</a>
      </form>
    </div>
  </div>
</div>
@endsection
