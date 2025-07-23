<?php
session_start();
include 'conexao.php';
include 'gerenciar_ciclos_metas.php';

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

try {
    // Finaliza ciclos expirados e cria novos se necessário
    finalizarCiclosExpirados();
    
    // Busca todos os salões ativos
    $query = "SELECT id, nome_fantasia FROM saloes WHERE ativo = 1 ORDER BY nome_fantasia";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $saloes_ativos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $saloes = [];
    $resumo = [
        'total_saloes' => 0,
        'meta_50' => 0,
        'meta_100' => 0
    ];
    
    foreach ($saloes_ativos as $salao) {
        // Atualiza a contagem de agendamentos confirmados para cada salão
        $ciclo_atual = atualizarContagemAgendamentos($salao['id']);
        
        if ($ciclo_atual) {
            $agendamentos = $ciclo_atual['agendamentos_confirmados'];
            
            // Calcula o bônus baseado nas metas atingidas
            $bonus_pago = 0;
            if ($ciclo_atual['meta_100_atingida']) {
                $bonus_pago = 150.00;
                $resumo['meta_100']++;
            } elseif ($ciclo_atual['meta_50_atingida']) {
                $bonus_pago = 50.00;
                $resumo['meta_50']++;
            }
            
            $resumo['total_saloes']++;
            
            $saloes[] = [
                'id' => $salao['id'],
                'nome_fantasia' => $salao['nome_fantasia'],
                'agendamentos_mes' => $agendamentos,
                'bonus_pago' => $bonus_pago,
                'dias_restantes' => (int)$ciclo_atual['dias_restantes'],
                'data_inicio' => $ciclo_atual['data_inicio'],
                'data_fim' => $ciclo_atual['data_fim'],
                'meta_50_atingida' => $ciclo_atual['meta_50_atingida'],
                'meta_100_atingida' => $ciclo_atual['meta_100_atingida']
            ];
        } else {
            // Se não conseguiu obter dados do ciclo, cria entrada com valores zerados
            $resumo['total_saloes']++;
            
            $saloes[] = [
                'id' => $salao['id'],
                'nome_fantasia' => $salao['nome_fantasia'],
                'agendamentos_mes' => 0,
                'bonus_pago' => 0,
                'dias_restantes' => 0,
                'data_inicio' => date('Y-m-d'),
                'data_fim' => date('Y-m-d', strtotime('+29 days')),
                'meta_50_atingida' => false,
                'meta_100_atingida' => false
            ];
        }
    }
    
    // Ordena por número de agendamentos (decrescente)
    usort($saloes, function($a, $b) {
        return $b['agendamentos_mes'] - $a['agendamentos_mes'];
    });
    
    echo json_encode([
        'status' => 'ok',
        'resumo' => $resumo,
        'saloes' => $saloes
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}

$conn = null;
?>
