'use strict';

document.addEventListener('DOMContentLoaded', function () {
  const tableBody = document.querySelector('#table-body-pedido-items');
  const addProductButton = document.querySelector('#add-product');
  const form = document.querySelector('#salvarForm');
  const existingProdutos = JSON.parse(document.getElementById('existingProdutosData').textContent || '[]');

  loadExistingRows();

  // Função para carregar os itens do pedido ao abrir a página
  function loadExistingRows() {
    const existingPedido = JSON.parse(document.getElementById('existingPedidoData').textContent || '[]');
    console.log('EXISTENTE');
    console.log(existingPedido.pedido_items);
    if (existingPedido.pedido_items) {
      existingPedido.pedido_items.forEach(item => {
        addRowToTable(
          item.produto_id,
          item.produto.descricao,
          item.produto.apresentacao,
          item.qtd,
          item.vlr_unitario,
          item.vlr_total,
          false
        );
      });
    }
  }

  // Função para adicionar um novo item ao pedido
  function addRowToTable(produtoId, descricao, apresentacao, qtd = 1, vlrUnit, vlrTotal, isNew = true) {
    let existingRow = document.querySelector(`tr[data-produto-id="${produtoId}"]`);

    if (existingRow) {
      // Se o produto já existe, apenas soma a quantidade e o valor total
      let qtdCell = existingRow.querySelector('.qtd');
      let vlrTotalCell = existingRow.querySelector('.vlr-total');

      let currentQtd = parseInt(qtdCell.textContent, 10);
      let newQtd = currentQtd + qtd;
      qtdCell.textContent = newQtd;

      let newTotal = newQtd * vlrUnit;
      //vlrTotalCell.textContent = newTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
      vlrTotalCell.textContent = newTotal;
    } else {
      // Criando nova linha no datatable
      const row = document.createElement('tr');
      row.setAttribute('data-produto-id', produtoId);

      row.innerHTML = `
        <td>${produtoId}</td>
        <td>${descricao}</td>
        <td>${apresentacao || ''}</td>
        <td style="text-align: center;">R$ ${vlrUnit.replace('.', ',')}</td>
        <td class="qtd" style="text-align: center;">${qtd}</td>
        <td class="vlr-total" style="text-align: center;">R$ ${vlrTotal.replace('.', ',')}</td>
        <td>
          <button type="button" class="btn btn-danger btn-sm remove-item-pedido">
            <i class="mdi mdi-trash-can-outline"></i>
          </button>
        </td>
      `;

      tableBody.appendChild(row);
    }
  }

  // Evento de clique para adicionar novo produto
  addProductButton.addEventListener('click', function () {
    const produtoSelect = document.querySelector('#produto');
    const produtoId = produtoSelect.value;
    const produtoDescricao = produtoSelect.options[produtoSelect.selectedIndex].text;
    const produtoPreco = parseFloat(produtoSelect.dataset.preco);

    console.log('Produto:', existingProdutos);
    console.log('Produto ID:', produtoId);
    console.log('Produto Descrição:', produtoDescricao);
    console.log('Produto Preço:', produtoPreco);

    if (produtoId && produtoPreco) {
      addRowToTable(produtoId, produtoDescricao, 1, produtoPreco);
    } else {
      showToast('Selecione um produto antes de adicionar.', 'warning');
    }
  });

  // Evento de clique para remover um item do pedido
  tableBody.addEventListener('click', function (event) {
    if (event.target.closest('.remove-item-pedido')) {
      const row = event.target.closest('tr');
      let qtdCell = row.querySelector('.qtd');
      let vlrTotalCell = row.querySelector('.vlr-total');
      console.log(qtdCell);
      console.log(vlrTotalCell);
      let vlrUnit = parseFloat(row.children[2].textContent.replace('R$', '').trim().replace(',', '.'));
      console.log(vlrUnit);

      let qtd = parseInt(qtdCell.textContent, 10);
      console.log(qtd);
      if (qtd > 1) {
        qtd -= 1;
        qtdCell.textContent = qtd;
        let newTotal = qtd * vlrUnit;
        vlrTotalCell.textContent = newTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
      } else {
        row.remove();
      }
    }
  });

  // Antes de enviar, salva os itens no input hidden
  form.addEventListener('submit', function (event) {
    const pedidos = [];
    const rows = tableBody.querySelectorAll('tr');

    rows.forEach(row => {
      const produtoId = row.getAttribute('data-produto-id');
      const qtd = parseInt(row.querySelector('.qtd').textContent, 10);
      const vlrTotal = parseFloat(
        row.querySelector('.vlr-total').textContent.replace('R$', '').trim().replace(',', '.')
      );

      pedidos.push({
        produto_id: produtoId,
        qtd: qtd,
        vlr_total: vlrTotal
      });
    });

    if (pedidos.length === 0) {
      showToast('Adicione pelo menos um produto antes de salvar!', 'danger');
      event.preventDefault();
      return;
    }

    const pedidosInput = document.createElement('input');
    pedidosInput.type = 'hidden';
    pedidosInput.name = 'pedidos';
    pedidosInput.value = JSON.stringify(pedidos);

    form.appendChild(pedidosInput);
  });

  function showToast(message, type) {
    const toastContainer = document.querySelector('.toast-container');

    // Remove toasts antigos
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
