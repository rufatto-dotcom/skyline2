<h1>Create Table Form</h1>

<form id="createTableForm" method="POST">
    <div>
        <label for="table_name">Nome da Tabela:</label>
        <input type="text" id="table_name" name="table_name" required placeholder="ex: clientes">
    </div>

    <h3>Campos da Tabela</h3>

    <div class="field-row">
        <strong>ID (Primário)</strong>
        <input type="hidden" name="fields[id][name]" value="id">
        <input type="hidden" name="fields[id][type]" value="int">
        <input type="hidden" name="fields[id][auto_increment]" value="1">
        <input type="hidden" name="fields[id][required]" value="1">
        <p>(será criado automaticamente como chave primária)</p>
    </div>

    <div id="fields-container"></div>

    <div>
        <label for="new_field_name">Adicionar novo campo:</label>
        <input type="text" id="new_field_name" placeholder="ex: email, data_nascimento">
        <button type="button" onclick="addField()">+ Adicionar</button>
        <p id="field-error" aria-live="polite"></p>
    </div>

    <button type="submit">Criar Tabela</button>
</form>

<script>
function addField() {
    const input = document.getElementById('new_field_name');
    const errorEl = document.getElementById('field-error');
    const container = document.getElementById('fields-container');
    const fieldName = input.value.trim();

    errorEl.textContent = '';

    if (!fieldName) {
        errorEl.textContent = 'O nome do campo não pode estar vazio.';
        return;
    }

    if (!/^[a-zA-Z_][a-zA-Z0-9_]*$/.test(fieldName)) {
        errorEl.textContent = 'Nome inválido. Use letras, números e _ (não comece com número).';
        return;
    }

    if (fieldName.toLowerCase() === 'id') {
        errorEl.textContent = 'O campo "id" já existe como chave primária.';
        return;
    }

    const existingInputs = container.querySelectorAll(`input[name^="fields[${fieldName}]"]`);
    if (existingInputs.length > 0) {
        errorEl.textContent = 'Já existe um campo com esse nome.';
        return;
    }

    const row = document.createElement('div');
    row.className = 'field-row';

    row.innerHTML = `
        <input type="hidden" name="fields[${fieldName}][name]" value="${fieldName}">
        <div>
            <strong>${fieldName}</strong>
            <button type="button" onclick="this.closest('.field-row').remove()">Remover</button>
        </div>

        <label>
            Tipo:
            <select name="fields[${fieldName}][type]">
                <option value="varchar">Texto curto (VARCHAR)</option>
                <option value="text">Texto longo (TEXT)</option>
                <option value="int">Número inteiro (INT)</option>
                <option value="decimal">Decimal (DECIMAL)</option>
                <option value="date">Data (DATE)</option>
                <option value="datetime">Data e Hora (DATETIME)</option>
                <option value="boolean">Sim/Não (BOOLEAN)</option>
            </select>
        </label>

        <label>
            Comprimento (ex: 255 para VARCHAR):
            <input type="number" name="fields[${fieldName}][length]" min="1" max="65535" placeholder="opcional">
        </label>

        <label>
            <input type="checkbox" name="fields[${fieldName}][required]" value="1"> Obrigatório?
        </label>
    `;

    container.appendChild(row);
    input.value = '';
}
</script>