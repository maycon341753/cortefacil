<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'parceiro') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'mensagem' => 'Usuário não autenticado']);
    exit;
}

require_once 'conexao.php';

try {
    $salao_id = $_SESSION['salao_id'];
    $mes_atual = date('Y-m');
    
    // Obtém estatísticas básicas
    $stats = [
        'total_agendamentos' => 0,
        'total_servicos' => 0,
        'total_profissionais' => 0,
        'faturamento_mensal' => 0.00
    ];
    
    // Dados do gráfico de agendamentos (últimos 7 dias)
    $bookings_chart = [
        'labels' => [],
        'values' => []
    ];
    
    // Próximos agendamentos (próximas 24 horas)
    $upcoming_bookings = [];
    
    // Simula dados para desenvolvimento
    // Em produção, estes dados viriam do banco de dados
    $stats['total_agendamentos'] = 150;
    $stats['total_servicos'] = 25;
    $stats['total_profissionais'] = 8;
    $stats['faturamento_mensal'] = 15000.00;
    
    // Simula dados do gráfico
    for ($i = 6; $i >= 0; $i--) {
        $date = date('d/m', strtotime("-$i days"));
        $bookings_chart['labels'][] = $date;
        $bookings_chart['values'][] = rand(5, 20);
    }
    
    // Simula próximos agendamentos
    $upcoming_bookings = [
        [
            'cliente_nome' => 'João Silva',
            'servico_nome' => 'Corte Masculino',
            'profissional_nome' => 'Carlos Santos',
            'data' => date('Y-m-d'),
            'horario' => '14:30'
        ],
        [
            'cliente_nome' => 'Maria Oliveira',
            'servico_nome' => 'Escova',
            'profissional_nome' => 'Ana Paula',
            'data' => date('Y-m-d'),
            'horario' => '15:00'
        ],
        [
            'cliente_nome' => 'Pedro Costa',
            'servico_nome' => 'Barba',
            'profissional_nome' => 'Carlos Santos',
            'data' => date('Y-m-d'),
            'horario' => '16:00'
        ]
    ];
    
    echo json_encode([
        'status' => 'success',
        'stats' => $stats,
        'bookings_chart' => $bookings_chart,
        'upcoming_bookings' => $upcoming_bookings
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'mensagem' => 'Erro ao carregar dados do dashboard: ' . $e->getMessage()
    ]);
}
?>