<?php

define('INSTALL_PIPELINE', true);
echo "<h1>Configuração Final</h1>";

require_once dirname(__DIR__, 1) . '/config/actions/generateMetadata.php';
flush();

require_once dirname(__DIR__, 1) . '/config/actions/generateModules.php';
flush();

$storageDir = dirname(__DIR__, 2) . '/storage';

if (!is_dir($storageDir)) {
    mkdir($storageDir, 0777, true);
}

$lockFile = $storageDir . '/install.lock';

file_put_contents($lockFile, json_encode([
    'installed_at' => date('Y-m-d H:i:s'),
    'version' => '1.0.0'
], JSON_PRETTY_PRINT));

echo "<h2>✔ Configuração concluída</h2>";
echo "<a href='?modulo=studio'>Ir para o Studio</a>";
echo "<br>";
echo "<a href='../../'>Ir para a Tela Inicial</a>";

?>
