<?php
require_once 'conexao.php';

echo "<h2>Teste de Faturamento Corrigido</h2>";

try {
    $conn = getConexao();
    
    // Simular um agendamento com valor específico para teste
    echo "<h3>1. Criando agendamento de teste com valor específico</h3>";
    
    // Primeiro, vamos verificar se existe um salão e serviço para teste
    $stmt = $conn->query("SELECT id FROM saloes LIMIT 1");
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $conn->query("SELECT id, nome, preco FROM servicos LIMIT 1");
    $servico = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $conn->query("SELECT id FROM usuarios WHERE tipo = 'cliente' LIMIT 1");
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($salao && $servico && $cliente) {
        echo "<p><strong>Salão ID:</strong> {$salao['id']}</p>";
        echo "<p><strong>Serviço:</strong> {$servico['nome']} - Preço original: R$ " . number_format($servico['preco'], 2, ',', '.') . "</p>";
        echo "<p><strong>Cliente ID:</strong> {$cliente['id']}</p>";
        
        // Criar agendamento de teste com valor específico diferente do preço do serviço
        $valor_teste = 75.50; // Valor diferente do preço original do serviço
        $data_hoje = date('Y-m-d');
        $hora_teste = '14:30:00';
        
        // Verificar se já existe um agendamento de teste hoje
        $stmt = $conn->prepare("
            SELECT id FROM agendamentos 
            WHERE salao_id = ? AND data = ? AND hora = ? AND status = 'realizado'
        ");
        $stmt->execute([$salao['id'], $data_hoje, $hora_teste]);
        $agendamento_existente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$agendamento_existente) {
            // Criar novo agendamento de teste
            $stmt = $conn->prepare("
                INSERT INTO agendamentos (
                    cliente_id, salao_id, servico_id, data, hora, status, 
                    valor_servico, taxa_servico, data_agendamento
                ) VALUES (?, ?, ?, ?, ?, 'realizado', ?, ?, NOW())
            ");
            $stmt->execute([
                $cliente['id'], 
                $salao['id'], 
                $servico['id'], 
                $data_hoje, 
                $hora_teste, 
                $valor_teste, 
                $valor_teste
            ]);
            
            echo "<p style='color: green;'>✓ Agendamento de teste criado com valor de R$ " . number_format($valor_teste, 2, ',', '.') . "</p>";
        } else {
            echo "<p style='color: blue;'>ℹ Agendamento de teste já existe</p>";
        }
        
        echo "<h3>2. Testando cálculo de faturamento</h3>";
        
        // Testar o cálculo antigo (apenas preço do serviço)
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(s.preco), 0) as total
            FROM agendamentos a
            JOIN servicos s ON s.id = a.servico_id
            WHERE a.salao_id = ?
            AND a.data = ?
            AND a.status = 'realizado'
        ");
        $stmt->execute([$salao['id'], $data_hoje]);
        $faturamento_antigo = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Testar o cálculo novo (valor real do serviço)
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
        $faturamento_novo = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Método de Cálculo</th><th>Valor</th><th>Descrição</th></tr>";
        echo "<tr>";
        echo "<td><strong>Cálculo Antigo</strong></td>";
        echo "<td>R$ " . number_format($faturamento_antigo, 2, ',', '.') . "</td>";
        echo "<td>Soma apenas o preço dos serviços da tabela 'servicos'</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td><strong>Cálculo Novo</strong></td>";
        echo "<td>R$ " . number_format($faturamento_novo, 2, ',', '.') . "</td>";
        echo "<td>Usa valor_servico quando disponível, senão usa preço do serviço</td>";
        echo "</tr>";
        echo "</table>";
        
        if ($faturamento_novo != $faturamento_antigo) {
            echo "<p style='color: green;'><strong>✓ Correção funcionando!</strong> O novo cálculo está considerando os valores reais dos serviços.</p>";
        } else {
            echo "<p style='color: orange;'><strong>⚠ Valores iguais.</strong> Pode ser que não há agendamentos com valor_servico diferente do preço.</p>";
        }
        
        echo "<h3>3. Detalhes dos agendamentos de hoje</h3>";
        $stmt = $conn->prepare("
            SELECT 
                a.id,
                a.data,
                a.hora,
                a.status,
                a.valor_servico,
                a.taxa_servico,
                s.nome as servico_nome,
                s.preco as servico_preco,
                u.nome as cliente_nome
            FROM agendamentos a
            JOIN servicos s ON s.id = a.servico_id
            JOIN usuarios u ON u.id = a.cliente_id
            WHERE a.salao_id = ? AND a.data = ?
            ORDER BY a.hora
        ");
        $stmt->execute([$salao['id'], $data_hoje]);
        $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($agendamentos)) {
            echo "<p>Nenhum agendamento encontrado para hoje.</p>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Cliente</th><th>Serviço</th><th>Hora</th><th>Status</th><th>Preço Serviço</th><th>Valor Serviço</th><th>Valor Usado</th></tr>";
            foreach ($agendamentos as $ag) {
                $valor_usado = ($ag['valor_servico'] && $ag['valor_servico'] > 0) ? $ag['valor_servico'] : $ag['servico_preco'];
                echo "<tr>";
                echo "<td>{$ag['id']}</td>";
                echo "<td>{$ag['cliente_nome']}</td>";
                echo "<td>{$ag['servico_nome']}</td>";
                echo "<td>{$ag['hora']}</td>";
                echo "<td>{$ag['status']}</td>";
                echo "<td>R$ " . number_format($ag['servico_preco'], 2, ',', '.') . "</td>";
                echo "<td>R$ " . number_format($ag['valor_servico'] ?? 0, 2, ',', '.') . "</td>";
                echo "<td style='background-color: #e8f5e8;'><strong>R$ " . number_format($valor_usado, 2, ',', '.') . "</strong></td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<p style='color: red;'>Erro: Não foi possível encontrar salão, serviço ou cliente para teste.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
}
?>