<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = $dao->createTable($_POST);
    if ($resultado) {
        $metadata->generate();
        echo "<h1>Criado com sucesso!<h1>";
    }
}

echo "<a href='index.php?modulo=studio'>← voltar</a>";

renderComponent('createTableForm', [
    'dao' => $dao
]);
