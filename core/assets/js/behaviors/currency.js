const { module: modulo, rules } = window.behavior;
const { virtual_fields } = rules;
const subtotal = virtual_fields.subtotal;

const tabela = document.querySelector(`table[data-modulo="${modulo}"]`);
const linhas = tabela.querySelectorAll('tbody tr');

function initCabecalho(tabela) {
  const tr = tabela.querySelector('thead tr');

  if (tr.querySelector('th[data-virtual="subtotal"]')) {
    return;
  }

  const th = document.createElement('th');
  th.setAttribute('data-virtual', 'subtotal');
  th.innerText = subtotal.label;

  tr.insertBefore(th, tr.lastElementChild);
}

initCabecalho(tabela);

function initSubtotalInput(linha) {
  if (linha.querySelector('td[data-virtual="subtotal"]')) {
    return;
  }

  const td = document.createElement('td');
  td.setAttribute('data-virtual', 'subtotal');

  const input = document.createElement('input');
  input.type = subtotal.type;
  input.readOnly = true;

  if (subtotal.required === true) {
    input.setAttribute('required', 'required');
  }

  input.value = '0';

  td.appendChild(input);
  linha.insertBefore(td, linha.lastElementChild);

  return input;
}

linhas.forEach((linha) => {
  const subtotalInput = initSubtotalInput(linha);

  const dependencias = {};

  subtotal.depends_on.forEach((campo) => {
    const input = linha.querySelector(`[data-field="${campo}"]`);

    if (!input) {
      console.warn(`[currency] campo '${campo}' não encontrado`);
      return;
    }

    dependencias[campo] = input;
  });

  function atualizar() {
    const valores = {};

    Object.keys(dependencias).forEach((campo) => {
      valores[campo] = Number(dependencias[campo].value || 0);
    });

    calcular(subtotalInput, valores);
    atualizarTotalGeral(tabela);
  }

  Object.values(dependencias).forEach((input) => {
    input.addEventListener('input', atualizar);
  });

  atualizar();
});

function atualizarTotalGeral(tabela) {
  const subtotalInputs = tabela.querySelectorAll(
    'td[data-virtual="subtotal"] input'
  );

  let total = 0;

  subtotalInputs.forEach((input) => {
    total += Number(input.value || 0);
  });

  const campoTotal = document.querySelector(`[data-field="${subtotal.output}"]`);

  if (campoTotal) {
    campoTotal.value = total.toFixed(2);
    return;
  }

  let resumo = tabela.nextElementSibling;

  if (!resumo || !resumo.classList.contains('total-geral-visual')) {
    resumo = document.createElement('div');
    resumo.className = 'total-geral-visual';
    resumo.style.marginTop = '10px';
    resumo.style.fontWeight = 'bold';

    tabela.parentNode.insertBefore(resumo, tabela.nextSibling);
  }

  resumo.innerText = 'Total: ' + total.toFixed(2);
}

function calcular(subtotalInput, valores) {
  const expressao = subtotal.calculation;

  try {
    const fn = new Function(
      ...Object.keys(valores),
      'return ' + expressao
    );

    const resultado = fn(...Object.values(valores));

    subtotalInput.value = Number(resultado || 0).toFixed(2);

  } catch (erro) {
    console.error('[currency] erro no cálculo:', expressao, erro);
    subtotalInput.value = 0;
  }
}
