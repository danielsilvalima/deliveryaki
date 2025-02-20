@extends('layouts/contentNavbarLayout')

@section('title', ' Cadastro de Produto')

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

<div class="col-xl">
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Editar Cadastro de Produto</h5>
      <small class="text-muted float-end"></small>
    </div>
    <div class="card-body">
      <form id="form_produto" action="{{ route('produto.edit', $produto->id) }}" method="POST" enctype="multipart/form-data">
        @csrf()
        @method('PUT')
        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-company2" class="input-group-text"></span>
          <input type="text" id="descricao" name="descricao" value="{{ $produto->descricao }}" class="form-control" placeholder="DESCRIÇÃO" aria-label="DESCRIÇÃO" required aria-describedby="basic-icon-default-company2"
            onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-company2" class="input-group-text"></span>
          <input type="text" id="apresentacao" name="apresentacao" value="{{ $produto->apresentacao }}" class="form-control" placeholder="APRESENTAÇÃO" aria-label="APRESENTAÇÃO" required aria-describedby="basic-icon-default-company2"
            onkeyup="var start = this.selectionStart;var end = this.selectionEnd;this.value = this.value.toUpperCase();this.setSelectionRange(start, end);" />
        </div>

        <div class="input-group input-group-merge mb-4">
          <span id="basic-icon-default-company2" class="input-group-text"></span>
          <input type="number" min="0" max="1000" step="any" id="vlr_unitario" name="vlr_unitario" value="{{ $produto->vlr_unitario }}" class="form-control" placeholder="VALOR UNITÁRIO" aria-label="VALOR UNITÁRIO" required aria-describedby="basic-icon-default-company2" />
        </div>

        <div class="form-floating form-floating-outline mb-4">
          <select class="form-select" id="categoria_id" name="categoria_id" aria-label="CATEGORIA" required>
            <option value="">SELECIONAR</option>
            @foreach($categorias as $categoria)
            @if ($categoria->id == $produto->categoria_id)
            <option selected value="{{ $categoria->id }}">{{ $categoria->descricao }}</option>
            @else
            <option value="{{ $categoria->id }}">{{ $categoria->descricao }}</option>
            @endif
            @endforeach
          </select>
          <label for="categoria_id">CATEGORIA</label>
        </div>

        <div class="form-floating form-floating-outline mb-4">
          <select class="form-select" id="status" name="status" aria-label="STATUS" required>
            <option value="A" {{ isset($produto) && $produto->status == 'A' ? 'selected' : '' }}>ATIVADO</option>
            <option value="D" {{ isset($produto) && $produto->status == 'D' ? 'selected' : '' }}>DESATIVADO</option>
          </select>
          <label for="status">STATUS</label>
        </div>

        <div class="card-body">
          <div class="d-flex align-items-start align-items-sm-center gap-4">
            <img src="{{ asset('storage/' . $produto->path) }}" id="uploadedAvatar" style="max-width: 200px; max-height: 110px; object-fit: cover;" />
            <div class="button-wrapper">
              <label for="logo" class="btn btn-primary me-2 mb-3" tabindex="0">
                <span class="d-none d-sm-block">Upload</span>
                <i class="mdi mdi-tray-arrow-up d-block d-sm-none"></i>
                <input type="file" id="logo" name="logo" class="account-file-input" hidden accept="image/png, image/jpeg" />
              </label>
              <button type="button" id="btn-remover-logo" class="btn btn-outline-danger account-image-reset mb-3">
                <i class="mdi mdi-reload d-block d-sm-none"></i>
                <span class="d-none d-sm-block">Limpar</span>
              </button>

              <div class="text-muted small">JPG ou PNG permitidos. Tamanho máximo de 5MB</div>
            </div>
          </div>
        </div>
        <script>
          document.addEventListener("DOMContentLoaded", function() {
            const fileInput = document.getElementById("logo");
            const imagePreview = document.getElementById("uploadedAvatar");
            const resetButton = document.querySelector(".account-image-reset");

            // Quando um novo arquivo for selecionado
            fileInput.addEventListener("change", function(event) {
              const file = event.target.files[0];

              if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                  imagePreview.src = e.target.result; // Atualiza a imagem com a prévia
                };

                reader.readAsDataURL(file);
              }
            });

            // Botão de limpar imagem
            resetButton.addEventListener("click", function() {
              fileInput.value = ""; // Reseta o campo de upload
              imagePreview.src = "{{ asset('storage/logo/' . $produto->path) }}"; // Restaura a imagem original
            });
          });
        </script>
        <script>
          function showToast(message, type) {
            let toastContainer = document.querySelector('.toast-container');

            let toastHtml = `
                        <div class="toast align-items-center text-bg-${type} border-0 show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                            <div class="d-flex">
                                <div class="toast-body">
                                    ${message}
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    `;

            toastContainer.innerHTML = toastHtml;
            let toastElement = toastContainer.querySelector('.toast');
            let bsToast = new bootstrap.Toast(toastElement);
            bsToast.show();
          }
          document.getElementById('btn-remover-logo').addEventListener('click', function() {
            fetch(`/produto/{{ $produto->id }}/remover-logo`, {
                method: 'DELETE',
                headers: {
                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                  'Content-Type': 'application/json',
                }
              })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  showToast(data.message, 'success');
                  document.getElementById('logo-preview').src = "{{ asset('storage/default-logo.png') }}"; // Caminho da imagem padrão
                } else {
                  showToast(data.message, 'danger');
                }
              })
              .catch(error => console.error('Erro:', error));
          });
        </script>

        <button type="submit" id="submitButton" class="btn btn-primary" style="width: 120px;">
          <span id="buttonText">SALVAR</span>
          <span id="spinner" class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true" style="display: none;"></span>
        </button>
        <a href="{{ route('produto.index') }}" class="btn btn-secondary">CANCELAR</a>
      </form>
      <script>
        document.getElementById('form_produto').addEventListener('submit', function() {
          var submitButton = document.getElementById('submitButton');
          var buttonText = document.getElementById('buttonText');
          var spinner = document.getElementById('spinner');

          buttonText.style.display = 'none'; // Esconde o texto
          spinner.style.display = 'inline-block'; // Mostra o spinner
          submitButton.disabled = true;
        });
      </script>
    </div>
  </div>
</div>
@endsection