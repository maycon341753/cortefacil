<?php
require_once 'conexao.php';
require_once 'gerenciar_ciclos_metas.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $conn = getConexao();
    
    echo "=== DIAGNÓSTICO E CORREÇÃO - METAS LIZ HADASSA ===\n\n";
    
    // 1. Buscar o salão da Liz
    $stmt = $conn->prepare("SELECT id, nome_fantasia FROM saloes WHERE nome_fantasia LIKE ?");
    $stmt->execute(['%Liz%']);
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        echo "❌ Salão da Liz não encontrado\n";
        exit;
    }
    
    echo "✅ Salão encontrado: {$salao['nome_fantasia']} (ID: {$salao['id']})\n\n";
    
    // 2. Verificar agendamentos
    $stmt = $conn->prepare("
        SELECT id, data, hora, status, status_pagamento, valor_servico 
        FROM agendamentos 
        WHERE salao_id = ? 
        ORDER BY data DESC, hora DESC
    ");
    $stmt->execute([$salao['id']]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 Total de agendamentos: " . count($agendamentos) . "\n\n";
    
    if (count($agendamentos) > 0) {
        echo "📋 Lista de agendamentos:\n";
        foreach ($agendamentos as $ag) {
            echo "   ID: {$ag['id']} | Data: {$ag['data']} | Hora: {$ag['hora']} | Status: {$ag['status']} | Pagamento: {$ag['status_pagamento']} | Valor: R$ {$ag['valor_servico']}\n";
        }
        echo "\n";
    }
    
    // 3. Verificar ciclo atual
    $stmt = $conn->prepare("SELECT * FROM ciclos_metas WHERE salao_id = ? AND ativo = TRUE");
    $stmt->execute([$salao['id']]);
    $ciclo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ciclo) {
        echo "📅 Ciclo atual encontrado:\n";
        echo "   ID: {$ciclo['id']}\n";
        echo "   Período: {$ciclo['data_inicio']} a {$ciclo['data_fim']}\n";
        echo "   Agendamentos confirmados: {$ciclo['agendamentos_confirmados']}\n\n";
    } else {
        echo "❌ Nenhum ciclo ativo encontrado\n\n";
    }
    
    // 4. Verificar agendamentos pagos que deveriam ser confirmados
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM agendamentos 
        WHERE salao_id = ? 
        AND status_pagamento = 'pago'
        AND status != 'confirmado'
    ");
    $stmt->execute([$salao['id']]);
    $agendamentos_pagos_nao_confirmados = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($agendamentos_pagos_nao_confirmados > 0) {
        echo "🔧 PROBLEMA ENCONTRADO: {$agendamentos_pagos_nao_confirmados} agendamentos pagos não estão com status 'confirmado'\n\n";
        
        // 5. Corrigir status dos agendamentos pagos
        echo "🔄 Corrigindo status dos agendamentos pagos...\n";
        $stmt = $conn->prepare("
            UPDATE agendamentos 
            SET status = 'confirmado' 
            WHERE salao_id = ? 
            AND status_pagamento = 'pago'
            AND status != 'confirmado'
        ");
        $stmt->execute([$salao['id']]);
        $agendamentos_corrigidos = $stmt->rowCount();
        
        echo "✅ {$agendamentos_corrigidos} agendamentos corrigidos para status 'confirmado'\n\n";
    } else {
        echo "✅ Todos os agendamentos pagos já estão com status correto\n\n";
    }
    
    // 6. Atualizar contagem do ciclo
    echo "🔄 Atualizando contagem do ciclo de metas...\n";
    
    try {
        $ciclo_atualizado = atualizarContagemAgendamentos($salao['id']);
        
        if ($ciclo_atualizado) {
            echo "✅ Ciclo atualizado com sucesso:\n";
            echo "   Agendamentos confirmados: {$ciclo_atualizado['agendamentos_confirmados']}\n";
            echo "   Meta 50 atingida: " . ($ciclo_atualizado['meta_50_atingida'] ? 'SIM' : 'NÃO') . "\n";
            echo "   Meta 100 atingida: " . ($ciclo_atualizado['meta_100_atingida'] ? 'SIM' : 'NÃO') . "\n";
            echo "   Dias restantes: {$ciclo_atualizado['dias_restantes']}\n";
            
            // Calcular bônus atual
            $bonus_atual = 0;
            if ($ciclo_atualizado['meta_100_atingida']) {
                $bonus_atual = 150.00; // R$ 50 + R$ 100
            } elseif ($ciclo_atualizado['meta_50_atingida']) {
                $bonus_atual = 50.00;
            }
            
            echo "   Bônus atual: R$ " . number_format($bonus_atual, 2, ',', '.') . "\n\n";
            
            echo "🎉 CORREÇÃO CONCLUÍDA! O sistema de metas agora deve exibir os valores corretos.\n";
        } else {
            echo "❌ Erro ao atualizar ciclo - função retornou false\n";
        }
    } catch (Exception $e) {
        echo "❌ Erro na atualização: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>