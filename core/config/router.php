<?php
/*
 * Skyline2
 * Database-driven PHP framework
 *
 * Copyright (c) 2026 Rufatto
 * Licensed under the MIT License
 * Contact: https://github.com/rufatto-dotcom
 */

$action = $_GET['action'] ?? null;

if ($action === 'studio') {
    include __DIR__ . '/studio/router.php';
    return;
}

switch ($action) {

    case 'generateMetadata':
        include __DIR__ . '/actions/generateMetadata.php';
        break;

    case 'saveConfig':
        include __DIR__ . '/actions/save.php';
        break;

    case 'generateModules':
        include __DIR__ . '/actions/generateModules.php';
        break;

    case 'generateRelationships':
        include __DIR__ . '/actions/generateRelationships.php';
        break;

    case 'generateReportMetadata':
        include __DIR__ . '/actions/generateReportMetadata.php';
        break;

    case null:
        include __DIR__ . '/index.php';
        break;

    default:
        echo 'Ação inválida em CONFIG.';
}

