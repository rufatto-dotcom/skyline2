<?php
/*
 * Skyline2
 * Database-driven PHP framework
 *
 * Copyright (c) 2026 Rufatto
 * Licensed under the MIT License
 * Contact: https://github.com/rufatto-dotcom
 */


$handlersPath = CORE_PATH . '/handlers';

foreach (scandir($handlersPath) as $file) {
    if ($file === '.' || $file === '..') {
        continue;
    }

    if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
        continue;
    }

    require_once $handlersPath . '/' . $file;
}

$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$segments = $path === '' ? [] : explode('/', $path);

$segments = array_values(array_filter($segments, fn($s) => $s !== ''));

$skylineIndex = array_search('skyline2', $segments);
if ($skylineIndex !== false) {
    $segments = array_values(array_slice($segments, $skylineIndex + 1));
}

$target = null;

if (!empty($segments[0]) && $segments[0] !== 'index.php') {
    $target = strtolower($segments[0]);
}

if (!$target && isset($_GET['modulo'])) {
    $target = strtolower($_GET['modulo']);
}

$target ??= 'core';

$operation = $_GET['operation'] ?? $_POST['operation'] ?? null;

if (!$operation) {
    return;
}

$jsonData = null;
$rawInput = '';

if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH'])) {
    $rawInput = file_get_contents('php://input');
    $jsonData = json_decode($rawInput, true);
}

$isApi =
    $target === 'api'
    || isset($_GET['api'])
    || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
    || $jsonData !== null;


$cleanPathSegments = array_slice($segments, 1);
$cleanPath = '/' . ltrim(implode('/', $cleanPathSegments), '/');

$context = [
    'method'     => $_SERVER['REQUEST_METHOD'],
    'target'     => $target,
    'path'       => $cleanPath,
    'fullPath'   => '/' . $path,
    'operation'  => $operation,
    'isApi'      => $isApi,
    'get'        => $_GET,
    'post'       => $_POST,
    'request'    => $_REQUEST,
    'json'       => $jsonData,
    'rawInput'   => $rawInput,
];

switch ($target) {
    case 'api':
        $handler = new ApiHandler();
        break;
    case 'studio':
        $handler = new StudioHandler();
        break;
    case 'core':
    default:
        $handler = new CoreHandler();
        break;
}

$response = $handler->handle($context);

if ($response !== null) {
    handlerResponse($response);
    exit;
}

function handlerResponse(array $response): void
{
    switch ($response['type'] ?? 'html') {
        case 'redirect':
            header("Location: " . $response['location']);
            exit;
        case 'json':
            http_response_code($response['code'] ?? 200);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(
                $response['data'],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
            exit;
        case 'error':
            http_response_code($response['code'] ?? 500);
            echo $response['message'];
            exit;
        default:
            echo $response['content'] ?? '';
            exit;
    }
}
