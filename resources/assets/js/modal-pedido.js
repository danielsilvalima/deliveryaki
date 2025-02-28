function openEditModal(pedidoId, itens, status, tipoEntrega, tipoPagamento) {
  // Atualizar o ID do pedido
  document.getElementById('pedido-id').textContent = pedidoId;

  // Atualizar a lista de itens
  const itensList = document.getElementById('pedido-itens');
  itensList.innerHTML = ''; // Limpar itens anteriores
  itens.forEach(item => {
    const vlrUnitario = Number(item.vlr_unitario); // Converter para número
    const vlrTotal = Number(item.vlr_total);
    const listItem = document.createElement('li');
    listItem.textContent = `${item.descricao} / VLR. UNITÁRIO R$ ${vlrUnitario.toFixed(2).replace('.', ',')}
                             X ${item.qtd} QTD = R$ ${vlrTotal.toFixed(2).replace('.', ',')}`;
    itensList.appendChild(listItem);
  });

  // Atualizar Tipo de Pagamento
  const tipoPagamentoElement = document.getElementById('tipo-pagamento');
  const pagamentoLabels = {
    CR: 'CRÉDITO',
    DE: 'DÉBITO',
    PI: 'PIX',
    DI: 'DINHEIRO'
  };
  tipoPagamentoElement.textContent = pagamentoLabels[tipoPagamento] || 'NÃO DEFINIDO';

  // Atualizar Tipo de Entrega
  const tipoEntregaElement = document.getElementById('tipo-entrega');
  const entregaLabels = {
    E: 'ENTREGA',
    R: 'RETIRA'
  };
  tipoEntregaElement.textContent = entregaLabels[tipoEntrega] || 'NÃO DEFINIDO';

  // Atualizar ou criar o elemento <select> para o status
  const statusContainer = document.getElementById('status-container'); // Container onde o <select> será adicionado
  statusContainer.innerHTML = ''; // Limpar qualquer conteúdo anterior

  // Criar o elemento <select>
  const select = document.createElement('select');
  select.className = 'form-select';
  select.id = 'status';
  select.name = 'status';

  // Definir as opções de status
  const statusOptions = [
    { value: 'A', label: 'ATIVADO' },
    { value: 'S', label: 'SAIU P/ ENTREGA' },
    { value: 'E', label: 'ENTREGUE' },
    { value: 'P', label: 'PENDENTE' },
    { value: 'C', label: 'CANCELADO' }
  ];

  // Adicionar as opções ao <select>
  statusOptions.forEach(option => {
    const optionElement = document.createElement('option');
    optionElement.value = option.value;
    optionElement.textContent = option.label;
    if (option.value === status) {
      optionElement.selected = true; // Marcar a opção atual como selecionada
    }
    select.appendChild(optionElement);
  });

  // Adicionar o <select> ao container
  statusContainer.appendChild(select);

  // Atualizar a rota do formulário com o ID do pedido
  const form = document.getElementById('editPedidoForm');
  const route = form.dataset.route.replace('__ID__', pedidoId);
  form.action = route;

  // Exibir o modal
  const modal = new bootstrap.Modal(document.getElementById('basicModal'));
  modal.show();
}
