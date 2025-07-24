<?php
header('Content-Type: text/html; charset=utf-8');

// Incluir configuração do banco
require_once 'config.php';

echo "<h2>Verificação da Estrutura de Profissionais e Serviços</h2>";

try {
    // Verificar se a tabela profissional_servicos existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'profissional_servicos'");
    $tabelaExiste = $stmt->rowCount() > 0;
    
    echo "<h3>1. Tabela profissional_servicos</h3>";
    if ($tabelaExiste) {
        echo "<p style='color: green;'>✓ Tabela profissional_servicos existe</p>";
        
        // Mostrar estrutura da tabela
        $stmt = $pdo->query("DESCRIBE profissional_servicos");
        $estrutura = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h4>Estrutura da tabela:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($estrutura as $campo) {
            echo "<tr>";
            echo "<td>{$campo['Field']}</td>";
            echo "<td>{$campo['Type']}</td>";
            echo "<td>{$campo['Null']}</td>";
            echo "<td>{$campo['Key']}</td>";
            echo "<td>{$campo['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Mostrar dados da tabela
        $stmt = $pdo->query("SELECT * FROM profissional_servicos");
        $associacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h4>Associações existentes (" . count($associacoes) . "):</h4>";
        if (!empty($associacoes)) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Profissional ID</th><th>Serviço ID</th></tr>";
            foreach ($associacoes as $assoc) {
                echo "<tr>";
                echo "<td>{$assoc['id']}</td>";
                echo "<td>{$assoc['profissional_id']}</td>";
                echo "<td>{$assoc['servico_id']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Nenhuma associação encontrada.</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Tabela profissional_servicos não existe</p>";
    }
    
    // Buscar o Salão do Eduardo
    echo "<h3>2. Dados do Salão do Eduardo</h3>";
    $stmt = $pdo->prepare("SELECT id, nome FROM saloes WHERE nome LIKE '%Eduardo%'");
    $stmt->execute();
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($salao) {
        echo "<p><strong>Salão:</strong> {$salao['nome']} (ID: {$salao['id']})</p>";
        $salao_id = $salao['id'];
        
        // Listar profissionais do salão
        echo "<h4>Profissionais do salão:</h4>";
        $stmt = $pdo->prepare("SELECT id, nome, ativo FROM profissionais WHERE salao_id = ?");
        $stmt->execute([$salao_id]);
        $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($profissionais)) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Nome</th><th>Ativo</th></tr>";
            foreach ($profissionais as $prof) {
                $ativo = $prof['ativo'] ? 'Sim' : 'Não';
                echo "<tr>";
                echo "<td>{$prof['id']}</td>";
                echo "<td>{$prof['nome']}</td>";
                echo "<td>{$ativo}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Nenhum profissional encontrado.</p>";
        }
        
        // Listar serviços do salão
        echo "<h4>Serviços do salão:</h4>";
        $stmt = $pdo->prepare("SELECT id, nome, preco, ativo FROM servicos WHERE salao_id = ?");
        $stmt->execute([$salao_id]);
        $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($servicos)) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Nome</th><th>Preço</th><th>Ativo</th></tr>";
            foreach ($servicos as $serv) {
                $ativo = $serv['ativo'] ? 'Sim' : 'Não';
                $preco = 'R$ ' . number_format($serv['preco'], 2, ',', '.');
                echo "<tr>";
                echo "<td>{$serv['id']}</td>";
                echo "<td>{$serv['nome']}</td>";
                echo "<td>{$preco}</td>";
                echo "<td>{$ativo}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Nenhum serviço encontrado.</p>";
        }
        
        // Se a tabela de associações existe, mostrar quais serviços estão vinculados aos profissionais
        if ($tabelaExiste && !empty($profissionais)) {
            echo "<h4>Serviços vinculados aos profissionais:</h4>";
            foreach ($profissionais as $prof) {
                echo "<h5>Profissional: {$prof['nome']} (ID: {$prof['id']})</h5>";
                
                $stmt = $pdo->prepare("
                    SELECT s.id, s.nome, s.preco 
                    FROM servicos s
                    INNER JOIN profissional_servicos ps ON s.id = ps.servico_id
                    WHERE ps.profissional_id = ? AND s.ativo = 1
                ");
                $stmt->execute([$prof['id']]);
                $servicosVinculados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($servicosVinculados)) {
                    echo "<ul>";
                    foreach ($servicosVinculados as $serv) {
                        $preco = 'R$ ' . number_format($serv['preco'], 2, ',', '.');
                        echo "<li>{$serv['nome']} - {$preco} (ID: {$serv['id']})</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p>Nenhum serviço vinculado a este profissional.</p>";
                }
            }
        }
        
    } else {
        echo "<p style='color: red;'>Salão do Eduardo não encontrado!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Erro:</strong> " . $e->getMessage() . "</p>";
}
?>