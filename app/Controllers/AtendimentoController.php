<?php

class AtendimentosController 
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function listar(): void 
    {
        header('Content-Type: application/json; charset=utf-8');

        $filtroStatus = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);
        $filtroPessoaId = filter_input(INPUT_GET, 'pessoa_id', FILTER_VALIDATE_INT);
        $filtroUsuarioId = filter_input(INPUT_GET, 'usuario_id', FILTER_VALIDATE_INT);
        $filtroData = filter_input(INPUT_GET, 'data', FILTER_SANITIZE_STRING);

        $sql = 'SELECT a.id, a.pessoa_id, a.tipo_atendimento, a.usuario_id,
                       a.data_atendimento, a.hora_atendimento, a.descricao,
                       a.observacao, a.status, a.criado_em,
                       p.nome as pessoa_nome, t.nome as tipo_nome, u.nome as usuario_nome
                FROM atendimentos a
                LEFT JOIN pessoas p ON a.pessoa_id = p.id
                LEFT JOIN tipos_atendimentos t ON a.tipo_atendimento = t.id
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                WHERE 1=1';
        
        $params = [];

        if ($filtroStatus) {
            if (!in_array($filtroStatus, ['ABERTO', 'EM_ANDAMENTO', 'CONCLUIDO', 'CANCELADO'], true)) {
                http_response_code(400);
                echo json_encode(['erro' => 'Status inválido. Use: ABERTO, EM_ANDAMENTO, CONCLUIDO ou CANCELADO']);
                return;
            }
            $sql .= ' AND a.status = :status';
            $params[':status'] = $filtroStatus;
        }

        if ($filtroPessoaId) {
            $sql .= ' AND a.pessoa_id = :pessoa_id';
            $params[':pessoa_id'] = $filtroPessoaId;
        }

        if ($filtroUsuarioId) {
            $sql .= ' AND a.usuario_id = :usuario_id';
            $params[':usuario_id'] = $filtroUsuarioId;
        }

        if ($filtroData) {
            $sql .= ' AND DATE(a.data_atendimento) = :data';
            $params[':data'] = $filtroData;
        }

        $sql .= ' ORDER BY a.data_atendimento DESC, a.hora_atendimento DESC';

        $stmt = $this->pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $atendimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($atendimentos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function buscarPorId(): void 
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido']);
            return;
        }

        $sql = 'SELECT a.id, a.pessoa_id, a.tipo_atendimento, a.usuario_id,
                       a.data_atendimento, a.hora_atendimento, a.descricao,
                       a.observacao, a.status, a.criado_em,
                       p.nome as pessoa_nome, t.nome as tipo_nome, u.nome as usuario_nome
                FROM atendimentos a
                LEFT JOIN pessoas p ON a.pessoa_id = p.id
                LEFT JOIN tipos_atendimentos t ON a.tipo_atendimento = t.id
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                WHERE a.id = :id';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento) {
            http_response_code(404);
            echo json_encode(['erro' => 'Atendimento não encontrado']);
            return;
        }

        echo json_encode($atendimento, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void 
    {
        header('Content-Type: application/json; charset=utf-8');

        $pessoaId = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $tipoAtendimento = filter_input(INPUT_POST, 'tipo_atendimento', FILTER_VALIDATE_INT);
        $usuarioId = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
        $dataAtendimento = filter_input(INPUT_POST, 'data_atendimento', FILTER_SANITIZE_STRING);
        $horaAtendimento = filter_input(INPUT_POST, 'hora_atendimento', FILTER_SANITIZE_STRING);
        $descricao = trim($_POST['descricao'] ?? '');
        $observacao = trim($_POST['observacao'] ?? '');
        $status = $_POST['status'] ?? 'ABERTO';

        // Validações
        if (!$pessoaId || !$tipoAtendimento || !$usuarioId || !$dataAtendimento || !$horaAtendimento || $descricao === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Pessoa, tipo de atendimento, usuário, data, hora e descrição são obrigatórios']);
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataAtendimento)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Data inválida. Use o formato: YYYY-MM-DD']);
            return;
        }

        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $horaAtendimento)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Hora inválida. Use o formato: HH:MM ou HH:MM:SS']);
            return;
        }

        if (!in_array($status, ['ABERTO', 'EM_ANDAMENTO', 'CONCLUIDO', 'CANCELADO'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido. Use: ABERTO, EM_ANDAMENTO, CONCLUIDO ou CANCELADO']);
            return;
        }

        try {
            // Verificar se pessoa existe
            $sqlVerificaPessoa = 'SELECT id FROM pessoas WHERE id = :id';
            $stmtVerificaPessoa = $this->pdo->prepare($sqlVerificaPessoa);
            $stmtVerificaPessoa->bindValue(':id', $pessoaId, PDO::PARAM_INT);
            $stmtVerificaPessoa->execute();

            if (!$stmtVerificaPessoa->fetch()) {
                http_response_code(404);
                echo json_encode(['erro' => 'Pessoa não encontrada']);
                return;
            }

            // Verificar se tipo de atendimento existe
            $sqlVerificaTipo = 'SELECT id FROM tipos_atendimentos WHERE id = :id';
            $stmtVerificaTipo = $this->pdo->prepare($sqlVerificaTipo);
            $stmtVerificaTipo->bindValue(':id', $tipoAtendimento, PDO::PARAM_INT);
            $stmtVerificaTipo->execute();

            if (!$stmtVerificaTipo->fetch()) {
                http_response_code(404);
                echo json_encode(['erro' => 'Tipo de atendimento não encontrado']);
                return;
            }

            // Verificar se usuário existe
            $sqlVerificaUsuario = 'SELECT id FROM usuarios WHERE id = :id';
            $stmtVerificaUsuario = $this->pdo->prepare($sqlVerificaUsuario);
            $stmtVerificaUsuario->bindValue(':id', $usuarioId, PDO::PARAM_INT);
            $stmtVerificaUsuario->execute();

            if (!$stmtVerificaUsuario->fetch()) {
                http_response_code(404);
                echo json_encode(['erro' => 'Usuário não encontrado']);
                return;
            }

            $sql = 'INSERT INTO atendimentos (pessoa_id, tipo_atendimento, usuario_id,
                                            data_atendimento, hora_atendimento, descricao,
                                            observacao, status)
                    VALUES (:pessoa_id, :tipo_atendimento, :usuario_id,
                            :data_atendimento, :hora_atendimento, :descricao,
                            :observacao, :status)';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pessoa_id', $pessoaId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_atendimento', $tipoAtendimento, PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':data_atendimento', $dataAtendimento);
            $stmt->bindValue(':hora_atendimento', $horaAtendimento);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':observacao', $observacao ?: null);
            $stmt->bindValue(':status', $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Atendimento cadastrado com sucesso',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar atendimento']);
        }
    }

    public function atualizar(): void 
    {
        header('Content-Type: application/json; charset=utf-8');
 
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $dataAtendimento = filter_input(INPUT_POST, 'data_atendimento', FILTER_SANITIZE_STRING);
        $horaAtendimento = filter_input(INPUT_POST, 'hora_atendimento', FILTER_SANITIZE_STRING);
        $descricao = trim($_POST['descricao'] ?? '');
        $observacao = trim($_POST['observacao'] ?? '');
        $status = $_POST['status'] ?? null;
        $pessoaId = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $tipoAtendimento = filter_input(INPUT_POST, 'tipo_atendimento', FILTER_VALIDATE_INT);
        $usuarioId = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido']);
            return;
        }

        if ($dataAtendimento && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataAtendimento)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Data inválida. Use o formato: YYYY-MM-DD']);
            return;
        }

        if ($horaAtendimento && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $horaAtendimento)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Hora inválida. Use o formato: HH:MM ou HH:MM:SS']);
            return;
        }

        if ($status && !in_array($status, ['ABERTO', 'EM_ANDAMENTO', 'CONCLUIDO', 'CANCELADO'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido. Use: ABERTO, EM_ANDAMENTO, CONCLUIDO ou CANCELADO']);
            return;
        }

        try {
            // Verificar se atendimento existe
            $sqlVerifica = 'SELECT id FROM atendimentos WHERE id = :id';
            $stmtVerifica = $this->pdo->prepare($sqlVerifica);
            $stmtVerifica->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtVerifica->execute();

            if (!$stmtVerifica->fetch()) {
                http_response_code(404);
                echo json_encode(['erro' => 'Atendimento não encontrado']);
                return;
            }

            // Validações de chaves estrangeiras se fornecidas
            if ($pessoaId) {
                $sqlVerificaPessoa = 'SELECT id FROM pessoas WHERE id = :id';
                $stmtVerificaPessoa = $this->pdo->prepare($sqlVerificaPessoa);
                $stmtVerificaPessoa->bindValue(':id', $pessoaId, PDO::PARAM_INT);
                $stmtVerificaPessoa->execute();

                if (!$stmtVerificaPessoa->fetch()) {
                    http_response_code(404);
                    echo json_encode(['erro' => 'Pessoa não encontrada']);
                    return;
                }
            }

            if ($tipoAtendimento) {
                $sqlVerificaTipo = 'SELECT id FROM tipos_atendimentos WHERE id = :id';
                $stmtVerificaTipo = $this->pdo->prepare($sqlVerificaTipo);
                $stmtVerificaTipo->bindValue(':id', $tipoAtendimento, PDO::PARAM_INT);
                $stmtVerificaTipo->execute();

                if (!$stmtVerificaTipo->fetch()) {
                    http_response_code(404);
                    echo json_encode(['erro' => 'Tipo de atendimento não encontrado']);
                    return;
                }
            }

            if ($usuarioId) {
                $sqlVerificaUsuario = 'SELECT id FROM usuarios WHERE id = :id';
                $stmtVerificaUsuario = $this->pdo->prepare($sqlVerificaUsuario);
                $stmtVerificaUsuario->bindValue(':id', $usuarioId, PDO::PARAM_INT);
                $stmtVerificaUsuario->execute();

                if (!$stmtVerificaUsuario->fetch()) {
                    http_response_code(404);
                    echo json_encode(['erro' => 'Usuário não encontrado']);
                    return;
                }
            }

            $campos = [];
            $params = [':id' => $id];

            if ($pessoaId) {
                $campos[] = 'pessoa_id = :pessoa_id';
                $params[':pessoa_id'] = $pessoaId;
            }
            if ($tipoAtendimento) {
                $campos[] = 'tipo_atendimento = :tipo_atendimento';
                $params[':tipo_atendimento'] = $tipoAtendimento;
            }
            if ($usuarioId) {
                $campos[] = 'usuario_id = :usuario_id';
                $params[':usuario_id'] = $usuarioId;
            }
            if ($dataAtendimento !== '') {
                $campos[] = 'data_atendimento = :data_atendimento';
                $params[':data_atendimento'] = $dataAtendimento;
            }
            if ($horaAtendimento !== '') {
                $campos[] = 'hora_atendimento = :hora_atendimento';
                $params[':hora_atendimento'] = $horaAtendimento;
            }
            if ($descricao !== '') {
                $campos[] = 'descricao = :descricao';
                $params[':descricao'] = $descricao;
            }
            if ($observacao !== '') {
                $campos[] = 'observacao = :observacao';
                $params[':observacao'] = $observacao;
            }
            if ($status) {
                $campos[] = 'status = :status';
                $params[':status'] = $status;
            }

            if (empty($campos)) {
                http_response_code(400);
                echo json_encode(['erro' => 'Nenhum campo para atualizar']);
                return;
            }

            $sql = 'UPDATE atendimentos SET ' . implode(', ', $campos) . ' WHERE id = :id';
            
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                if (in_array($key, [':id', ':pessoa_id', ':tipo_atendimento', ':usuario_id'])) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->execute();

            http_response_code(200);
            echo json_encode([
                'mensagem' => 'Atendimento atualizado com sucesso'
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar atendimento']);
        }
    }

    public function excluir(): void 
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido']);
            return;
        }

        try {
            $sql = 'DELETE FROM atendimentos WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['erro' => 'Atendimento não encontrado']);
                return;
            }

            http_response_code(200);
            echo json_encode(['mensagem' => 'Atendimento excluído com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao excluir atendimento.']);
        }
    }
}