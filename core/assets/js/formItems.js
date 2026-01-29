document.addEventListener('DOMContentLoaded', () => {
    console.log('[DEBUG] DOMContentLoaded disparado. Iniciando script de tabela-itens.');

    document.querySelectorAll('.tabela-itens').forEach((table) => {
        console.log('[DEBUG] Processando tabela:', table);

        const modulo = table.dataset.modulo;
        const tbody = table.querySelector('tbody');
        const addBtn = table.nextElementSibling?.classList.contains('addItem')
            ? table.nextElementSibling
            : null;

        if (!modulo || !tbody) {
            console.warn('[DEBUG] Tabela ignorada: módulo ou tbody ausente.', { modulo, tbody });
            return;
        }

        let index = getMaxIndex(tbody) + 1;
        console.log(`[DEBUG] Índice inicial calculado para módulo "${modulo}":`, index);

        // =========================
        // EXCLUIR ITEM
        // =========================
        table.addEventListener('click', (e) => {
            if (!e.target.classList.contains('deleteItem')) return;

            console.log('[DEBUG] Clique detectado em botão de exclusão:', e.target);
            const row = e.target.closest('tr');
            if (!row) {
                console.warn('[DEBUG] Linha não encontrada para exclusão.');
                return;
            }

            const idInput = row.querySelector(
                `input[type="hidden"][name^="items[${modulo}]"][name$="[id]"]`
            );

            if (idInput) {
                // item existente → soft delete
                console.log('[DEBUG] Item existente identificado. Aplicando soft delete.');
                let deletedInput = row.querySelector(
                    `input[name^="items[${modulo}]"][name$="[deleted]"]`
                );

                if (!deletedInput) {
                    deletedInput = document.createElement('input');
                    deletedInput.type = 'hidden';
                    deletedInput.name = idInput.name.replace('[id]', '[deleted]');
                    row.appendChild(deletedInput);
                    console.log('[DEBUG] Campo [deleted] criado:', deletedInput.name);
                }

                deletedInput.value = 1;
                row.style.display = 'none';
                console.log('[DEBUG] Linha ocultada via soft delete.');
            } else {
                // item novo → remove
                console.log('[DEBUG] Item novo detectado. Removendo linha do DOM.');
                row.remove();
            }
        });

        // =========================
        // ADICIONAR ITEM
        // =========================
        if (addBtn) {
            console.log('[DEBUG] Botão de adicionar encontrado:', addBtn);
            addBtn.addEventListener('click', () => {
                console.log('[DEBUG] Botão "Adicionar Item" clicado.');

                const templateRow = tbody.querySelector('tr[data-template="1"]');
                if (!templateRow) {
                    console.warn('[DEBUG] Template de linha não encontrado no tbody.');
                    return;
                }

                const newRow = templateRow.cloneNode(true);
                console.log('[DEBUG] Linha template clonada. Novo índice:', index);

                newRow.querySelectorAll('input, select, textarea').forEach(el => {
                    if (el.name) {
                        const oldName = el.name;
                        el.name = el.name.replace('__INDEX__', index);
                        console.log(`[DEBUG] Atualizado name: "${oldName}" → "${el.name}"`);
                    }

                    if (el.type === 'checkbox') {
                        el.checked = false;
                        console.log('[DEBUG] Checkbox resetado:', el.name || 'sem nome');
                    } else {
                        el.value = '';
                        console.log('[DEBUG] Campo limpo:', el.name || 'sem nome');
                    }

                    if (el.id) {
                        el.removeAttribute('id');
                        console.log('[DEBUG] ID removido do elemento:', el.tagName);
                    }
                });

                newRow.querySelectorAll('.autocomplete-box').forEach(el => {
                    console.log('[DEBUG] Removendo autocomplete-box:', el);
                    el.remove();
                });

                newRow.removeAttribute('data-template');
                newRow.style.display = '';

                tbody.appendChild(newRow);

                initAutocomplete(newRow);

                console.log('[DEBUG] Nova linha adicionada ao tbody.');

                index++;

                if (typeof runBehaviorEngine === 'function') {
                    console.log('[DEBUG] Executando runBehaviorEngine...');
                    runBehaviorEngine();
                } else {
                    console.log('[DEBUG] runBehaviorEngine não está definida.');
                }
            });
        } else {
            console.log('[DEBUG] Nenhum botão "addItem" associado à tabela.');
        }
    });

    // =========================
    // UTIL
    // =========================
    function getMaxIndex(tbody) {
        console.log('[DEBUG] Calculando índice máximo no tbody:', tbody);
        let max = -1;

        tbody.querySelectorAll('input[name^="items["]').forEach(input => {
            const match = input.name.match(/\[(\d+)\]/);
            if (match) {
                const num = parseInt(match[1], 10);
                max = Math.max(max, num);
                console.log(`[DEBUG] Encontrado índice no name: ${num}, max atual: ${max}`);
            }
        });

        console.log(`[DEBUG] Índice máximo encontrado: ${max}`);
        return max;
    }
});