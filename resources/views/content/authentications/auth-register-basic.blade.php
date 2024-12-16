@extends('layouts/blankLayout')

@section('title', 'Registro')

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
@endsection


@section('content')
<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-4">

      <!-- Register Card -->
      <div class="card p-2">
        <!-- Logo -->
        <div class="app-brand justify-content-center mt-5">
          <a href="{{url('/')}}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">@include('_partials.macros',["height"=>20])</span>
            <span class="app-brand-text demo text-heading fw-semibold">{{ config('variables.templateName') }}</span>
          </a>
        </div>
        <!-- /Logo -->
        <div class="card-body mt-2">
          <h4 class="mb-2">Suas vendas começam aqui 🚀</h4>
          <p class="mb-4">Torne o gerenciamento do seu aplicativo fácil e divertido!</p>

          @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
              @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif

          <form id="formAuthentication" class="mb-3" action="{{ route('auth-register-store') }}" method="POST">
            @csrf
            <div class="row">
              <div class="col-md-6">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" class="form-control" id="cnpj" name="cnpj" placeholder="CNPJ" maxlength="14"
                  onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);">
                  <label for="cnpj">CNPJ</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" class="form-control" id="celular" name="celular" required placeholder="WhatsApp" maxlength="16"
                  onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);">
                  <label for="celular">WhatsApp</label>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" class="form-control" id="razao_social" name="razao_social" placeholder="Razão Social"
                  onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);">
                  <label for="razao_social">Razão Social</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="number" class="form-control" id="cep" name="cep" maxlength="9" required placeholder="CEP"
                  onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);"
                  onblur="getCEP()">
                  <label for="cep">CEP</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" class="form-control" id="numero" name="numero" required placeholder="Número"
                  onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);">
                  <label for="numero">Número</label>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" class="form-control" id="logradouro" readonly name="logradouro" placeholder="Logradouro"
                  onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);">
                  <label for="logradouro">Logradouro</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" class="form-control" id="complemento" readonly name="complemento" placeholder="Complemento"
                  onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);">
                  <label for="complemento">Complemento</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" class="form-control" id="bairro" readonly name="bairro" placeholder="Bairro"
                  onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);">
                  <label for="bairro">Bairro</label>
                </div>
              </div>
              <div class="col-md-9">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" class="form-control" id="cidade" readonly name="cidade" placeholder="Cidade"
                  onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);">
                  <label for="cidade">Cidade</label>
                </div>
              </div><div class="col-md-3">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" class="form-control" id="uf" readonly name="uf" placeholder="UF"
                  onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);">
                  <label for="uf">UF</label>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" class="form-control" id="email" name="email" placeholder="E-mail"
                  onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);">
                  <label for="email">E-mail</label>
                </div>
              </div>
                <div class="mb-3 form-password-toggle">
                  <div class="input-group input-group-merge">
                    <div class="form-floating form-floating-outline">
                      <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                      <label for="password">Password</label>
                    </div>
                    <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
                  </div>
                </div>
              </div>

            <button class="btn btn-primary d-grid w-100">
              Inscrever-se
            </button>
          </form>

          <p class="text-center">
            <span>Já tem uma conta?</span>
            <a href="{{url('/')}}">
              <span>Em vez disso, faça login</span>
            </a>
          </p>
        </div>
      </div>
      <!-- Register Card -->
      <img src="{{asset('assets/img/illustrations/tree-3.png')}}" alt="auth-tree" class="authentication-image-object-left d-none d-lg-block">
      <img src="{{asset('assets/img/illustrations/auth-basic-mask-light.png')}}" class="authentication-image d-none d-lg-block" alt="triangle-bg">
      <img src="{{asset('assets/img/illustrations/tree.png')}}" alt="auth-tree" class="authentication-image-object-right d-none d-lg-block">
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('js/cep-handler.js') }}"></script>
@endsection
