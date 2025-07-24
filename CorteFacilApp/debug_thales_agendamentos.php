<?php
require_once 'php/conexao.php';

echo "<h2>Debug - Agendamentos do Thales (Tabela Usuarios)</h2>";

try {
    $pdo = getConexao();
    
    // 1. Buscar o usuário Thales na tabela usuarios
    echo "<h3>1. Buscando usuário Thales</h3>";
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nome LIKE '%thales%' OR nome LIKE '%Thales%'");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($usuarios)) {
        echo "<p style='color: red;'>❌ Usuário Thales não encontrado!</p>";
        
        // Listar todos os usuários para verificar
        echo "<h4>Todos os usuários cadastrados:</h4>";
        $stmt = $pdo->prepare("SELECT id, nome, email, tipo FROM usuarios ORDER BY nome");
        $stmt->execute();
        $todosUsuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($todosUsuarios as $usuario) {
            echo "<p>ID: {$usuario['id']} - Nome: {$usuario['nome']} - Email: {$usuario['email']} - Tipo: {$usuario['tipo']}</p>";
        }
    } else {
        foreach ($usuarios as $usuario) {
            echo "<p style='color: green;'>✅ Usuário encontrado: ID {$usuario['id']} - {$usuario['nome']} ({$usuario['email']}) - Tipo: {$usuario['tipo']}</p>";
        }
    }
    
    // 2. Buscar o Salão do Eduardo
    echo "<h3>2. Buscando Salão do Eduardo</h3>";
    $stmt = $pdo->prepare("SELECT * FROM saloes WHERE nome_fantasia LIKE '%Eduardo%'");
    $stmt->execute();
    $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($saloes)) {
        echo "<p style='color: red;'>❌ Salão do Eduardo não encontrado!</p>";
        
        // Listar todos os salões
        echo "<h4>Todos os salões cadastrados:</h4>";
        $stmt = $pdo->prepare("SELECT id, nome_fantasia FROM saloes ORDER BY nome_fantasia");
        $stmt->execute();
        $todosSaloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($todosSaloes as $salao) {
            echo "<p>ID: {$salao['id']} - {$salao['nome_fantasia']}</p>";
        }
    } else {
        foreach ($saloes as $salao) {
            echo "<p style='color: green;'>✅ Salão encontrado: ID {$salao['id']} - {$salao['nome_fantasia']}</p>";
        }
    }
    
    // 3. Buscar agendamentos do Thales (se encontrado)
    if (!empty($usuarios)) {
        echo "<h3>3. Agendamentos do Thales</h3>";
        
        foreach ($usuarios as $usuario) {
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
            $stmt->execute([$usuario['id']]);
            $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($agendamentos)) {
                echo "<p style='color: orange;'>⚠️ Nenhum agendamento encontrado para {$usuario['nome']}</p>";
            } else {
                echo "<p style='color: green;'>✅ Encontrados " . count($agendamentos) . " agendamento(s) para {$usuario['nome']}:</p>";
                
                foreach ($agendamentos as $agendamento) {
                    $statusColor = $agendamento['status'] == 'confirmado' ? 'green' : 
                                  ($agendamento['status'] == 'pago' ? 'blue' : 'orange');
                    
                    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px 0;'>";
                    echo "<strong>ID:</strong> {$agendamento['id']}<br>";
                    echo "<strong>Salão:</strong> {$agendamento['salao_nome']} (ID: {$agendamento['salao_id']})<br>";
                    echo "<strong>Serviço:</strong> {$agendamento['servico_nome']}<br>";
                    echo "<strong>Profissional:</strong> {$agendamento['profissional_nome']}<br>";
                    echo "<strong>Data:</strong> {$agendamento['data']}<br>";
                    echo "<strong>Hora:</strong> {$agendamento['hora']}<br>";
                    echo "<strong>Status:</strong> <span style='color: {$statusColor}; font-weight: bold;'>{$agendamento['status']}</span><br>";
                    echo "<strong>Criado em:</strong> {$agendamento['criado_em']}<br>";
                    echo "<strong>Valor:</strong> R$ " . number_format($agendamento['valor'], 2, ',', '.') . "<br>";
                    echo "</div>";
                }
            }
        }
    }
    
    // 4. Verificar todos os agendamentos recentes para debug
    echo "<h3>4. Todos os agendamentos recentes (últimos 10)</h3>";
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            u.nome as cliente_nome,
            s.nome_fantasia as salao_nome,
            srv.nome as servico_nome,
            p.nome as profissional_nome
        FROM agendamentos a
        LEFT JOIN usuarios u ON a.cliente_id = u.id
        LEFT JOIN saloes s ON a.salao_id = s.id
        LEFT JOIN servicos srv ON a.servico_id = srv.id
        LEFT JOIN profissionais p ON a.profissional_id = p.id
        ORDER BY a.criado_em DESC
        LIMIT 10
    ");
    $stmt->execute();
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($agendamentos)) {
        echo "<p style='color: orange;'>⚠️ Nenhum agendamento encontrado no sistema</p>";
    } else {
        echo "<p style='color: green;'>✅ Últimos " . count($agendamentos) . " agendamento(s) no sistema:</p>";
        
        foreach ($agendamentos as $agendamento) {
            $statusColor = $agendamento['status'] == 'confirmado' ? 'green' : 
                          ($agendamento['status'] == 'pago' ? 'blue' : 'orange');
            
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px 0;'>";
            echo "<strong>ID:</strong> {$agendamento['id']}<br>";
            echo "<strong>Cliente:</strong> {$agendamento['cliente_nome']} (ID: {$agendamento['cliente_id']})<br>";
            echo "<strong>Salão:</strong> {$agendamento['salao_nome']} (ID: {$agendamento['salao_id']})<br>";
            echo "<strong>Serviço:</strong> {$agendamento['servico_nome']}<br>";
            echo "<strong>Profissional:</strong> {$agendamento['profissional_nome']}<br>";
            echo "<strong>Data:</strong> {$agendamento['data']}<br>";
            echo "<strong>Hora:</strong> {$agendamento['hora']}<br>";
            echo "<strong>Status:</strong> <span style='color: {$statusColor}; font-weight: bold;'>{$agendamento['status']}</span><br>";
            echo "<strong>Criado em:</strong> {$agendamento['criado_em']}<br>";
            echo "<strong>Valor:</strong> R$ " . number_format($agendamento['valor'], 2, ',', '.') . "<br>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>