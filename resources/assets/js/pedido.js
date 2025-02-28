'use strict';

document.addEventListener('DOMContentLoaded', function () {
  // Elementos principais
  const form = document.querySelector('#form_empresa');
  const tableBody = document.querySelector('#table-body-empresa-expediente');
  const addRowButton = document.querySelector('#add-row-expediente');

  loadExistingRows();

  //const horarioExpedientes = JSON.parse(document.getElementById('horarioExpedientesData').textContent);

  // Função para adicionar uma nova linha ao datatable
  function addRowToTable() {
    const row = document.createElement('tr');
    const horario_expediente = document.querySelector('#horario_expediente_id option:checked');
    const hora_abertura = document.querySelector('#hora_abertura');
    const intervalo_inicio = document.querySelector('#intervalo_inicio');
    const intervalo_fim = document.querySelector('#intervalo_fim');
    const hora_fechamento = document.querySelector('#hora_fechamento');

    if (
      horario_expediente.value &&
      hora_abertura.value &&
      intervalo_inicio.value &&
      intervalo_fim.value &&
      hora_fechamento.value
    ) {
      row.innerHTML = `
      <td id="horario_expediente_id">${horario_expediente.value}</td>
      <td>${horario_expediente.text}</td>
      <td id="hora_abertura" style="text-align: center;">${hora_abertura.value}</td>
      <td id="intervalo_inicio" style="text-align: center;">${intervalo_inicio.value}</td>
      <td id="intervalo_fim" style="text-align: center;">${intervalo_fim.value}</td>
      <td id="hora_fechamento" style="text-align: center;">${hora_fechamento.value}</td>
      <td>
        <div class="dropdown">
          <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
          <div class="dropdown-menu">
            <button type="button" class="btn btn-danger btn-sm remove-expediente">
              <i class="mdi mdi-trash-can-outline"></i> Excluir
            </button>
          </div>
      </td>
      `;

      tableBody.appendChild(row);

      clearInputFields();
    } else {
      showToast('PREENCHA TODOS OS CAMPOS OBRIGATÓRIOS DE EXPEDIENTE ANTES DE ADICIONAR', 'warning');
    }
  }

  // Função para excluir uma linha específica antes do submit
  tableBody.addEventListener('click', function (event) {
    if (event.target.classList.contains('remove-expediente')) {
      const row = event.target.closest('tr');
      row.remove();
    }
  });

  // Função para preparar os dados do formulário antes de enviar
  form.addEventListener('submit', function (event) {
    const expedientes = [];

    const rows = tableBody.querySelectorAll('tr');

    rows.forEach(row => {
      const horarioExpedienteId = row.querySelector('#horario_expediente_id').innerHTML;
      const horaAbertura = row.querySelector('#hora_abertura').innerHTML;
      const horaFechamento = row.querySelector('#hora_fechamento').innerHTML;
      const intervaloInicio = row.querySelector('#intervalo_inicio').innerHTML;
      const intervaloFim = row.querySelector('#intervalo_fim').innerHTML;

      expedientes.push({
        horario_expediente_id: horarioExpedienteId,
        hora_abertura: horaAbertura || null,
        hora_fechamento: horaFechamento || null,
        intervalo_inicio: intervaloInicio || null,
        intervalo_fim: intervaloFim || null
      });
    });
    const expedientesInput = document.createElement('input');
    expedientesInput.type = 'hidden';
    expedientesInput.name = 'expedientes';
    expedientesInput.value = JSON.stringify(expedientes);

    form.appendChild(expedientesInput);
  });

  // Adiciona o evento ao botão de adicionar linha
  addRowButton.addEventListener('click', addRowToTable);

  // Função para carregar os expedientes existentes ao abrir a página
  function loadExistingRows() {
    const existingExpedientes = JSON.parse(
      document.getElementById('existingHorarioExpedientesData').textContent || '[]'
    );

    existingExpedientes.forEach(expediente => {
      const row = document.createElement('tr');

      row.innerHTML = `
    <td id="horario_expediente_id">${expediente.horario_expediente_id}</td>
    <td>${expediente.horario_expedientes.descricao}</td>
    <td id="hora_abertura" style="text-align: center;">${expediente.hora_abertura || ''}</td>
    <td id="intervalo_inicio" style="text-align: center;">${expediente.intervalo_inicio || ''}</td>
    <td id="intervalo_fim" style="text-align: center;">${expediente.intervalo_fim || ''}</td>
    <td id="hora_fechamento" style="text-align: center;">${expediente.hora_fechamento || ''}</td>
    <td>
      <div class="dropdown">
        <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
        <div class="dropdown-menu">
          <button type="button" class="btn btn-danger btn-sm remove-expediente">
            <i class="mdi mdi-trash-can-outline"></i> Excluir
          </button>
        </div>
      </div>
    </td>
  `;

      tableBody.appendChild(row);
    });
  }

  function clearInputFields() {
    document.querySelector('#hora_abertura').value = '';
    document.querySelector('#intervalo_inicio').value = '';
    document.querySelector('#intervalo_fim').value = '';
    document.querySelector('#hora_fechamento').value = '';
    document.querySelector('#horario_expediente_id').value = '';
  }

  function showToast(message, type) {
    const toastContainer = document.querySelector('.toast-container');

    // Remove toasts antigos, se necessário
    const existingToasts = toastContainer.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());

    // Cria o novo toast
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0 show`;
    toast.role = 'alert';
    toast.ariaLive = 'assertive';
    toast.ariaAtomic = 'true';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    // Adiciona o toast ao container
    toastContainer.appendChild(toast);

    // Remove o toast após 5 segundos
    setTimeout(() => {
      toast.remove();
    }, 5000);
  }
});
