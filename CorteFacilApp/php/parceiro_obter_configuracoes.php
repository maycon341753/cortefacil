<?php
require_once 'conexao.php';
session_start();

header('Content-Type: application/json');

// Verifica se é um parceiro
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'salao' || !isset($_SESSION['salao_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            nome_fantasia,
            documento,
            horario_abertura,
            horario_fechamento,
            dias_funcionamento
        FROM saloes 
        WHERE id = :salao_id
    ");
    
    $stmt->execute(['salao_id' => $_SESSION['salao_id']]);
    $configuracoes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($configuracoes) {
        // Formata os horários para o formato HH:mm
        $configuracoes['horario_abertura'] = substr($configuracoes['horario_abertura'], 0, 5);
        $configuracoes['horario_fechamento'] = substr($configuracoes['horario_fechamento'], 0, 5);
        
        echo json_encode([
            'status' => 'sucesso',
            'dados' => $configuracoes
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Salão não encontrado'
        ]);
    }

} catch (PDOException $e) {
    error_log("Erro ao obter configurações: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao obter configurações',
        'debug' => $e->getMessage()
    ]);
}
?>