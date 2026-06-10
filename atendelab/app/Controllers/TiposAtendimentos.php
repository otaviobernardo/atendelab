<?php

class TiposAtendimentosController 
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

        $sql = 'SELECT id, nome, descricao, status
                FROM tipos_atendimentos
                WHERE 1=1';
        
        if ($filtroStatus) {
            if (!in_array($filtroStatus, ['ATIVO', 'INATIVO'], true)) {
                http_response_code(400);
                echo json_encode(['erro' => 'Status inválido. Use: ATIVO ou INATIVO']);
                return;
            }
            $sql .= ' AND status = :status';
        }

        $sql .= ' ORDER BY nome ASC';

        $stmt = $this->pdo->prepare($sql);
        
        if ($filtroStatus) {
            $stmt->bindValue(':status', $filtroStatus);
        }
        
        $stmt->execute();
        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($tipos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

        $sql = 'SELECT id, nome, descricao, status
                FROM tipos_atendimentos
                WHERE id = :id';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $tipo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tipo) {
            http_response_code(404);
            echo json_encode(['erro' => 'Tipo de atendimento não encontrado']);
            return;
        }

        echo json_encode($tipo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void 
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $status = $_POST['status'] ?? 'ATIVO';

        if ($nome === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome é obrigatório']);
            return;
        }

        if (!in_array($status, ['ATIVO', 'INATIVO'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido. Use: ATIVO ou INATIVO']);
            return;
        }

        try {
            // Verificar se nome já existe
            $sqlVerifica = 'SELECT id FROM tipos_atendimentos WHERE nome = :nome';
            $stmtVerifica = $this->pdo->prepare($sqlVerifica);
            $stmtVerifica->bindValue(':nome', $nome);
            $stmtVerifica->execute();

            if ($stmtVerifica->fetch()) {
                http_response_code(409);
                echo json_encode(['erro' => 'Tipo de atendimento com este nome já existe']);
                return;
            }

            $sql = 'INSERT INTO tipos_atendimentos (nome, descricao, status)
                    VALUES (:nome, :descricao, :status)';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':descricao', $descricao ?: null);
            $stmt->bindValue(':status', $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Tipo de atendimento cadastrado com sucesso',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar tipo de atendimento']);
        }
    }

    public function atualizar(): void 
    {
        header('Content-Type: application/json; charset=utf-8');
 
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $status = $_POST['status'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido']);
            return;
        }

        if ($status && !in_array($status, ['ATIVO', 'INATIVO'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido. Use: ATIVO ou INATIVO']);
            return;
        }

        try {
            // Verificar se existe
            $sqlVerifica = 'SELECT id FROM tipos_atendimentos WHERE id = :id';
            $stmtVerifica = $this->pdo->prepare($sqlVerifica);
            $stmtVerifica->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtVerifica->execute();

            if (!$stmtVerifica->fetch()) {
                http_response_code(404);
                echo json_encode(['erro' => 'Tipo de atendimento não encontrado']);
                return;
            }

            // Verificar se nome já existe (se foi alterado)
            if ($nome !== '') {
                $sqlVerificaNome = 'SELECT id FROM tipos_atendimentos WHERE nome = :nome AND id != :id';
                $stmtVerificaNome = $this->pdo->prepare($sqlVerificaNome);
                $stmtVerificaNome->bindValue(':nome', $nome);
                $stmtVerificaNome->bindValue(':id', $id, PDO::PARAM_INT);
                $stmtVerificaNome->execute();

                if ($stmtVerificaNome->fetch()) {
                    http_response_code(409);
                    echo json_encode(['erro' => 'Tipo de atendimento com este nome já existe']);
                    return;
                }
            }

            $campos = [];
            $params = [':id' => $id];

            if ($nome !== '') {
                $campos[] = 'nome = :nome';
                $params[':nome'] = $nome;
            }
            if ($descricao !== '') {
                $campos[] = 'descricao = :descricao';
                $params[':descricao'] = $descricao;
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

            $sql = 'UPDATE tipos_atendimentos SET ' . implode(', ', $campos) . ' WHERE id = :id';
            
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                if ($key === ':id') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->execute();

            http_response_code(200);
            echo json_encode([
                'mensagem' => 'Tipo de atendimento atualizado com sucesso'
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar tipo de atendimento']);
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
            // Verificar se tipo tem atendimentos
            $sqlVerifica = 'SELECT COUNT(*) as total FROM atendimentos WHERE tipo_atendimento = :id';
            $stmtVerifica = $this->pdo->prepare($sqlVerifica);
            $stmtVerifica->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtVerifica->execute();
            $resultado = $stmtVerifica->fetch(PDO::FETCH_ASSOC);

            if ($resultado['total'] > 0) {
                http_response_code(409);
                echo json_encode(['erro' => 'Não é possível deletar tipo de atendimento com registros associados']);
                return;
            }

            $sql = 'DELETE FROM tipos_atendimentos WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['erro' => 'Tipo de atendimento não encontrado']);
                return;
            }

            http_response_code(200);
            echo json_encode(['mensagem' => 'Tipo de atendimento excluído com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao excluir tipo de atendimento.']);
        }
    }
}