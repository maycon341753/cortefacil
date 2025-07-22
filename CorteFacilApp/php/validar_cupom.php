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

// Recebe o código do cupom
$codigo = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_STRING);
if (!$codigo) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Código do cupom não fornecido']);
    exit;
}

try {
    $pdo = getConexao();

    // Busca o cupom
    $stmt = $pdo->prepare("
        SELECT c.*, s.nome_fantasia as nome_salao
        FROM cupons c
        JOIN saloes s ON c.salao_id = s.id
        WHERE c.codigo = ?
    ");
    $stmt->execute([$codigo]);
    $cupom = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cupom) {
        throw new Exception('Cupom não encontrado');
    }

    // Verifica o status do cupom
    if ($cupom['status'] !== 'disponivel') {
        throw new Exception('Este cupom já foi ' . ($cupom['status'] === 'utilizado' ? 'utilizado' : 'expirado'));
    }

    // Verifica se o cupom está expirado
    if (strtotime($cupom['data_expiracao']) < time()) {
        // Atualiza o status para expirado
        $stmt = $pdo->prepare("UPDATE cupons SET status = 'expirado' WHERE codigo = ?");
        $stmt->execute([$codigo]);
        throw new Exception('Este cupom está expirado');
    }

    // Retorna as informações do cupom
    echo json_encode([
        'status' => 'sucesso',
        'cupom' => [
            'codigo' => $cupom['codigo'],
            'salao_id' => $cupom['salao_id'],
            'nome_salao' => $cupom['nome_fantasia'],
            'data_expiracao' => date('d/m/Y', strtotime($cupom['data_expiracao'])),
            'valor_ressarcimento' => $cupom['valor_ressarcimento']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}