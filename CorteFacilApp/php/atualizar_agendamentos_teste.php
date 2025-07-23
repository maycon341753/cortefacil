<?php
require_once 'conexao.php';

try {
    $conn = getConexao();
    
    echo "<h2>Atualizando estrutura da tabela agendamentos...</h2>";
    
    // Verificar se a coluna status_pagamento já existe
    $result = $conn->query("SHOW COLUMNS FROM agendamentos LIKE 'status_pagamento'");
    if ($result->rowCount() == 0) {
        // Adicionar coluna status_pagamento
        $sql = "ALTER TABLE agendamentos ADD COLUMN status_pagamento ENUM('pendente', 'pago', 'cancelado') DEFAULT 'pendente'";
        $conn->exec($sql);
        echo "<p>✅ Coluna status_pagamento adicionada com sucesso!</p>";
    } else {
        echo "<p>ℹ️ Coluna status_pagamento já existe.</p>";
    }
    
    // Verificar se a coluna valor_servico já existe
    $result = $conn->query("SHOW COLUMNS FROM agendamentos LIKE 'valor_servico'");
    if ($result->rowCount() == 0) {
        // Adicionar coluna valor_servico
        $sql = "ALTER TABLE agendamentos ADD COLUMN valor_servico DECIMAL(10,2) DEFAULT 0.99";
        $conn->exec($sql);
        echo "<p>✅ Coluna valor_servico adicionada com sucesso!</p>";
    } else {
        echo "<p>ℹ️ Coluna valor_servico já existe.</p>";
    }
    
    echo "<h2>Inserindo dados de teste...</h2>";
    
    // Obter IDs necessários
    $cliente = $conn->query("SELECT id FROM usuarios WHERE tipo = 'cliente' LIMIT 1")->fetch();
    $salao = $conn->query("SELECT id FROM saloes LIMIT 1")->fetch();
    $profissional = $conn->query("SELECT id FROM profissionais LIMIT 1")->fetch();
    $servico = $conn->query("SELECT id FROM servicos LIMIT 1")->fetch();
    
    if ($cliente && $salao && $profissional && $servico) {
        // Inserir agendamentos de teste para hoje
        $hoje = date('Y-m-d');
        $ontem = date('Y-m-d', strtotime('-1 day'));
        $semanaPassada = date('Y-m-d', strtotime('-7 days'));
        
        // Agendamentos de hoje (pagos) - todos com taxa de serviço de R$ 0,99
        $agendamentosHoje = [
            [$cliente['id'], $salao['id'], $profissional['id'], $servico['id'], $hoje, '09:00', 'realizado', 'pago', 0.99],
            [$cliente['id'], $salao['id'], $profissional['id'], $servico['id'], $hoje, '10:00', 'realizado', 'pago', 0.99],
            [$cliente['id'], $salao['id'], $profissional['id'], $servico['id'], $hoje, '11:00', 'confirmado', 'pago', 0.99],
        ];
        
        // Agendamentos dos últimos 30 dias (para faturamento mensal) - todos com taxa de serviço de R$ 0,99
        $agendamentosMes = [
            [$cliente['id'], $salao['id'], $profissional['id'], $servico['id'], $ontem, '14:00', 'realizado', 'pago', 0.99],
            [$cliente['id'], $salao['id'], $profissional['id'], $servico['id'], $ontem, '15:00', 'realizado', 'pago', 0.99],
            [$cliente['id'], $salao['id'], $profissional['id'], $servico['id'], $semanaPassada, '16:00', 'realizado', 'pago', 0.99],
            [$cliente['id'], $salao['id'], $profissional['id'], $servico['id'], $semanaPassada, '17:00', 'realizado', 'pago', 0.99],
        ];
        
        $stmt = $conn->prepare("INSERT INTO agendamentos (cliente_id, salao_id, profissional_id, servico_id, data, hora, status, status_pagamento, taxa_servico) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE status_pagamento = VALUES(status_pagamento), taxa_servico = VALUES(taxa_servico)");
        
        $totalInseridos = 0;
        
        // Inserir agendamentos de hoje
        foreach ($agendamentosHoje as $agendamento) {
            $stmt->execute($agendamento);
            $totalInseridos++;
        }
        
        // Inserir agendamentos do mês
        foreach ($agendamentosMes as $agendamento) {
            $stmt->execute($agendamento);
            $totalInseridos++;
        }
        
        echo "<p>✅ $totalInseridos agendamentos de teste inseridos com sucesso!</p>";
        
        // Inserir mais salões para teste
        $saloesExtras = [
            ['Barbearia Central', '98765432109876', 'Rio de Janeiro', 'Av. Central, 456', '21999999999'],
            ['Salão Elegante', '11122233344455', 'Belo Horizonte', 'Rua Elegante, 789', '31888888888'],
        ];
        
        foreach ($saloesExtras as $salaoData) {
            // Verificar se já existe
            $existe = $conn->prepare("SELECT id FROM saloes WHERE documento = ?");
            $existe->execute([$salaoData[1]]);
            
            if ($existe->rowCount() == 0) {
                // Criar usuário para o salão
                $stmtUser = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'salao')");
                $email = strtolower(str_replace(' ', '', $salaoData[0])) . '@teste.com';
                $stmtUser->execute([$salaoData[0], $email, password_hash('123456', PASSWORD_DEFAULT)]);
                $userId = $conn->lastInsertId();
                
                // Criar salão
                $stmtSalao = $conn->prepare("INSERT INTO saloes (nome_fantasia, documento, cidade, endereco, whatsapp, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmtSalao->execute(array_merge($salaoData, [$userId]));
                
                echo "<p>✅ Salão '{$salaoData[0]}' criado com sucesso!</p>";
            }
        }
        
        // Atualizar contagem de salões
        $totalSaloes = $conn->query("SELECT COUNT(*) FROM saloes WHERE ativo = 1")->fetchColumn();
        echo "<p>📊 Total de salões ativos: $totalSaloes</p>";
        
        // Inserir algumas promoções ativas
        $promocoes = [
            [$salao['id'], 'Desconto de Verão', 'Corte + Barba com 20% de desconto', 20.00, date('Y-m-d'), date('Y-m-d', strtotime('+30 days'))],
            [$salao['id'], 'Promoção Especial', 'Desconto especial para novos clientes', 15.00, date('Y-m-d'), date('Y-m-d', strtotime('+15 days'))],
        ];
        
        $stmtPromo = $conn->prepare("INSERT INTO promocoes (salao_id, titulo, descricao, desconto, data_inicio, data_fim) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE titulo = VALUES(titulo)");
        
        foreach ($promocoes as $promo) {
            $stmtPromo->execute($promo);
        }
        
        $totalPromocoes = $conn->query("SELECT COUNT(*) FROM promocoes WHERE status = 'ativa' AND data_fim >= CURDATE()")->fetchColumn();
        echo "<p>🎯 Total de promoções ativas: $totalPromocoes</p>";
        
    } else {
        echo "<p>❌ Erro: Não foi possível encontrar dados básicos necessários (cliente, salão, profissional, serviço)</p>";
    }
    
    echo "<h2>Resumo das Estatísticas:</h2>";
    
    // Mostrar estatísticas atuais
    $stats = [];
    
    // Total de salões
    $stats['totalSaloes'] = $conn->query("SELECT COUNT(*) FROM saloes WHERE ativo = 1")->fetchColumn();
    
    // Agendamentos pagos hoje
    $hoje = date('Y-m-d');
    $stmt = $conn->prepare("SELECT COUNT(*) FROM agendamentos WHERE data = ? AND status_pagamento = 'pago'");
    $stmt->execute([$hoje]);
    $stats['agendamentosHoje'] = $stmt->fetchColumn();
    
    // Valor das taxas de serviço hoje
    $stmt = $conn->prepare("SELECT SUM(taxa_servico) FROM agendamentos WHERE data = ? AND status_pagamento = 'pago'");
    $stmt->execute([$hoje]);
    $stats['valorHoje'] = $stmt->fetchColumn() ?: 0;
    
    // Promoções ativas
    $stats['promocoesAtivas'] = $conn->query("SELECT COUNT(*) FROM promocoes WHERE status = 'ativa' AND data_fim >= CURDATE()")->fetchColumn();
    
    // Faturamento em taxas de serviço dos últimos 30 dias
    $dataInicio = date('Y-m-d', strtotime('-30 days'));
    $dataFim = date('Y-m-d');
    $stmt = $conn->prepare("SELECT SUM(taxa_servico) FROM agendamentos WHERE data BETWEEN ? AND ? AND status_pagamento = 'pago'");
    $stmt->execute([$dataInicio, $dataFim]);
    $stats['faturamento30dias'] = $stmt->fetchColumn() ?: 0;
    
    echo "<ul>";
    echo "<li><strong>Total de Salões:</strong> {$stats['totalSaloes']}</li>";
    echo "<li><strong>Agendamentos Pagos Hoje:</strong> {$stats['agendamentosHoje']}</li>";
    echo "<li><strong>Valor em Taxas de Serviço Hoje:</strong> R$ " . number_format($stats['valorHoje'], 2, ',', '.') . "</li>";
    echo "<li><strong>Promoções Ativas:</strong> {$stats['promocoesAtivas']}</li>";
    echo "<li><strong>Faturamento em Taxas de Serviço (Últimos 30 Dias):</strong> R$ " . number_format($stats['faturamento30dias'], 2, ',', '.') . "</li>";
    echo "</ul>";
    
    echo "<p>✅ <strong>Atualização concluída com sucesso!</strong></p>";
    echo "<p>🔗 <a href='../admin/painel.php'>Acessar Painel Administrativo</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}
?>