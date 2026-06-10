<?php

/**
 * Sistema de Rotas - AtendeLab
 * 
 * Este arquivo gerencia todas as rotas da aplicação
 * Inclui controllers para: Usuarios, Pessoas, Tipos de Atendimentos e Atendimentos
 */

// ==================== INCLUDES ====================
require_once __DIR__ . '/app/Controllers/UsuariosController.php';
require_once __DIR__ . '/app/Controllers/PessoasController.php';
require_once __DIR__ . '/app/Controllers/TiposAtendimentosController.php';
require_once __DIR__ . '/app/Controllers/AtendimentosController.php';

// ==================== OBTER MÉTODO E CAMINHO ====================
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove o prefixo da aplicação (ajuste conforme necessário)
$basePath = '/atendelab/public';
$path = str_replace($basePath, '', $requestUri);

// Se a rota não começar com /, adiciona
if (!str_starts_with($path, '/')) {
    $path = '/' . $path;
}

// Remove trailing slash (exceto para raiz)
if ($path !== '/' && str_ends_with($path, '/')) {
    $path = rtrim($path, '/');
}

// ==================== ROTEAMENTO ====================

// ROTAS: USUARIOS
if (str_starts_with($path, '/usuarios')) {
    $controller = new UsuariosController();
    
    switch (true) {
        case $path === '/usuarios/listar':
            $controller->listar();
            break;
        case $path === '/usuarios/buscarPorId':
            $controller->buscarPorId();
            break;
        case $path === '/usuarios/criar' && $method === 'POST':
            $controller->criar();
            break;
        case $path === '/usuarios/atualizar' && $method === 'POST':
            $controller->atualizar();
            break;
        case $path === '/usuarios/excluir' && $method === 'POST':
            $controller->excluir();
            break;
        default:
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['erro' => 'Rota não encontrada'], JSON_UNESCAPED_UNICODE);
            break;
    }
    exit;
}

// ROTAS: PESSOAS
if (str_starts_with($path, '/pessoas')) {
    $controller = new PessoasController();
    
    switch (true) {
        case $path === '/pessoas/listar':
            $controller->listar();
            break;
        case $path === '/pessoas/buscarPorId':
            $controller->buscarPorId();
            break;
        case $path === '/pessoas/criar' && $method === 'POST':
            $controller->criar();
            break;
        case $path === '/pessoas/atualizar' && $method === 'POST':
            $controller->atualizar();
            break;
        case $path === '/pessoas/excluir' && $method === 'POST':
            $controller->excluir();
            break;
        default:
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['erro' => 'Rota não encontrada'], JSON_UNESCAPED_UNICODE);
            break;
    }
    exit;
}

// ROTAS: TIPOS DE ATENDIMENTOS
if (str_starts_with($path, '/tipos-atendimentos')) {
    $controller = new TiposAtendimentosController();
    
    switch (true) {
        case $path === '/tipos-atendimentos/listar':
            $controller->listar();
            break;
        case $path === '/tipos-atendimentos/buscarPorId':
            $controller->buscarPorId();
            break;
        case $path === '/tipos-atendimentos/criar' && $method === 'POST':
            $controller->criar();
            break;
        case $path === '/tipos-atendimentos/atualizar' && $method === 'POST':
            $controller->atualizar();
            break;
        case $path === '/tipos-atendimentos/excluir' && $method === 'POST':
            $controller->excluir();
            break;
        default:
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['erro' => 'Rota não encontrada'], JSON_UNESCAPED_UNICODE);
            break;
    }
    exit;
}

// ROTAS: ATENDIMENTOS
if (str_starts_with($path, '/atendimentos')) {
    $controller = new AtendimentosController();
    
    switch (true) {
        case $path === '/atendimentos/listar':
            $controller->listar();
            break;
        case $path === '/atendimentos/buscarPorId':
            $controller->buscarPorId();
            break;
        case $path === '/atendimentos/criar' && $method === 'POST':
            $controller->criar();
            break;
        case $path === '/atendimentos/atualizar' && $method === 'POST':
            $controller->atualizar();
            break;
        case $path === '/atendimentos/excluir' && $method === 'POST':
            $controller->excluir();
            break;
        default:
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['erro' => 'Rota não encontrada'], JSON_UNESCAPED_UNICODE);
            break;
    }
    exit;
}

// ROTA PADRÃO (raiz)
if ($path === '/') {
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'mensagem' => 'Bem-vindo ao AtendeLab API',
        'versao' => '1.0.0',
        'endpoints' => [
            'usuarios' => '/usuarios/listar, /usuarios/buscarPorId, /usuarios/criar, /usuarios/atualizar, /usuarios/excluir',
            'pessoas' => '/pessoas/listar, /pessoas/buscarPorId, /pessoas/criar, /pessoas/atualizar, /pessoas/excluir',
            'tipos_atendimentos' => '/tipos-atendimentos/listar, /tipos-atendimentos/buscarPorId, /tipos-atendimentos/criar, /tipos-atendimentos/atualizar, /tipos-atendimentos/excluir',
            'atendimentos' => '/atendimentos/listar, /atendimentos/buscarPorId, /atendimentos/criar, /atendimentos/atualizar, /atendimentos/excluir'
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// ROTA NÃO ENCONTRADA
http_response_code(404);
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'erro' => 'Rota não encontrada',
    'path' => $path,
    'metodo' => $method
], JSON_UNESCAPED_UNICODE);
exit;