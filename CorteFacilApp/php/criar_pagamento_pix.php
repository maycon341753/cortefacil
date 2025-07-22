<?php
session_start();
require_once 'conexao.php';
require_once 'mercadopago_config.php';

header('Content-Type: application/json');

try {
    // Verificar se o usuário está logado
    if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'cliente') {
        throw new Exception('Usuário não autenticado');
    }

    // Obter dados do POST
    $input = json_decode(file_get_contents('php://input'), true);
    $agendamento_id = isset($input['agendamento_id']) ? (int)$input['agendamento_id'] : 0;

    if (!$agendamento_id) {
        throw new Exception('ID do agendamento não fornecido');
    }

    $conn = getConexao();
    
    // Buscar dados do agendamento
    $stmt = $conn->prepare("
        SELECT a.*, s.nome as servico_nome, s.preco, sal.nome_fantasia as salao_nome, p.nome as profissional_nome
        FROM agendamentos a
        JOIN servicos s ON a.servico_id = s.id
        JOIN saloes sal ON a.salao_id = sal.id
        JOIN profissionais p ON a.profissional_id = p.id
        WHERE a.id = ? AND a.cliente_id = ?
    ");
    $stmt->execute([$agendamento_id, $_SESSION['id']]);
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$agendamento) {
        throw new Exception('Agendamento não encontrado');
    }

    // Usar valor fixo de R$ 0,99 conforme solicitado
    $valor = 0.99;

    // Criar pagamento no Mercado Pago
    $paymentData = [
        'transaction_amount' => $valor,
        'description' => "Agendamento #{$agendamento_id} - {$agendamento['servico_nome']} - {$agendamento['salao_nome']}",
        'payment_method_id' => 'pix',
        'payer' => [
            'email' => $_SESSION['email'] ?? 'cliente@cortefacil.com',
            'first_name' => $_SESSION['nome'] ?? 'Cliente',
            'identification' => [
                'type' => 'CPF',
                'number' => $_SESSION['cpf'] ?? '00000000000'
            ]
        ],
        'notification_url' => 'https://webhook.site/unique-id-here', // Substitua por sua URL de webhook
        'external_reference' => "agendamento_{$agendamento_id}"
    ];

    $payment = mercadoPagoRequest('/v1/payments', 'POST', $paymentData);

    if (!isset($payment['id'])) {
        throw new Exception('Erro ao criar pagamento no Mercado Pago');
    }

    // Salvar payment_id no banco de dados
    $stmt = $conn->prepare("UPDATE agendamentos SET payment_id = ? WHERE id = ?");
    $stmt->execute([$payment['id'], $agendamento_id]);

    // Extrair dados do QR Code
    $qr_code_base64 = $payment['point_of_interaction']['transaction_data']['qr_code_base64'] ?? '';
    $qr_code = $payment['point_of_interaction']['transaction_data']['qr_code'] ?? '';
    $pix_copia_cola = $payment['point_of_interaction']['transaction_data']['qr_code'] ?? '';

    echo json_encode([
        'success' => true,
        'payment_id' => $payment['id'],
        'qr_code_base64' => $qr_code_base64,
        'qr_code' => $qr_code,
        'pix_copia_cola' => $pix_copia_cola,
        'valor' => $valor,
        'agendamento' => [
            'id' => $agendamento['id'],
            'servico' => $agendamento['servico_nome'],
            'salao' => $agendamento['salao_nome'],
            'profissional' => $agendamento['profissional_nome'],
            'data' => $agendamento['data'],
            'horario' => $agendamento['hora']
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>