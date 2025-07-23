-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS cortefacil;
USE cortefacil;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('cliente', 'salao', 'admin') DEFAULT 'cliente',
    cpf VARCHAR(14) DEFAULT NULL,
    data_nascimento DATE DEFAULT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY email (email),
    UNIQUE KEY cpf (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de salões
CREATE TABLE IF NOT EXISTS saloes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nome_fantasia VARCHAR(100) NOT NULL,
    documento VARCHAR(18) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    whatsapp VARCHAR(20) NOT NULL,
    num_funcionarios INT NOT NULL DEFAULT 1,
    media_diaria INT NOT NULL DEFAULT 0,
    media_semanal INT NOT NULL DEFAULT 0,
    pix_chave VARCHAR(255) DEFAULT NULL,
    usuario_id INT(11) NOT NULL,
    ativo BOOLEAN DEFAULT 1,
    horario_abertura TIME NOT NULL DEFAULT '09:00',
    horario_fechamento TIME NOT NULL DEFAULT '18:00',
    intervalo_agendamento INT NOT NULL DEFAULT 30,
    dias_funcionamento VARCHAR(20) DEFAULT '1,2,3,4,5,6',
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY documento (documento),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de profissionais
CREATE TABLE IF NOT EXISTS profissionais (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    salao_id INT(11) NOT NULL,
    especialidade VARCHAR(100) DEFAULT NULL,
    telefone VARCHAR(20) DEFAULT NULL,
    ativo BOOLEAN DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (salao_id) REFERENCES saloes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de serviços
CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    salao_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    duracao_minutos INT NOT NULL,
    ativo BOOLEAN DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (salao_id) REFERENCES saloes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de agendamentos
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    cliente_id INT(11) NOT NULL,
    salao_id INT(11) NOT NULL,
    profissional_id INT(11) NOT NULL,
    servico_id INT(11) NOT NULL,
    data DATE NOT NULL,
    hora TIME NOT NULL,
    status ENUM('pendente', 'confirmado', 'realizado', 'cancelado') DEFAULT 'pendente',
    taxa_servico DECIMAL(10,2) DEFAULT 0.99,
    transaction_id VARCHAR(100) DEFAULT NULL,
    payment_id VARCHAR(255) DEFAULT NULL,
    data_confirmacao DATETIME DEFAULT NULL,
    data_cancelamento DATETIME DEFAULT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (salao_id) REFERENCES saloes(id) ON DELETE CASCADE,
    FOREIGN KEY (profissional_id) REFERENCES profissionais(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de metas
CREATE TABLE IF NOT EXISTS metas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    salao_id INT(11) NOT NULL,
    mes VARCHAR(7) NOT NULL,
    cortes_mes INT(11) DEFAULT 0,
    bonus_pago DECIMAL(10,2) DEFAULT 0.00,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (salao_id) REFERENCES saloes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_meta (salao_id, mes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de ciclos de metas (novo sistema de 30 dias)
CREATE TABLE IF NOT EXISTS ciclos_metas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    salao_id INT(11) NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    agendamentos_confirmados INT(11) DEFAULT 0,
    meta_50_atingida BOOLEAN DEFAULT FALSE,
    meta_100_atingida BOOLEAN DEFAULT FALSE,
    bonus_50_pago BOOLEAN DEFAULT FALSE,
    bonus_100_pago BOOLEAN DEFAULT FALSE,
    finalizado BOOLEAN DEFAULT FALSE,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (salao_id) REFERENCES saloes(id) ON DELETE CASCADE,
    INDEX idx_salao_data (salao_id, data_inicio, data_fim),
    INDEX idx_data_fim (data_fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de promoções
CREATE TABLE IF NOT EXISTS promocoes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    salao_id INT(11) NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT,
    desconto DECIMAL(5,2) NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    status ENUM('ativa', 'inativa') DEFAULT 'ativa',
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (salao_id) REFERENCES saloes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de avaliações
CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    cliente_id INT(11) NOT NULL,
    salao_id INT(11) NOT NULL,
    profissional_id INT(11) NOT NULL,
    nota INT(1) NOT NULL CHECK (nota >= 1 AND nota <= 5),
    comentario TEXT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (salao_id) REFERENCES saloes(id) ON DELETE CASCADE,
    FOREIGN KEY (profissional_id) REFERENCES profissionais(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserir usuário administrador padrão (se não existir)
INSERT IGNORE INTO usuarios (nome, email, senha, tipo) VALUES 
('Administrador', 'admin@cortefacil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Inserir usuário do salão de exemplo (se não existir)
INSERT IGNORE INTO usuarios (nome, email, senha, tipo, cpf, data_nascimento) VALUES
('Salão Exemplo', 'salao@exemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'salao', '12345678901', '1990-01-01');

-- Inserir cliente de exemplo (se não existir)
INSERT IGNORE INTO usuarios (nome, email, senha, tipo, cpf, data_nascimento) VALUES
('Cliente Teste', 'cliente@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', '98765432109', '1995-05-15');

-- Inserir salão de exemplo
INSERT IGNORE INTO saloes (nome_fantasia, documento, cidade, endereco, whatsapp, num_funcionarios, media_diaria, media_semanal, pix_chave, usuario_id) 
SELECT 'Salão Exemplo', '12345678901234', 'São Paulo', 'Rua Teste, 123', '11999999999', 10, 50, 350, '12345678901', id 
FROM usuarios WHERE email = 'salao@exemplo.com' LIMIT 1;

-- Inserir profissionais de exemplo
INSERT IGNORE INTO profissionais (nome, salao_id, especialidade, telefone) 
SELECT 'João Silva', s.id, 'Corte Masculino', '11988888888'
FROM saloes s WHERE s.nome_fantasia = 'Salão Exemplo' LIMIT 1;

INSERT IGNORE INTO profissionais (nome, salao_id, especialidade, telefone) 
SELECT 'Maria Oliveira', s.id, 'Corte Feminino', '11977777777'
FROM saloes s WHERE s.nome_fantasia = 'Salão Exemplo' LIMIT 1;

-- Inserir serviços de exemplo
INSERT IGNORE INTO servicos (salao_id, nome, preco, duracao_minutos) 
SELECT s.id, 'Corte Masculino', 35.00, 30
FROM saloes s WHERE s.nome_fantasia = 'Salão Exemplo' LIMIT 1;

INSERT IGNORE INTO servicos (salao_id, nome, preco, duracao_minutos) 
SELECT s.id, 'Corte Feminino', 50.00, 60
FROM saloes s WHERE s.nome_fantasia = 'Salão Exemplo' LIMIT 1;

INSERT IGNORE INTO servicos (salao_id, nome, preco, duracao_minutos) 
SELECT s.id, 'Barba', 25.00, 30
FROM saloes s WHERE s.nome_fantasia = 'Salão Exemplo' LIMIT 1;
