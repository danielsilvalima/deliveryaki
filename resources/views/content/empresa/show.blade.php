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
      <h5 class="mb-0">Editar Cadastro de Empresa</h5>
      <small class="text-muted float-end"></small>
    </div>
    <div class="card-header">
      <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
          <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-tab-home" aria-controls="navs-tab-home" aria-selected="true">Home</button>
        </li>
        <li class="nav-item"><button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-tab-expediente" aria-controls="navs-tab-expediente" aria-selected="false">Expediente</button>
        </li>
      </ul>
    </div>
    <div class="card-body">
      <form id="form_empresa"  method="POST"><!--action="{{ route('empresa.edit', $empresa->id) }}"-->
        @csrf()
        @method('PUT')
        <div class="tab-content p-0">
          <div class="tab-pane fade show active" id="navs-tab-home" role="tabpanel">
            <div class="row">
              <div class="col-md-2">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" id="cnpj" name="cnpj" disabled value="{{ $empresa->cnpj }}" class="form-control" placeholder="CNPJ" aria-label="CNPJ" required aria-describedby="basic-icon-default-company2" />
                  <label for="cnpj">CNPJ</label>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" class="form-control" value="{{ $empresa->razao_social }}" id="razao_social" name="razao_social" placeholder="RAZÃO SOCIAL" required aria-label="RAZÃO SOCIAL" aria-describedby="basic-icon-default-fullname2" />
                  <label for="razao_social">RAZÃO SOCIAL</label>
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" id="telefone" name="telefone" value="{{ $empresa->telefone }}" class="form-control phone-mask" placeholder="TELEFONE" aria-label="TELEFONE" aria-describedby="basic-icon-default-phone2" />
                  <label for="telefone">TELEFONE</label>
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" id="celular" name="celular" value="{{ $empresa->celular }}" class="form-control phone-mask" placeholder="WHATSAPP" required aria-label="WHATSAPP" aria-describedby="basic-icon-default-phone2" />
                  <label for="celular">WHATSAPP</label>
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="number" id="cep" name="cep" value="{{ $empresa->cep }}" class="form-control phone-mask" placeholder="CEP" required aria-label="CEP" aria-describedby="basic-icon-default-phone2"
                  onblur="getCEP()" />
                  <label for="cep">CEP</label>
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" id="numero" name="numero" value="{{ $empresa->numero }}" class="form-control phone-mask" placeholder="NÚMERO" required aria-label="NÚMERO" aria-describedby="basic-icon-default-phone2" />
                  <label for="numero">NÚMERO</label>
                </div>
              </div>

              <div class="col-md-8">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" id="logradouro" name="logradouro" readonly value="{{ $empresa->logradouro }}" class="form-control phone-mask" placeholder="LOGRADOURO" required aria-label="LOGRADOURO" aria-describedby="basic-icon-default-phone2" />
                  <label for="logradouro">LOGRADOURO</label>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" id="bairro" name="bairro" readonly value="{{ $empresa->bairro }}" class="form-control phone-mask" placeholder="BAIRRO" required aria-label="BAIRRO" aria-describedby="basic-icon-default-phone2" />
                  <label for="bairro">BAIRRO</label>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" id="complemento" name="complemento" readonly value="{{ $empresa->complemento }}" class="form-control phone-mask" placeholder="COMPLEMENTO"  aria-label="COMPLEMENTO" aria-describedby="basic-icon-default-phone2" />
                  <label for="complemento">COMPLEMENTO</label>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" id="cidade" name="cidade" readonly value="{{ $empresa->cidade }}" class="form-control phone-mask" placeholder="CIDADE" required aria-label="CIDADE" aria-describedby="basic-icon-default-phone2" />
                  <label for="cidade">CIDADE</label>
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="text" id="uf" name="uf" readonly value="{{ $empresa->uf }}" class="form-control phone-mask" placeholder="UF" required aria-label="UF" aria-describedby="basic-icon-default-phone2" />
                  <label for="uf">UF</label>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-floating form-floating-outline mb-4">
                  <select class="form-select" id="status" disabled name="status" aria-label="STATUS" required>
                    <option value="A" {{ $empresa->status == "A" ? "selected" : '' }}">ATIVADO</option>
                    <option value="D" {{ $empresa->status == "D" ? "selected" : '' }}>DESATIVADO</option>
                  </select>
                  <label for="status">STATUS</label>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-floating form-floating-outline mb-4">
                  <select class="form-select" id="tipo_taxa" name="tipo_taxa" aria-label="TIPO TAXA" required>
                    <option value="F" {{ $empresa->tipo_taxa == "A" ? "selected" : '' }}">FIXA</option>
                    <option value="D" {{ $empresa->tipo_taxa == "D" ? "selected" : '' }}>DISTÂNCIA</option>
                  </select>
                  <label for="tipo_taxa">TIPO TAXA</label>
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="number" id="inicio_distancia" name="inicio_distancia" value="{{ $empresa->inicio_distancia }}"
                  class="form-control phone-mask " placeholder="INÍCIO DISTÂNCIA" required aria-label="INÍCIO DISTÂNCIA" aria-describedby="basic-icon-default-phone2" />
                  <label for="inicio_distancia">INÍCIO DISTÂNCIA (KM)</label>
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="number" id="vlr_km" name="vlr_km" value="{{ $empresa->vlr_km }}" class="form-control phone-mask" placeholder="VALOR KM (R$)" required aria-label="VALOR KM (R$)" aria-describedby="basic-icon-default-phone2" />
                  <label for="vlr_km">VALOR KM (R$)</label>
                </div>
              </div>

              <div class="col-md-12">
                <div class="form-floating form-floating-outline mb-3">
                <input type="text" disabled id="hash" name="hash" value="{{ config('app.url_pedido') }}{{ $empresa->hash }}" class="form-control phone-mask" placeholder="LINK" required aria-label="LINK" aria-describedby="basic-icon-default-phone2" />
                <label for="hash">LINK</label>
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-primary">SALVAR</button>
            <a href="{{ route('empresa.index') }}" class="btn btn-secondary">CANCELAR</a>
          </div>
          <div class="tab-pane fade show" id="navs-tab-expediente" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <div class="form-floating form-floating-outline mb-3">
                  <select class="form-select" id="horario_expediente_id" name="horario_expediente_id" aria-label="DIAS DA SEMANA" >
                    <option selected value="">SELECIONAR</option>
                    @foreach($horarioExpedientes as $horarioExpediente)
                    <option value="{{ $horarioExpediente->id }}">{{ $horarioExpediente->descricao }}</option>
                    @endforeach
                  </select>
                  <label for="horario_expediente_id">DIAS DA SEMANA</label>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="time" id="hora_abertura" name="hora_abertura" class="form-control" placeholder="ABERTURA" aria-label="ABERTURA"
                    aria-describedby="basic-icon-default-phone2"/>
                  <label for="hora_abertura">ABERTURA</label>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="time" id="intervalo_inicio" name="intervalo_inicio" class="form-control" placeholder="INÍCIO INTERVALO" aria-label="INÍCIO INTERVALO"
                    aria-describedby="basic-icon-default-phone2"/>
                  <label for="intervalo_inicio">INÍCIO INTERVALO</label>
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="time" id="intervalo_fim" name="intervalo_fim" class="form-control" placeholder="TÉRMINO INTERVALOR" aria-label="TÉRMINO INTERVALOR"
                    aria-describedby="basic-icon-default-phone2"/>
                  <label for="intervalo_fim">TÉRMINO INTERVALOR</label>
                </div>
              </div>

              <div class="col-md-2">
                <div class="form-floating form-floating-outline mb-3">
                  <input type="time" id="hora_fechamento" name="hora_fechamento" class="form-control" placeholder="FECHAMENTO" aria-label="FECHAMENTO"
                    aria-describedby="basic-icon-default-phone2"/>
                  <label for="hora_fechamento">FECHAMENTO</label>
                </div>
              </div>

              <div class="col-md-1">
                <div class="form-floating form-floating-outline mb-3">
                  <button type="button" id="add-row-expediente" class="btn btn-primary">ADICIONAR</button>
                </div>
              </div>

              <div class="col-md-12">
                <div class="form-floating form-floating-outline mb-12">
                  <div class="table-responsive text-nowrap mb-4">
                    <table class="table">
                      <thead class="table-dark">
                        <tr>
                          <th>ID</th>
                          <th>DESCRIÇÃO</th>
                          <th>ABERTURA</th>
                          <th>INÍCIO INTERVALO</th>
                          <th>TÉRMINO INTERVALO</th>
                          <th>FECHAMENTO</th>
                          <th>AÇÕES</th>
                        </tr>
                      </thead>
                      <tbody class="table-border-bottom-0" id="table-body-empresa-expediente">
                      </tbody>
                    </table>
                  </div>
                  <script src="{{ asset('assets/js/empresa-expediente.js') }}"></script>
                  <div id="horarioExpedientesData" style="display: none;">@json($horarioExpedientes)</div>
                  <div id="existingHorarioExpedientesData" style="display: none;">@json($empresaExpedientes)</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
