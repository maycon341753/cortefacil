<?php
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getConexao();
    
    // 1. Verificar se a tabela agendamentos existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'agendamentos'");
    $tabelaExiste = $stmt->rowCount() > 0;
    
    $resultado = ['status' => 'success'];
    
    if (!$tabelaExiste) {
        // Criar a tabela agendamentos
        $sql = "CREATE TABLE IF NOT EXISTS agendamentos (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        $pdo->exec($sql);
        $resultado['tabela_criada'] = true;
    }
    
    // 2. Verificar se existem dados de teste necessários
    $stmt = $pdo->query("SELECT id FROM usuarios WHERE tipo = 'cliente' LIMIT 1");
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT id FROM saloes LIMIT 1");
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT id FROM profissionais LIMIT 1");
    $profissional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT id FROM servicos LIMIT 1");
    $servico = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 3. Criar agendamento de teste se todos os dados necessários existirem
    if ($cliente && $salao && $profissional && $servico) {
        // Verificar se já existe um agendamento de teste
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE cliente_id = ?");
        $stmt->execute([$cliente['id']]);
        $temAgendamento = $stmt->fetchColumn() > 0;
        
        if (!$temAgendamento) {
            // Criar um agendamento para amanhã
            $amanha = date('Y-m-d', strtotime('+1 day'));
            
            $sql = "INSERT INTO agendamentos (
                cliente_id, salao_id, profissional_id, servico_id,
                data, hora, status, criado_em
            ) VALUES (?, ?, ?, ?, ?, '10:00', 'pendente', NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $cliente['id'],
                $salao['id'],
                $profissional['id'],
                $servico['id'],
                $amanha
            ]);
            
            $resultado['agendamento_criado'] = true;
        }
    }
    
    // 4. Buscar estrutura atual da tabela e dados
    $stmt = $pdo->query("DESCRIBE agendamentos");
    $resultado['estrutura_tabela'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM agendamentos");
    $resultado['total_agendamentos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($resultado['total_agendamentos'] > 0) {
        $stmt = $pdo->query("SELECT * FROM agendamentos LIMIT 3");
        $resultado['exemplos_agendamentos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}