-- ------------------------------------------------------------------
--  BANCO ACÔNITO
-- ------------------------------------------------------------------

CREATE DATABASE IF NOT EXISTS aconito
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE aconito;

-- ==============================================================
-- 1) USUÁRIOS
-- ==============================================================

DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(120) DEFAULT NULL UNIQUE,
    nome VARCHAR(120) DEFAULT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin','cliente') NOT NULL DEFAULT 'cliente',
    data_cadastro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==============================================================
-- 2) BEBIDAS
-- ==============================================================

DROP TABLE IF EXISTS bebidas;

CREATE TABLE bebidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    litragem VARCHAR(50) DEFAULT NULL,
    descricao TEXT DEFAULT NULL,
    imagem VARCHAR(255) DEFAULT NULL,
    preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estoque INT NOT NULL DEFAULT 0,
    sabor VARCHAR(120) DEFAULT NULL,
    ingredientes TEXT DEFAULT NULL,

    usuario_id INT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) 
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==============================================================
-- 3) CARRINHO (itens temporários)
-- ==============================================================

DROP TABLE IF EXISTS carrinho;

CREATE TABLE carrinho (
    id INT AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT NOT NULL,
    bebida_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (bebida_id) REFERENCES bebidas(id) ON DELETE CASCADE,

    UNIQUE KEY unique_item (usuario_id, bebida_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==============================================================
-- 4) COMPRAS
-- ==============================================================

DROP TABLE IF EXISTS compras;

CREATE TABLE compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    data_compra TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==============================================================
-- 5) ITENS DAS COMPRAS
-- ==============================================================

DROP TABLE IF EXISTS itens_compra;

CREATE TABLE itens_compra (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compra_id INT NOT NULL,
    bebida_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (compra_id) REFERENCES compras(id) ON DELETE CASCADE,
    FOREIGN KEY (bebida_id) REFERENCES bebidas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==============================================================
-- 6) INSERT DO ADMINISTRADOR
-- ==============================================================

INSERT INTO usuarios (login, email, nome, senha, tipo)
VALUES (
    'Bruxeiro06',
    NULL,
    'Administrador',
    '$2b$12$LxEGV9BWPyFzmKRxIe595eb0EtVl8FeIjtw3QhFbINEgwgqC/qX.q',
    'admin'
);


-- =============================================================
-- 7) ADIÇÕES POSTERIORES
-- ==============================================================

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    itens LONGTEXT NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);
