<?php

require_once __DIR__ . '/app/Controllers/UsuariosController.php';
require_once __DIR__ . '/app/Controllers/PessoasController.php';
require_once __DIR__ . '/app/Controllers/TiposAtendimentosController.php';
require_once __DIR__ . '/app/Controllers/AtendimentosController.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Middleware/auth.php';

$controller = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'login';

switch ($controller) {

    case 'auth':
        $authController = new AuthController();

        switch ($action) {
            case 'login':
                $authController->exibirLogin();
                break;

            case 'entrar':
                $authController->entrar();
                break;

            case 'dashboard':
                $authController->dashboard();
                break;

            case 'logout':
                $authController->logout();
                break;

            default:
                http_response_code(404);
                echo 'Ação de autenticação não encontrada';
        }
        break;

    case 'usuarios':
        $usuariosController = new UsuariosController();

        switch ($action) {
            case 'listar':
                $usuariosController->listar();
                break;

            case 'buscar':
                $usuariosController->buscarPorId();
                break;

            case 'criar':
                $usuariosController->criar();
                break;

            case 'atualizar':
                $usuariosController->atualizar();
                break;

            case 'excluir':
                $usuariosController->excluir();
                break;

            default:
                http_response_code(404);
                echo 'Ação de usuários não encontrada';
        }
        break;

    case 'pessoas':
        $pessoasController = new PessoasController();

        switch ($action) {
            case 'listar':
                $pessoasController->listar();
                break;

            case 'buscar':
                $pessoasController->buscarPorId();
                break;

            case 'criar':
                $pessoasController->criar();
                break;

            case 'atualizar':
                $pessoasController->atualizar();
                break;

            case 'excluir':
                $pessoasController->excluir();
                break;

            default:
                http_response_code(404);
                echo 'Ação de pessoas não encontrada';
        }
        break;

    case 'atendimentos':
        $atendimentosController = new AtendimentosController();

        switch ($action) {
            case 'listar':
                $atendimentosController->listar();
                break;

            case 'buscar':
                $atendimentosController->buscarPorId();
                break;

            case 'criar':
                $atendimentosController->criar();
                break;

            case 'atualizar':
                $atendimentosController->atualizar();
                break;

            case 'excluir':
                $atendimentosController->excluir();
                break;

            default:
                http_response_code(404);
                echo 'Ação de atendimentos não encontrada';
        }
        break;

    case 'tiposatendimentos':
        $tiposAtendimentosController = new TiposAtendimentosController();

        switch ($action) {
            case 'listar':
                $tiposAtendimentosController->listar();
                break;

            case 'buscar':
                $tiposAtendimentosController->buscarPorId();
                break;

            case 'criar':
                $tiposAtendimentosController->criar();
                break;

            case 'atualizar':
                $tiposAtendimentosController->atualizar();
                break;

            case 'excluir':
                $tiposAtendimentosController->excluir();
                break;

            default:
                http_response_code(404);
                echo 'Ação de tipos de atendimentos não encontrada';
        }
        break;

    default:
        http_response_code(404);
        echo '<p>Controller não encontrado.</p>';
}
