<?php
require_once '../config/conexao.php';

try {
    $pdo = getConexao();
    
    // Buscar categorias distintas (usando nomes de serviços)
    $sql = "SELECT DISTINCT nome as categoria FROM servicos WHERE ativo = 1 ORDER BY nome";
    $stmt = $pdo->query($sql);
    $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    header('Content-Type: application/json');
    echo json_encode($categorias);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erro ao buscar categorias: ' . $e->getMessage()]);
}
?>