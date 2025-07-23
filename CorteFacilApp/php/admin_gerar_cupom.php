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
    $salao_id = filter_input(INPUT_POST, 'salao_id', FILTER_VALIDATE_INT);
    $tipo_cupom = filter_input(INPUT_POST, 'tipo_cupom', FILTER_SANITIZE_STRING) ?: 'normal';
    
    // Se for cupom normal, pega os valores normais
    if ($tipo_cupom === 'normal') {
        $data_expiracao = filter_input(INPUT_POST, 'data_expiracao', FILTER_SANITIZE_STRING);
        $valor_ressarcimento = filter_input(INPUT_POST, 'valor_ressarcimento', FILTER_VALIDATE_FLOAT);

        if (!$salao_id || !$data_expiracao || !$valor_ressarcimento) {
            throw new Exception('Dados inválidos');
        }

        // Valida a data de expiração
        $data_expiracao_obj = new DateTime($data_expiracao);
        $hoje = new DateTime();
        if ($data_expiracao_obj <= $hoje) {
            throw new Exception('A data de expiração deve ser maior que a data atual');
        }
    } 
    // Se for cupom de corte grátis
    else if ($tipo_cupom === 'corte_gratis') {
        if (!$salao_id) {
            throw new Exception('Salão não selecionado');
        }
        
        // Define a data de expiração para 60 dias a partir de hoje
        $hoje = new DateTime();
        $data_expiracao_obj = clone $hoje;
        $data_expiracao_obj->add(new DateInterval('P60D')); // Adiciona 60 dias
        $data_expiracao = $data_expiracao_obj->format('Y-m-d');
        
        // Busca o valor médio dos serviços do salão para ressarcimento
        $pdo = getConexao();
        $stmt = $pdo->prepare("SELECT AVG(preco) as preco_medio FROM servicos WHERE salao_id = ? AND ativo = 1");
        $stmt->execute([$salao_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$resultado || !$resultado['preco_medio']) {
            $valor_ressarcimento = 50.00; // Valor padrão se não houver serviços
        } else {
            $valor_ressarcimento = floatval($resultado['preco_medio']);
        }
    } else {
        throw new Exception('Tipo de cupom inválido');
    }

    $pdo = getConexao();

    // Verifica se o salão existe
    $stmt = $pdo->prepare("SELECT id FROM saloes WHERE id = ? AND ativo = 1");
    $stmt->execute([$salao_id]);
    if ($stmt->rowCount() === 0) {
        throw new Exception('Salão não encontrado ou inativo');
    }

    // Gera um código único para o cupom
    do {
        $codigo = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        $stmt = $pdo->prepare("SELECT id FROM cupons WHERE codigo = ?");
        $stmt->execute([$codigo]);
    } while ($stmt->rowCount() > 0);

    // Insere o cupom
    $stmt = $pdo->prepare("INSERT INTO cupons (codigo, salao_id, tipo_cupom, data_expiracao, valor_ressarcimento) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$codigo, $salao_id, $tipo_cupom, $data_expiracao, $valor_ressarcimento]);

    // Notifica o salão (implementar sistema de notificação)
    // TODO: Implementar sistema de notificação por email ou push notification

    echo json_encode([
        'status' => 'sucesso',
        'mensagem' => 'Cupom gerado com sucesso',
        'codigo' => $codigo
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
}