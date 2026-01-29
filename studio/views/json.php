<?php

try {
    $baseMetadata   = $studio->getMetadataDefault($entity);
    $customMetadata = $studio->getMetadataCustom($entity);
    $mergedMetadata = $studio->getMetadataMerged($entity);
} catch (Throwable $e) {
    echo '<h2>Erro ao carregar metadata</h2>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    return;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Studio – <?= htmlspecialchars($entity) ?></title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
            background: #f5f5f5;
        }

        h1 {
            margin-bottom: 5px;
        }

        .top {
            margin-bottom: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
        }

        .box {
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 12px;
            overflow: auto;
            max-height: 70vh;
        }

        .box h3 {
            margin-top: 0;
            font-size: 14px;
            background: #eee;
            padding: 6px;
            border-radius: 4px;
        }

        pre {
            font-size: 12px;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .muted {
            color: #777;
            font-size: 12px;
        }

        a {
            text-decoration: none;
            color: #0077cc;
        }
    </style>
</head>
<body>

<div class="top">
    <a href="index.php?modulo=studio">← voltar</a>
    <h1>Studio / <?= htmlspecialchars($entity) ?></h1>
    <div class="muted">
        Base | Custom | Merged
    </div>
</div>

<div class="grid">

    <div class="box">
        <h3>Metadata Base</h3>
        <pre><?= htmlspecialchars(json_encode($baseMetadata, JSON_PRETTY_PRINT)) ?></pre>
    </div>

    <div class="box">
        <h3>Metadata Custom</h3>
        <?php if ($customMetadata): ?>
            <pre><?= htmlspecialchars(json_encode($customMetadata, JSON_PRETTY_PRINT)) ?></pre>
        <?php else: ?>
            <p class="muted">Nenhum override custom.</p>
        <?php endif; ?>
    </div>

    <div class="box">
        <h3>Metadata Merged</h3>
        <pre><?= htmlspecialchars(json_encode($mergedMetadata, JSON_PRETTY_PRINT)) ?></pre>
    </div>

</div>

</body>
</html>