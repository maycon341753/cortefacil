<?php
session_start();
include 'conexao.php';

// Definir admin_id para teste
$_SESSION['admin_id'] = 1;

try {
    $stats = [
        'totalSaloes' => 0,
        'agendamentosHoje' => 0,
        'promocoesAtivas' => 0,
        'faturamentoMensal' => 'R$ 0,00'
    ];

    // Total de salões
    $query = "SELECT COUNT(*) as total FROM saloes";
    $stmt = $conn->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['totalSaloes'] = $row['total'];
    
    // Adicionar informações de debug
    $stats['debug'] = [
        'query' => $query,
        'row' => $row,
        'pdo_error' => $conn->errorInfo()[2]Info()
    ];

    header('Content-Type: application/json');
    echo json_encode($stats, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'erro', 
        'mensagem' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>