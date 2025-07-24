<?php
// Teste direto da consulta SQL com nova ordenação
require_once 'php/conexao.php';

echo "<h2>🔄 Teste: Nova Ordenação dos Agendamentos do Thales</h2>";

try {
    $pdo = getConexao();
    
    // Buscar dados do Thales
    $sqlThales = "SELECT id, nome FROM usuarios WHERE nome LIKE '%Thales%' AND tipo = 'cliente' LIMIT 1";
    $stmtThales = $pdo->prepare($sqlThales);
    $stmtThales->execute();
    $thales = $stmtThales->fetch(PDO::FETCH_ASSOC);
    
    if (!$thales) {
        echo "<p style='color: red;'>❌ Usuário Thales não encontrado</p>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Usuário encontrado: " . $thales['nome'] . " (ID: " . $thales['id'] . ")</p>";
    
    // Executar a mesma consulta que está no meus_agendamentos.php
    $sql = "SELECT 
                a.id,
                a.data,
                a.hora,
                a.status,
                a.criado_em,
                s.nome_fantasia as salao,
                p.nome as profissional,
                serv.nome as servico,
                serv.preco
            FROM agendamentos a
            JOIN saloes s ON a.salao_id = s.id
            JOIN profissionais p ON a.profissional_id = p.id
            JOIN servicos serv ON a.servico_id = serv.id
            WHERE a.cliente_id = :cliente_id 
            ORDER BY 
                CASE 
                    WHEN a.status IN ('confirmado', 'pago') THEN 1
                    WHEN a.status = 'realizado' THEN 2
                    WHEN a.status = 'cancelado' THEN 3
                    ELSE 4
                END,
                a.criado_em DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['cliente_id' => $thales['id']]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📊 Agendamentos ordenados (Total: " . count($agendamentos) . "):</h3>";
    echo "<p><strong>Ordem:</strong> 1º Confirmados/Pagos → 2º Realizados → 3º Cancelados (por data de criação)</p>";
    
    $statusCount = ['confirmado' => 0, 'pago' => 0, 'realizado' => 0, 'cancelado' => 0];
    $currentGroup = '';
    
    foreach ($agendamentos as $index => $agendamento) {
        $statusCount[$agendamento['status']]++;
        
        // Verificar se mudou de grupo de status
        $statusGroup = '';
        if (in_array($agendamento['status'], ['confirmado', 'pago'])) {
            $statusGroup = 'Confirmados/Pagos';
        } elseif ($agendamento['status'] == 'realizado') {
            $statusGroup = 'Realizados';
        } elseif ($agendamento['status'] == 'cancelado') {
            $statusGroup = 'Cancelados';
        }
        
        if ($statusGroup != $currentGroup) {
            $currentGroup = $statusGroup;
            echo "<h4 style='background: #f8f9fa; padding: 10px; margin: 20px 0 10px 0; border-left: 4px solid #007bff;'>📋 $statusGroup</h4>";
        }
        
        // Definir cor do status
        $statusColor = '';
        switch ($agendamento['status']) {
            case 'confirmado':
            case 'pago':
                $statusColor = '#28a745'; // Verde
                break;
            case 'realizado':
                $statusColor = '#007bff'; // Azul
                break;
            case 'cancelado':
                $statusColor = '#dc3545'; // Vermelho
                break;
        }
        
        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px; background: #f9f9f9;'>";
        echo "<div style='display: flex; justify-content: space-between; align-items: center;'>";
        echo "<h4 style='margin: 0; color: #333;'>#" . ($index + 1) . " - " . $agendamento['salao'] . "</h4>";
        echo "<span style='background: $statusColor; color: white; padding: 5px 10px; border-radius: 15px; font-weight: bold; font-size: 12px;'>" . strtoupper($agendamento['status']) . "</span>";
        echo "</div>";
        
        echo "<div style='margin-top: 10px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;'>";
        echo "<div><strong>👨‍💼 Profissional:</strong> " . $agendamento['profissional'] . "</div>";
        echo "<div><strong>✂️ Serviço:</strong> " . $agendamento['servico'] . "</div>";
        
        $data = new DateTime($agendamento['data']);
        $dataFormatada = $data->format('d/m/Y');
        $horaFormatada = substr($agendamento['hora'], 0, 5);
        $precoFormatado = 'R$ ' . number_format($agendamento['preco'], 2, ',', '.');
        
        echo "<div><strong>📅 Data:</strong> " . $dataFormatada . "</div>";
        echo "<div><strong>🕐 Hora:</strong> " . $horaFormatada . "</div>";
        echo "<div><strong>💰 Preço:</strong> " . $precoFormatado . "</div>";
        echo "<div><strong>📝 Criado:</strong> " . date('d/m/Y H:i', strtotime($agendamento['criado_em'])) . "</div>";
        echo "</div>";
        
        // Destacar agendamento do Eduardo
        if (strpos($agendamento['salao'], 'Eduardo') !== false) {
            echo "<div style='background: #e7f3ff; border: 2px solid #007bff; padding: 10px; margin-top: 10px; border-radius: 5px;'>";
            echo "<strong>🎯 SALÃO DO EDUARDO</strong> - Agendamento que agora aparece corretamente!";
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    // Resumo por status
    echo "<h3>📈 Resumo por Status:</h3>";
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;'>";
    
    foreach ($statusCount as $status => $count) {
        if ($count == 0) continue;
        
        $color = '';
        $icon = '';
        switch ($status) {
            case 'confirmado':
                $color = '#28a745';
                $icon = '✅';
                break;
            case 'pago':
                $color = '#28a745';
                $icon = '💳';
                break;
            case 'realizado':
                $color = '#007bff';
                $icon = '✂️';
                break;
            case 'cancelado':
                $color = '#dc3545';
                $icon = '❌';
                break;
        }
        
        echo "<div style='background: $color; color: white; padding: 15px; border-radius: 8px; text-align: center;'>";
        echo "<div style='font-size: 24px;'>$icon</div>";
        echo "<div style='font-weight: bold; text-transform: uppercase;'>$status</div>";
        echo "<div style='font-size: 20px; font-weight: bold;'>$count</div>";
        echo "</div>";
    }
    echo "</div>";
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb; margin: 20px 0;'>";
    echo "<h4>✅ Ajustes Realizados no Painel do Cliente:</h4>";
    echo "<ul>";
    echo "<li><strong>Todos os agendamentos</strong> agora são exibidos (confirmados, realizados e cancelados)</li>";
    echo "<li><strong>Ordenação por prioridade:</strong> Confirmados/Pagos primeiro, depois Realizados, por último Cancelados</li>";
    echo "<li><strong>Ordenação por data:</strong> Dentro de cada status, ordenado por data de criação (mais recente primeiro)</li>";
    echo "<li><strong>Agendamento do Eduardo:</strong> Agora aparece corretamente na lista</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>