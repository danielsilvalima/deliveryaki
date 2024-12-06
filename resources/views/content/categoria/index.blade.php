@extends('layouts/contentNavbarLayout')

@section('title', 'Categorias')

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

<a type="button" class="btn btn-primary" href="{{ route('categoria.create') }}"> Novo cadastro</a>
<p></p>
<p></p>
<div class="card">

  <h5 class="card-header">CATEGORIAS</h5>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead class="table-dark">
        <tr>
          <th>DESCRIÇÃO</th>
          <th>STATUS</th>
          <th>AÇÕES</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        @foreach($categorias as $categoria)
        <tr>
          <td>{{ $categoria->descricao }}</td>
          <td><span class="badge rounded-pill bg-label-primary me-1">{{ $categoria->status == "D" ? "DESATIVADO" : ($categoria->status == "A" ? "ATIVADO": "")}}</span></td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('categoria.show', $categoria->id) }}"><i class="mdi mdi-pencil-outline me-1"></i> EDITAR</a>
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
@if (isset($categoria))
<div class="modal fade" id="basicModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel1">EXCLUIR CATEGORIA</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('categoria.delete', $categoria->id ) }}" method="POST">
          @csrf()
          @method('DELETE')
          <div class="row">
            <div class="col mb-4 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="text" id="nome_completo" value="{{ $categoria->descricao }} " class=" form-control" placeholder="DESCRIÇÃO">
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
