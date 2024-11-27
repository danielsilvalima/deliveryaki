@extends('layouts/contentNavbarLayout')

@section('title', 'Pedidos')

@section('content')




<script src="https://cdn.datatables.net/2.0.7/js/dataTables.js"></script>
<div class="card">

  <h5 class="card-header">PEDIDOS</h5>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead class="table-dark">
        <tr>
          <th style="width: 10%;">DETALHES</th>
          <th style="width: 20%;">CLIENTE</th>
          <th style="width: 50%;">ENDEREÇO</th>
          <th style="width: 5%;">DATA</th>
          <th style="width: 5%;">VLR TAXA</th>
          <th style="width: 5%;">VLR TOTAL</th>
          <th style="width: 10%;">TIPO ENTREGA</th>
          <th style="width: 10%;">STATUS</th>
          <th style="width: 10%;">AÇÕES</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">

        @foreach($pedidos as $pedido)
        <tr>
          <td>
            <button type="button" class="btn btn-link p-0" onclick="toggleDetails({{ $pedido->id }})">
              <i class="mdi mdi-eye-outline"></i> Detalhes
            </button>
          </td>
          <td>{{ $pedido->nome_completo }}</td>
          <td>{{ $pedido->logradouro }}, {{ $pedido->numero }} - {{ $pedido->bairro }}
          </td>
          <td>{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
          <td>{{ number_format($pedido->vlr_taxa , 2, ',', '.')}}</td>
          <td>{{ number_format($pedido->vlr_total, 2, ',', '.') }}</td>
          <td>{!! $pedido->tipo_entrega == "E"
          ? '<span class="badge rounded-pill bg-label-warning me-1">ENTREGA</span>'
          : ($pedido->tipo_entrega == "R" ? '<span class="badge rounded-pill bg-label-danger me-1">RETIRA</span>' : '') !!}</td>
          <td><span class="badge rounded-pill bg-label-primary me-1">{{ $pedido->status == "D" ? "DESATIVADO" : ($pedido->status == "A" ? "ATIVADO": "")}}</span></td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('pedido.show', $pedido->id) }}"><i class="mdi mdi-pencil-outline me-1"></i> EDITAR</a>
                <!--<a class="dropdown-item" href="javascript:void(0);"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>-->
                <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#basicModal" href="javascript:void(0);"><i class="mdi mdi-trash-can-outline me-1"></i> EXCLUIR</a>
              </div>
            </div>
          </td>
        </tr>
        <tr id="details-{{ $pedido->id }}" style="display: none;">
          <td colspan="7">
              <div class="details-content">
                  <!-- Coloque aqui os detalhes do pedido -->
                  <p><strong>Detalhes:</strong> </p>
                  <ul>
                      @foreach($pedido->itens as $item)
                          <li>QUANTIDADE X {{ $item->qtd }} / {{ $item->descricao }} / VLR. UNITÁRIO {{ $item->vlr_unitario }} = {{ $item->vlr_total }}</li>
                      @endforeach
                  </ul>
              </div>
          </td>
        </tr>
        @endforeach
        <script>
          function toggleDetails(id) {
              var row = document.getElementById('details-' + id);
              if (row.style.display === 'none') {
                  row.style.display = 'table-row';
              } else {
                  row.style.display = 'none';
              }
          }
      </script>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
@if (isset($pedido))
<div class="modal fade" id="basicModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel1">EXCLUIR PEDIDO</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('pedido.delete', $pedido->id ) }}" method="POST">
          @csrf()
          @method('DELETE')
          <div class="row">
            <div class="col mb-4 mt-2">
              <div class="form-floating form-floating-outline">
                <input type="text" id="descricao" value="{{ $pedido->descricao }} " class=" form-control" placeholder="DESCRIÇÃO">
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
