<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    // Para fins de teste, vamos ignorar a verificação de admin
    $_SESSION['admin_id'] = 1; // Temporário para teste
}

try {
    $conn = getConexao();
    
    // Receber filtros via POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    $dataInicio = $input['dataInicio'] ?? date('Y-m-d', strtotime('-30 days'));
    $dataFim = $input['dataFim'] ?? date('Y-m-d');
    $salaoId = $input['salaoId'] ?? '';
    $status = $input['status'] ?? '';
    
    // Construir query base
    $query = "SELECT 
                a.id,
                a.data,
                a.hora,
                a.status_pagamento,
                a.taxa_servico,
                u.nome as cliente_nome,
                s.nome_fantasia as salao_nome,
                p.nome as profissional_nome,
                sv.nome as servico_nome
              FROM agendamentos a
              INNER JOIN usuarios u ON a.cliente_id = u.id
              INNER JOIN saloes s ON a.salao_id = s.id
              INNER JOIN profissionais p ON a.profissional_id = p.id
              INNER JOIN servicos sv ON a.servico_id = sv.id
              WHERE a.data BETWEEN :data_inicio AND :data_fim";
    
    $params = [
        'data_inicio' => $dataInicio,
        'data_fim' => $dataFim
    ];
    
    // Adicionar filtro de salão se especificado
    if (!empty($salaoId)) {
        $query .= " AND a.salao_id = :salao_id";
        $params['salao_id'] = $salaoId;
    }
    
    // Adicionar filtro de status se especificado
    if (!empty($status)) {
        $query .= " AND a.status_pagamento = :status";
        $params['status'] = $status;
    }
    
    $query .= " ORDER BY a.data DESC, a.hora DESC LIMIT 1000";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'sucesso',
        'movimentacoes' => $movimentacoes
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao carregar movimentações: ' . $e->getMessage(),
        'movimentacoes' => []
    ]);
}
?>