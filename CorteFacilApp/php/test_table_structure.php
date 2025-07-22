<?php
require 'conexao.php';

// Habilitar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definir cabeçalho JSON
header('Content-Type: application/json');

try {
    // Verificar a estrutura da tabela saloes
    $stmt = $conn->query('DESCRIBE saloes');
    $estrutura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Verificar os índices da tabela
    $stmt = $conn->query('SHOW INDEX FROM saloes');
    $indices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'ok',
        'estrutura' => $estrutura,
        'indices' => $indices
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => $e->getMessage()
    ]);
}
?>