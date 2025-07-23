<?php
// Debug corrigido da função atualizarContagemAgendamentos
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug CORRIGIDO da função atualizarContagemAgendamentos</h2>";

try {
    include 'conexao.php';
    include 'gerenciar_ciclos_metas.php';
    
    $salao_id = 4; // ID do salão Liz Hadassa
    
    echo "<h3>1. Verificando se o salão existe</h3>";
    $stmt = $conn->prepare("SELECT * FROM saloes WHERE id = ?");
    $stmt->execute([$salao_id]);
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($salao) {
        echo "✅ Salão encontrado:<br>";
        echo "<pre>";
        print_r($salao);
        echo "</pre>";
    } else {
        echo "❌ Salão não encontrado<br>";
        exit;
    }
    
    echo "<h3>2. Verificando ciclo ativo</h3>";
    $stmt = $conn->prepare("
        SELECT id, data_inicio, data_fim, agendamentos_confirmados, ativo,
               DATEDIFF(data_fim, CURDATE()) + 1 as dias_restantes
        FROM ciclos_metas 
        WHERE salao_id = ? AND ativo = TRUE
    ");
    $stmt->execute([$salao_id]);
    $ciclo_ativo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ciclo_ativo) {
        echo "✅ Ciclo ativo encontrado:<br>";
        echo "<pre>";
        print_r($ciclo_ativo);
        echo "</pre>";
    } else {
        echo "❌ Nenhum ciclo ativo encontrado. Tentando criar um novo...<br>";
        
        // Tenta criar um novo ciclo
        $data_inicio = date('Y-m-d');
        $data_fim = date('Y-m-d', strtotime($data_inicio . ' + 29 days'));
        
        $stmt = $conn->prepare("
            INSERT INTO ciclos_metas (salao_id, data_inicio, data_fim, ativo) 
            VALUES (?, ?, ?, TRUE)
        ");
        
        if ($stmt->execute([$salao_id, $data_inicio, $data_fim])) {
            $ciclo_id = $conn->lastInsertId();
            echo "✅ Novo ciclo criado com ID: $ciclo_id<br>";
            
            // Busca o ciclo recém-criado
            $stmt = $conn->prepare("
                SELECT id, data_inicio, data_fim, agendamentos_confirmados, ativo,
                       DATEDIFF(data_fim, CURDATE()) + 1 as dias_restantes
                FROM ciclos_metas 
                WHERE id = ?
            ");
            $stmt->execute([$ciclo_id]);
            $ciclo_ativo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "Dados do novo ciclo:<br>";
            echo "<pre>";
            print_r($ciclo_ativo);
            echo "</pre>";
        } else {
            echo "❌ Erro ao criar novo ciclo<br>";
            exit;
        }
    }
    
    echo "<h3>3. Contando agendamentos no período</h3>";
    echo "Período: " . $ciclo_ativo['data_inicio'] . " até " . $ciclo_ativo['data_fim'] . "<br>";
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total,
               GROUP_CONCAT(CONCAT(id, ':', status, ':', data) SEPARATOR '; ') as detalhes
        FROM agendamentos 
        WHERE salao_id = ? 
        AND (status = 'confirmado' OR status = 'realizado') 
        AND data BETWEEN ? AND ?
    ");
    
    $stmt->execute([$salao_id, $ciclo_ativo['data_inicio'], $ciclo_ativo['data_fim']]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Total de agendamentos confirmados/realizados: " . $resultado['total'] . "<br>";
    echo "Detalhes: " . ($resultado['detalhes'] ?: 'Nenhum agendamento') . "<br>";
    
    echo "<h3>4. Verificando todos os agendamentos do salão</h3>";
    $stmt = $conn->prepare("
        SELECT id, status, data, valor_servico 
        FROM agendamentos 
        WHERE salao_id = ? 
        ORDER BY data DESC 
        LIMIT 10
    ");
    $stmt->execute([$salao_id]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($agendamentos) {
        echo "Últimos 10 agendamentos:<br>";
        foreach ($agendamentos as $agendamento) {
            echo "ID: " . $agendamento['id'] . " | Status: " . $agendamento['status'] . " | Data: " . $agendamento['data'] . " | Valor: " . $agendamento['valor_servico'] . "<br>";
        }
    } else {
        echo "❌ Nenhum agendamento encontrado<br>";
    }
    
    echo "<h3>5. Tentando atualizar o ciclo manualmente</h3>";
    $total_confirmados = $resultado['total'];
    $meta_50_atingida = $total_confirmados >= 50 ? 1 : 0;
    $meta_100_atingida = $total_confirmados >= 100 ? 1 : 0;
    
    echo "Valores a serem atualizados:<br>";
    echo "- Total confirmados: $total_confirmados<br>";
    echo "- Meta 50 atingida: " . ($meta_50_atingida ? 'SIM' : 'NÃO') . " ($meta_50_atingida)<br>";
    echo "- Meta 100 atingida: " . ($meta_100_atingida ? 'SIM' : 'NÃO') . " ($meta_100_atingida)<br>";
    
    $stmt = $conn->prepare("
        UPDATE ciclos_metas 
        SET agendamentos_confirmados = ?,
            meta_50_atingida = ?,
            meta_100_atingida = ?
        WHERE id = ?
    ");
    
    if ($stmt->execute([$total_confirmados, $meta_50_atingida, $meta_100_atingida, $ciclo_ativo['id']])) {
        echo "✅ Ciclo atualizado com sucesso<br>";
        
        // Verifica a atualização
        $stmt = $conn->prepare("SELECT * FROM ciclos_metas WHERE id = ?");
        $stmt->execute([$ciclo_ativo['id']]);
        $ciclo_atualizado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Dados após atualização:<br>";
        echo "<pre>";
        print_r($ciclo_atualizado);
        echo "</pre>";
        
        echo "<h3>6. Testando a função atualizarContagemAgendamentos()</h3>";
        $resultado_funcao = atualizarContagemAgendamentos($salao_id);
        
        if ($resultado_funcao) {
            echo "✅ Função executada com sucesso:<br>";
            echo "<pre>";
            print_r($resultado_funcao);
            echo "</pre>";
        } else {
            echo "❌ Função retornou false<br>";
        }
        
    } else {
        echo "❌ Erro ao atualizar o ciclo<br>";
        $errorInfo = $stmt->errorInfo();
        echo "Erro SQL: " . $errorInfo[2] . "<br>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ ERRO CAPTURADO:</h3>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack Trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>