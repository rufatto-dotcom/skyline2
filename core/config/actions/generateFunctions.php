<?php

require_once dirname(__DIR__, 2) . '/services/log.php';

// =============== FUNÇÕES DE METADATA ===============

function regenerateMetadata(): void
{
    require_once dirname(__DIR__, 3) . '/metadata/metadata.php';
    $meta = new Metadata();
    $meta->generate();
}

// =============== FUNÇÕES DE MÓDULOS ===============

function deleteDirectory(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}

function clearModulesDirectory(string $modulesPath, Log $log): void
{
    if (!is_dir($modulesPath)) {
        return;
    }

    $items = scandir($modulesPath);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === 'custom') {
            continue;
        }

        $path = $modulesPath . '/' . $item;
        if (is_dir($path)) {
            deleteDirectory($path);
            $log->info("Módulo removido", ['module' => $item]);
        }
    }
}

function ensureDirectory(string $path, Log $log): void
{
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
        $log->info("Pasta criada", ['path' => $path]);
    }
}

function ensureFile(string $filePath, string $content, Log $log, string $tag): void
{
    if (!file_exists($filePath)) {
        file_put_contents($filePath, $content);
        $log->info("$tag criado", ['file' => $filePath]);
    }
}

function createModuleFolders(string $modulePath, Log $log): void
{
    $folders = [
        $modulePath,
        $modulePath . '/views',
        $modulePath . '/js',
        $modulePath . '/css',
        $modulePath . '/pdf',
    ];

    foreach ($folders as $folder) {
        ensureDirectory($folder, $log);
    }
}

function createModuleFiles(string $module, string $modulePath, Log $log): void
{
    require_once dirname(__DIR__, 2) . '/templates.php';

    $views = ['edit', 'detail'];
    foreach ($views as $view) {
        $file = $modulePath . "/views/$view.php";
        ensureFile($file, EDIT_AND_VIEW_TEMPLATE, $log, 'View');
    }

    ensureFile($modulePath . "/router.php", ROUTER_TEMPLATE, $log, 'Router');
    ensureFile($modulePath . "/views/index.php", INDEX_TEMPLATE, $log, 'Index View');
    ensureFile($modulePath . "/js/$module.js", '', $log, 'JS');
    ensureFile($modulePath . "/css/$module.css", '', $log, 'CSS');
    ensureFile(
        $modulePath . "/pdf/template.php",
        PDF_TEMPLATE,
        $log,
        'PDF Template'
    );
}

function generateModule(string $jsonFile, Log $log): void
{
    $module = basename($jsonFile, '.json');
    $modulePath = dirname(__DIR__, 3) . '/modules/' . $module;

    createModuleFolders($modulePath, $log);
    createModuleFiles($module, $modulePath, $log);

    $log->info('Módulo gerado', ['module' => $module]);
}

function regenerateModules(): void
{
    $log = new Log();

    ensureDirectory(dirname(__DIR__, 3) . "/modules/", $log);
    clearModulesDirectory(dirname(__DIR__, 3) . "/modules/", $log);

    $jsonFiles = glob(dirname(__DIR__, 3) . '/metadata/*.json');
    foreach ($jsonFiles as $jsonFile) {
        generateModule($jsonFile, $log);
    }

}
