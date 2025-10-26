-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS aconito_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE aconito_db;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

-- Tabela de bebidas
CREATE TABLE IF NOT EXISTS bebidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    imagem VARCHAR(255),
    preco DECIMAL(10,2) NOT NULL,
    estoque INT DEFAULT 0,
    sabor VARCHAR(100),
    ingredientes TEXT,
    usuario_id INT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

ALTER TABLE usuarios
ADD COLUMN email VARCHAR(100) NOT NULL UNIQUE AFTER login;

ALTER TABLE usuarios
ADD COLUMN nome VARCHAR(100) AFTER email;

ALTER TABLE bebidas ADD litragem VARCHAR(50) AFTER nome;ALTER TABLE bebidas ADD litragem VARCHAR(50) AFTER nome;

ALTER TABLE usuarios
ADD COLUMN data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP;