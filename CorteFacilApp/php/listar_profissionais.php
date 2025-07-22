<?php
require_once 'conexao.php';
session_start();

header('Content-Type: application/json');

// Verifica se o salão_id foi fornecido
$salao_id = isset($_GET['salao_id']) ? (int)$_GET['salao_id'] : 0;

if ($salao_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID do salão inválido']);
    exit;
}

try {
    $pdo = getConexao();
    $stmt = $pdo->prepare("
        SELECT id, nome, especialidade, telefone 
        FROM profissionais 
        WHERE salao_id = :salao_id 
        AND ativo = 1 
        ORDER BY nome
    ");
    
    $stmt->execute(['salao_id' => $salao_id]);
    $profissionais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'profissionais' => $profissionais
    ]);

} catch (PDOException $e) {
    error_log("Erro ao listar profissionais: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao listar profissionais'
    ]);
}
?>
