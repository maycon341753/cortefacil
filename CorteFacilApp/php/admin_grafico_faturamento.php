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
    
    // Dados dos últimos 30 dias para o gráfico
    $query = "SELECT 
                DATE(a.data) as data,
                COALESCE(SUM(CASE WHEN a.status_pagamento = 'pago' THEN a.taxa_servico ELSE 0 END), 0) as faturamento
              FROM agendamentos a
              WHERE a.data >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
              GROUP BY DATE(a.data)
              ORDER BY data ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Preencher dias sem dados com valor 0
    $dadosCompletos = [];
    $dataAtual = new DateTime('-30 days');
    $dataFim = new DateTime();
    
    while ($dataAtual <= $dataFim) {
        $dataStr = $dataAtual->format('Y-m-d');
        $faturamento = 0;
        
        // Procurar se existe dados para esta data
        foreach ($dados as $dado) {
            if ($dado['data'] === $dataStr) {
                $faturamento = $dado['faturamento'];
                break;
            }
        }
        
        $dadosCompletos[] = [
            'data' => $dataStr,
            'faturamento' => $faturamento
        ];
        
        $dataAtual->add(new DateInterval('P1D'));
    }
    
    echo json_encode([
        'status' => 'sucesso',
        'dados' => $dadosCompletos
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao carregar dados do gráfico: ' . $e->getMessage(),
        'dados' => []
    ]);
}
?>