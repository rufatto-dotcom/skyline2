<?php
require_once dirname(__DIR__, 1) . '/dao/DAO.php';
$dao = new DAO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dao->createTable($_POST);
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

$tables = $dao->whitelist();
$hasTables = empty($tables) ? false : true;

if (!$hasTables) {
?>
    <h1>Nenhuma tabela encontrada</h1>
    <h2>Crie pelo menos uma tabela para começar</h2>
<?php
    renderComponent(
        'createTableForm',
        [
            'dao' => $dao,
        ]
    );
} else {
    echo "<h1>Tabelas encontradas</h1>";
    echo "<h4>Você pode usar o Studio para criar mais tabelas após a instalação.</h4>";
    echo "<h3>Tabelas Encontradas:</h3>";
    foreach ($tables as $table) {
        echo "<h4>$table</h4>";
        echo "<br>";
    }
    echo "<a href='step4.php' class='btn btn-primary'>Próximo</a>";
}

?>