@extends('layouts/contentNavbarLayout')

@section('title', 'Empresas')

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


<div class="card">

  <h5 class="card-header">EMPRESAS</h5>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead class="table-dark">
        <tr>
          <th>CNPJ</th>
          <th>RAZÃO SOCIAL</th>
          <th>TELEFONE</th>
          <th>CELULAR</th>
          <th>LINK</th>
          <th>STATUS</th>
          <th>AÇÕES</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach($empresas as $empresa)
        <tr>
          <td>{{ $empresa->cnpj }}</td>
          <td>{{ $empresa->razao_social }}</td>
          <td>{{ $empresa->telefone }}</td>
          <td>{{ $empresa->celular }}</td>
          <td>{{ config('app.url_pedido') }}{{ $empresa->hash }}</td>
          <td><span class="badge rounded-pill bg-label-primary me-1">{{ $empresa->status == "D" ? "DESATIVADO" : ($empresa->status == "A" ? "ATIVADO": "")}}</span></td>
          <!--<td>{{ $empresa->created_at }}</td>-->
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('empresa.show', $empresa->id) }}"><i class="mdi mdi-pencil-outline me-1"></i> EDITAR</a>
                <!--<a class="dropdown-item" href="javascript:void(0);"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>-->
                <!--<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#basicModal" href="javascript:void(0);"><i class="mdi mdi-trash-can-outline me-1"></i> Deletar</a>-->
              </div>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection
