<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

try {
    $pdo = getConexao();

    // Atualiza status dos cupons expirados
    $stmt = $pdo->prepare(
        "UPDATE cupons 
        SET status = 'expirado' 
        WHERE status = 'disponivel' 
        AND data_expiracao < CURRENT_DATE()"
    );
    $stmt->execute();

    // Busca todos os cupons com informações do salão
    $query = "
        SELECT 
            c.*,
            s.nome_fantasia as nome_salao,
            CASE 
                WHEN c.status = 'disponivel' AND c.data_expiracao < CURRENT_DATE() THEN 'expirado'
                ELSE c.status
            END as status_atual
        FROM cupons c
        JOIN saloes s ON c.salao_id = s.id
        ORDER BY c.data_geracao DESC
    ";

    $stmt = $pdo->query($query);
    $cupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formata os dados para exibição
    foreach ($cupons as &$cupom) {
        $cupom['status'] = $cupom['status_atual'];
        unset($cupom['status_atual']);
    }

    echo json_encode($cupons);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao listar cupons: ' . $e->getMessage()]);
}