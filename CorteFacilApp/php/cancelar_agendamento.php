<?php
require_once 'conexao.php';
session_start();

header('Content-Type: application/json');

// Verificar se o usuário está logado
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'cliente') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuário não autenticado']);
    exit;
}

// Obter ID do agendamento da URL ou do POST
$agendamento_id = null;
if (isset($_GET['id'])) {
    $agendamento_id = $_GET['id'];
} else {
    $dados = json_decode(file_get_contents('php://input'), true);
    if (isset($dados['id'])) {
        $agendamento_id = $dados['id'];
    }
}

if (!$agendamento_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID do agendamento não fornecido']);
    exit;
}

try {
    $pdo = getConexao();
    
    // Verifica se o agendamento existe e pertence ao cliente
    $stmt = $pdo->prepare("
        SELECT id, status, data, hora 
        FROM agendamentos 
        WHERE id = ? AND cliente_id = ?
    ");
    $stmt->execute([$agendamento_id, $_SESSION['id']]);
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$agendamento) {
        throw new Exception('Agendamento não encontrado');
    }
    
    // Verifica se o agendamento pode ser cancelado
    if ($agendamento['status'] === 'cancelado') {
        throw new Exception('Este agendamento já está cancelado');
    }
    
    if ($agendamento['status'] === 'realizado') {
        throw new Exception('Não é possível cancelar um agendamento já realizado');
    }
    
    // Verifica se está tentando cancelar com menos de 24h de antecedência
    $data_hora_agendamento = strtotime($agendamento['data'] . ' ' . $agendamento['hora']);
    $agora = time();
    $diferenca_horas = ($data_hora_agendamento - $agora) / 3600;
    
    if ($diferenca_horas < 24) {
        throw new Exception('Não é possível cancelar agendamentos com menos de 24 horas de antecedência');
    }
    
    // Cancela o agendamento
    $stmt = $pdo->prepare("
        UPDATE agendamentos 
        SET status = 'cancelado',
            data_cancelamento = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$agendamento_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Agendamento cancelado com sucesso'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 
?>