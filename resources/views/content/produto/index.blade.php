@extends('layouts/contentNavbarLayout')

@section('title', 'Produtos')

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

<a type="button" class="btn btn-primary" href="{{ route('produto.create') }}"> Novo cadastro</a>
<p></p>
<p></p>
<div class="card">

  <h5 class="card-header">PRODUTOS</h5>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead class="table-dark">
        <tr>
          <th>DESCRIÇÃO</th>
          <th>APRESENTAÇÃO</th>
          <th>CATEGORIA</th>
          <th>VALOR</th>
          <th>STATUS</th>
          <th>AÇÕES</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        @foreach($produtos as $produto)
        <tr>
          <td>{{ $produto->produto }}</td>
          <td>{{ $produto->apresentacao }}</td>
          <td>{{ $produto->categoria }}</td>
          <td style="text-align: right;">{{ number_format($produto->vlr_unitario, 2, ',', '.') }}</td>
          <td>
            <span class="badge rounded-pill
        {{ $produto->status == 'A' ? 'bg-label-success' : ($produto->status == 'D' ? 'bg-label-danger' : 'bg-label-secondary') }}
        me-1">
              {{ $produto->status == 'A' ? 'ATIVADO' : ($produto->status == 'D' ? 'DESATIVADO' : 'DESCONHECIDO') }}
            </span>
          </td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('produto.show', $produto->id) }}"><i class="mdi mdi-pencil-outline me-1"></i> EDITAR</a>
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
@if (isset($produto))
<div class="modal fade" id="basicModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel1">EXCLUIR PRODUTO</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('produto.delete', $produto->id ) }}" method="POST">
          @csrf()
          @method('DELETE')
          <div class="row">
            <div class="col mb-4 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="text" id="descricao" value="{{ $produto->descricao }} " class=" form-control" placeholder="DESCRIÇÃO">
                <label for="descricao">DESCRIÇÃO</label>
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