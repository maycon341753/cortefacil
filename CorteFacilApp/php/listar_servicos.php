<?php
require_once 'conexao.php';
session_start();

header('Content-Type: application/json');

if (!isset($_GET['salao_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID do salão não fornecido']);
    exit;
}

$salao_id = $_GET['salao_id'];

try {
    $pdo = getConexao();
    
    // Buscar apenas serviços que estão vinculados a profissionais ativos do salão
    $stmt = $pdo->prepare("
        SELECT DISTINCT s.id, s.nome, s.preco, s.duracao_minutos
        FROM servicos s
        INNER JOIN profissional_servicos ps ON s.id = ps.servico_id
        INNER JOIN profissionais p ON ps.profissional_id = p.id
        WHERE p.salao_id = ? AND s.ativo = 1 AND p.ativo = 1
        ORDER BY s.nome
    ");
    
    $stmt->execute([$salao_id]);
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar preços
    $servicosFormatados = array_map(function($servico) {
        $servico['preco_formatado'] = 'R$ ' . number_format($servico['preco'], 2, ',', '.');
        return $servico;
    }, $servicos);
    
    echo json_encode([
        'status' => 'success',
        'data' => $servicosFormatados
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao buscar serviços: ' . $e->getMessage()]);
} 
?>