<?php

define('INSTALL_PIPELINE', true);
echo "<h1>Configuração Final</h1>";

require_once dirname(__DIR__, 1) . '/config/actions/generateMetadata.php';
flush();

require_once dirname(__DIR__, 1) . '/config/actions/generateModules.php';
flush();

$lockFile = dirname(__DIR__, 2) . '/storage/install.lock';

if (file_exists($lockFile)) {
    unlink($lockFile);
}

if (!file_exists($lockFile)) {
    file_put_contents($lockFile, json_encode([
        'installed_at' => date('Y-m-d H:i:s'),
        'version' => '1.0.0'
    ], JSON_PRETTY_PRINT));
}

echo "<h2>✔ Configuração concluída</h2>";
echo "<a href='?modulo=studio'>Ir para o Studio</a>";
echo "<br>";
echo "<a href='../../'>Ir para a Tela Inicial</a>";

?>