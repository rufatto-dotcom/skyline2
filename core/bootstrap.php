<?php
/*
 * Skyline2
 * Database-driven PHP framework
 *
 * Copyright (c) 2026 Rufatto
 * Licensed under the MIT License
 * Contact: https://github.com/rufatto-dotcom
 */


/* =========================================================
 | Paths & Constants
 ========================================================= */

define('ROOT_PATH', dirname(__DIR__));

define('CORE_PATH', ROOT_PATH . '/core');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('METADATA_PATH', ROOT_PATH . '/metadata');
define('API_PATH', ROOT_PATH . '/api');
define('CONFIG_PATH', CORE_PATH . '/config');

define('CORE_VIEWS', CORE_PATH . '/views');
define('CORE_LAYOUT', CORE_VIEWS . '/layout');
define('CORE_COMPONENTS', CORE_VIEWS . '/components');
define('CORE_ROUTES', CORE_PATH . '/routes');
define('CORE_SERVICES', CORE_PATH . '/services');
define('CORE_DAO', CORE_PATH . '/dao');

define('ASSETS_PATH', CORE_PATH . '/assets');
define('ASSETS_CSS', ASSETS_PATH . '/css');
define('ASSETS_JS', ASSETS_PATH . '/js');
define('ASSETS_UI', ASSETS_JS . '/ui');
define('ASSETS_IMG', ASSETS_PATH . '/img');

/* =========================================================
 | Base URL
 ========================================================= */

function getBaseUrl(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    $basePath = ($basePath === '.' || $basePath === '/') ? '' : $basePath . '/';

    return "{$protocol}://{$host}/{$basePath}";
}

define('BASE_URL', getBaseUrl());

/* =========================================================
 | Core utilities & services
 ========================================================= */

require_once CORE_PATH . '/utils.php';
require_once CORE_SERVICES . '/log.php';

$log = new Log();

/* =========================================================
 | Installation state
 ========================================================= */

$installLock = ROOT_PATH . '/storage/install.lock';
$isInstalled = file_exists($installLock);

/* =========================================================
 | System services (only if installed)
 ========================================================= */

$dao = null;
$metadata = null;

if ($isInstalled) {
    require_once CORE_DAO . '/DAO.php';
    require_once METADATA_PATH . '/metadata.php';

    try {
        $dao = new DAO();
        $metadata = new Metadata();
    } catch (PDOException $e) {

        if (isInstallError($e)) {
            header('Location: core/install/reinstall.php');
            exit;
        }

        error_log($e);
        http_response_code(500);
        echo 'Erro interno do sistema.';
        exit;
    }
} else {
    header('Location: core/install/step1.php');
    exit;
}

function isInstallError(PDOException $e): bool
{
    $info = $e->errorInfo;

    return isset($info[1]) && in_array($info[1], [
        1049,
        1045,
        2002,
    ]);
}
