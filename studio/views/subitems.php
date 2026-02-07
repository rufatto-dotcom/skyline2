<?php
try {
    $items = $studio->getItems($entity);
} catch (Throwable $e) {
    echo '<h2>Erro ao carregar relacionamentos</h2>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    return;
}

if (!is_array($items)) {
    $items = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Studio – Subitens – <?= htmlspecialchars($entity) ?></title>
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
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f0f0f0;
        }

        .toggle {
            display: inline-block;
            width: 40px;
            height: 20px;
            background: #ccc;
            border-radius: 10px;
            position: relative;
            cursor: pointer;
        }

        .toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle input:checked+.slider {
            background: #4CAF50;
        }

        .slider {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #ccc;
            border-radius: 10px;
            transition: .2s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 2px;
            bottom: 2px;
            background: white;
            border-radius: 50%;
            transition: .2s;
        }

        input:checked+.slider:before {
            transform: translateX(20px);
        }

        a {
            text-decoration: none;
            color: #0077cc;
        }

        button {
            margin-top: 16px;
            padding: 8px 16px;
            background: #222;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #444;
        }

        tr.custom {
            background: #fffbe6;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 11px;
            border-radius: 4px;
            margin-left: 6px;
        }

        .badge.custom {
            background: #ff9800;
            color: #000;
        }

        .badge.base {
            background: #ddd;
            color: #333;
        }
    </style>
</head>

<body>

    <a href="index.php?modulo=studio">← voltar</a>

    <h1>Studio / <?= htmlspecialchars($entity) ?> / Subitens</h1>
    <p>Configure os subpainéis (relacionamentos 1:N)</p>

    <form method="post"
        action="index.php?modulo=studio&operation=saveMetadata&entity=<?= htmlspecialchars($entity) ?>">
        <input type="hidden" name="entity" value="<?= htmlspecialchars($entity) ?>">

        <?php if (empty($items)): ?>
            <p>Nenhum relacionamento encontrado.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Módulo</th>
                        <th>Chave Estrangeira</th>
                        <th>Label</th>
                        <th>Ativo?</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <?php
                        [$module, $foreign_key] = array_pad(explode(':', $index, 2), 2, '');
                        $label = $item['label'];
                        $enabled = $item['enabled'] ? 'checked' : '';
                        $origin = $item['origin'] ?? 'base';
                        ?>
                        <tr class="<?= $origin === 'custom' ? 'custom' : '' ?>">
                            <td>
                                <?= htmlspecialchars($module) ?>
                                <span class="badge <?= $origin ?>">
                                    <?= $origin ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($foreign_key) ?></td>
                            <td>
                                <input type="text" name="items[<?= $index ?>][label]" value="<?= htmlspecialchars($label) ?>">
                            </td>
                            <td>
                                <input type="hidden" name="items[<?= $index ?>][enabled]" value="0">
                                <input type="checkbox" name="items[<?= $index ?>][enabled]" value="1" <?= $enabled ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        <?php endif; ?>

        <button type="submit">Salvar Subitens</button>
    </form>

</body>

</html>