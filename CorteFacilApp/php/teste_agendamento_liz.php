<?php
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getConexao();
    
    // 1. Verificar se a cliente Liz Hadassa existe
    $stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE nome LIKE '%Liz%Hadassa%' OR nome LIKE '%Maitê%Silva%' LIMIT 1");
    $stmt->execute();
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cliente) {
        // Criar a cliente se não existir
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo, cpf, data_nascimento) VALUES (?, ?, ?, 'cliente', ?, ?)");
        $stmt->execute([
            'Liz Hadassa Maitê Silva',
            'liz.hadassa@email.com',
            password_hash('123456', PASSWORD_DEFAULT),
            '12345678901',
            '1995-03-15'
        ]);
        $cliente_id = $pdo->lastInsertId();
        echo json_encode(['status' => 'success', 'message' => 'Cliente criada', 'cliente_id' => $cliente_id]);
    } else {
        $cliente_id = $cliente['id'];
        echo json_encode(['status' => 'success', 'message' => 'Cliente encontrada', 'cliente' => $cliente]);
    }
    
    // 2. Buscar dados do salão (assumindo que é o salão ID 4 baseado nos arquivos encontrados)
    $stmt = $pdo->prepare("SELECT id FROM saloes WHERE id = 4 LIMIT 1");
    $stmt->execute();
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        echo json_encode(['status' => 'error', 'message' => 'Salão ID 4 não encontrado']);
        exit;
    }
    
    // 3. Buscar profissional do salão
    $stmt = $pdo->prepare("SELECT id FROM profissionais WHERE salao_id = 4 LIMIT 1");
    $stmt->execute();
    $profissional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profissional) {
        echo json_encode(['status' => 'error', 'message' => 'Nenhum profissional encontrado para o salão ID 4']);
        exit;
    }
    
    // 4. Buscar serviço do salão
    $stmt = $pdo->prepare("SELECT id, nome, preco FROM servicos WHERE salao_id = 4 LIMIT 1");
    $stmt->execute();
    $servico = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$servico) {
        echo json_encode(['status' => 'error', 'message' => 'Nenhum serviço encontrado para o salão ID 4']);
        exit;
    }
    
    // 5. Verificar se já existe agendamento para esta cliente neste salão
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE cliente_id = ? AND salao_id = 4");
    $stmt->execute([$cliente_id]);
    $temAgendamento = $stmt->fetchColumn() > 0;
    
    if (!$temAgendamento) {
        // 6. Criar agendamento de teste (pago/realizado)
        $hoje = date('Y-m-d');
        
        $sql = "INSERT INTO agendamentos (
            cliente_id, salao_id, profissional_id, servico_id,
            data, hora, status, transaction_id, payment_id, data_confirmacao, criado_em
        ) VALUES (?, 4, ?, ?, ?, '14:30', 'realizado', 'TEST_TXN_123', 'PAY_TEST_456', NOW(), NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $cliente_id,
            $profissional['id'],
            $servico['id'],
            $hoje
        ]);
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Agendamento criado com sucesso',
            'agendamento_id' => $pdo->lastInsertId(),
            'cliente' => 'Liz Hadassa Maitê Silva',
            'servico' => $servico['nome'],
            'preco' => $servico['preco']
        ]);
    } else {
        echo json_encode(['status' => 'info', 'message' => 'Agendamento já existe para esta cliente']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>