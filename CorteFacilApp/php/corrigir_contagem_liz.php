<?php
require_once 'conexao.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $conn = getConexao();
    
    echo "=== VERIFICAÇÃO DA ESTRUTURA DA TABELA AGENDAMENTOS ===\n\n";
    
    // 1. Verificar estrutura da tabela agendamentos
    $stmt = $conn->prepare("DESCRIBE agendamentos");
    $stmt->execute();
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 Estrutura da tabela 'agendamentos':\n";
    foreach ($colunas as $coluna) {
        echo "   - {$coluna['Field']} ({$coluna['Type']}) - {$coluna['Null']} - Default: {$coluna['Default']}\n";
    }
    echo "\n";
    
    // 2. Buscar o salão da Liz
    $stmt = $conn->prepare("SELECT id, nome_fantasia FROM saloes WHERE nome_fantasia LIKE ?");
    $stmt->execute(['%Liz%']);
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        echo "❌ Salão da Liz não encontrado\n";
        exit;
    }
    
    echo "✅ Salão encontrado: {$salao['nome_fantasia']} (ID: {$salao['id']})\n\n";
    
    // 3. Buscar ciclo atual
    $stmt = $conn->prepare("SELECT * FROM ciclos_metas WHERE salao_id = ? AND ativo = TRUE");
    $stmt->execute([$salao['id']]);
    $ciclo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ciclo) {
        echo "❌ Nenhum ciclo ativo encontrado\n";
        exit;
    }
    
    echo "📅 Ciclo atual: {$ciclo['data_inicio']} a {$ciclo['data_fim']}\n\n";
    
    // 4. Verificar todos os agendamentos (sem filtro de pagamento)
    $stmt = $conn->prepare("
        SELECT * 
        FROM agendamentos 
        WHERE salao_id = ? 
        AND data BETWEEN ? AND ?
        ORDER BY data DESC, hora DESC
    ");
    $stmt->execute([$salao['id'], $ciclo['data_inicio'], $ciclo['data_fim']]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 TODOS os agendamentos no período:\n";
    foreach ($agendamentos as $ag) {
        echo "   ID: {$ag['id']} | {$ag['data']} {$ag['hora']} | Status: {$ag['status']}";
        if (isset($ag['valor'])) {
            echo " | Valor: R$ " . number_format($ag['valor'], 2, ',', '.');
        }
        if (isset($ag['payment_status'])) {
            echo " | Payment Status: {$ag['payment_status']}";
        }
        echo "\n";
    }
    echo "\n";
    
    // 5. Contar agendamentos confirmados
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM agendamentos 
        WHERE salao_id = ? 
        AND status = 'confirmado'
        AND data BETWEEN ? AND ?
    ");
    $stmt->execute([$salao['id'], $ciclo['data_inicio'], $ciclo['data_fim']]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_confirmados = $resultado['total'];
    
    echo "📊 Agendamentos CONFIRMADOS no período: {$total_confirmados}\n\n";
    
    // 6. Atualizar o ciclo com os agendamentos confirmados
    if ($total_confirmados > 0) {
        echo "🔄 Atualizando ciclo com agendamentos confirmados...\n";
        
        $meta_50_atingida = $total_confirmados >= 50 ? 1 : 0;
        $meta_100_atingida = $total_confirmados >= 100 ? 1 : 0;
        
        $stmt = $conn->prepare("
            UPDATE ciclos_metas 
            SET agendamentos_confirmados = ?,
                meta_50_atingida = ?,
                meta_100_atingida = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $total_confirmados,
            $meta_50_atingida,
            $meta_100_atingida,
            $ciclo['id']
        ]);
        
        echo "✅ Ciclo atualizado com sucesso:\n";
        echo "   Agendamentos confirmados: {$total_confirmados}\n";
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
        
        echo "🎉 CORREÇÃO APLICADA! O painel agora deve mostrar {$total_confirmados} agendamentos.\n";
    } else {
        echo "⚠️  Nenhum agendamento confirmado encontrado no período.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>