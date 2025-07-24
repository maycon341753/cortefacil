<?php
require_once 'conexao.php';

echo "<h2>Teste Direto - Salão do Eduardo</h2>";

try {
    $pdo = getConexao();
    
    // 1. Buscar o Salão do Eduardo
    $stmt = $pdo->prepare("SELECT * FROM saloes WHERE nome LIKE '%Eduardo%'");
    $stmt->execute();
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        echo "<p style='color: red;'>❌ Salão não encontrado!</p>";
        exit;
    }
    
    echo "<p>✅ Salão: {$salao['nome']} (ID: {$salao['id']})</p>";
    $salao_id = $salao['id'];
    
    // 2. Verificar se existe a tabela profissional_servicos
    $stmt = $pdo->query("SHOW TABLES LIKE 'profissional_servicos'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Tabela profissional_servicos não existe! Criando...</p>";
        
        // Criar a tabela
        $sql = "CREATE TABLE IF NOT EXISTS profissional_servicos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            profissional_id INT NOT NULL,
            servico_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (profissional_id) REFERENCES profissionais(id) ON DELETE CASCADE,
            FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE,
            UNIQUE KEY unique_profissional_servico (profissional_id, servico_id)
        )";
        $pdo->exec($sql);
        echo "<p>✅ Tabela criada!</p>";
    } else {
        echo "<p>✅ Tabela profissional_servicos existe</p>";
    }
    
    // 3. Buscar profissionais ativos do salão
    $stmt = $pdo->prepare("SELECT * FROM profissionais WHERE salao_id = ? AND ativo = 1");
    $stmt->execute([$salao_id]);
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Profissionais ativos: " . count($profissionais) . "</p>";
    
    // 4. Buscar serviços ativos do salão
    $stmt = $pdo->prepare("SELECT * FROM servicos WHERE salao_id = ? AND ativo = 1");
    $stmt->execute([$salao_id]);
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Serviços ativos: " . count($servicos) . "</p>";
    
    // 5. Verificar associações existentes
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM profissional_servicos ps
        JOIN profissionais p ON ps.profissional_id = p.id
        WHERE p.salao_id = ?
    ");
    $stmt->execute([$salao_id]);
    $associacoes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Associações existentes: {$associacoes['total']}</p>";
    
    // 6. Se não há associações, criar
    if ($associacoes['total'] == 0 && !empty($profissionais) && !empty($servicos)) {
        echo "<p>Criando associações...</p>";
        
        foreach ($profissionais as $prof) {
            foreach ($servicos as $serv) {
                $stmt = $pdo->prepare("
                    INSERT IGNORE INTO profissional_servicos (profissional_id, servico_id) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$prof['id'], $serv['id']]);
            }
        }
        
        echo "<p>✅ Associações criadas!</p>";
    }
    
    // 7. Testar a consulta da API
    echo "<h3>Teste da API:</h3>";
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.id, s.nome, s.preco, s.duracao_minutos
        FROM servicos s
        INNER JOIN profissional_servicos ps ON s.id = ps.servico_id
        INNER JOIN profissionais p ON ps.profissional_id = p.id
        WHERE p.salao_id = ? AND s.ativo = 1 AND p.ativo = 1
        ORDER BY s.nome
    ");
    
    $stmt->execute([$salao_id]);
    $servicos_api = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Serviços retornados pela API: " . count($servicos_api) . "</p>";
    
    foreach ($servicos_api as $serv) {
        echo "<p>- {$serv['nome']} (R$ {$serv['preco']})</p>";
    }
    
    // 8. Testar a URL da API
    echo "<h3>Teste da URL da API:</h3>";
    $api_url = "http://localhost:8081/listar_servicos.php?salao_id=" . $salao_id;
    echo "<p><a href='$api_url' target='_blank'>$api_url</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>