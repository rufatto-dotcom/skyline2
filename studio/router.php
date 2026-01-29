<?php
$entity = $_GET['entity'] ?? null;
$view   = $_GET['view'] ?? 'home';

$specialViews = ['table/create', 'table/delete'];

if (in_array($view, $specialViews)) {
    $viewFile = __DIR__ . "/views/{$view}.php";
    if (is_file($viewFile)) {
        include $viewFile;
        return;
    }
}

if (!$entity) {
    include __DIR__ . '/views/home.php';
    return;
}

$viewFile = __DIR__ . "/views/{$view}.php";

if (is_file($viewFile)) {
    include $viewFile;
    return;
}

http_response_code(404);
echo "View '{$view}' não encontrada";
