<?php
try {
    $fields = $studio->getEntityFields($entity);
} catch (Throwable $e) {
    echo '<h2>Erro ao carregar metadata</h2>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    return;
}

$formOptions = [
    'hidden',
    'remove',
    'show',
];

$listOptions = [
    'remove',
    'show',
];

uasort($fields, function ($a, $b) {
    $orderA = $a['order'] ?? 0;
    $orderB = $b['order'] ?? 0;

    return $orderA <=> $orderB;
});


?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Studio – <?= htmlspecialchars($entity) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f6f6f6;
        }

        h1 {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        th,
        td {
            padding: 8px 10px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #f0f0f0;
        }

        .origin-base {
            color: #555;
        }

        .origin-custom {
            color: #c0392b;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            background: #eee;
        }

        .badge-show {
            background: #dff0d8;
        }

        .badge-hidden {
            background: #fcf8e3;
        }

        .badge-remove {
            background: #f2dede;
        }

        a {
            text-decoration: none;
            color: #0077cc;
        }
    </style>
</head>

<body>

    <a href="index.php?modulo=studio">← voltar</a>

    <h1>Studio / <?= htmlspecialchars($entity) ?></h1>

    <form method="post"
        action="index.php?modulo=studio&operation=saveMetadata&entity=<?= htmlspecialchars($entity) ?>">
        <input type="hidden" name="entity" value="<?= htmlspecialchars($entity) ?>">
        <div style="margin-bottom: 20px;">
            <label for="label"><strong>Label da Entidade:</strong></label>
            <input type="text" id="label" name="label"
                value="<?= htmlspecialchars($studio->getEntityLabel($entity) ?? $entity) ?>"
                style="margin-left: 10px; padding: 5px; width: 300px;">
        </div>
        <table>
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Label</th>
                    <th>Input</th>
                    <th>Form</th>
                    <th>Lista</th>
                    <th>Origem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fields as $name => $field): ?>
                    <tr data-field="<?= htmlspecialchars($name) ?>">
                        <input type="hidden" name="fields[<?= htmlspecialchars($name) ?>][order]"
                            value="<?= (int) ($field['order'] ?? 0) ?>" data-order-field="<?= htmlspecialchars($name) ?>">
                        <td><?= htmlspecialchars($name) ?></td>
                        <td> <input name="fields[<?= $name ?>][label]" type="text"
                                value="<?= htmlspecialchars($field['label'] ?? '-') ?>"></td>
                        <td><?= htmlspecialchars($field['input'] ?? '-') ?></td>
                        <td>
                            <select name="fields[<?= $name ?>][treatment]">
                                <?php foreach ($formOptions as $option): ?>
                                    <?php
                                    $selected = '';
                                    if ($option === $field['treatment']) {
                                        $selected = 'selected';
                                    }
                                    ?>
                                    <option value="<?= $option ?>" <?= $selected ?>><?= $option ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="badge badge-<?= $field['treatment'] ?>">
                                <?= htmlspecialchars($field['treatment']) ?>
                            </span>
                        </td>
                        <td>
                            <select name="fields[<?= $name ?>][listview_treatment]">
                                <?php foreach ($listOptions as $option): ?>
                                    <?php
                                    $selected = '';
                                    if ($option === $field['listview_treatment']) {
                                        $selected = 'selected';
                                    }
                                    ?>
                                    <option value="<?= $option ?>" <?= $selected ?>><?= $option ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="badge badge-<?= $field['listview_treatment'] ?>">
                                <?= htmlspecialchars($field['listview_treatment']) ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $origin = $field['origin'] ?? 'base';
                            ?>
                            <span class="<?= $origin === 'custom' ? 'origin-custom' : 'origin-base' ?>">
                                <?= $origin ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit">Salvar alterações</button>
    </form>
</body>

</html>

<script src="studio/assets/js/form.js"></script>