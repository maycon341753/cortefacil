<?php
session_start();
require_once 'php/conexao.php';

echo "<h2>Debug - Sessão Atual e Agendamentos</h2>";

try {
    // 1. Verificar sessão atual
    echo "<h3>1. Informações da Sessão</h3>";
    if (isset($_SESSION['id'])) {
        echo "<p style='color: green;'>✅ Usuário logado - ID: {$_SESSION['id']}</p>";
        if (isset($_SESSION['nome'])) {
            echo "<p>Nome: {$_SESSION['nome']}</p>";
        }
        if (isset($_SESSION['email'])) {
            echo "<p>Email: {$_SESSION['email']}</p>";
        }
        if (isset($_SESSION['tipo'])) {
            echo "<p>Tipo: {$_SESSION['tipo']}</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Nenhum usuário logado</p>";
        echo "<p>Dados da sessão:</p>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
    }
    
    // 2. Se há usuário logado, buscar seus agendamentos
    if (isset($_SESSION['id'])) {
        $pdo = getConexao();
        $usuario_id = $_SESSION['id'];
        
        echo "<h3>2. Agendamentos do Usuário Atual (ID: {$usuario_id})</h3>";
        
        // Buscar TODOS os agendamentos (sem filtro de status)
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
        $stmt->execute([$usuario_id]);
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($agendamentos)) {
            echo "<p style='color: orange;'>⚠️ Nenhum agendamento encontrado para este usuário</p>";
        } else {
            echo "<p style='color: green;'>✅ Encontrados " . count($agendamentos) . " agendamento(s):</p>";
            
            foreach ($agendamentos as $agendamento) {
                $statusColor = $agendamento['status'] === 'confirmado' ? 'green' : 
                              ($agendamento['status'] === 'cancelado' ? 'red' : 'orange');
                
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
        
        // 3. Buscar agendamentos apenas confirmados (como faz o sistema)
        echo "<h3>3. Agendamentos Confirmados (como exibido no sistema)</h3>";
        
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
            WHERE a.cliente_id = ? AND a.status = 'confirmado'
            ORDER BY a.criado_em DESC
        ");
        $stmt->execute([$usuario_id]);
        $agendamentosConfirmados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($agendamentosConfirmados)) {
            echo "<p style='color: orange;'>⚠️ Nenhum agendamento confirmado encontrado</p>";
        } else {
            echo "<p style='color: green;'>✅ Encontrados " . count($agendamentosConfirmados) . " agendamento(s) confirmado(s):</p>";
            
            foreach ($agendamentosConfirmados as $agendamento) {
                echo "<div style='border: 1px solid green; padding: 10px; margin: 5px 0;'>";
                echo "<strong>ID:</strong> {$agendamento['id']}<br>";
                echo "<strong>Salão:</strong> {$agendamento['salao_nome']}<br>";
                echo "<strong>Serviço:</strong> {$agendamento['servico_nome']}<br>";
                echo "<strong>Data:</strong> " . date('d/m/Y', strtotime($agendamento['data'])) . "<br>";
                echo "<strong>Hora:</strong> " . date('H:i', strtotime($agendamento['hora'])) . "<br>";
                echo "</div>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>