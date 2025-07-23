<?php
require_once 'conexao.php';

echo "<h2>Teste de Agendamento com Valor Real do Serviço</h2>";

try {
    $conn = getConexao();
    
    // 1. Verificar se existe um serviço com preço diferente de R$ 0,99
    echo "<h3>1. Verificando serviços disponíveis</h3>";
    $stmt = $conn->query("SELECT id, nome, preco FROM servicos ORDER BY preco DESC LIMIT 5");
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Preço</th></tr>";
    foreach ($servicos as $servico) {
        echo "<tr>";
        echo "<td>{$servico['id']}</td>";
        echo "<td>{$servico['nome']}</td>";
        echo "<td>R$ " . number_format($servico['preco'], 2, ',', '.') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Pegar dados necessários para criar um agendamento de teste
    $stmt = $conn->query("SELECT id FROM saloes LIMIT 1");
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $conn->query("SELECT id FROM usuarios WHERE tipo = 'cliente' LIMIT 1");
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $conn->query("SELECT id FROM profissionais LIMIT 1");
    $profissional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($salao && $cliente && $profissional && !empty($servicos)) {
        $servico_teste = $servicos[0]; // Pegar o serviço com maior preço
        $data_hoje = date('Y-m-d');
        $hora_teste = '15:00:00';
        
        echo "<h3>2. Criando agendamento de teste</h3>";
        echo "<p><strong>Serviço escolhido:</strong> {$servico_teste['nome']} - R$ " . number_format($servico_teste['preco'], 2, ',', '.') . "</p>";
        
        // Verificar se já existe um agendamento de teste
        $stmt = $conn->prepare("
            SELECT id, valor_servico, taxa_servico, status 
            FROM agendamentos 
            WHERE salao_id = ? AND data = ? AND hora = ? AND servico_id = ?
        ");
        $stmt->execute([$salao['id'], $data_hoje, $hora_teste, $servico_teste['id']]);
        $agendamento_existente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$agendamento_existente) {
            // Criar novo agendamento de teste
            $stmt = $conn->prepare("
                INSERT INTO agendamentos (
                    cliente_id, salao_id, profissional_id, servico_id, 
                    data, hora, status, status_pagamento,
                    valor_servico, taxa_servico, criado_em
                ) VALUES (?, ?, ?, ?, ?, ?, 'realizado', 'pago', ?, ?, NOW())
            ");
            $stmt->execute([
                $cliente['id'], 
                $salao['id'], 
                $profissional['id'],
                $servico_teste['id'], 
                $data_hoje, 
                $hora_teste, 
                $servico_teste['preco'], 
                $servico_teste['preco']
            ]);
            
            $agendamento_id = $conn->lastInsertId();
            echo "<p style='color: green;'>✓ Agendamento criado com ID: {$agendamento_id}</p>";
        } else {
            echo "<p style='color: blue;'>ℹ Agendamento já existe com ID: {$agendamento_existente['id']}</p>";
            echo "<p>Status: {$agendamento_existente['status']}</p>";
            echo "<p>Valor do serviço: R$ " . number_format($agendamento_existente['valor_servico'] ?? 0, 2, ',', '.') . "</p>";
            echo "<p>Taxa do serviço: R$ " . number_format($agendamento_existente['taxa_servico'] ?? 0, 2, ',', '.') . "</p>";
        }
        
        echo "<h3>3. Testando cálculo do faturamento</h3>";
        
        // Testar o cálculo do faturamento hoje
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(
                CASE 
                    WHEN a.valor_servico IS NOT NULL AND a.valor_servico > 0 THEN a.valor_servico
                    ELSE s.preco
                END
            ), 0) as total
            FROM agendamentos a
            JOIN servicos s ON s.id = a.servico_id
            WHERE a.salao_id = ?
            AND a.data = ?
            AND a.status = 'realizado'
        ");
        $stmt->execute([$salao['id'], $data_hoje]);
        $faturamento_hoje = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo "<p><strong>Faturamento hoje:</strong> R$ " . number_format($faturamento_hoje, 2, ',', '.') . "</p>";
        
        // Mostrar detalhes dos agendamentos de hoje
        echo "<h3>4. Agendamentos realizados hoje</h3>";
        $stmt = $conn->prepare("
            SELECT 
                a.id,
                a.status,
                a.valor_servico,
                a.taxa_servico,
                s.nome as servico_nome,
                s.preco as servico_preco,
                u.nome as cliente_nome,
                CASE 
                    WHEN a.valor_servico IS NOT NULL AND a.valor_servico > 0 THEN a.valor_servico
                    ELSE s.preco
                END as valor_usado_calculo
            FROM agendamentos a
            JOIN servicos s ON s.id = a.servico_id
            JOIN usuarios u ON u.id = a.cliente_id
            WHERE a.salao_id = ? AND a.data = ? AND a.status = 'realizado'
            ORDER BY a.hora
        ");
        $stmt->execute([$salao['id'], $data_hoje]);
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($agendamentos)) {
            echo "<p>Nenhum agendamento realizado encontrado para hoje.</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Cliente</th><th>Serviço</th><th>Preço Serviço</th><th>Valor Serviço</th><th>Valor Usado no Cálculo</th></tr>";
            foreach ($agendamentos as $ag) {
                echo "<tr>";
                echo "<td>{$ag['id']}</td>";
                echo "<td>{$ag['cliente_nome']}</td>";
                echo "<td>{$ag['servico_nome']}</td>";
                echo "<td>R$ " . number_format($ag['servico_preco'], 2, ',', '.') . "</td>";
                echo "<td>R$ " . number_format($ag['valor_servico'] ?? 0, 2, ',', '.') . "</td>";
                echo "<td style='background-color: #e8f5e8;'><strong>R$ " . number_format($ag['valor_usado_calculo'], 2, ',', '.') . "</strong></td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        echo "<h3>5. Verificação final</h3>";
        if ($faturamento_hoje > 0.99) {
            echo "<p style='color: green; font-weight: bold;'>✅ SUCESSO! O faturamento está refletindo o valor real dos serviços!</p>";
        } else {
            echo "<p style='color: orange; font-weight: bold;'>⚠️ O faturamento ainda está baixo. Pode ser que não há agendamentos com valores maiores.</p>";
        }
        
    } else {
        echo "<p style='color: red;'>Erro: Dados necessários não encontrados (salão, cliente, profissional ou serviços).</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>