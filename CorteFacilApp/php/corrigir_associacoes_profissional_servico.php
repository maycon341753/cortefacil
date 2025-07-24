<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';

echo "<h2>Verificação e Correção de Associações Profissional-Serviço</h2>";

try {
    // Verificar se a tabela profissional_servicos existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'profissional_servicos'");
    $tabelaExiste = $stmt->rowCount() > 0;
    
    if (!$tabelaExiste) {
        echo "<p style='color: orange;'>Criando tabela profissional_servicos...</p>";
        $pdo->exec("
            CREATE TABLE profissional_servicos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                profissional_id INT NOT NULL,
                servico_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (profissional_id) REFERENCES profissionais(id) ON DELETE CASCADE,
                FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE,
                UNIQUE KEY unique_prof_serv (profissional_id, servico_id)
            )
        ");
        echo "<p style='color: green;'>✓ Tabela profissional_servicos criada!</p>";
    } else {
        echo "<p style='color: green;'>✓ Tabela profissional_servicos já existe</p>";
    }
    
    // Buscar o Salão do Eduardo
    $stmt = $pdo->prepare("SELECT id, nome FROM saloes WHERE nome LIKE '%Eduardo%'");
    $stmt->execute();
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        echo "<p style='color: red;'>✗ Salão do Eduardo não encontrado!</p>";
        exit;
    }
    
    echo "<h3>Salão: {$salao['nome']} (ID: {$salao['id']})</h3>";
    $salao_id = $salao['id'];
    
    // Listar profissionais ativos do salão
    $stmt = $pdo->prepare("SELECT id, nome FROM profissionais WHERE salao_id = ? AND ativo = 1");
    $stmt->execute([$salao_id]);
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Profissionais ativos:</h4>";
    if (empty($profissionais)) {
        echo "<p style='color: red;'>Nenhum profissional ativo encontrado!</p>";
        exit;
    }
    
    foreach ($profissionais as $prof) {
        echo "<p>- {$prof['nome']} (ID: {$prof['id']})</p>";
    }
    
    // Listar serviços ativos do salão
    $stmt = $pdo->prepare("SELECT id, nome, preco FROM servicos WHERE salao_id = ? AND ativo = 1");
    $stmt->execute([$salao_id]);
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Serviços ativos:</h4>";
    if (empty($servicos)) {
        echo "<p style='color: red;'>Nenhum serviço ativo encontrado!</p>";
        exit;
    }
    
    foreach ($servicos as $serv) {
        $preco = 'R$ ' . number_format($serv['preco'], 2, ',', '.');
        echo "<p>- {$serv['nome']} - {$preco} (ID: {$serv['id']})</p>";
    }
    
    // Verificar associações existentes
    echo "<h4>Associações existentes:</h4>";
    $stmt = $pdo->prepare("
        SELECT ps.*, p.nome as profissional_nome, s.nome as servico_nome
        FROM profissional_servicos ps
        INNER JOIN profissionais p ON ps.profissional_id = p.id
        INNER JOIN servicos s ON ps.servico_id = s.id
        WHERE p.salao_id = ?
    ");
    $stmt->execute([$salao_id]);
    $associacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($associacoes)) {
        echo "<p style='color: orange;'>Nenhuma associação encontrada. Criando associações automáticas...</p>";
        
        // Criar associações automáticas: cada profissional pode fazer todos os serviços do salão
        $stmt = $pdo->prepare("INSERT IGNORE INTO profissional_servicos (profissional_id, servico_id) VALUES (?, ?)");
        $associacoesCriadas = 0;
        
        foreach ($profissionais as $prof) {
            foreach ($servicos as $serv) {
                $stmt->execute([$prof['id'], $serv['id']]);
                if ($stmt->rowCount() > 0) {
                    $associacoesCriadas++;
                    echo "<p style='color: green;'>✓ Associado: {$prof['nome']} → {$serv['nome']}</p>";
                }
            }
        }
        
        echo "<p style='color: green;'><strong>Total de associações criadas: {$associacoesCriadas}</strong></p>";
        
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Profissional</th><th>Serviço</th></tr>";
        foreach ($associacoes as $assoc) {
            echo "<tr>";
            echo "<td>{$assoc['profissional_nome']}</td>";
            echo "<td>{$assoc['servico_nome']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Testar a nova API
    echo "<h4>Teste da API modificada:</h4>";
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.id, s.nome, s.preco, s.duracao_minutos
        FROM servicos s
        INNER JOIN profissional_servicos ps ON s.id = ps.servico_id
        INNER JOIN profissionais p ON ps.profissional_id = p.id
        WHERE p.salao_id = ? AND s.ativo = 1 AND p.ativo = 1
        ORDER BY s.nome
    ");
    $stmt->execute([$salao_id]);
    $servicosVinculados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Serviços que aparecerão no modal (vinculados aos profissionais ativos):</strong></p>";
    if (!empty($servicosVinculados)) {
        echo "<ul>";
        foreach ($servicosVinculados as $serv) {
            $preco = 'R$ ' . number_format($serv['preco'], 2, ',', '.');
            echo "<li>{$serv['nome']} - {$preco} ({$serv['duracao_minutos']} min)</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>Nenhum serviço será exibido (problema nas associações)</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Erro:</strong> " . $e->getMessage() . "</p>";
}
?>