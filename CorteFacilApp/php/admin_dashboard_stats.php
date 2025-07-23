<?php
// Habilitar exibição de erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar cabeçalhos CORS
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Se for uma requisição OPTIONS, retornar apenas os headers
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

session_start();
include 'conexao.php';

// Definir cabeçalho JSON
header('Content-Type: application/json');

// Verifica se é um admin (para fins de teste, vamos permitir acesso)
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['admin_id'] = 1; // Temporário para teste
}

try {
    error_log("Iniciando carregamento das estatísticas do dashboard");
    
    $stats = [
        'totalSaloes' => 0,
        'agendamentosHoje' => 0,
        'valorAgendamentosHoje' => 'R$ 0,00',
        'promocoesAtivas' => 0,
        'faturamentoMensal' => 'R$ 0,00'
    ];
    
    // Verificar se a tabela saloes existe
    $checkTable = $conn->query("SHOW TABLES LIKE 'saloes'")->fetchColumn();
    error_log("Tabela saloes existe: " . ($checkTable > 0 ? 'Sim' : 'Não'));
    
    if ($checkTable > 0) {
        // Total de salões ativos
        $query = "SELECT COUNT(*) as total FROM saloes WHERE ativo = 1";
        error_log("Executando query: " . $query);
        $stmt = $conn->query($query);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['totalSaloes'] = (int)$row['total'];
        
        // Log para debug
        error_log("Total de salões ativos encontrados: " . $stats['totalSaloes']);
        
        // Buscar todos os salões ativos para debug
        $query = "SELECT id, nome_fantasia, ativo FROM saloes WHERE ativo = 1";
        $stmt = $conn->query($query);
        $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Salões ativos encontrados: " . print_r($saloes, true));
    } else {
        error_log("Tabela saloes não existe!");
    }

    // Agendamentos pagos hoje (de todos os salões)
    $checkTable = $conn->query("SHOW TABLES LIKE 'agendamentos'")->fetchColumn();
    if ($checkTable > 0) {
        $hoje = date('Y-m-d');
        
        // Contar agendamentos pagos hoje de todos os salões
        $query = "SELECT COUNT(*) as total FROM agendamentos WHERE data = :hoje AND status_pagamento = 'pago'";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':hoje', $hoje, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['agendamentosHoje'] = (int)$row['total'];
        
        // Valor das taxas de serviço pagas hoje (0,99 por agendamento)
        $query = "SELECT SUM(taxa_servico) as total FROM agendamentos 
                 WHERE data = :hoje AND status_pagamento = 'pago'";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':hoje', $hoje, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $valorHoje = $row['total'] ?? 0;
        $stats['valorAgendamentosHoje'] = 'R$ ' . number_format($valorHoje, 2, ',', '.');
        
        error_log("Agendamentos pagos hoje: " . $stats['agendamentosHoje'] . " - Taxa de serviço: " . $stats['valorAgendamentosHoje']);
    } else {
        error_log("Tabela agendamentos não existe");
    }

    // Cupons disponíveis (Promoções Ativas)
    $checkTable = $conn->query("SHOW TABLES LIKE 'cupons'")->fetchColumn();
    if ($checkTable > 0) {
        try {
            // Primeiro atualiza cupons expirados
            $updateQuery = "UPDATE cupons SET status = 'expirado' WHERE status = 'disponivel' AND data_expiracao < CURDATE()";
            $conn->exec($updateQuery);
            
            // Conta cupons disponíveis (não expirados e não utilizados)
            $query = "SELECT COUNT(*) as total FROM cupons WHERE status = 'disponivel' AND data_expiracao >= CURDATE()";
            $stmt = $conn->query($query);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['promocoesAtivas'] = (int)$row['total'];
            
            error_log("Cupons disponíveis encontrados: " . $stats['promocoesAtivas']);
        } catch (Exception $e) {
            // A tabela cupons pode existir mas ter estrutura diferente
            error_log("Erro ao consultar cupons: " . $e->getMessage());
            $stats['promocoesAtivas'] = 0;
        }
    } else {
        error_log("Tabela cupons não existe");
    }

    // Faturamento mensal em taxas de serviço (últimos 30 dias - R$ 0,99 por agendamento pago)
    $checkTable = $conn->query("SHOW TABLES LIKE 'agendamentos'")->fetchColumn();
    if ($checkTable > 0) {
        // Calcular data de 30 dias atrás
        $dataInicio = date('Y-m-d', strtotime('-30 days'));
        $dataFim = date('Y-m-d');
        
        $query = "SELECT SUM(taxa_servico) as total FROM agendamentos 
                 WHERE data BETWEEN :data_inicio AND :data_fim AND status_pagamento = 'pago'";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':data_inicio', $dataInicio, PDO::PARAM_STR);
        $stmt->bindParam(':data_fim', $dataFim, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $faturamento = $row['total'] ?? 0;
        $stats['faturamentoMensal'] = 'R$ ' . number_format($faturamento, 2, ',', '.');
        
        error_log("Faturamento em taxas de serviço dos últimos 30 dias (de $dataInicio a $dataFim): " . $stats['faturamentoMensal']);
    }

    error_log("Estatísticas finais: " . json_encode($stats));
    echo json_encode($stats);

} catch (Exception $e) {
    error_log("Erro no dashboard: " . $e->getMessage());
    echo json_encode([
        'status' => 'erro', 
        'mensagem' => 'Erro ao carregar estatísticas do dashboard: ' . $e->getMessage(),
        'totalSaloes' => 0,
        'agendamentosHoje' => 0,
        'valorAgendamentosHoje' => 'R$ 0,00',
        'promocoesAtivas' => 0,
        'faturamentoMensal' => 'R$ 0,00'
    ]);
}
?>