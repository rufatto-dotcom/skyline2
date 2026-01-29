<?php
/*
 * Skyline2
 * Database-driven PHP framework
 *
 * Copyright (c) 2026 Rufatto
 * Licensed under the MIT License
 * Contact: https://github.com/rufatto-dotcom
 */


require_once __DIR__ . '/core/bootstrap.php';
require_once CORE_PATH . '/requestHandler.php';

$modulo = $_GET['modulo'] ?? '';
$responseType = $_REQUEST['_response'] ?? 'html';

if ($modulo === 'studio') {
    include 'studio/index.php';
    exit;
}

$modulosEspeciais = ['config'];

if ($responseType === 'html')  {
    include_once CORE_LAYOUT . '/header.php';
}

if ($modulo === '') {
    include_once CORE_LAYOUT . '/indexView.php';

} else if (in_array($modulo, $modulosEspeciais)) {
    include_once CONFIG_PATH . '/router.php';
} else {
    $moduleRouter = MODULES_PATH . "/$modulo/router.php";
    if (file_exists($moduleRouter)) {
        include_once $moduleRouter;
    } else if (!file_exists($moduleRouter)) {
        $moduleRouter = MODULES_PATH . "/custom/$modulo/router.php";
        include_once $moduleRouter;
    } else {
        echo "<h2>Módulo '$modulo' não encontrado.</h2>";
    }
}

if ($responseType === 'html') {

    include_once CORE_LAYOUT . '/footer.php';
}

?>