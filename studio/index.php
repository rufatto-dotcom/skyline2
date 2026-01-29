<?php
/*
 * Skyline2
 * Database-driven PHP framework
 *
 * Copyright (c) 2026 Rufatto
 * Licensed under the MIT License
 * Contact: https://github.com/rufatto-dotcom
 */

require_once 'core/bootstrap.php';
require_once 'studio.php';
require_once 'core/requestHandler.php';

$studio = new Studio($metadata);

$modules = [];

foreach (['modules'] as $base) {
    $path = ROOT_PATH . '/' . $base;

    foreach (scandir($path) as $dir) {
        if ($dir[0] === '.') continue;
        if (!is_dir("$path/$dir")) continue;

        if ($base === 'modules' && $dir === 'custom') continue;

        $modules[] = $dir;
    }
}

$modules = array_unique($modules);

$layoutData = [
    'tabelas' => $modules,
    'app' => [
        'nome' => 'Skyline',
        'versao' => '0.5.9'
    ]
];

$tabelas = $layoutData['tabelas'] ?? [];

include 'router.php';