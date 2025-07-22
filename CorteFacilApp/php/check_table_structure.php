<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require 'conexao.php';

try {
    // Verificar a estrutura da tabela saloes
    $stmt = $conn->query('SHOW CREATE TABLE saloes');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar os dados da tabela
    $stmt = $conn->query('SELECT * FROM saloes WHERE ativo = 1');
    $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'ok',
        'estrutura' => $result,
        'total_saloes_ativos' => count($saloes),
        'saloes' => $saloes
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => $e->getMessage()
    ]);
}
?>