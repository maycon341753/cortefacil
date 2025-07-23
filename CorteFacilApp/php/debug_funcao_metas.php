<?php
// Debug específico para a função atualizarContagemAgendamentos
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug da função atualizarContagemAgendamentos</h2>";

try {
    include 'conexao.php';
    include 'gerenciar_ciclos_metas.php';
    
    $salao_id = 4; // ID do salão Liz Hadassa
    
    echo "<h3>1. Verificando se o salão existe</h3>";
    $stmt = $conn->prepare("SELECT id, nome FROM saloes WHERE id = ?");
    $stmt->execute([$salao_id]);
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($salao) {
        echo "✅ Salão encontrado: " . $salao['nome'] . "<br>";
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
    
    echo "<h3>4. Verificando estrutura da tabela agendamentos</h3>";
    $stmt = $conn->prepare("DESCRIBE agendamentos");
    $stmt->execute();
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . $coluna['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>5. Verificando estrutura da tabela ciclos_metas</h3>";
    $stmt = $conn->prepare("DESCRIBE ciclos_metas");
    $stmt->execute();
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . $coluna['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>6. Tentando atualizar o ciclo manualmente</h3>";
    $total_confirmados = $resultado['total'];
    $meta_50_atingida = $total_confirmados >= 50;
    $meta_100_atingida = $total_confirmados >= 100;
    
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
        
        echo "<h3>7. Testando retorno da função</h3>";
        $retorno = [
            'ciclo_id' => $ciclo_ativo['id'],
            'agendamentos_confirmados' => $total_confirmados,
            'meta_50_atingida' => $meta_50_atingida,
            'meta_100_atingida' => $meta_100_atingida,
            'data_inicio' => $ciclo_ativo['data_inicio'],
            'data_fim' => $ciclo_ativo['data_fim'],
            'dias_restantes' => max(0, (strtotime($ciclo_ativo['data_fim']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24) + 1)
        ];
        
        echo "✅ Retorno simulado da função:<br>";
        echo "<pre>";
        print_r($retorno);
        echo "</pre>";
        
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