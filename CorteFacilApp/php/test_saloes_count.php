<?php
require 'conexao.php';

// Habilitar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definir cabeçalho JSON
header('Content-Type: application/json');

try {
    // Verificar se a tabela saloes existe
    $checkTable = $conn->query("SHOW TABLES LIKE 'saloes'")->fetchColumn();
    
    if ($checkTable > 0) {
        // Total de salões ativos
        $query = "SELECT COUNT(*) as total FROM saloes WHERE ativo = 1";
        $stmt = $conn->query($query);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = (int)$row['total'];
        
        // Listar todos os salões para debug
        $query = "SELECT id, nome_fantasia, ativo FROM saloes";
        $stmt = $conn->query($query);
        $saloes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'ok',
            'total_saloes_ativos' => $total,
            'lista_saloes' => $saloes
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