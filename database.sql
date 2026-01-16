CREATE DATABASE IF NOT EXISTS financeiro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE financeiro;

CREATE TABLE IF NOT EXISTS lancamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    tipo ENUM('credito','debito') NOT NULL,
    data_lancamento DATE NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
); 