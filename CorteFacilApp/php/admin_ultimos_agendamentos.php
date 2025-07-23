<?php
session_start();
require_once 'conexao.php';

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    // Tenta fazer login automático para teste
    include_once 'admin_login_temp.php';
    if (!isset($_SESSION['admin_id'])) {
        echo json_encode([]);
        exit;
    }
}

try {
    $conn = getConexao();
    
    // Consulta que inclui agendamentos pagos com prioridade
    $query = "SELECT 
                a.id,
                a.data,
                a.hora,
                a.status,
                a.status_pagamento,
                u.nome as cliente_nome,
                s.nome_fantasia as salao_nome
              FROM agendamentos a
              JOIN usuarios u ON a.cliente_id = u.id
              JOIN saloes s ON a.salao_id = s.id
              ORDER BY 
                CASE WHEN a.status_pagamento = 'pago' THEN 0 ELSE 1 END, 
                a.data DESC, 
                a.hora DESC
              LIMIT 3";
              
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $agendamentos = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Formata a data
        if ($row['data']) {
            $data = new DateTime($row['data']);
            $row['data'] = $data->format('d/m/Y');
        }
        
        // Formata a hora
        if ($row['hora']) {
            $hora = new DateTime($row['hora']);
            $row['hora'] = $hora->format('H:i');
        }
        
        $agendamentos[] = $row;
    }
    
    // Retorna sempre um array, mesmo que vazio
    echo json_encode($agendamentos);

} catch (Exception $e) {
    // Em caso de erro, retorna um array vazio para não quebrar o frontend
    echo json_encode([]);
}
?>