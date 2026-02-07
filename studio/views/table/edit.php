<?php
if (empty($_GET['entity'])) {
    http_response_code(400);
    echo "<p>❌ Erro: nome da tabela não informado.</p>";
    return;
}

$table = trim($_GET['entity']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = false;
    $message = '';

    if (!empty($_POST['action'])) {
        try {
            if ($_POST['action'] === 'add' && !empty($_POST['field']['name'])) {
                if ($dao->alterTableAddColumn($table, $_POST['field'])) {
                    $success = true;
                    $message = "✅ Coluna '{$_POST['field']['name']}' adicionada com sucesso!";
                } else {
                    $message = "❌ Falha ao adicionar coluna. Verifique o nome e tipo.";
                }
            } elseif ($_POST['action'] === 'drop' && !empty($_POST['column'])) {
                if ($dao->alterTableDropColumn($table, $_POST['column'])) {
                    $success = true;
                    $message = "✅ Coluna '{$_POST['column']}' removida com sucesso!";
                } else {
                    $message = "❌ Não foi possível remover a coluna '{$_POST['column']}'. Pode estar em uso ou ser chave primária.";
                }
            }
        } catch (Exception $e) {
            $message = "⚠️ Erro: " . htmlspecialchars($e->getMessage());
        }
    }

    $feedback = [
        'success' => $success,
        'message' => $message
    ];
}

$currentColumns = $dao->generateTable($table);
?>

<a href='index.php?modulo=studio'>← voltar</a>

<div class="studio-table-alter">
    <h2>🛠️ Alterar Estrutura da Tabela: <code><?= htmlspecialchars($table) ?></code></h2>

    <?php if (!empty($feedback)): ?>
        <div style="margin: 16px 0; padding: 12px; background: <?= $feedback['success'] ? '#d4edda' : '#f8d7da' ?>; border: 1px solid <?= $feedback['success'] ? '#c3e6cb' : '#f5c6cb' ?>; color: #000;">
            <?= htmlspecialchars($feedback['message']) ?>
        </div>
    <?php endif; ?>

    <section style="margin-top: 24px;">
        <h3>📋 Colunas existentes (<?= count($currentColumns) ?>)</h3>
        <?php if (!empty($currentColumns)): ?>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <?php foreach ($currentColumns as $col):
                    $isPrimaryKey = ($col['Key'] === 'PRI');
                    $isAutoIncrement = (strpos($col['Extra'], 'auto_increment') !== false);
                ?>
                    <li style="display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid #eee;">
                        <span style="flex: 1; font-family: monospace;">
                            <strong><?= htmlspecialchars($col['Field']) ?></strong>
                            <small style="color: #666; margin-left: 8px;">
                                <?= htmlspecialchars($col['Type']) ?>
                                <?= $col['Null'] === 'NO' ? 'NOT NULL' : '' ?>
                                <?= $isPrimaryKey ? '🔑 PK' : '' ?>
                                <?= $isAutoIncrement ? '🔄 AUTO' : '' ?>
                            </small>
                        </span>

                        <?php if (!$isPrimaryKey): ?>
                            <form method="POST" style="display:inline;"
                                onsubmit="return confirm('Tem certeza que deseja remover a coluna \" <?= htmlspecialchars($col['Field']) ?>\"? Esta ação não pode ser desfeita.');">
                                <input type="hidden" name="action" value="drop">
                                <input type="hidden" name="column" value="<?= htmlspecialchars($col['Field']) ?>">
                                <button type="submit"
                                    style="background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer;"
                                    title="Remover coluna">
                                    🗑️ Remover
                                </button>
                            </form>
                        <?php else: ?>
                            <span style="color: #999; font-style: italic;">(protegida)</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Nenhuma coluna encontrada.</p>
        <?php endif; ?>
    </section>

    <section style="margin-top: 32px; padding-top: 20px; border-top: 2px solid #ddd;">
        <h3>➕ Adicionar Nova Coluna</h3>
        <form method="POST" style="max-width: 500px;">
            <input type="hidden" name="action" value="add">

            <div style="margin-bottom: 12px;">
                <label for="field_name" style="display: block; margin-bottom: 4px; font-weight: bold;">
                    Nome da coluna:
                </label>
                <input type="text"
                    id="field_name"
                    name="field[name]"
                    placeholder="ex: email, data_nascimento"
                    required
                    pattern="[a-zA-Z_][a-zA-Z0-9_]*"
                    title="Use letras, números e _ (não comece com número)"
                    style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display: block; margin-bottom: 4px; font-weight: bold;">
                    Tipo de dado:
                </label>
                <select name="field[type]" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="varchar">Texto curto (VARCHAR)</option>
                    <option value="text">Texto longo (TEXT)</option>
                    <option value="int">Número inteiro (INT)</option>
                    <option value="decimal">Decimal (DECIMAL)</option>
                    <option value="date">Data (DATE)</option>
                    <option value="datetime">Data e Hora (DATETIME)</option>
                    <option value="boolean">Sim/Não (BOOLEAN)</option>
                </select>
            </div>
            <div style="margin-bottom: 12px;">
                <label for="field_length" style="display: block; margin-bottom: 4px; font-weight: bold;">
                    Comprimento (apenas para VARCHAR):
                </label>
                <input type="number"
                    id="field_length"
                    name="field[length]"
                    min="1"
                    max="65535"
                    placeholder="ex: 255"
                    style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <small style="color: #666;">Deixe vazio para usar 255 (padrão).</small>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display: block; margin-bottom: 4px; font-weight: bold;">
                    <input type="checkbox" name="field[required]" value="1"> Obrigatório?
                </label>
            </div>

            <button type="submit"
                style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">
                ➕ Adicionar Coluna
            </button>
        </form>
    </section>

    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px dashed #ccc; font-size: 0.9em; color: #666;">
        <p>⚠️ <strong>Atenção:</strong> Alterações na estrutura da tabela afetam diretamente os dados e módulos associados. Faça backup antes de operações críticas.</p>
    </div>
</div>