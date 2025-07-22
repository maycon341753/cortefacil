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

// Recebe os dados do POST
$dados = $_POST;

// Validação básica
if (empty($dados['horario_abertura']) || empty($dados['horario_fechamento']) || empty($dados['dias_funcionamento'])) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Dados incompletos']);
    exit;
}

try {
    $stmt = $conn->prepare("
        UPDATE saloes 
        SET 
            horario_abertura = :horario_abertura,
            horario_fechamento = :horario_fechamento,
            dias_funcionamento = :dias_funcionamento
        WHERE id = :salao_id
    ");
    
    $stmt->execute([
        'horario_abertura' => $dados['horario_abertura'],
        'horario_fechamento' => $dados['horario_fechamento'],
        'dias_funcionamento' => $dados['dias_funcionamento'],
        'salao_id' => $_SESSION['salao_id']
    ]);
    
    echo json_encode([
        'status' => 'sucesso',
        'mensagem' => 'Configurações salvas com sucesso'
    ]);

} catch (PDOException $e) {
    error_log("Erro ao salvar configurações: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao salvar configurações',
        'debug' => $e->getMessage()
    ]);
}
?>