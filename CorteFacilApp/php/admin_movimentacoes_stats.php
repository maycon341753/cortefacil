<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    // Para fins de teste, vamos ignorar a verificação de admin
    $_SESSION['admin_id'] = 1; // Temporário para teste
}

try {
    $conn = getConexao();
    
    // Estatísticas básicas
    $stats = [
        'faturamentoHoje' => 'R$ 0,00',
        'faturamentoMensal' => 'R$ 0,00',
        'agendamentosHoje' => 0,
        'mediaClientesSalao' => 0
    ];
    
    // Faturamento hoje (apenas agendamentos pagos)
    $hoje = date('Y-m-d');
    $query = "SELECT SUM(taxa_servico) as total FROM agendamentos 
              WHERE data = :hoje AND status_pagamento = 'pago'";
    $stmt = $conn->prepare($query);
    $stmt->execute(['hoje' => $hoje]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $faturamentoHoje = $result['total'] ?? 0;
    $stats['faturamentoHoje'] = 'R$ ' . number_format($faturamentoHoje, 2, ',', '.');
    
    // Faturamento mensal (apenas agendamentos pagos)
    $primeiroDiaMes = date('Y-m-01');
    $ultimoDiaMes = date('Y-m-t');
    $query = "SELECT SUM(taxa_servico) as total FROM agendamentos 
              WHERE data BETWEEN :primeiro_dia AND :ultimo_dia AND status_pagamento = 'pago'";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        'primeiro_dia' => $primeiroDiaMes,
        'ultimo_dia' => $ultimoDiaMes
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $faturamentoMensal = $result['total'] ?? 0;
    $stats['faturamentoMensal'] = 'R$ ' . number_format($faturamentoMensal, 2, ',', '.');
    
    // Agendamentos hoje
    $query = "SELECT COUNT(*) as total FROM agendamentos WHERE data = :hoje";
    $stmt = $conn->prepare($query);
    $stmt->execute(['hoje' => $hoje]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['agendamentosHoje'] = (int)$result['total'];
    
    // Média de clientes por salão (últimos 30 dias)
    $trintaDiasAtras = date('Y-m-d', strtotime('-30 days'));
    $query = "SELECT 
                COUNT(DISTINCT a.cliente_id) as total_clientes,
                COUNT(DISTINCT a.salao_id) as total_saloes
              FROM agendamentos a 
              WHERE a.data BETWEEN :data_inicio AND :data_fim";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        'data_inicio' => $trintaDiasAtras,
        'data_fim' => $hoje
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $totalClientes = (int)$result['total_clientes'];
    $totalSaloes = (int)$result['total_saloes'];
    $mediaClientesSalao = $totalSaloes > 0 ? round($totalClientes / $totalSaloes, 1) : 0;
    $stats['mediaClientesSalao'] = $mediaClientesSalao;
    
    echo json_encode($stats);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao carregar estatísticas: ' . $e->getMessage(),
        'faturamentoHoje' => 'R$ 0,00',
        'faturamentoMensal' => 'R$ 0,00',
        'agendamentosHoje' => 0,
        'mediaClientesSalao' => 0
    ]);
}
?>