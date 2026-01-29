<?php

define('INSTALL_PIPELINE', true);
echo "<h1>Configuração Final</h1>";

require_once dirname(__DIR__, 1) . '/config/actions/generateMetadata.php';
flush();

require_once dirname(__DIR__, 1) . '/config/actions/generateModules.php';
flush();

$requiredDirs = [
    ROOT_PATH . '/modules',
    ROOT_PATH . '/modules/custom',
    ROOT_PATH . '/metadata/custom',
    ROOT_PATH . '/storage'
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

$lockFile = ROOT_PATH . '/storage/install.lock';

file_put_contents(
    $lockFile,
    json_encode([
        'installed_at' => date('Y-m-d H:i:s'),
        'version'      => '1.0.0'
    ], JSON_PRETTY_PRINT)
);

echo "<h2>✔ Configuração concluída</h2>";
echo "<a href='?modulo=studio'>Ir para o Studio</a>";
echo "<br>";
echo "<a href='../../'>Ir para a Tela Inicial</a>";

?>
