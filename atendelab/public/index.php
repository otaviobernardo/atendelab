<?php

define('APP_ROOT', dirname(__DIR__));
define('APP_NAME', 'AtendeLab');
define('APP_VERSION', '1.0.0');

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/atendelab/public';

$path = str_replace($basePath, '', $requestUri);

if (!str_starts_with($path, '/')) {
    $path = '/' . $path;
}


if (
    str_starts_with($path, '/usuarios') ||
    str_starts_with($path, '/pessoas') ||
    str_starts_with($path, '/tipos-atendimentos') ||
    str_starts_with($path, '/atendimentos') ||
    $path === '/'
) {
    require_once APP_ROOT . '/routes.php';
} else {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'erro' => 'Rota não encontrada',
        'path' => $path,
        'sugestao' => 'Use /usuarios, /pessoas, /tipos-atendimentos ou /atendimentos'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>