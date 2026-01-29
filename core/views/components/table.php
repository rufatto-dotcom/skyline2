<?php
$dao = new DAO();

$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

$rows  = $dao->selectPage($modulo, $page, $perPage);
$total = $dao->countAll($modulo);

$totalPages = (int) ceil($total / $perPage);
?>

<link rel="stylesheet" href="core/assets/css/table.css">

<div class="list-view">
    <div class="container">
        <?php if ($totalPages > 1): ?>
            <div class="pagination" style="margin-top: 15px; display: flex; gap: 10px; align-items: center;">

                <?php if ($page > 1): ?>
                    <a href="index.php?modulo=<?= htmlspecialchars($modulo) ?>&page=<?= $page - 1 ?>">
                        ← Anterior
                    </a>
                <?php endif; ?>

                <span>
                    Página <?= $page ?> de <?= $totalPages ?>
                </span>

                <?php if ($page < $totalPages): ?>
                    <a href="index.php?modulo=<?= htmlspecialchars($modulo) ?>&page=<?= $page + 1 ?>">
                        Próxima →
                    </a>
                <?php endif; ?>

            </div>
        <?php endif; ?>
        <div class="container-head" style="display: flex; align-items: center; justify-content: space-between;">
            <h1><?= htmlspecialchars($moduleLabel); ?></h1>
            <a href="index.php?modulo=<?= $modulo ?>&action=EditView">
                <button type="button">Inserir novo <?= htmlspecialchars($moduleLabel) ?></button>
            </a>
        </div>

        <table class="container-table">
            <thead>
                <tr>
                    <?php foreach ($fields as $fieldName => $fieldMeta):
                        $showInList = ($fieldMeta['listview_treatment'] ?? 'show') !== 'remove';
                        if (!$showInList) continue;
                    ?>
                        <th><?= htmlspecialchars($fieldMeta['label'] ?? ucfirst($fieldName)) ?></th>
                    <?php endforeach; ?>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($fields as $fieldName => $fieldMeta):
                            $showInList = ($fieldMeta['listview_treatment'] ?? 'show') !== 'remove';
                            if (!$showInList) continue;

                            $cellValue = $row[$fieldName] ?? null;
                        ?>
                            <td>
                                <?php
                                $relatedModuleName = $fieldMeta['related_module'] ?? null;

                                if ($relatedModuleName && $cellValue) {
                                    $relatedRecord = $dao->select($relatedModuleName, $cellValue);

                                    $displayField = null;
                                    foreach ($fieldMeta['related_fields'] ?? [] as $relatedFieldMeta) {
                                        if ($relatedFieldMeta['Field'] !== 'id') {
                                            $displayField = $relatedFieldMeta['Field'];
                                            break;
                                        }
                                    }

                                    $displayText = $relatedRecord[$displayField] ?? $cellValue;
                                    $linkUrl = "index.php?modulo=" . htmlspecialchars($relatedModuleName) . "&action=DetailView&id=" . htmlspecialchars($relatedRecord['id'] ?? '');
                                } else {
                                    if (($fieldMeta['input'] ?? '') === 'checkbox') {
                                        $displayText = $cellValue ? '✔' : 'X';
                                        $linkUrl = "index.php?modulo=" . htmlspecialchars($modulo) . "&action=DetailView&id=" . htmlspecialchars($row['id'] ?? '');
                                    } else {
                                        $displayText = $cellValue;
                                        $linkUrl = "index.php?modulo=" . htmlspecialchars($modulo) . "&action=DetailView&id=" . htmlspecialchars($row['id'] ?? '');
                                    }
                                }
                                ?>
                                <a href="<?= $linkUrl ?>"><?= htmlspecialchars($displayText ?? '') ?></a>
                            </td>
                        <?php endforeach; ?>

                        <td>
                            <a href="#"
                                data-action="delete"
                                data-id="<?= htmlspecialchars($row['id']) ?>"
                                data-modulo="<?= htmlspecialchars($modulo) ?>">
                                Delete
                            </a>
                            <a href="index.php?modulo=<?= htmlspecialchars($modulo) ?>&action=EditView&id=<?= htmlspecialchars($row['id'] ?? '') ?>">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</div>

<script>
    document.addEventListener('click', async (e) => {
        const el = e.target.closest('[data-action="delete"]');
        if (!el) return;

        e.preventDefault();

        const id = el.dataset.id;
        const modulo = el.dataset.modulo;

        if (!id || !modulo) return;

        if (!confirm('Deseja mesmo deletar este registro?')) return;

        try {
            const res = await fetch(
                `index.php?modulo=${encodeURIComponent(modulo)}&operation=delete&id=${encodeURIComponent(id)}`, {
                    method: 'POST'
                }
            );

            if (!res.ok) {
                alert('Erro ao deletar');
                return;
            }

            location.reload();
        } catch {
            alert('Erro de conexão');
        }
    });
</script>
