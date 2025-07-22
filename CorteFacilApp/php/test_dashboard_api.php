<?php
session_start();

// Habilitar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar cabeçalhos CORS
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Se for uma requisição OPTIONS, retornar apenas os headers
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'conexao.php';

try {
    // Definir admin_id para teste
    $_SESSION['admin_id'] = 1;

    // Verificar se a tabela saloes existe
    $checkTable = $conn->query("SHOW TABLES LIKE 'saloes'")->fetchColumn();
    error_log("Tabela saloes existe: " . ($checkTable > 0 ? 'Sim' : 'Não'));
    
    if ($checkTable > 0) {
        // Total de salões ativos
        $query = "SELECT COUNT(*) as total FROM saloes WHERE ativo = 1";
        error_log("Executando query: " . $query);
        $stmt = $conn->query($query);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = (int)$row['total'];
        
        // Buscar todos os salões ativos para debug
        $query = "SELECT id, nome_fantasia, ativo FROM saloes WHERE ativo = 1";
        $stmt = $conn->query($query);
        $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [
            'status' => 'ok',
            'totalSaloes' => $total,
            'agendamentosHoje' => 0,
            'valorAgendamentosHoje' => 'R$ 0,00',
            'promocoesAtivas' => 0,
            'faturamentoMensal' => 'R$ 0,00',
            'debug' => [
                'saloes_encontrados' => $saloes
            ]
        ];
        
        error_log("Resposta: " . json_encode($response));
        echo json_encode($response);
    } else {
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Tabela saloes não existe',
            'totalSaloes' => 0,
            'agendamentosHoje' => 0,
            'valorAgendamentosHoje' => 'R$ 0,00',
            'promocoesAtivas' => 0,
            'faturamentoMensal' => 'R$ 0,00'
        ]);
    }
} catch (Exception $e) {
    error_log("Erro na API: " . $e->getMessage());
    echo json_encode([
        'status' => 'erro',
        'mensagem' => $e->getMessage(),
        'totalSaloes' => 0,
        'agendamentosHoje' => 0,
        'valorAgendamentosHoje' => 'R$ 0,00',
        'promocoesAtivas' => 0,
        'faturamentoMensal' => 'R$ 0,00'
    ]);
}
?>