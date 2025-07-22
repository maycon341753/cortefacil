<?php
session_start();
include 'conexao.php';

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

try {
    $mes_atual = date('Y-m');
    
    // Busca todos os salões e seus agendamentos do mês
    $query = "SELECT 
                s.id,
                s.nome_fantasia,
                COALESCE(COUNT(a.id), 0) as agendamentos_mes,
                COALESCE(m.bonus_pago, 0) as bonus_pago
              FROM saloes s
              LEFT JOIN agendamentos a ON s.id = a.salao_id 
                   AND DATE_FORMAT(a.data, '%Y-%m') = ?
                   AND a.status = 'realizado'
              LEFT JOIN metas m ON s.id = m.salao_id 
                   AND m.mes = ?
              GROUP BY s.id, s.nome_fantasia
              ORDER BY agendamentos_mes DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->execute([$mes_atual, $mes_atual]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $saloes = [];
    $resumo = [
        'total_saloes' => 0,
        'meta_50' => 0,
        'meta_100' => 0
    ];
    
    foreach ($result as $row) {
        // Verifica se precisa pagar bônus
        $agendamentos = intval($row['agendamentos_mes']);
        $bonus_atual = floatval($row['bonus_pago']);
        $novo_bonus = 0;
        
        if ($agendamentos >= 100 && $bonus_atual < 150) {
            $novo_bonus = 150;
        } elseif ($agendamentos >= 50 && $bonus_atual < 50) {
            $novo_bonus = 50;
        }
        
        // Se tem novo bônus, atualiza na tabela de metas
        if ($novo_bonus > 0) {
            $stmt = $conn->prepare("INSERT INTO metas (salao_id, mes, cortes_mes, bonus_pago) 
                                  VALUES (:salao_id, :mes, :cortes_mes, :bonus_pago)
                                  ON DUPLICATE KEY UPDATE 
                                  cortes_mes = VALUES(cortes_mes),
                                  bonus_pago = VALUES(bonus_pago)");
            $stmt->execute([
                ':salao_id' => $row['id'],
                ':mes' => $mes_atual,
                ':cortes_mes' => $agendamentos,
                ':bonus_pago' => $novo_bonus
            ]);
        }
        
        // Atualiza o resumo
        $resumo['total_saloes']++;
        if ($agendamentos >= 100) $resumo['meta_100']++;
        elseif ($agendamentos >= 50) $resumo['meta_50']++;
        
        $saloes[] = [
            'id' => $row['id'],
            'nome_fantasia' => $row['nome_fantasia'],
            'agendamentos_mes' => $agendamentos,
            'bonus_pago' => $novo_bonus > 0 ? $novo_bonus : $bonus_atual
        ];
    }
    
    echo json_encode([
        'status' => 'ok',
        'resumo' => $resumo,
        'saloes' => $saloes
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}

$conn->close();
?>
