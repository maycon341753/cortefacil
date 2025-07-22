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

// Recebe o ID do serviço
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID inválido']);
    exit;
}

try {
    // Ao invés de excluir, apenas desativa o serviço
    $stmt = $conn->prepare("
        UPDATE servicos 
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
            'mensagem' => 'Serviço excluído com sucesso'
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Serviço não encontrado'
        ]);
    }

} catch (PDOException $e) {
    error_log("Erro ao excluir serviço: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao excluir serviço'
    ]);
}
?> 