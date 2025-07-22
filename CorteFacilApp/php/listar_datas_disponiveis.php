<?php
header('Content-Type: application/json');
require_once 'conexao.php';

try {
    if (!isset($_GET['salao_id']) || !isset($_GET['profissional_id'])) {
        throw new Exception('Parâmetros inválidos');
    }

    $salao_id = filter_input(INPUT_GET, 'salao_id', FILTER_VALIDATE_INT);
    $profissional_id = filter_input(INPUT_GET, 'profissional_id', FILTER_VALIDATE_INT);

    if (!$salao_id || !$profissional_id) {
        throw new Exception('ID do salão ou profissional inválido');
    }

    $pdo = getConexao();

    // Primeiro, verifica se o profissional trabalha no salão
    $stmt = $pdo->prepare("
        SELECT 1 
        FROM profissionais 
        WHERE id = ? AND salao_id = ? AND ativo = 1
    ");
    $stmt->execute([$profissional_id, $salao_id]);

    if (!$stmt->fetch()) {
        throw new Exception('Profissional não encontrado neste salão');
    }

    // Busca os próximos 30 dias disponíveis
    $dataInicio = date('Y-m-d');
    $dataFim = date('Y-m-d', strtotime('+30 days'));

    // Busca os agendamentos existentes para o profissional
    $stmt = $pdo->prepare("
        SELECT data, COUNT(*) as total_agendamentos
        FROM agendamentos 
        WHERE profissional_id = ? 
        AND data BETWEEN ? AND ? 
        AND status NOT IN ('CANCELADO')
        GROUP BY data
        HAVING total_agendamentos >= 8 /* Máximo de 8 agendamentos por dia */
    ");
    $stmt->execute([$profissional_id, $dataInicio, $dataFim]);
    
    $datasOcupadas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Gera array com as datas disponíveis
    $datasDisponiveis = [];
    $data = new DateTime($dataInicio);
    $dataLimite = new DateTime($dataFim);

    while ($data <= $dataLimite) {
        $dataStr = $data->format('Y-m-d');
        
        // Verifica se a data não está na lista de datas ocupadas
        if (!in_array($dataStr, $datasOcupadas)) {
            $datasDisponiveis[] = $dataStr;
        }
        
        $data->modify('+1 day');
    }

    echo json_encode([
        'status' => 'success',
        'data' => $datasDisponiveis
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao consultar o banco de dados'
    ]);
} 