<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'conexao.php';

echo "<h2>Diagnóstico Completo - Salão do Eduardo</h2>";

try {
    $pdo = getConexao();
    
    // 1. Buscar o Salão do Eduardo
    echo "<h3>1. Informações do Salão</h3>";
    $stmt = $pdo->prepare("SELECT * FROM saloes WHERE nome LIKE '%Eduardo%'");
    $stmt->execute();
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        echo "<p style='color: red;'>❌ Salão do Eduardo não encontrado!</p>";
        exit;
    }
    
    echo "<p><strong>Salão:</strong> {$salao['nome']} (ID: {$salao['id']})</p>";
    $salao_id = $salao['id'];
    
    // 2. Verificar profissionais
    echo "<h3>2. Profissionais do Salão</h3>";
    $stmt = $pdo->prepare("SELECT * FROM profissionais WHERE salao_id = ?");
    $stmt->execute([$salao_id]);
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($profissionais)) {
        echo "<p style='color: red;'>❌ Nenhum profissional encontrado!</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Ativo</th><th>Email</th></tr>";
        foreach ($profissionais as $prof) {
            $ativo = $prof['ativo'] ? '✅ Sim' : '❌ Não';
            echo "<tr>";
            echo "<td>{$prof['id']}</td>";
            echo "<td>{$prof['nome']}</td>";
            echo "<td>{$ativo}</td>";
            echo "<td>{$prof['email']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Verificar serviços
    echo "<h3>3. Serviços do Salão</h3>";
    $stmt = $pdo->prepare("SELECT * FROM servicos WHERE salao_id = ?");
    $stmt->execute([$salao_id]);
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($servicos)) {
        echo "<p style='color: red;'>❌ Nenhum serviço encontrado!</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Preço</th><th>Ativo</th></tr>";
        foreach ($servicos as $serv) {
            $ativo = $serv['ativo'] ? '✅ Sim' : '❌ Não';
            $preco = 'R$ ' . number_format($serv['preco'], 2, ',', '.');
            echo "<tr>";
            echo "<td>{$serv['id']}</td>";
            echo "<td>{$serv['nome']}</td>";
            echo "<td>{$preco}</td>";
            echo "<td>{$ativo}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 4. Verificar tabela profissional_servicos
    echo "<h3>4. Tabela profissional_servicos</h3>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'profissional_servicos'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Tabela profissional_servicos não existe!</p>";
        
        // Criar a tabela
        echo "<p>Criando tabela profissional_servicos...</p>";
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
        echo "<p style='color: green;'>✅ Tabela criada!</p>";
    } else {
        echo "<p style='color: green;'>✅ Tabela profissional_servicos existe</p>";
    }
    
    // 5. Verificar associações existentes
    echo "<h3>5. Associações Profissional-Serviço</h3>";
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
        echo "<p style='color: orange;'>⚠️ Nenhuma associação encontrada!</p>";
        
        // Criar associações automáticas
        if (!empty($profissionais) && !empty($servicos)) {
            echo "<p>Criando associações automáticas...</p>";
            $stmt = $pdo->prepare("INSERT IGNORE INTO profissional_servicos (profissional_id, servico_id) VALUES (?, ?)");
            
            foreach ($profissionais as $prof) {
                if ($prof['ativo']) {
                    foreach ($servicos as $serv) {
                        if ($serv['ativo']) {
                            $stmt->execute([$prof['id'], $serv['id']]);
                            if ($stmt->rowCount() > 0) {
                                echo "<p style='color: green;'>✅ Associado: {$prof['nome']} → {$serv['nome']}</p>";
                            }
                        }
                    }
                }
            }
        }
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Profissional</th><th>Serviço</th></tr>";
        foreach ($associacoes as $assoc) {
            echo "<tr>";
            echo "<td>{$assoc['id']}</td>";
            echo "<td>{$assoc['profissional_nome']}</td>";
            echo "<td>{$assoc['servico_nome']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 6. Testar a consulta da API
    echo "<h3>6. Teste da Consulta da API</h3>";
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.id, s.nome, s.preco, s.duracao_minutos
        FROM servicos s
        INNER JOIN profissional_servicos ps ON s.id = ps.servico_id
        INNER JOIN profissionais p ON ps.profissional_id = p.id
        WHERE p.salao_id = ? AND s.ativo = 1 AND p.ativo = 1
        ORDER BY s.nome
    ");
    $stmt->execute([$salao_id]);
    $servicosAPI = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Resultado da consulta da API:</strong></p>";
    if (empty($servicosAPI)) {
        echo "<p style='color: red;'>❌ Nenhum serviço retornado pela API!</p>";
        
        // Debug da consulta
        echo "<h4>Debug da Consulta:</h4>";
        echo "<pre>";
        echo "Consulta SQL:\n";
        echo "SELECT DISTINCT s.id, s.nome, s.preco, s.duracao_minutos\n";
        echo "FROM servicos s\n";
        echo "INNER JOIN profissional_servicos ps ON s.id = ps.servico_id\n";
        echo "INNER JOIN profissionais p ON ps.profissional_id = p.id\n";
        echo "WHERE p.salao_id = {$salao_id} AND s.ativo = 1 AND p.ativo = 1\n";
        echo "ORDER BY s.nome\n\n";
        
        // Verificar cada parte da consulta
        echo "Verificações:\n";
        
        // Profissionais ativos
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM profissionais WHERE salao_id = ? AND ativo = 1");
        $stmt->execute([$salao_id]);
        $profAtivos = $stmt->fetch()['count'];
        echo "- Profissionais ativos: {$profAtivos}\n";
        
        // Serviços ativos
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM servicos WHERE salao_id = ? AND ativo = 1");
        $stmt->execute([$salao_id]);
        $servAtivos = $stmt->fetch()['count'];
        echo "- Serviços ativos: {$servAtivos}\n";
        
        // Associações
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM profissional_servicos ps
            INNER JOIN profissionais p ON ps.profissional_id = p.id
            WHERE p.salao_id = ?
        ");
        $stmt->execute([$salao_id]);
        $assocCount = $stmt->fetch()['count'];
        echo "- Associações: {$assocCount}\n";
        
        echo "</pre>";
        
    } else {
        echo "<p style='color: green;'>✅ {count($servicosAPI)} serviço(s) encontrado(s):</p>";
        echo "<ul>";
        foreach ($servicosAPI as $serv) {
            $preco = 'R$ ' . number_format($serv['preco'], 2, ',', '.');
            echo "<li>{$serv['nome']} - {$preco} ({$serv['duracao_minutos']} min)</li>";
        }
        echo "</ul>";
    }
    
    // 7. Testar a API diretamente
    echo "<h3>7. Teste Direto da API</h3>";
    $url = "http://localhost/cortefacil/CorteFacilApp/php/listar_servicos.php?salao_id={$salao_id}";
    echo "<p><strong>URL:</strong> <a href='{$url}' target='_blank'>{$url}</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Erro:</strong> " . $e->getMessage() . "</p>";
}
?>