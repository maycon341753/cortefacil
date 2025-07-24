<?php
require_once 'php/conexao.php';

echo "<h2>Debug - Agendamentos do Thales</h2>";

try {
    $pdo = getConexao();
    
    // 1. Buscar o cliente Thales
    echo "<h3>1. Buscando cliente Thales</h3>";
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE nome LIKE '%thales%' OR nome LIKE '%Thales%'");
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($clientes)) {
        echo "<p style='color: red;'>❌ Cliente Thales não encontrado!</p>";
        
        // Listar todos os clientes para verificar
        echo "<h4>Todos os clientes cadastrados:</h4>";
        $stmt = $pdo->prepare("SELECT id, nome, email FROM clientes ORDER BY nome");
        $stmt->execute();
        $todosClientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($todosClientes as $cliente) {
            echo "<p>ID: {$cliente['id']} - Nome: {$cliente['nome']} - Email: {$cliente['email']}</p>";
        }
    } else {
        foreach ($clientes as $cliente) {
            echo "<p style='color: green;'>✅ Cliente encontrado: ID {$cliente['id']} - {$cliente['nome']} ({$cliente['email']})</p>";
        }
    }
    
    // 2. Buscar o Salão do Eduardo
    echo "<h3>2. Buscando Salão do Eduardo</h3>";
    $stmt = $pdo->prepare("SELECT * FROM saloes WHERE nome_fantasia LIKE '%Eduardo%' OR nome LIKE '%Eduardo%'");
    $stmt->execute();
    $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($saloes)) {
        echo "<p style='color: red;'>❌ Salão do Eduardo não encontrado!</p>";
        
        // Listar todos os salões
        echo "<h4>Todos os salões cadastrados:</h4>";
        $stmt = $pdo->prepare("SELECT id, nome_fantasia, nome FROM saloes ORDER BY nome_fantasia");
        $stmt->execute();
        $todosSaloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($todosSaloes as $salao) {
            echo "<p>ID: {$salao['id']} - {$salao['nome_fantasia']} ({$salao['nome']})</p>";
        }
    } else {
        foreach ($saloes as $salao) {
            echo "<p style='color: green;'>✅ Salão encontrado: ID {$salao['id']} - {$salao['nome_fantasia']}</p>";
        }
    }
    
    // 3. Buscar agendamentos do Thales (se encontrado)
    if (!empty($clientes)) {
        echo "<h3>3. Agendamentos do Thales</h3>";
        
        foreach ($clientes as $cliente) {
            $stmt = $pdo->prepare("
                SELECT 
                    a.*,
                    s.nome_fantasia as salao_nome,
                    srv.nome as servico_nome,
                    p.nome as profissional_nome
                FROM agendamentos a
                LEFT JOIN saloes s ON a.salao_id = s.id
                LEFT JOIN servicos srv ON a.servico_id = srv.id
                LEFT JOIN profissionais p ON a.profissional_id = p.id
                WHERE a.cliente_id = ?
                ORDER BY a.criado_em DESC
            ");
            $stmt->execute([$cliente['id']]);
            $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($agendamentos)) {
                echo "<p style='color: orange;'>⚠️ Nenhum agendamento encontrado para {$cliente['nome']}</p>";
            } else {
                echo "<p style='color: green;'>✅ Encontrados " . count($agendamentos) . " agendamento(s) para {$cliente['nome']}:</p>";
                
                foreach ($agendamentos as $agendamento) {
                    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px 0;'>";
                    echo "<strong>ID:</strong> {$agendamento['id']}<br>";
                    echo "<strong>Salão:</strong> {$agendamento['salao_nome']} (ID: {$agendamento['salao_id']})<br>";
                    echo "<strong>Serviço:</strong> {$agendamento['servico_nome']}<br>";
                    echo "<strong>Profissional:</strong> {$agendamento['profissional_nome']}<br>";
                    echo "<strong>Data:</strong> {$agendamento['data']}<br>";
                    echo "<strong>Hora:</strong> {$agendamento['hora']}<br>";
                    echo "<strong>Status:</strong> {$agendamento['status']}<br>";
                    echo "<strong>Criado em:</strong> {$agendamento['criado_em']}<br>";
                    echo "<strong>Valor:</strong> R$ " . number_format($agendamento['valor'], 2, ',', '.') . "<br>";
                    echo "</div>";
                }
            }
        }
    }
    
    // 4. Verificar agendamentos recentes no salão do Eduardo (se encontrado)
    if (!empty($saloes)) {
        echo "<h3>4. Agendamentos recentes no Salão do Eduardo</h3>";
        
        foreach ($saloes as $salao) {
            $stmt = $pdo->prepare("
                SELECT 
                    a.*,
                    c.nome as cliente_nome,
                    srv.nome as servico_nome,
                    p.nome as profissional_nome
                FROM agendamentos a
                LEFT JOIN clientes c ON a.cliente_id = c.id
                LEFT JOIN servicos srv ON a.servico_id = srv.id
                LEFT JOIN profissionais p ON a.profissional_id = p.id
                WHERE a.salao_id = ?
                ORDER BY a.criado_em DESC
                LIMIT 10
            ");
            $stmt->execute([$salao['id']]);
            $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($agendamentos)) {
                echo "<p style='color: orange;'>⚠️ Nenhum agendamento encontrado no {$salao['nome_fantasia']}</p>";
            } else {
                echo "<p style='color: green;'>✅ Últimos " . count($agendamentos) . " agendamento(s) no {$salao['nome_fantasia']}:</p>";
                
                foreach ($agendamentos as $agendamento) {
                    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px 0;'>";
                    echo "<strong>ID:</strong> {$agendamento['id']}<br>";
                    echo "<strong>Cliente:</strong> {$agendamento['cliente_nome']} (ID: {$agendamento['cliente_id']})<br>";
                    echo "<strong>Serviço:</strong> {$agendamento['servico_nome']}<br>";
                    echo "<strong>Profissional:</strong> {$agendamento['profissional_nome']}<br>";
                    echo "<strong>Data:</strong> {$agendamento['data']}<br>";
                    echo "<strong>Hora:</strong> {$agendamento['hora']}<br>";
                    echo "<strong>Status:</strong> {$agendamento['status']}<br>";
                    echo "<strong>Criado em:</strong> {$agendamento['criado_em']}<br>";
                    echo "<strong>Valor:</strong> R$ " . number_format($agendamento['valor'], 2, ',', '.') . "<br>";
                    echo "</div>";
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>