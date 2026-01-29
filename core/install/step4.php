<?php

define('INSTALL_PIPELINE', true);
$rootPath = dirname(__DIR__, 2);
echo "<h1>Configuração Final</h1>";

require_once dirname(__DIR__, 1) . '/config/actions/generateMetadata.php';
flush();

require_once dirname(__DIR__, 1) . '/config/actions/generateModules.php';
flush();

$requiredDirs = [
    $rootPath . '/modules',
    $rootPath . '/modules/custom',
    $rootPath . '/metadata/custom',
    $rootPath . '/storage'
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

$lockFile = $rootPath . '/storage/install.lock';

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
