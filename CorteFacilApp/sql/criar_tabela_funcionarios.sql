-- Criação da tabela funcionários
CREATE TABLE IF NOT EXISTS funcionarios (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) DEFAULT NULL,
    salao_id INT(11) NOT NULL,
    especialidade VARCHAR(100) DEFAULT NULL,
    valor_servico DECIMAL(10,2) DEFAULT 0.00,
    horario_trabalho_inicio TIME DEFAULT '09:00',
    horario_trabalho_fim TIME DEFAULT '18:00',
    dias_trabalho VARCHAR(20) DEFAULT '1,2,3,4,5,6',
    ativo BOOLEAN DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY email (email),
    FOREIGN KEY (salao_id) REFERENCES saloes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;