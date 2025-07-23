<?php
session_start();
include 'conexao.php';
include 'gerenciar_ciclos_metas.php';

// Verifica se é um salão
if (!isset($_SESSION['salao_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

try {
    $salao_id = $_SESSION['salao_id'];
    
    // Finaliza ciclos expirados e cria novos se necessário
    finalizarCiclosExpirados();
    
    // Atualiza a contagem de agendamentos confirmados para o ciclo atual
    $ciclo_atual = atualizarContagemAgendamentos($salao_id);
    
    if (!$ciclo_atual) {
        throw new Exception('Erro ao obter dados do ciclo atual');
    }
    
    // Calcula o bônus atual baseado nas metas atingidas
    $bonus_atual = 0;
    if ($ciclo_atual['meta_100_atingida']) {
        $bonus_atual = 150.00;
    } elseif ($ciclo_atual['meta_50_atingida']) {
        $bonus_atual = 50.00;
    }
    
    // Prepara os dados da meta atual
    $meta_atual = [
        'agendamentos_confirmados' => $ciclo_atual['agendamentos_confirmados'],
        'agendamentos_mes' => $ciclo_atual['agendamentos_confirmados'], // Compatibilidade
        'bonus_pago' => number_format($bonus_atual, 2, ',', '.'),
        'dias_restantes' => (int)$ciclo_atual['dias_restantes'],
        'data_inicio' => $ciclo_atual['data_inicio'],
        'data_fim' => $ciclo_atual['data_fim'],
        'meta_50_atingida' => $ciclo_atual['meta_50_atingida'],
        'meta_100_atingida' => $ciclo_atual['meta_100_atingida']
    ];
    
    // Busca o histórico dos últimos 6 ciclos
    $historico_ciclos = obterHistoricoCiclos($salao_id, 6);
    
    $historico = [];
    foreach ($historico_ciclos as $ciclo) {
        $bonus_ciclo = 0;
        if ($ciclo['meta_100_atingida']) {
            $bonus_ciclo = 150.00;
        } elseif ($ciclo['meta_50_atingida']) {
            $bonus_ciclo = 50.00;
        }
        
        $historico[] = [
            'mes_formatado' => $ciclo['periodo_formatado'],
            'agendamentos' => $ciclo['agendamentos_confirmados'],
            'bonus_pago' => number_format($bonus_ciclo, 2, ',', '.'),
            'meta_50_atingida' => $ciclo['meta_50_atingida'],
            'meta_100_atingida' => $ciclo['meta_100_atingida']
        ];
    }
    
    echo json_encode([
        'status' => 'ok',
        'meta_atual' => $meta_atual,
        'historico' => $historico
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}

$conn = null;
?>