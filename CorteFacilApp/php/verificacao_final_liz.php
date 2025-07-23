<?php
require_once 'conexao.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $conn = getConexao();
    
    echo "=== TESTE SIMPLES - VERIFICAÇÃO FINAL ===\n\n";
    
    // 1. Buscar o salão da Liz
    $stmt = $conn->prepare("SELECT id, nome_fantasia FROM saloes WHERE nome_fantasia LIKE ?");
    $stmt->execute(['%Liz%']);
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ Salão: {$salao['nome_fantasia']} (ID: {$salao['id']})\n\n";
    
    // 2. Verificar ciclo atual na base de dados
    $stmt = $conn->prepare("SELECT * FROM ciclos_metas WHERE salao_id = ? AND ativo = TRUE");
    $stmt->execute([$salao['id']]);
    $ciclo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ciclo) {
        echo "📅 Ciclo ativo encontrado:\n";
        echo "   ID: {$ciclo['id']}\n";
        echo "   Período: {$ciclo['data_inicio']} a {$ciclo['data_fim']}\n";
        echo "   Agendamentos confirmados: {$ciclo['agendamentos_confirmados']}\n";
        echo "   Meta 50 atingida: " . ($ciclo['meta_50_atingida'] ? 'SIM' : 'NÃO') . "\n";
        echo "   Meta 100 atingida: " . ($ciclo['meta_100_atingida'] ? 'SIM' : 'NÃO') . "\n\n";
        
        // 3. Verificar se os dados estão corretos
        if ($ciclo['agendamentos_confirmados'] > 0) {
            echo "✅ DADOS CORRETOS! O ciclo tem {$ciclo['agendamentos_confirmados']} agendamentos confirmados.\n";
            echo "📱 O problema pode estar na interface web ou na sessão do usuário.\n\n";
            
            echo "🔧 SOLUÇÕES POSSÍVEIS:\n";
            echo "   1. Limpar cache do navegador\n";
            echo "   2. Fazer logout e login novamente no painel do salão\n";
            echo "   3. Verificar se a sessão está funcionando corretamente\n\n";
            
            echo "📋 Para testar, acesse: http://localhost:8000/salao/metas.php\n";
            echo "   (Certifique-se de estar logado como o salão da Liz)\n";
        } else {
            echo "⚠️  O ciclo ainda mostra 0 agendamentos. Vou forçar uma atualização...\n";
            
            // Forçar atualização
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
            
            echo "   Agendamentos confirmados encontrados: {$total_confirmados}\n";
            
            if ($total_confirmados > 0) {
                $stmt = $conn->prepare("
                    UPDATE ciclos_metas 
                    SET agendamentos_confirmados = ?
                    WHERE id = ?
                ");
                $stmt->execute([$total_confirmados, $ciclo['id']]);
                
                echo "✅ Ciclo atualizado com {$total_confirmados} agendamentos!\n";
            }
        }
    } else {
        echo "❌ Nenhum ciclo ativo encontrado para este salão.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>