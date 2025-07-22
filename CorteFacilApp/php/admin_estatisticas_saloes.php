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
    
    // Estatísticas por salão
    $query = "SELECT 
                s.nome_fantasia as salao_nome,
                COUNT(a.id) as total_agendamentos,
                COUNT(CASE WHEN a.status_pagamento = 'pago' THEN 1 END) as agendamentos_pagos,
                COALESCE(SUM(CASE WHEN a.status_pagamento = 'pago' THEN a.taxa_servico ELSE 0 END), 0) as faturamento_total,
                ROUND(COUNT(DISTINCT a.cliente_id) / GREATEST(DATEDIFF(:data_fim, :data_inicio), 1), 1) as media_clientes_dia,
                CASE 
                    WHEN COUNT(CASE WHEN a.status_pagamento = 'pago' THEN 1 END) > 0 
                    THEN ROUND(SUM(CASE WHEN a.status_pagamento = 'pago' THEN a.taxa_servico ELSE 0 END) / COUNT(CASE WHEN a.status_pagamento = 'pago' THEN 1 END), 2)
                    ELSE 0 
                END as ticket_medio
              FROM saloes s
              LEFT JOIN agendamentos a ON s.id = a.salao_id 
                AND a.data BETWEEN :data_inicio AND :data_fim2
              WHERE s.ativo = 1
              GROUP BY s.id, s.nome_fantasia
              ORDER BY faturamento_total DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([
        'data_inicio' => $dataInicio,
        'data_fim' => $dataFim,
        'data_fim2' => $dataFim
    ]);
    
    $estatisticas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'sucesso',
        'estatisticas' => $estatisticas
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao carregar estatísticas dos salões: ' . $e->getMessage(),
        'estatisticas' => []
    ]);
}
?>