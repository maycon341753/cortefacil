<?php
require_once 'conexao.php';

echo "<h2>🔍 Diagnóstico do Problema do Faturamento</h2>";

try {
    $conn = getConexao();
    
    echo "<h3>1. Verificando agendamentos existentes</h3>";
    
    // Verificar todos os agendamentos do dia atual
    $hoje = date('Y-m-d');
    $stmt = $conn->prepare("
        SELECT 
            a.id,
            a.data,
            a.status,
            a.status_pagamento,
            a.valor_servico,
            a.taxa_servico,
            s.nome as servico_nome,
            s.preco as servico_preco,
            CASE 
                WHEN a.valor_servico IS NOT NULL AND a.valor_servico > 0 THEN a.valor_servico
                ELSE s.preco
            END as valor_usado_calculo
        FROM agendamentos a
        JOIN servicos s ON s.id = a.servico_id
        WHERE a.data = ?
        ORDER BY a.id DESC
    ");
    $stmt->execute([$hoje]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($agendamentos)) {
        echo "<p style='color: orange;'>⚠️ Não há agendamentos para hoje ($hoje)</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th><th>Serviço</th><th>Status</th><th>Status Pagamento</th>";
        echo "<th>Valor Serviço</th><th>Taxa Serviço</th><th>Preço Original</th><th>Valor Usado no Cálculo</th>";
        echo "</tr>";
        
        foreach ($agendamentos as $ag) {
            echo "<tr>";
            echo "<td>{$ag['id']}</td>";
            echo "<td>{$ag['servico_nome']}</td>";
            echo "<td>{$ag['status']}</td>";
            echo "<td>{$ag['status_pagamento']}</td>";
            echo "<td>R$ " . number_format($ag['valor_servico'] ?? 0, 2, ',', '.') . "</td>";
            echo "<td>R$ " . number_format($ag['taxa_servico'] ?? 0, 2, ',', '.') . "</td>";
            echo "<td>R$ " . number_format($ag['servico_preco'], 2, ',', '.') . "</td>";
            echo "<td style='background-color: #e8f5e8;'><strong>R$ " . number_format($ag['valor_usado_calculo'], 2, ',', '.') . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>2. Calculando faturamento atual</h3>";
    
    // Calcular faturamento usando a mesma lógica do dashboard
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(
            CASE 
                WHEN a.valor_servico IS NOT NULL AND a.valor_servico > 0 THEN a.valor_servico
                ELSE s.preco
            END
        ), 0) as total
        FROM agendamentos a
        JOIN servicos s ON s.id = a.servico_id
        WHERE a.data = ?
        AND a.status = 'realizado'
    ");
    $stmt->execute([$hoje]);
    $faturamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Faturamento calculado para hoje:</strong> R$ " . number_format($faturamento['total'], 2, ',', '.') . "</p>";
    
    echo "<h3>3. Verificando agendamentos com status 'realizado'</h3>";
    
    $stmt = $conn->prepare("
        SELECT 
            a.id,
            a.status,
            a.status_pagamento,
            a.valor_servico,
            s.nome as servico_nome,
            s.preco as servico_preco
        FROM agendamentos a
        JOIN servicos s ON s.id = a.servico_id
        WHERE a.data = ?
        AND a.status = 'realizado'
    ");
    $stmt->execute([$hoje]);
    $agendamentos_realizados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($agendamentos_realizados)) {
        echo "<p style='color: red;'>❌ <strong>PROBLEMA ENCONTRADO:</strong> Não há agendamentos com status 'realizado' para hoje!</p>";
        echo "<p>O dashboard só conta agendamentos com status 'realizado'. Vamos verificar quais status existem:</p>";
        
        $stmt = $conn->prepare("
            SELECT status, COUNT(*) as quantidade
            FROM agendamentos
            WHERE data = ?
            GROUP BY status
        ");
        $stmt->execute([$hoje]);
        $status_count = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
        echo "<tr style='background-color: #f0f0f0;'><th>Status</th><th>Quantidade</th></tr>";
        foreach ($status_count as $sc) {
            echo "<tr><td>{$sc['status']}</td><td>{$sc['quantidade']}</td></tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color: green;'>✅ Encontrados " . count($agendamentos_realizados) . " agendamentos realizados</p>";
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th><th>Serviço</th><th>Status Pagamento</th><th>Valor Serviço</th><th>Preço Original</th>";
        echo "</tr>";
        
        foreach ($agendamentos_realizados as $ag) {
            echo "<tr>";
            echo "<td>{$ag['id']}</td>";
            echo "<td>{$ag['servico_nome']}</td>";
            echo "<td>{$ag['status_pagamento']}</td>";
            echo "<td>R$ " . number_format($ag['valor_servico'] ?? 0, 2, ',', '.') . "</td>";
            echo "<td>R$ " . number_format($ag['servico_preco'], 2, ',', '.') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>4. Solução</h3>";
    if (empty($agendamentos_realizados)) {
        echo "<p style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
        echo "<strong>💡 Solução:</strong> Precisamos atualizar os agendamentos existentes para status 'realizado' ou criar um novo agendamento de teste com o status correto.";
        echo "</p>";
    } else {
        echo "<p style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
        echo "<strong>✅ Status OK:</strong> Os agendamentos estão com status correto. O problema pode estar no valor_servico.";
        echo "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>