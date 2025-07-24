<?php
require_once 'php/conexao.php';

echo "<h2>Status dos Agendamentos do Thales</h2>";

try {
    $pdo = getConexao();
    
    // Buscar agendamentos do Thales (ID 7)
    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            a.status,
            a.data,
            a.hora,
            a.criado_em,
            s.nome_fantasia as salao_nome,
            srv.nome as servico_nome
        FROM agendamentos a
        LEFT JOIN saloes s ON a.salao_id = s.id
        LEFT JOIN servicos srv ON a.servico_id = srv.id
        WHERE a.cliente_id = 7
        ORDER BY a.criado_em DESC
    ");
    $stmt->execute();
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Total de agendamentos: " . count($agendamentos) . "</p>";
    
    foreach ($agendamentos as $agendamento) {
        $statusColor = $agendamento['status'] == 'confirmado' ? 'green' : 
                      ($agendamento['status'] == 'pago' ? 'blue' : 'orange');
        
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px 0;'>";
        echo "<strong>ID:</strong> {$agendamento['id']}<br>";
        echo "<strong>Salão:</strong> {$agendamento['salao_nome']}<br>";
        echo "<strong>Serviço:</strong> {$agendamento['servico_nome']}<br>";
        echo "<strong>Data:</strong> {$agendamento['data']}<br>";
        echo "<strong>Hora:</strong> {$agendamento['hora']}<br>";
        echo "<strong>Status:</strong> <span style='color: {$statusColor}; font-weight: bold;'>{$agendamento['status']}</span><br>";
        echo "<strong>Criado em:</strong> {$agendamento['criado_em']}<br>";
        echo "</div>";
    }
    
    // Verificar quantos são de cada status
    echo "<h3>Resumo por Status:</h3>";
    $statusCount = [];
    foreach ($agendamentos as $agendamento) {
        $status = $agendamento['status'];
        if (!isset($statusCount[$status])) {
            $statusCount[$status] = 0;
        }
        $statusCount[$status]++;
    }
    
    foreach ($statusCount as $status => $count) {
        echo "<p><strong>{$status}:</strong> {$count} agendamento(s)</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>