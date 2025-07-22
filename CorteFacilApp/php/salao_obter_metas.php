<?php
session_start();
include 'conexao.php';

// Verifica se é um salão
if (!isset($_SESSION['salao_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

try {
    $salao_id = $_SESSION['salao_id'];
    $mes_atual = date('Y-m');
    $ultimo_dia = date('t');
    $dias_restantes = $ultimo_dia - date('d');
    
    // Busca os dados do mês atual
    $query = "SELECT 
                COALESCE(COUNT(a.id), 0) as agendamentos_mes,
                COALESCE(m.bonus_pago, 0) as bonus_pago
              FROM saloes s
              LEFT JOIN agendamentos a ON s.id = a.salao_id 
                   AND DATE_FORMAT(a.data, '%Y-%m') = ?
                   AND a.status = 'realizado'
              LEFT JOIN metas m ON s.id = m.salao_id 
                   AND m.mes = ?
              WHERE s.id = ?
              GROUP BY s.id";
              
    $stmt = $conn->prepare($query);
    $stmt->execute([$mes_atual, $mes_atual, $salao_id]);
    $meta_atual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Adiciona os dias restantes
    $meta_atual['dias_restantes'] = $dias_restantes;
    
    // Busca o histórico dos últimos 6 meses
    $query = "SELECT 
                DATE_FORMAT(STR_TO_DATE(m.mes, '%Y-%m'), '%m/%Y') as mes_formatado,
                m.mes,
                m.cortes_mes as agendamentos,
                m.bonus_pago
              FROM metas m
              WHERE m.salao_id = ?
                AND m.mes < ?
              ORDER BY m.mes DESC
              LIMIT 6";
              
    $stmt = $conn->prepare($query);
    $stmt->execute([$salao_id, $mes_atual]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $historico = [];
    foreach ($result as $row) {
        $row['bonus_pago'] = number_format($row['bonus_pago'], 2, ',', '.');
        $historico[] = $row;
    }
    
    echo json_encode([
        'status' => 'ok',
        'meta_atual' => $meta_atual,
        'historico' => $historico
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}

$conn = null;
?>