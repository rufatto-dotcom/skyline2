<?php
$modules = [];

foreach (['modules', 'modules/custom'] as $base) {
    $path = ROOT_PATH . '/' . $base;

    if (!is_dir($path)) {
        continue;
    }

    foreach (scandir($path) as $dir) {
        if ($dir[0] === '.') {
            continue;
        }

        if (!is_dir("$path/$dir")) {
            continue;
        }

        if ($base === 'modules' && $dir === 'custom') {
            continue;
        }

        $modules[] = $dir;
    }
}

$modules = array_unique($modules);

$layoutData = [
    'tabelas' => $modules,
    'app' => [
        'nome' => 'Skyline2',
        'versao' => '1.0.0'
    ]
];

$tabelas = $layoutData['tabelas'] ?? [];

$maxVisible = 5;
$visible = array_slice($tabelas, 0, $maxVisible);
$overflow = array_slice($tabelas, $maxVisible);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="core/assets/css/header.css">
    <script src="core/assets/js/moduleBase.js"></script>

    <title><?= htmlspecialchars($layoutData['app']['nome']) ?></title>
</head>

<body>
    <div class="header-container">
        <div class="header-logo">
            <img src="core/assets/img/logo.png" alt="Logo">
        </div>

        <div class="header-search-bar">
            <input type="text" placeholder="Pesquisar...">
        </div>

        <nav class="header-navbar">
            <ul class="header-navbar-list">
                <li><a href="index.php">Início</a></li>

                <?php foreach ($visible as $tabela): ?>
                    <li>
                        <a href="index.php?modulo=<?= htmlspecialchars($tabela) ?>">
                            <?= ucfirst(str_replace('_', ' ', $tabela)) ?>
                        </a>
                    </li>
                <?php endforeach; ?>

                <?php if (!empty($overflow)): ?>
                    <li class="dropdown">
                        <a href="#">Mais ▾</a>
                        <ul class="dropdown-menu">
                            <?php foreach ($overflow as $tabela): ?>
                                <li>
                                    <a href="index.php?modulo=<?= htmlspecialchars($tabela) ?>">
                                        <?= ucfirst(str_replace('_', ' ', $tabela)) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>

            </ul>
        </nav>

        <div class="header-tools">
            <a href="index.php?modulo=config">Configurações</a>
        </div>
    </div>
