<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

// Verifica se o usuário está logado e é do tipo salão
if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'salao') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'mensagem' => 'Não autorizado']);
    exit;
}

$salao_id = $_SESSION['salao_id'];
$hoje = date('Y-m-d');
$primeiro_dia_mes = date('Y-m-01');
$ultimo_dia_mes = date('Y-m-t');

try {
    // Agendamentos de hoje
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM agendamentos
        WHERE salao_id = :salao_id
        AND data = :hoje
    ");
    $stmt->execute(['salao_id' => $salao_id, 'hoje' => $hoje]);
    $agendamentos_hoje = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Faturamento de hoje
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(s.preco), 0) as total
        FROM agendamentos a
        JOIN servicos s ON s.id = a.servico_id
        WHERE a.salao_id = :salao_id
        AND a.data = :hoje
        AND a.status = 'realizado'
    ");
    $stmt->execute(['salao_id' => $salao_id, 'hoje' => $hoje]);
    $faturamento_hoje = floatval($stmt->fetch(PDO::FETCH_ASSOC)['total']);

    // Clientes atendidos no mês
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT cliente_id) as total
        FROM agendamentos
        WHERE salao_id = :salao_id
        AND data BETWEEN :primeiro_dia AND :ultimo_dia
        AND status = 'realizado'
    ");
    $stmt->execute([
        'salao_id' => $salao_id,
        'primeiro_dia' => $primeiro_dia_mes,
        'ultimo_dia' => $ultimo_dia_mes
    ]);
    $clientes_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Meta mensal (baseada em número de cortes)
    $stmt = $conn->prepare("
        SELECT cortes_mes
        FROM metas
        WHERE salao_id = :salao_id
        AND mes = :mes_atual
    ");
    $mes_atual = date('Y-m');
    $stmt->execute(['salao_id' => $salao_id, 'mes_atual' => $mes_atual]);
    $meta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Número de agendamentos realizados no mês
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM agendamentos
        WHERE salao_id = :salao_id
        AND data BETWEEN :primeiro_dia AND :ultimo_dia
        AND status = 'realizado'
    ");
    $stmt->execute([
        'salao_id' => $salao_id,
        'primeiro_dia' => $primeiro_dia_mes,
        'ultimo_dia' => $ultimo_dia_mes
    ]);
    $agendamentos_realizados_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Calcula percentual da meta
    $meta_cortes = $meta ? intval($meta['cortes_mes']) : 0;
    $meta_mensal = $meta_cortes > 0 ? round(($agendamentos_realizados_mes / $meta_cortes) * 100) : 0;

    // Últimos agendamentos
    $stmt = $conn->prepare("
        SELECT 
            a.id,
            u.nome as cliente,
            s.nome as servico,
            a.data,
            a.hora,
            a.status
        FROM agendamentos a
        JOIN usuarios u ON u.id = a.cliente_id
        JOIN servicos s ON s.id = a.servico_id
        WHERE a.salao_id = :salao_id
        ORDER BY a.data DESC, a.hora DESC
        LIMIT 5
    ");
    $stmt->execute(['salao_id' => $salao_id]);
    $ultimos_agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formata os últimos agendamentos
    foreach ($ultimos_agendamentos as &$agendamento) {
        $data_hora = $agendamento['data'] . ' ' . $agendamento['hora'];
        $data = new DateTime($data_hora);
        $agendamento['data_hora'] = $data->format('d/m/Y H:i');
        // Remove campos separados após formatação
        unset($agendamento['data']);
        unset($agendamento['hora']);
    }

    // Faturamento semanal
    $faturamento_semanal = [];
    for ($i = 6; $i >= 0; $i--) {
        $data = date('Y-m-d', strtotime("-$i days"));
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(s.preco), 0) as total
            FROM agendamentos a
            JOIN servicos s ON s.id = a.servico_id
            WHERE a.salao_id = :salao_id
            AND a.data = :data
            AND a.status = 'realizado'
        ");
        $stmt->execute(['salao_id' => $salao_id, 'data' => $data]);
        $valor = floatval($stmt->fetch(PDO::FETCH_ASSOC)['total']);
        
        $faturamento_semanal[] = [
            'dia' => date('d/m', strtotime($data)),
            'valor' => $valor
        ];
    }

    echo json_encode([
        'status' => 'success',
        'agendamentos_hoje' => $agendamentos_hoje,
        'faturamento_hoje' => $faturamento_hoje,
        'clientes_mes' => $clientes_mes,
        'meta_mensal' => $meta_mensal,
        'ultimos_agendamentos' => $ultimos_agendamentos,
        'faturamento_semanal' => $faturamento_semanal
    ]);

} catch (PDOException $e) {
    error_log("Erro ao buscar estatísticas do dashboard: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'mensagem' => 'Erro ao carregar dados do dashboard'
    ]);
}
?>