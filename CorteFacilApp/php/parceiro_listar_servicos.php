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

try {
    $stmt = $conn->prepare("
        SELECT id, nome, duracao_minutos, preco, descricao, ativo
        FROM servicos 
        WHERE salao_id = :salao_id 
        ORDER BY ativo DESC, nome ASC
    ");
    
    $stmt->execute(['salao_id' => $_SESSION['salao_id']]);
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'sucesso',
        'data' => $servicos
    ]);

} catch (PDOException $e) {
    error_log("Erro ao listar serviços: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao listar serviços'
    ]);
}
?>