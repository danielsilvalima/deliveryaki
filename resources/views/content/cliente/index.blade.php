@extends('layouts/contentNavbarLayout')

@section('title', 'Clientes')

@section('content')

<a type="button" class="btn btn-primary" href="{{ route('cliente.create') }}"> Novo cadastro</a>
<p></p>
<p></p>
<div class="card">

  <h5 class="card-header">CLIENTES</h5>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead class="table-dark">
        <tr>
          <th>NOME COMPLETO</th>
          <th>STATUS</th>
          <th>AÇÕES</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        @foreach($clientes as $cliente)
        <tr>
          <td>{{ $cliente->nome_completo }}</td>
          <td><span class="badge rounded-pill bg-label-primary me-1">{{ $cliente->status == "D" ? "DESATIVADO" : ($cliente->status == "A" ? "ATIVADO": "")}}</span></td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('cliente.show', $cliente->id) }}"><i class="mdi mdi-pencil-outline me-1"></i> EDITAR</a>
                <!--<a class="dropdown-item" href="javascript:void(0);"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>-->
                <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#basicModal" href="javascript:void(0);"><i class="mdi mdi-trash-can-outline me-1"></i> EXCLUIR</a>
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
@if (isset($cliente))
<div class="modal fade" id="basicModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel1">EXCLUIR CLIENTE</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('cliente.delete', $cliente->id ) }}" method="POST">
          @csrf()
          @method('DELETE')
          <div class="row">
            <div class="col mb-4 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="text" id="nome_completo" value="{{ $cliente->nome_completo }} " class=" form-control" placeholder="NOME COMPLETO">
                <label for="nome_completo">NOME COMPLETO</label>
              </div>
            </div>
          </div>
          <!--<div class="row g-2">
            <div class="col mb-2">
              <div class="form-floating form-floating-outline">
                <input type="text" id="razao_social" value="" class="form-control" placeholder="Razão Social">
                <label for="razao_social">Razão Social</label>
              </div>
            </div>
          </div>-->
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Excluir</button>
      </div>
      </form>
    </div>
  </div>
</div>
@endif

@endsection
