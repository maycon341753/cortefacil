<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require 'conexao.php';

try {
    // Verificar se a tabela saloes existe
    $checkTable = $conn->query("SHOW TABLES LIKE 'saloes'")->fetchColumn();
    
    if ($checkTable > 0) {
        // Total de salões ativos
        $query = "SELECT COUNT(*) as total FROM saloes WHERE ativo = 1";
        $stmt = $conn->query($query);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = (int)$row['total'];
        
        // Buscar todos os salões ativos para debug
        $query = "SELECT id, nome_fantasia, ativo FROM saloes WHERE ativo = 1";
        $stmt = $conn->query($query);
        $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'ok',
            'total_saloes' => $total,
            'saloes' => $saloes
        ]);
    } else {
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Tabela saloes não existe'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => $e->getMessage()
    ]);
}
?>