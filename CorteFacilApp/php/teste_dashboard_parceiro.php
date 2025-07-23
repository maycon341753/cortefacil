<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getConexao();
    
    // 1. Simular login do parceiro (salão ID 4)
    $stmt = $pdo->prepare("SELECT s.*, u.nome as usuario_nome FROM saloes s JOIN usuarios u ON u.id = s.usuario_id WHERE s.id = 4");
    $stmt->execute();
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        echo json_encode(['status' => 'error', 'message' => 'Salão ID 4 não encontrado']);
        exit;
    }
    
    // Simular sessão do parceiro
    $_SESSION['usuario_id'] = $salao['usuario_id'];
    $_SESSION['tipo'] = 'salao';
    $_SESSION['salao_id'] = $salao['id'];
    $_SESSION['nome'] = $salao['usuario_nome'];
    
    echo json_encode(['status' => 'success', 'message' => 'Sessão simulada', 'salao' => $salao]);
    
    // 2. Agora testar o dashboard
    $salao_id = $_SESSION['salao_id'];
    $hoje = date('Y-m-d');
    $primeiro_dia_mes = date('Y-m-01');
    $ultimo_dia_mes = date('Y-m-t');

    // Agendamentos de hoje
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM agendamentos
        WHERE salao_id = :salao_id
        AND data = :hoje
    ");
    $stmt->execute(['salao_id' => $salao_id, 'hoje' => $hoje]);
    $agendamentos_hoje = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Faturamento de hoje - usando valor real do serviço
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(
            CASE 
                WHEN a.valor_servico IS NOT NULL AND a.valor_servico > 0 THEN a.valor_servico
                ELSE s.preco
            END
        ), 0) as total
        FROM agendamentos a
        JOIN servicos s ON s.id = a.servico_id
        WHERE a.salao_id = :salao_id
        AND a.data = :hoje
        AND a.status = 'realizado'
    ");
    $stmt->execute(['salao_id' => $salao_id, 'hoje' => $hoje]);
    $faturamento_hoje = floatval($stmt->fetch(PDO::FETCH_ASSOC)['total']);

    // Últimos agendamentos
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            u.nome as cliente,
            s.nome as servico,
            a.data,
            a.hora,
            a.status
        FROM agendamentos a
        JOIN usuarios u ON u.id = a.cliente_id
        JOIN servicos s ON s.id = a.servico_id
        WHERE a.salao_id = :salao_id
        ORDER BY a.data DESC, a.hora DESC
        LIMIT 5
    ");
    $stmt->execute(['salao_id' => $salao_id]);
    $ultimos_agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatar os últimos agendamentos
    foreach ($ultimos_agendamentos as &$agendamento) {
        $data_hora = $agendamento['data'] . ' ' . $agendamento['hora'];
        $data = new DateTime($data_hora);
        $agendamento['data_hora'] = $data->format('d/m/Y H:i');
        unset($agendamento['data']);
        unset($agendamento['hora']);
    }

    echo json_encode([
        'status' => 'success',
        'dashboard_data' => [
            'agendamentos_hoje' => $agendamentos_hoje,
            'faturamento_hoje' => $faturamento_hoje,
            'ultimos_agendamentos' => $ultimos_agendamentos
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>