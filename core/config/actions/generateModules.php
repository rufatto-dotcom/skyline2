<?php
require_once __DIR__ . '/generateFunctions.php';

regenerateModules();

if (!defined('INSTALL_PIPELINE')) {
    echo '<h2>Módulos gerados!</h2>';
    echo "<a href='?modulo=config'>Voltar</a>";
}