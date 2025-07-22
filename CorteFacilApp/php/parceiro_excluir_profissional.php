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

// Recebe os dados do JSON
$json = file_get_contents('php://input');
$dados = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Dados inválidos']);
    exit;
}

$id = isset($dados['id']) ? (int)$dados['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID inválido']);
    exit;
}

try {
    // Ao invés de excluir, apenas desativa o profissional
    $stmt = $conn->prepare("
        UPDATE profissionais 
        SET ativo = 0 
        WHERE id = :id 
        AND salao_id = :salao_id
    ");
    
    $stmt->execute([
        'id' => $id,
        'salao_id' => $_SESSION['salao_id']
    ]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'success',
            'mensagem' => 'Profissional excluído com sucesso'
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Profissional não encontrado'
        ]);
    }

} catch (PDOException $e) {
    error_log("Erro ao excluir profissional: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao excluir profissional'
    ]);
}
?>