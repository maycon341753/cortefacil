<?php
session_start();
require_once 'conexao.php';
require_once 'mercadopago_config.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'cliente') {
        throw new Exception('Usuário não autenticado');
    }

    $agendamento_id = $_GET['agendamento_id'] ?? 0;
    
    if (!$agendamento_id) {
        throw new Exception('ID do agendamento não fornecido');
    }

    $conn = getConexao();
    
    // Verificar se o agendamento existe e pertence ao usuário
    $stmt = $conn->prepare("SELECT * FROM agendamentos WHERE id = ? AND cliente_id = ?");
    $stmt->execute([$agendamento_id, $_SESSION['id']]);
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$agendamento) {
        throw new Exception('Agendamento não encontrado');
    }
    
    // Verificar se existe payment_id
    if (!$agendamento['payment_id']) {
        echo json_encode([
            'success' => true,
            'pago' => false,
            'message' => 'Pagamento não iniciado'
        ]);
        exit;
    }
    
    // Consultar status do pagamento no Mercado Pago
    try {
        $payment = mercadoPagoRequest('/v1/payments/' . $agendamento['payment_id']);
        
        $status = $payment['status'] ?? 'pending';
        $status_detail = $payment['status_detail'] ?? '';
        
        if ($status === 'approved') {
            // Pagamento aprovado - atualizar agendamento para realizado
            $stmt = $conn->prepare("UPDATE agendamentos SET status = 'realizado', status_pagamento = 'pago' WHERE id = ?");
            $stmt->execute([$agendamento_id]);
            
            echo json_encode([
                'success' => true,
                'pago' => true,
                'message' => 'Pagamento confirmado! Agendamento realizado com sucesso.',
                'payment_status' => $status,
                'payment_detail' => $status_detail
            ]);
        } elseif ($status === 'rejected' || $status === 'cancelled') {
            // Pagamento rejeitado ou cancelado
            echo json_encode([
                'success' => true,
                'pago' => false,
                'message' => 'Pagamento rejeitado ou cancelado. Tente novamente.',
                'payment_status' => $status,
                'payment_detail' => $status_detail,
                'error' => true
            ]);
        } else {
            // Pagamento pendente
            echo json_encode([
                'success' => true,
                'pago' => false,
                'message' => 'Aguardando pagamento...',
                'payment_status' => $status,
                'payment_detail' => $status_detail
            ]);
        }
        
    } catch (Exception $mpError) {
        // Erro ao consultar Mercado Pago - manter como pendente
        echo json_encode([
            'success' => true,
            'pago' => false,
            'message' => 'Verificando pagamento...',
            'mp_error' => $mpError->getMessage()
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>