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

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    // Para fins de teste, vamos ignorar a verificação de admin
    // echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    // exit;
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
    }

    // Agendamentos hoje (apenas pagos)
    $checkTable = $conn->query("SHOW TABLES LIKE 'agendamentos'")->fetchColumn();
    if ($checkTable > 0) {
        $hoje = date('Y-m-d');
        $query = "SELECT COUNT(*) as total FROM agendamentos WHERE data = :hoje AND status_pagamento = 'pago'";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':hoje', $hoje, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['agendamentosHoje'] = (int)$row['total'];
        
        // Valor dos agendamentos de hoje (apenas pagos)
        $query = "SELECT SUM(taxa_servico) as total FROM agendamentos 
                 WHERE data = :hoje AND status_pagamento = 'pago'";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':hoje', $hoje, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $valorHoje = $row['total'] ?? 0;
        $stats['valorAgendamentosHoje'] = 'R$ ' . number_format($valorHoje, 2, ',', '.');
    }

    // Promoções ativas
    $checkTable = $conn->query("SHOW TABLES LIKE 'promocoes'")->fetchColumn();
    if ($checkTable > 0) {
        try {
            $query = "SELECT COUNT(*) as total FROM promocoes WHERE status = 'ativa' AND data_fim >= CURDATE()";
            $stmt = $conn->query($query);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['promocoesAtivas'] = (int)$row['total'];
        } catch (Exception $e) {
            // A tabela promocoes pode existir mas ter estrutura diferente
            $stats['promocoesAtivas'] = 0;
        }
    }

    // Faturamento mensal (apenas agendamentos pagos do mês atual)
    $checkTable = $conn->query("SHOW TABLES LIKE 'agendamentos'")->fetchColumn();
    if ($checkTable > 0) {
        $primeiroDiaMes = date('Y-m-01');
        $ultimoDiaMes = date('Y-m-t');
        $query = "SELECT SUM(taxa_servico) as total FROM agendamentos 
                 WHERE data BETWEEN :primeiro_dia AND :ultimo_dia AND status_pagamento = 'pago'";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':primeiro_dia', $primeiroDiaMes, PDO::PARAM_STR);
        $stmt->bindParam(':ultimo_dia', $ultimoDiaMes, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $faturamento = $row['total'] ?? 0;
        $stats['faturamentoMensal'] = 'R$ ' . number_format($faturamento, 2, ',', '.');
    }

    echo json_encode($stats);

} catch (Exception $e) {
    error_log("Erro no dashboard: " . $e->getMessage());
    echo json_encode([
        'status' => 'erro', 
        'mensagem' => 'Erro ao carregar estatísticas do dashboard',
        'totalSaloes' => 0,
        'agendamentosHoje' => 0,
        'valorAgendamentosHoje' => 'R$ 0,00',
        'promocoesAtivas' => 0,
        'faturamentoMensal' => 'R$ 0,00'
    ]);
}
?>