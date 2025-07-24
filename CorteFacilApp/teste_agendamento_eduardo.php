<?php
// Teste específico para verificar o agendamento do Salão do Eduardo
require_once 'php/conexao.php';

echo "<h2>🔍 Teste: Agendamento do Thales no Salão do Eduardo</h2>";

try {
    $pdo = getConexao();
    
    // 1. Verificar se existe o agendamento no Salão do Eduardo
    echo "<h3>1. Verificando agendamento no Salão do Eduardo:</h3>";
    
    $sql = "SELECT 
                a.id,
                a.cliente_id,
                a.data,
                a.hora,
                a.status,
                a.criado_em,
                s.nome_fantasia as salao,
                p.nome as profissional,
                serv.nome as servico,
                serv.preco,
                u.nome as cliente_nome
            FROM agendamentos a
            JOIN saloes s ON a.salao_id = s.id
            JOIN profissionais p ON a.profissional_id = p.id
            JOIN servicos serv ON a.servico_id = serv.id
            JOIN usuarios u ON a.cliente_id = u.id
            WHERE s.nome_fantasia LIKE '%Eduardo%'
            AND u.nome LIKE '%Thales%'
            ORDER BY a.criado_em DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($agendamentos)) {
        echo "<p style='color: red;'>❌ Nenhum agendamento encontrado para Thales no Salão do Eduardo</p>";
    } else {
        echo "<p style='color: green;'>✅ Encontrados " . count($agendamentos) . " agendamento(s):</p>";
        foreach ($agendamentos as $agendamento) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px 0;'>";
            echo "<strong>ID:</strong> " . $agendamento['id'] . "<br>";
            echo "<strong>Cliente:</strong> " . $agendamento['cliente_nome'] . " (ID: " . $agendamento['cliente_id'] . ")<br>";
            echo "<strong>Salão:</strong> " . $agendamento['salao'] . "<br>";
            echo "<strong>Profissional:</strong> " . $agendamento['profissional'] . "<br>";
            echo "<strong>Serviço:</strong> " . $agendamento['servico'] . "<br>";
            echo "<strong>Data:</strong> " . $agendamento['data'] . "<br>";
            echo "<strong>Hora:</strong> " . $agendamento['hora'] . "<br>";
            echo "<strong>Status:</strong> <span style='color: " . ($agendamento['status'] == 'realizado' ? 'blue' : 'green') . "; font-weight: bold;'>" . $agendamento['status'] . "</span><br>";
            echo "<strong>Criado em:</strong> " . $agendamento['criado_em'] . "<br>";
            echo "<strong>Preço:</strong> R$ " . number_format($agendamento['preco'], 2, ',', '.') . "<br>";
            echo "</div>";
        }
    }
    
    // 2. Simular login do Thales e testar API
    echo "<h3>2. Simulando login do Thales e testando API:</h3>";
    
    // Buscar dados do Thales
    $sqlThales = "SELECT id, nome, email FROM usuarios WHERE nome LIKE '%Thales%' AND tipo = 'cliente' LIMIT 1";
    $stmtThales = $pdo->prepare($sqlThales);
    $stmtThales->execute();
    $thales = $stmtThales->fetch(PDO::FETCH_ASSOC);
    
    if (!$thales) {
        echo "<p style='color: red;'>❌ Usuário Thales não encontrado</p>";
    } else {
        echo "<p style='color: green;'>✅ Usuário Thales encontrado: " . $thales['nome'] . " (ID: " . $thales['id'] . ")</p>";
        
        // Simular sessão do Thales
        session_start();
        $_SESSION['id'] = $thales['id'];
        $_SESSION['tipo'] = 'cliente';
        $_SESSION['nome'] = $thales['nome'];
        
        // Chamar a API meus_agendamentos.php
        echo "<h4>Testando API meus_agendamentos.php:</h4>";
        
        ob_start();
        include 'php/meus_agendamentos.php';
        $response = ob_get_clean();
        
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>";
        echo htmlspecialchars($response);
        echo "</pre>";
        
        // Decodificar e analisar resposta
        $data = json_decode($response, true);
        if ($data && isset($data['status']) && $data['status'] === 'success') {
            echo "<h4>Agendamentos retornados pela API:</h4>";
            if (empty($data['data'])) {
                echo "<p style='color: orange;'>⚠️ API retornou sucesso mas nenhum agendamento</p>";
            } else {
                echo "<p style='color: green;'>✅ API retornou " . count($data['data']) . " agendamento(s):</p>";
                foreach ($data['data'] as $agendamento) {
                    echo "<div style='border: 1px solid #ddd; padding: 8px; margin: 3px 0; background: #f9f9f9;'>";
                    echo "<strong>ID:</strong> " . $agendamento['id'] . " | ";
                    echo "<strong>Salão:</strong> " . $agendamento['salao'] . " | ";
                    echo "<strong>Status:</strong> " . $agendamento['status'] . " | ";
                    echo "<strong>Data:</strong> " . $agendamento['data'] . " " . $agendamento['hora'];
                    echo "</div>";
                    
                    // Verificar se é o agendamento do Eduardo
                    if (strpos($agendamento['salao'], 'Eduardo') !== false) {
                        echo "<p style='color: blue; font-weight: bold;'>🎯 ENCONTRADO: Agendamento no Salão do Eduardo!</p>";
                    }
                }
            }
        } else {
            echo "<p style='color: red;'>❌ Erro na API ou resposta inválida</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>