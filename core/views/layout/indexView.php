<?php

$panels = [];

foreach ($layoutData['tabelas'] as $tabela) {
    $whitelist = $dao->whitelist();
    if (in_array($tabela, $whitelist)) {
        $colunas = $dao->generateTable($tabela);
        $fields = array_column($colunas, 'Field');

        $fields = array_values(
            array_filter($fields, fn($f) => $f !== 'id')
        );

        $page = (int) ($_GET['page'] ?? 1);
        $perPage = 20;

        $rows = $dao->selectPage($tabela, $page, $perPage);
        $total = $dao->countAll($tabela);

        $panels[] = [
            'titulo' => strtoupper($tabela),
            'fields' => $fields,
            'rows'   => $rows,
        ];
    }
}

?>

<link rel="stylesheet" href="core/assets/css/indexView.php">

<div class="container">
    <h1>Home</h1>

    <div class="content-container">
        <?php foreach ($panels as $panel): ?>
            <?php renderComponent('panel', $panel); ?>
        <?php endforeach; ?>
    </div>
</div>