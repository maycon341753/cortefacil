<?php
// Verificação final do agendamento do Eduardo
require_once __DIR__ . '/php/conexao.php';

echo "<h2>🔍 Verificação Final: Agendamento do Eduardo</h2>";

// Obter conexão
$pdo = getConexao();

// Simular login do Thales
$_SESSION['cliente_id'] = 7;
$_SESSION['cliente_nome'] = 'Thales Theo Gustavo Viana';

// Buscar agendamentos usando a mesma lógica do meus_agendamentos.php
$stmt = $pdo->prepare("
    SELECT 
        a.id,
        a.data,
        a.hora,
        a.status,
        a.criado_em,
        s.nome_fantasia as salao,
        p.nome as profissional,
        sv.nome as servico,
        sv.preco
    FROM agendamentos a
    JOIN saloes s ON a.salao_id = s.id
    JOIN profissionais p ON a.profissional_id = p.id
    JOIN servicos sv ON a.servico_id = sv.id
    WHERE a.cliente_id = ?
    ORDER BY 
        CASE 
            WHEN a.status IN ('confirmado', 'pago') THEN 1
            WHEN a.status = 'realizado' THEN 2
            WHEN a.status = 'cancelado' THEN 3
            ELSE 4
        END,
        a.criado_em DESC
");

$stmt->execute([7]);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p><strong>Total de agendamentos encontrados:</strong> " . count($agendamentos) . "</p>";

$encontrou_eduardo = false;
foreach ($agendamentos as $index => $agendamento) {
    if (strpos($agendamento['salao'], 'Eduardo') !== false) {
        $encontrou_eduardo = true;
        echo "<div style='background: #d4edda; border: 2px solid #28a745; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
        echo "<h3>🎯 AGENDAMENTO DO EDUARDO ENCONTRADO!</h3>";
        echo "<p><strong>Posição na lista:</strong> " . ($index + 1) . "º</p>";
        echo "<p><strong>ID:</strong> " . $agendamento['id'] . "</p>";
        echo "<p><strong>Salão:</strong> " . $agendamento['salao'] . "</p>";
        echo "<p><strong>Status:</strong> " . $agendamento['status'] . "</p>";
        echo "<p><strong>Data:</strong> " . date('d/m/Y', strtotime($agendamento['data'])) . "</p>";
        echo "<p><strong>Hora:</strong> " . $agendamento['hora'] . "</p>";
        echo "<p><strong>Profissional:</strong> " . $agendamento['profissional'] . "</p>";
        echo "<p><strong>Serviço:</strong> " . $agendamento['servico'] . "</p>";
        echo "<p><strong>Preço:</strong> R$ " . number_format($agendamento['preco'], 2, ',', '.') . "</p>";
        echo "</div>";
        break;
    }
}

if (!$encontrou_eduardo) {
    echo "<div style='background: #f8d7da; border: 2px solid #dc3545; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
    echo "<h3>❌ Agendamento do Eduardo NÃO encontrado</h3>";
    echo "<p>Verificando todos os salões na lista:</p>";
    foreach ($agendamentos as $agendamento) {
        echo "<p>- " . $agendamento['salao'] . " (Status: " . $agendamento['status'] . ")</p>";
    }
    echo "</div>";
}

echo "<h3>📊 Resumo por Status:</h3>";
$status_count = [];
foreach ($agendamentos as $agendamento) {
    $status = $agendamento['status'];
    $status_count[$status] = ($status_count[$status] ?? 0) + 1;
}

foreach ($status_count as $status => $count) {
    echo "<p><strong>" . ucfirst($status) . ":</strong> " . $count . " agendamento(s)</p>";
}
?>