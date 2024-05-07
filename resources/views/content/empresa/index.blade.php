@extends('layouts/contentNavbarLayout')

@section('title', 'Empresas')

@section('content')

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

<!-- Modal -->
<div class="modal fade" id="basicModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel1">EXCLUIR EMPRESA</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('empresa.delete', $empresa->id) }}" method="POST">
          @csrf()
          @method('DELETE')
          <div class="row">
            <div class="col mb-4 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="text" id="cnpj" value="{{ $empresa->cnpj }}" class="form-control" placeholder="CNPJ">
                <label for="cnpj">CNPJ</label>
              </div>
            </div>
          </div>
          <div class="row g-2">
            <div class="col mb-2">
              <div class="form-floating form-floating-outline">
                <input type="text" id="razao_social" value="{{ $empresa->razao_social }}" class="form-control" placeholder="RAZÃO SOCIAL">
                <label for="razao_social">RAZÃO SOCIAL</label>
              </div>
            </div>
          </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Excluir</button>
      </div>
      </form>
    </div>
  </div>
</div>

@endsection
