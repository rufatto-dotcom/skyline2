<?php
require_once __DIR__ . '/generateFunctions.php';

regenerateMetadata();

if (!defined('INSTALL_PIPELINE')) {
    echo "<h2>Metadata gerado!</h2>";
    echo "<a href='?modulo=config'>Voltar</a>";
}