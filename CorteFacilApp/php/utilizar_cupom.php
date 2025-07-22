<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário não autenticado']);
    exit;
}

// Recebe os parâmetros
$codigo = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_STRING);
$agendamento_id = filter_input(INPUT_POST, 'agendamento_id', FILTER_VALIDATE_INT);

if (!$codigo || !$agendamento_id) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Parâmetros inválidos']);
    exit;
}

try {
    $pdo = getConexao();
    $pdo->beginTransaction();

    // Busca o cupom
    $stmt = $pdo->prepare("
        SELECT c.*, s.id as salao_id
        FROM cupons c
        JOIN saloes s ON c.salao_id = s.id
        WHERE c.codigo = ? AND c.status = 'disponivel'
        FOR UPDATE
    ");
    $stmt->execute([$codigo]);
    $cupom = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cupom) {
        throw new Exception('Cupom não encontrado ou já utilizado');
    }

    // Verifica se o cupom está expirado
    if (strtotime($cupom['data_expiracao']) < time()) {
        $stmt = $pdo->prepare("UPDATE cupons SET status = 'expirado' WHERE codigo = ?");
        $stmt->execute([$codigo]);
        throw new Exception('Este cupom está expirado');
    }

    // Verifica se o agendamento existe e é para o salão correto
    $stmt = $pdo->prepare("
        SELECT a.id, a.salao_id, a.cliente_id, a.status
        FROM agendamentos a
        WHERE a.id = ? AND a.salao_id = ?
    ");
    $stmt->execute([$agendamento_id, $cupom['salao_id']]);
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$agendamento) {
        throw new Exception('Agendamento não encontrado ou não pertence ao salão do cupom');
    }

    if ($agendamento['status'] !== 'confirmado') {
        throw new Exception('O agendamento precisa estar confirmado para utilizar o cupom');
    }

    // Atualiza o status do cupom
    $stmt = $pdo->prepare("
        UPDATE cupons 
        SET status = 'utilizado',
            data_utilizacao = NOW(),
            cliente_id = ?,
            agendamento_id = ?
        WHERE codigo = ?
    ");
    $stmt->execute([$agendamento['cliente_id'], $agendamento_id, $codigo]);

    // Registra o ressarcimento para o salão
    $stmt = $pdo->prepare("
        INSERT INTO ressarcimentos (cupom_id, salao_id, valor, data_ressarcimento, status)
        SELECT id, salao_id, valor_ressarcimento, NOW(), 'pendente'
        FROM cupons
        WHERE codigo = ?
    ");
    $stmt->execute([$codigo]);

    $pdo->commit();

    echo json_encode([
        'status' => 'sucesso',
        'mensagem' => 'Cupom utilizado com sucesso'
    ]);

} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}