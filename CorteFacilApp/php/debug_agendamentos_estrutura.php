<?php
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getConexao();
    
    // Verificar estrutura da tabela agendamentos
    $stmt = $pdo->prepare("DESCRIBE agendamentos");
    $stmt->execute();
    $estrutura_agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Verificar se existem dados na tabela
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM agendamentos");
    $stmt->execute();
    $total_agendamentos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Mostrar alguns registros de exemplo (se existirem)
    $exemplos = [];
    if ($total_agendamentos > 0) {
        $stmt = $pdo->prepare("SELECT * FROM agendamentos LIMIT 3");
        $stmt->execute();
        $exemplos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'estrutura_tabela_agendamentos' => $estrutura_agendamentos,
        'total_agendamentos' => $total_agendamentos,
        'exemplos_registros' => $exemplos
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Erro ao verificar estrutura: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>