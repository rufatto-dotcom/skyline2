document.addEventListener('DOMContentLoaded', () => {
  initAutocomplete();
});

function initAutocomplete(context = document) {
  // Busca todos os inputs de autocomplete DENTRO do contexto
  const inputs = context.querySelectorAll
    ? context.querySelectorAll('[data-related-module]')
    : [context].filter(el => el.hasAttribute('data-related-module'));

  inputs.forEach(input => {
    if (input.dataset.autocompleteReady) return;
    input.dataset.autocompleteReady = '1';

    const relatedModule = input.dataset.relatedModule;
    const relatedPk = input.dataset.relatedPk || 'id';
    const searchField = input.dataset.searchField;

    // 🔑 Correção: encontra o hiddenInput PELO NOME, não pelo ID
    // Supõe que o hiddenInput está logo antes ou no mesmo container
    const nameAttr = input.name;
    if (!nameAttr) return;

    // O nome do hiddenInput é o mesmo do input de texto (mas ele é hidden)
    // Na verdade, no seu HTML, o hiddenInput tem o mesmo `name` do campo real!
    // Mas o input de texto NÃO tem `name` — só o hidden tem!
    // Então vamos buscar o hiddenInput pelo `id` que corresponde ao `data-related-name`
    const relatedName = input.dataset.relatedName; // ex: items[modulo][0][campo]
    const hiddenInput = context.querySelector(`input[type="hidden"][name="${CSS.escape(relatedName)}"]`);

    if (!hiddenInput) {
      console.warn('Hidden input not found for:', relatedName, 'in context', context);
      return;
    }

    // Cria o box de autocomplete
    const autocompleteBox = document.createElement('div');
    autocompleteBox.className = 'autocomplete-box';
    input.parentNode.appendChild(autocompleteBox);

    /* =========================
       LOAD INITIAL LABEL
       ========================= */
    if (hiddenInput.value) {
      const url =
        `api/index.php?modulo=${encodeURIComponent(relatedModule)}` +
        `&operation=get&id=${encodeURIComponent(hiddenInput.value)}` +
        `&labelField=${encodeURIComponent(searchField)}`;

      fetch(url)
        .then(res => res.ok ? res.json() : Promise.reject())
        .then(data => {
          input.value = data?.label ?? `ID: ${hiddenInput.value}`;
        })
        .catch(() => {
          input.value = `ID: ${hiddenInput.value}`;
        });
    }

    /* =========================
       AUTOCOMPLETE SEARCH
       ========================= */
    input.addEventListener('input', async () => {
      const term = input.value.trim();
      autocompleteBox.innerHTML = '';

      if (term.length < 2) return;

      try {
        const url =
          `api/index.php?modulo=${encodeURIComponent(relatedModule)}` +
          `&operation=search&campo=${encodeURIComponent(searchField)}` +
          `&q=${encodeURIComponent(term)}`;

        const res = await fetch(url);

        if (!res.ok) {
          autocompleteBox.innerHTML = `<div class="item empty">Erro na requisição</div>`;
          return;
        }

        const results = await res.json();

        if (!Array.isArray(results) || results.length === 0) {
          autocompleteBox.innerHTML = `<div class="item empty">Nenhum resultado</div>`;
          return;
        }

        results.forEach(item => {
          const itemEl = document.createElement('div');
          itemEl.className = 'item';
          itemEl.textContent = item.label ?? item[searchField];

          itemEl.addEventListener('click', () => {
            input.value = item.label ?? item[searchField];
            hiddenInput.value = item[relatedPk] ?? item.id;
            autocompleteBox.innerHTML = '';
          });

          autocompleteBox.appendChild(itemEl);
        });

      } catch (err) {
        console.error('Autocomplete error:', err);
        autocompleteBox.innerHTML = `<div class="item empty">Erro na busca</div>`;
      }
    });
  });
}