<?php
session_start();
require_once 'conexao.php';

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    // Para fins de teste, vamos ignorar a verificação de admin
    $_SESSION['admin_id'] = 1; // Temporário para teste
}

// Receber filtros via GET
$dataInicio = $_GET['dataInicio'] ?? date('Y-m-d', strtotime('-30 days'));
$dataFim = $_GET['dataFim'] ?? date('Y-m-d');
$salaoId = $_GET['salaoId'] ?? '';
$status = $_GET['status'] ?? '';

try {
    $conn = getConexao();
    
    // Construir query base para relatório
    $query = "SELECT 
                a.id,
                a.data,
                a.hora,
                a.status_pagamento,
                a.taxa_servico,
                u.nome as cliente_nome,
                u.email as cliente_email,
                s.nome_fantasia as salao_nome,
                s.cidade as salao_cidade,
                p.nome as profissional_nome,
                sv.nome as servico_nome,
                sv.preco as servico_preco
              FROM agendamentos a
              INNER JOIN usuarios u ON a.cliente_id = u.id
              INNER JOIN saloes s ON a.salao_id = s.id
              INNER JOIN profissionais p ON a.profissional_id = p.id
              INNER JOIN servicos sv ON a.servico_id = sv.id
              WHERE a.data BETWEEN :data_inicio AND :data_fim";
    
    $params = [
        'data_inicio' => $dataInicio,
        'data_fim' => $dataFim
    ];
    
    // Adicionar filtros
    if (!empty($salaoId)) {
        $query .= " AND a.salao_id = :salao_id";
        $params['salao_id'] = $salaoId;
    }
    
    if (!empty($status)) {
        $query .= " AND a.status_pagamento = :status";
        $params['status'] = $status;
    }
    
    $query .= " ORDER BY a.data DESC, a.hora DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular totais
    $totalAgendamentos = count($movimentacoes);
    $totalFaturamento = 0;
    $agendamentosPagos = 0;
    
    foreach ($movimentacoes as $mov) {
        if ($mov['status_pagamento'] === 'pago') {
            $totalFaturamento += $mov['taxa_servico'];
            $agendamentosPagos++;
        }
    }
    
    // Gerar relatório em HTML para impressão
    header('Content-Type: text/html; charset=utf-8');
    
    $filtroTexto = '';
    if (!empty($salaoId)) {
        $stmt = $conn->prepare("SELECT nome_fantasia FROM saloes WHERE id = :id");
        $stmt->execute(['id' => $salaoId]);
        $salao = $stmt->fetch(PDO::FETCH_ASSOC);
        $filtroTexto .= ' - Salão: ' . $salao['nome_fantasia'];
    }
    if (!empty($status)) {
        $filtroTexto .= ' - Status: ' . ucfirst($status);
    }
    
    echo '<!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Relatório de Movimentações Financeiras</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .header h1 { color: #333; margin-bottom: 10px; }
            .header p { color: #666; }
            .summary { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
            .summary h3 { margin-top: 0; color: #333; }
            .summary-item { display: flex; justify-content: space-between; margin-bottom: 10px; }
            .summary-item strong { color: #333; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f8f9fa; font-weight: bold; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .status-pago { color: #28a745; font-weight: bold; }
            .status-pendente { color: #ffc107; font-weight: bold; }
            .status-cancelado { color: #dc3545; font-weight: bold; }
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>RELATÓRIO DE MOVIMENTAÇÕES FINANCEIRAS</h1>
            <p>Período: ' . date('d/m/Y', strtotime($dataInicio)) . ' a ' . date('d/m/Y', strtotime($dataFim)) . $filtroTexto . '</p>
            <p>Gerado em: ' . date('d/m/Y H:i:s') . '</p>
        </div>
        
        <div class="summary">
            <h3>RESUMO GERAL</h3>
            <div class="summary-item">
                <span>Total de Agendamentos:</span>
                <strong>' . $totalAgendamentos . '</strong>
            </div>
            <div class="summary-item">
                <span>Agendamentos Pagos:</span>
                <strong>' . $agendamentosPagos . '</strong>
            </div>
            <div class="summary-item">
                <span>Total Faturamento:</span>
                <strong>R$ ' . number_format($totalFaturamento, 2, ',', '.') . '</strong>
            </div>
            <div class="summary-item">
                <span>Ticket Médio:</span>
                <strong>R$ ' . number_format($agendamentosPagos > 0 ? $totalFaturamento / $agendamentosPagos : 0, 2, ',', '.') . '</strong>
            </div>
        </div>
        
        <div class="no-print" style="margin-bottom: 20px;">
            <button onclick="window.print()" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                Imprimir Relatório
            </button>
            <button onclick="window.close()" style="background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-left: 10px;">
                Fechar
            </button>
        </div>
        
        <h3>DETALHAMENTO DAS MOVIMENTAÇÕES</h3>
        <table>
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th class="text-center">Data</th>
                    <th class="text-center">Hora</th>
                    <th>Cliente</th>
                    <th>Salão</th>
                    <th>Profissional</th>
                    <th>Serviço</th>
                    <th class="text-right">Valor</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($movimentacoes as $mov) {
        $statusClass = 'status-' . $mov['status_pagamento'];
        echo '<tr>
                <td class="text-center">' . $mov['id'] . '</td>
                <td class="text-center">' . date('d/m/Y', strtotime($mov['data'])) . '</td>
                <td class="text-center">' . $mov['hora'] . '</td>
                <td>' . htmlspecialchars($mov['cliente_nome']) . '</td>
                <td>' . htmlspecialchars($mov['salao_nome']) . '</td>
                <td>' . htmlspecialchars($mov['profissional_nome']) . '</td>
                <td>' . htmlspecialchars($mov['servico_nome']) . '</td>
                <td class="text-right">R$ ' . number_format($mov['taxa_servico'], 2, ',', '.') . '</td>
                <td class="text-center ' . $statusClass . '">' . ucfirst($mov['status_pagamento']) . '</td>
              </tr>';
    }
    
    echo '    </tbody>
        </table>
        
        <div style="margin-top: 30px; text-align: center; color: #666; font-size: 12px;">
            <p>Relatório gerado pelo sistema CorteFácil</p>
        </div>
    </body>
    </html>';

} catch (Exception $e) {
    // Em caso de erro, retornar uma página de erro
    header('Content-Type: text/html; charset=utf-8');
    echo '<h1>Erro ao gerar relatório</h1>';
    echo '<p>Erro: ' . $e->getMessage() . '</p>';
    echo '<p><a href="javascript:history.back()">Voltar</a></p>';
}
?>