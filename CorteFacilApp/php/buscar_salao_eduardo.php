<?php
require_once 'conexao.php';
header('Content-Type: application/json');

try {
    $pdo = getConexao();
    
    $stmt = $pdo->prepare("SELECT id, nome_fantasia FROM saloes WHERE nome_fantasia LIKE '%Eduardo%' LIMIT 1");
    $stmt->execute();
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($salao) {
        echo json_encode([
            'status' => 'success',
            'salao_id' => $salao['id'],
            'salao_nome' => $salao['nome_fantasia']
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Salão do Eduardo não encontrado'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}
?>