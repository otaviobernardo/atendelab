<?php

class PessoasController 
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
        $filtroCurso = filter_input(INPUT_GET, 'curso', FILTER_SANITIZE_STRING);

        $sql = 'SELECT id, nome, documento, telefone, curso, periodo, status
                FROM pessoas
                WHERE 1=1';
        
        $params = [];

        if ($filtroStatus) {
            $sql .= ' AND status = :status';
            $params[':status'] = $filtroStatus;
        }

        if ($filtroCurso) {
            $sql .= ' AND curso LIKE :curso';
            $params[':curso'] = "%{$filtroCurso}%";
        }

        $sql .= ' ORDER BY nome ASC';

        $stmt = $this->pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($pessoas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

        $sql = 'SELECT id, nome, documento, telefone, curso, periodo, status
                FROM pessoas
                WHERE id = :id';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            http_response_code(404);
            echo json_encode(['erro' => 'Pessoa não encontrada']);
            return;
        }

        echo json_encode($pessoa, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void 
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $curso = trim($_POST['curso'] ?? '');
        $periodo = trim($_POST['periodo'] ?? '');
        $status = $_POST['status'] ?? 'ATIVO';

        if ($nome === '' || $documento === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Nome e documento são obrigatórios']);
            return;
        }

        if (!in_array($status, ['ATIVO', 'INATIVO'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido. Use: ATIVO ou INATIVO']);
            return;
        }

        try {
            // Verificar se documento já existe
            $sqlVerifica = 'SELECT id FROM pessoas WHERE documento = :documento';
            $stmtVerifica = $this->pdo->prepare($sqlVerifica);
            $stmtVerifica->bindValue(':documento', $documento);
            $stmtVerifica->execute();

            if ($stmtVerifica->fetch()) {
                http_response_code(409);
                echo json_encode(['erro' => 'Documento já cadastrado']);
                return;
            }

            $sql = 'INSERT INTO pessoas (nome, documento, telefone, curso, periodo, status)
                    VALUES (:nome, :documento, :telefone, :curso, :periodo, :status)';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':documento', $documento);
            $stmt->bindValue(':telefone', $telefone ?: null);
            $stmt->bindValue(':curso', $curso ?: null);
            $stmt->bindValue(':periodo', $periodo ?: null);
            $stmt->bindValue(':status', $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Pessoa cadastrada com sucesso',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar pessoa']);
        }
    }

    public function atualizar(): void 
    {
        header('Content-Type: application/json; charset=utf-8');
 
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $curso = trim($_POST['curso'] ?? '');
        $periodo = trim($_POST['periodo'] ?? '');
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
            // Verificar se pessoa existe
            $sqlVerifica = 'SELECT id FROM pessoas WHERE id = :id';
            $stmtVerifica = $this->pdo->prepare($sqlVerifica);
            $stmtVerifica->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtVerifica->execute();

            if (!$stmtVerifica->fetch()) {
                http_response_code(404);
                echo json_encode(['erro' => 'Pessoa não encontrada']);
                return;
            }

            // Verificar se documento já existe (se foi alterado)
            if ($documento) {
                $sqlVerificaDoc = 'SELECT id FROM pessoas WHERE documento = :documento AND id != :id';
                $stmtVerificaDoc = $this->pdo->prepare($sqlVerificaDoc);
                $stmtVerificaDoc->bindValue(':documento', $documento);
                $stmtVerificaDoc->bindValue(':id', $id, PDO::PARAM_INT);
                $stmtVerificaDoc->execute();

                if ($stmtVerificaDoc->fetch()) {
                    http_response_code(409);
                    echo json_encode(['erro' => 'Documento já cadastrado']);
                    return;
                }
            }

            $campos = [];
            $params = [':id' => $id];

            if ($nome !== '') {
                $campos[] = 'nome = :nome';
                $params[':nome'] = $nome;
            }
            if ($documento !== '') {
                $campos[] = 'documento = :documento';
                $params[':documento'] = $documento;
            }
            if ($telefone !== '') {
                $campos[] = 'telefone = :telefone';
                $params[':telefone'] = $telefone;
            }
            if ($curso !== '') {
                $campos[] = 'curso = :curso';
                $params[':curso'] = $curso;
            }
            if ($periodo !== '') {
                $campos[] = 'periodo = :periodo';
                $params[':periodo'] = $periodo;
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

            $sql = 'UPDATE pessoas SET ' . implode(', ', $campos) . ' WHERE id = :id';
            
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
                'mensagem' => 'Pessoa atualizada com sucesso'
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar pessoa']);
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
            // Verificar se pessoa tem atendimentos
            $sqlVerifica = 'SELECT COUNT(*) as total FROM atendimentos WHERE pessoa_id = :id';
            $stmtVerifica = $this->pdo->prepare($sqlVerifica);
            $stmtVerifica->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtVerifica->execute();
            $resultado = $stmtVerifica->fetch(PDO::FETCH_ASSOC);

            if ($resultado['total'] > 0) {
                http_response_code(409);
                echo json_encode(['erro' => 'Não é possível deletar pessoa com atendimentos cadastrados']);
                return;
            }

            $sql = 'DELETE FROM pessoas WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['erro' => 'Pessoa não encontrada']);
                return;
            }

            http_response_code(200);
            echo json_encode(['mensagem' => 'Pessoa excluída com sucesso.'], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao excluir pessoa.']);
        }
    }
}