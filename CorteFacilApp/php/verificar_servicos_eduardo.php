<?php
require_once 'conexao.php';

try {
    $pdo = getConexao();
    
    echo "<h2>Verificação de Serviços - Salão do Eduardo</h2>";
    
    // 1. Verificar se a tabela servicos existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'servicos'");
    if ($stmt->rowCount() == 0) {
        echo "<div style='color: red;'>❌ Tabela 'servicos' não existe!</div>";
        
        // Criar a tabela servicos
        echo "<h3>Criando tabela 'servicos'...</h3>";
        $createTable = "CREATE TABLE IF NOT EXISTS servicos (
            id INT(11) NOT NULL AUTO_INCREMENT,
            nome VARCHAR(100) NOT NULL,
            preco DECIMAL(10,2) NOT NULL,
            duracao_minutos INT NOT NULL,
            salao_id INT(11) NOT NULL,
            ativo BOOLEAN DEFAULT 1,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (salao_id) REFERENCES saloes(id)
        )";
        
        $pdo->exec($createTable);
        echo "<div style='color: green;'>✅ Tabela 'servicos' criada com sucesso!</div>";
    } else {
        echo "<div style='color: green;'>✅ Tabela 'servicos' existe</div>";
    }
    
    // 2. Buscar o ID do Salão do Eduardo
    $stmt = $pdo->prepare("SELECT id, nome_fantasia FROM saloes WHERE nome_fantasia LIKE '%Eduardo%'");
    $stmt->execute();
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$salao) {
        echo "<div style='color: red;'>❌ Salão do Eduardo não encontrado!</div>";
        exit;
    }
    
    $salaoId = $salao['id'];
    echo "<div style='color: blue;'>📍 Salão encontrado: {$salao['nome_fantasia']} (ID: {$salaoId})</div>";
    
    // 3. Verificar serviços existentes
    $stmt = $pdo->prepare("SELECT * FROM servicos WHERE salao_id = ?");
    $stmt->execute([$salaoId]);
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Serviços cadastrados:</h3>";
    if (empty($servicos)) {
        echo "<div style='color: orange;'>⚠️ Nenhum serviço cadastrado para este salão!</div>";
        
        // Criar serviços padrão
        echo "<h3>Criando serviços padrão...</h3>";
        $servicosPadrao = [
            ['nome' => 'Corte Masculino', 'preco' => 25.00, 'duracao' => 30],
            ['nome' => 'Corte Feminino', 'preco' => 35.00, 'duracao' => 45],
            ['nome' => 'Barba', 'preco' => 15.00, 'duracao' => 20],
            ['nome' => 'Escova', 'preco' => 30.00, 'duracao' => 40],
            ['nome' => 'Coloração', 'preco' => 80.00, 'duracao' => 120]
        ];
        
        foreach ($servicosPadrao as $servico) {
            $stmt = $pdo->prepare("INSERT INTO servicos (nome, preco, duracao_minutos, salao_id, ativo) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$servico['nome'], $servico['preco'], $servico['duracao'], $salaoId]);
            echo "<div style='color: green;'>✅ Serviço '{$servico['nome']}' criado - R$ {$servico['preco']}</div>";
        }
        
        echo "<div style='color: green;'><strong>✅ Todos os serviços foram criados com sucesso!</strong></div>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Preço</th><th>Duração (min)</th><th>Ativo</th></tr>";
        foreach ($servicos as $servico) {
            $ativo = $servico['ativo'] ? 'Sim' : 'Não';
            echo "<tr>";
            echo "<td>{$servico['id']}</td>";
            echo "<td>{$servico['nome']}</td>";
            echo "<td>R$ " . number_format($servico['preco'], 2, ',', '.') . "</td>";
            echo "<td>{$servico['duracao_minutos']}</td>";
            echo "<td>{$ativo}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 4. Testar a API de listagem
    echo "<h3>Testando API de listagem de serviços:</h3>";
    $url = "http://localhost/cortefacil/CorteFacilApp/php/listar_servicos.php?salao_id={$salaoId}";
    echo "<div>URL: <a href='{$url}' target='_blank'>{$url}</a></div>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Erro: " . $e->getMessage() . "</div>";
}
?>