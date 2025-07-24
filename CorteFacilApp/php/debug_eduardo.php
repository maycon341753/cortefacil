<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'conexao.php';

echo "<h2>Debug - Salão do Eduardo</h2>";

try {
    $pdo = getConexao();
    
    // 1. Buscar o Salão do Eduardo
    echo "<h3>1. Salão do Eduardo</h3>";
    $stmt = $pdo->prepare("SELECT * FROM saloes WHERE nome LIKE '%Eduardo%'");
    $stmt->execute();
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        echo "<p style='color: red;'>❌ Salão não encontrado!</p>";
        exit;
    }
    
    echo "<p>✅ Salão: {$salao['nome']} (ID: {$salao['id']})</p>";
    $salao_id = $salao['id'];
    
    // 2. Profissionais do salão
    echo "<h3>2. Profissionais do Salão</h3>";
    $stmt = $pdo->prepare("SELECT * FROM profissionais WHERE salao_id = ? AND ativo = 1");
    $stmt->execute([$salao_id]);
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($profissionais)) {
        echo "<p style='color: red;'>❌ Nenhum profissional ativo encontrado!</p>";
    } else {
        echo "<p>✅ Profissionais ativos: " . count($profissionais) . "</p>";
        foreach ($profissionais as $prof) {
            echo "<p>- {$prof['nome']} (ID: {$prof['id']})</p>";
        }
    }
    
    // 3. Serviços do salão
    echo "<h3>3. Serviços do Salão</h3>";
    $stmt = $pdo->prepare("SELECT * FROM servicos WHERE salao_id = ? AND ativo = 1");
    $stmt->execute([$salao_id]);
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($servicos)) {
        echo "<p style='color: red;'>❌ Nenhum serviço ativo encontrado!</p>";
    } else {
        echo "<p>✅ Serviços ativos: " . count($servicos) . "</p>";
        foreach ($servicos as $serv) {
            echo "<p>- {$serv['nome']} (ID: {$serv['id']})</p>";
        }
    }
    
    // 4. Verificar tabela profissional_servicos
    echo "<h3>4. Tabela profissional_servicos</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'profissional_servicos'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Tabela profissional_servicos não existe!</p>";
    } else {
        echo "<p>✅ Tabela profissional_servicos existe</p>";
        
        // Verificar associações
        $stmt = $pdo->prepare("
            SELECT ps.*, p.nome as profissional_nome, s.nome as servico_nome 
            FROM profissional_servicos ps
            JOIN profissionais p ON ps.profissional_id = p.id
            JOIN servicos s ON ps.servico_id = s.id
            WHERE p.salao_id = ?
        ");
        $stmt->execute([$salao_id]);
        $associacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($associacoes)) {
            echo "<p style='color: red;'>❌ Nenhuma associação profissional-serviço encontrada!</p>";
        } else {
            echo "<p>✅ Associações encontradas: " . count($associacoes) . "</p>";
            foreach ($associacoes as $assoc) {
                echo "<p>- {$assoc['profissional_nome']} → {$assoc['servico_nome']}</p>";
            }
        }
    }
    
    // 5. Testar a consulta da API
    echo "<h3>5. Teste da Consulta da API</h3>";
    $sql = "
        SELECT DISTINCT s.id, s.nome, s.preco, s.duracao, s.descricao
        FROM servicos s
        INNER JOIN profissional_servicos ps ON s.id = ps.servico_id
        INNER JOIN profissionais p ON ps.profissional_id = p.id
        WHERE p.salao_id = ? AND s.ativo = 1 AND p.ativo = 1
        ORDER BY s.nome
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$salao_id]);
    $servicos_api = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($servicos_api)) {
        echo "<p style='color: red;'>❌ A consulta da API não retornou nenhum serviço!</p>";
        echo "<p><strong>SQL:</strong> $sql</p>";
        echo "<p><strong>Parâmetro:</strong> $salao_id</p>";
    } else {
        echo "<p>✅ A consulta da API retornou " . count($servicos_api) . " serviços:</p>";
        foreach ($servicos_api as $serv) {
            echo "<p>- {$serv['nome']} (ID: {$serv['id']})</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>