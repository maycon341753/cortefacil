<?php
require_once 'conexao.php';
session_start();

header('Content-Type: application/json');

// Verifica se é um parceiro
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'salao') {
    http_response_code(401);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

// Recebe o ID do profissional
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID inválido']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT id, nome, especialidade, telefone
        FROM profissionais 
        WHERE id = :id 
        AND salao_id = :salao_id 
        AND ativo = 1
    ");
    
    $stmt->execute([
        'id' => $id,
        'salao_id' => $_SESSION['salao_id']
    ]);
    
    $profissional = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($profissional) {
        echo json_encode([
            'status' => 'success',
            'profissional' => $profissional
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Profissional não encontrado'
        ]);
    }

} catch (PDOException $e) {
    error_log("Erro ao obter profissional: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao obter profissional'
    ]);
}
?> 