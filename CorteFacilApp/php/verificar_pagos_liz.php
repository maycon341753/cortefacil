<?php
require_once 'conexao.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $conn = getConexao();
    
    echo "=== VERIFICAÇÃO DETALHADA - AGENDAMENTOS PAGOS ===\n\n";
    
    // 1. Buscar o salão da Liz
    $stmt = $conn->prepare("SELECT id, nome_fantasia FROM saloes WHERE nome_fantasia LIKE ?");
    $stmt->execute(['%Liz%']);
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ Salão: {$salao['nome_fantasia']} (ID: {$salao['id']})\n\n";
    
    // 2. Buscar ciclo atual
    $stmt = $conn->prepare("SELECT * FROM ciclos_metas WHERE salao_id = ? AND ativo = TRUE");
    $stmt->execute([$salao['id']]);
    $ciclo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "📅 Ciclo atual: {$ciclo['data_inicio']} a {$ciclo['data_fim']}\n\n";
    
    // 3. Verificar TODOS os agendamentos no período
    $stmt = $conn->prepare("
        SELECT id, data, hora, status, pagamento, valor 
        FROM agendamentos 
        WHERE salao_id = ? 
        AND data BETWEEN ? AND ?
        ORDER BY data DESC, hora DESC
    ");
    $stmt->execute([$salao['id'], $ciclo['data_inicio'], $ciclo['data_fim']]);
    $todos_agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 TODOS os agendamentos no período ({$ciclo['data_inicio']} a {$ciclo['data_fim']}):\n";
    foreach ($todos_agendamentos as $ag) {
        $valor_formatado = 'R$ ' . number_format($ag['valor'], 2, ',', '.');
        echo "   ID: {$ag['id']} | {$ag['data']} {$ag['hora']} | Status: {$ag['status']} | Pagamento: {$ag['pagamento']} | Valor: {$valor_formatado}\n";
    }
    echo "\n";
    
    // 4. Verificar agendamentos PAGOS especificamente
    $stmt = $conn->prepare("
        SELECT id, data, hora, status, pagamento, valor 
        FROM agendamentos 
        WHERE salao_id = ? 
        AND data BETWEEN ? AND ?
        AND pagamento = 'pago'
        ORDER BY data DESC, hora DESC
    ");
    $stmt->execute([$salao['id'], $ciclo['data_inicio'], $ciclo['data_fim']]);
    $agendamentos_pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "💰 Agendamentos PAGOS no período:\n";
    $total_pagos = 0;
    $valor_total_pago = 0;
    foreach ($agendamentos_pagos as $ag) {
        $valor_formatado = 'R$ ' . number_format($ag['valor'], 2, ',', '.');
        echo "   ID: {$ag['id']} | {$ag['data']} {$ag['hora']} | Status: {$ag['status']} | Valor: {$valor_formatado}\n";
        $total_pagos++;
        $valor_total_pago += $ag['valor'];
    }
    echo "   TOTAL PAGOS: {$total_pagos} agendamentos\n";
    echo "   VALOR TOTAL: R$ " . number_format($valor_total_pago, 2, ',', '.') . "\n\n";
    
    // 5. Verificar agendamentos confirmados/realizados (independente do pagamento)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM agendamentos 
        WHERE salao_id = ? 
        AND (status = 'confirmado' OR status = 'realizado') 
        AND data BETWEEN ? AND ?
    ");
    $stmt->execute([$salao['id'], $ciclo['data_inicio'], $ciclo['data_fim']]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_confirmados = $resultado['total'];
    
    echo "📊 Agendamentos confirmados/realizados (independente do pagamento): {$total_confirmados}\n\n";
    
    // 6. Sugestão de correção
    echo "🔧 ANÁLISE E SUGESTÃO:\n";
    echo "   - Agendamentos pagos encontrados: {$total_pagos}\n";
    echo "   - Agendamentos confirmados/realizados: {$total_confirmados}\n";
    
    if ($total_pagos > 0 && $total_confirmados != $total_pagos) {
        echo "   ⚠️  PROBLEMA IDENTIFICADO: O sistema está contando apenas agendamentos confirmados/realizados,\n";
        echo "       mas deveria contar apenas os PAGOS para as metas.\n\n";
        
        echo "🔄 Aplicando correção - contando apenas agendamentos PAGOS...\n";
        
        // Atualizar o ciclo com base nos agendamentos PAGOS
        $meta_50_atingida = $total_pagos >= 50 ? 1 : 0;
        $meta_100_atingida = $total_pagos >= 100 ? 1 : 0;
        
        $stmt = $conn->prepare("
            UPDATE ciclos_metas 
            SET agendamentos_confirmados = ?,
                meta_50_atingida = ?,
                meta_100_atingida = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $total_pagos,
            $meta_50_atingida,
            $meta_100_atingida,
            $ciclo['id']
        ]);
        
        echo "✅ Ciclo corrigido:\n";
        echo "   Agendamentos PAGOS: {$total_pagos}\n";
        echo "   Meta 50 atingida: " . ($meta_50_atingida ? 'SIM' : 'NÃO') . "\n";
        echo "   Meta 100 atingida: " . ($meta_100_atingida ? 'SIM' : 'NÃO') . "\n";
        
        // Calcular bônus
        $bonus_atual = 0;
        if ($meta_100_atingida) {
            $bonus_atual = 150.00;
        } elseif ($meta_50_atingida) {
            $bonus_atual = 50.00;
        }
        
        echo "   Bônus atual: R$ " . number_format($bonus_atual, 2, ',', '.') . "\n\n";
        
        echo "🎉 CORREÇÃO APLICADA! Agora o sistema conta apenas agendamentos PAGOS.\n";
    } else {
        echo "   ✅ Sistema está funcionando corretamente.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>