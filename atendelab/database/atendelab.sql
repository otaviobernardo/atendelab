CREATE DATABASE atendlab;
USE atendlab;

-- ==========================
-- Tabela: usuarios
-- ==========================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('ADMIN', 'ATENDENTE', 'PROFESSOR') NOT NULL,
    status ENUM('ATIVO', 'INATIVO') NOT NULL DEFAULT 'ATIVO',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================
-- Tabela: pessoas
-- ==========================
CREATE TABLE pessoas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    documento VARCHAR(20) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    curso VARCHAR(100),
    periodo VARCHAR(100),
    status VARCHAR(100) DEFAULT 'ATIVO'
);

-- ==========================
-- Tabela: tipos_atendimentos
-- ==========================
CREATE TABLE tipos_atendimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    status ENUM('ATIVO', 'INATIVO') NOT NULL DEFAULT 'ATIVO'
);

-- ==========================
-- Tabela: atendimentos
-- ==========================
CREATE TABLE atendimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,

    pessoa_id INT NOT NULL,
    tipo_atendimento INT NOT NULL,
    usuario_id INT NOT NULL,

    data_atendimento DATE NOT NULL,
    hora_atendimento TIME NOT NULL,

    descricao TEXT NOT NULL,
    observacao TEXT,

    status ENUM('ABERTO', 'EM_ANDAMENTO', 'CONCLUIDO', 'CANCELADO')
        NOT NULL DEFAULT 'ABERTO',

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_atendimento_pessoa
        FOREIGN KEY (pessoa_id)
        REFERENCES pessoas(id),

    CONSTRAINT fk_atendimento_tipo
        FOREIGN KEY (tipo_atendimento)
        REFERENCES tipos_atendimentos(id),

    CONSTRAINT fk_atendimento_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
);