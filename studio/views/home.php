<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Studio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .modules {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }

        .module {
            border: 1px solid #ccc;
            padding: 16px;
            text-align: center;
            border-radius: 6px;
            background: #f9f9f9;
        }

        .module a {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            background: #222;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }

        .module a:hover {
            background: #444;
        }
    </style>
</head>

<body>
    <a href="index.php?modulo=config">← voltar</a>
    <h1>Studio</h1>
    <p>Selecione um módulo para editar</p>

    <a href="index.php?modulo=studio&view=table/create">
        + Criar novo módulo
    </a>

    <div class="modules">
        <?php foreach ($layoutData['tabelas'] as $modulo): ?>
            <div class="module">
                <strong><?= ucfirst($modulo) ?></strong>
                <br>
                <a href="?modulo=studio&view=json&entity=<?= urlencode($modulo) ?>">Json</a>

                <a href="?modulo=studio&view=form&entity=<?= urlencode($modulo) ?>">
                    Formulário
                </a>

                <a href="?modulo=studio&view=subitems&entity=<?= urlencode($modulo) ?>">
                    SubPainéis
                </a>

                <a href="?modulo=studio&view=table/edit&entity=<?= urlencode($modulo) ?>">
                    Editar campos
                </a>
                <a href="?modulo=studio&operation=delete&entity=<?= urlencode($modulo) ?>"
                    onclick="return confirm('Deseja realmente deletar esta tabela?')">
                    🗑️ Deletar Tabela
                </a>
            </div>
        <?php endforeach; ?>
    </div>

</body>

</html>