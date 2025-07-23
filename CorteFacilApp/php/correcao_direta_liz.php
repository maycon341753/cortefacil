<?php
require_once 'conexao.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $conn = getConexao();
    
    echo "=== CORREÇÃO DIRETA - METAS LIZ HADASSA ===\n\n";
    
    // 1. Buscar o salão da Liz
    $stmt = $conn->prepare("SELECT id, nome_fantasia FROM saloes WHERE nome_fantasia LIKE ?");
    $stmt->execute(['%Liz%']);
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        echo "❌ Salão da Liz não encontrado\n";
        exit;
    }
    
    echo "✅ Salão encontrado: {$salao['nome_fantasia']} (ID: {$salao['id']})\n\n";
    
    // 2. Verificar se a tabela ciclos_metas existe
    $stmt = $conn->prepare("SHOW TABLES LIKE 'ciclos_metas'");
    $stmt->execute();
    $tabela_existe = $stmt->fetch();
    
    if (!$tabela_existe) {
        echo "❌ Tabela ciclos_metas não existe. Criando...\n";
        
        $sql = "CREATE TABLE IF NOT EXISTS ciclos_metas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            salao_id INT NOT NULL,
            data_inicio DATE NOT NULL,
            data_fim DATE NOT NULL,
            agendamentos_confirmados INT DEFAULT 0,
            meta_50_atingida BOOLEAN DEFAULT FALSE,
            meta_100_atingida BOOLEAN DEFAULT FALSE,
            bonus_50_pago BOOLEAN DEFAULT FALSE,
            bonus_100_pago BOOLEAN DEFAULT FALSE,
            valor_bonus_pago DECIMAL(10,2) DEFAULT 0.00,
            ativo BOOLEAN DEFAULT TRUE,
            finalizado BOOLEAN DEFAULT FALSE,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (salao_id) REFERENCES saloes(id)
        )";
        
        $conn->exec($sql);
        echo "✅ Tabela ciclos_metas criada\n\n";
    } else {
        echo "✅ Tabela ciclos_metas existe\n\n";
    }
    
    // 3. Verificar ciclo atual
    $stmt = $conn->prepare("SELECT * FROM ciclos_metas WHERE salao_id = ? AND ativo = TRUE");
    $stmt->execute([$salao['id']]);
    $ciclo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ciclo) {
        echo "❌ Nenhum ciclo ativo encontrado. Criando novo ciclo...\n";
        
        $data_inicio = date('Y-m-d');
        $data_fim = date('Y-m-d', strtotime($data_inicio . ' + 29 days'));
        
        $stmt = $conn->prepare("
            INSERT INTO ciclos_metas (salao_id, data_inicio, data_fim, ativo) 
            VALUES (?, ?, ?, TRUE)
        ");
        $stmt->execute([$salao['id'], $data_inicio, $data_fim]);
        
        $ciclo_id = $conn->lastInsertId();
        echo "✅ Novo ciclo criado (ID: {$ciclo_id})\n\n";
        
        // Buscar o ciclo recém-criado
        $stmt = $conn->prepare("SELECT * FROM ciclos_metas WHERE id = ?");
        $stmt->execute([$ciclo_id]);
        $ciclo = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "✅ Ciclo ativo encontrado (ID: {$ciclo['id']})\n";
        echo "   Período: {$ciclo['data_inicio']} a {$ciclo['data_fim']}\n\n";
    }
    
    // 4. Contar agendamentos confirmados e realizados
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
    
    echo "📊 Agendamentos confirmados/realizados no período: {$total_confirmados}\n\n";
    
    // 5. Verificar se as metas foram atingidas
    $meta_50_atingida = $total_confirmados >= 50 ? 1 : 0;
    $meta_100_atingida = $total_confirmados >= 100 ? 1 : 0;
    
    // 6. Atualizar o ciclo
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
    
    // Calcular dias restantes
    $dias_restantes = max(0, (strtotime($ciclo['data_fim']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24) + 1);
    echo "   Dias restantes: {$dias_restantes}\n";
    
    // Calcular bônus atual
    $bonus_atual = 0;
    if ($meta_100_atingida) {
        $bonus_atual = 150.00; // R$ 50 + R$ 100
    } elseif ($meta_50_atingida) {
        $bonus_atual = 50.00;
    }
    
    echo "   Bônus atual: R$ " . number_format($bonus_atual, 2, ',', '.') . "\n\n";
    
    echo "🎉 CORREÇÃO CONCLUÍDA! O sistema de metas agora deve exibir os valores corretos.\n";
    echo "📱 Acesse o painel do salão para verificar as metas atualizadas.\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>