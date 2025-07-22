<?php
require_once 'conexao.php';
session_start();

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

// Recebe os dados do agendamento
$dados = json_decode(file_get_contents('php://input'), true);

if (!isset($dados['salaoId']) || !isset($dados['profissionalId']) || 
    !isset($dados['servicoId']) || !isset($dados['data']) || !isset($dados['hora'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados incompletos']);
    exit;
}

try {
    // Gera um ID único para a transação
    $transaction_id = uniqid('PIX_', true);
    
    // Insere o agendamento como pendente
    $stmt = $conn->prepare("
        INSERT INTO agendamentos 
        (cliente_id, salao_id, profissional_id, servico_id, data, hora, status, transaction_id)
        VALUES (?, ?, ?, ?, ?, ?, 'pendente', ?)
    ");
    
    $stmt->execute([
        $_SESSION['id'],
        $dados['salaoId'],
        $dados['profissionalId'],
        $dados['servicoId'],
        $dados['data'],
        $dados['hora'],
        $transaction_id
    ]);
    
    // Busca o valor do serviço
    $stmt = $conn->prepare("SELECT preco FROM servicos WHERE id = ?");
    $stmt->execute([$dados['servicoId']]);
    $servico = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$servico) {
        http_response_code(400);
        echo json_encode(['error' => 'Serviço não encontrado']);
        exit;
    }
    
    // Aqui você deve integrar com seu provedor de PIX
    $pixKey = "05286558178"; // Chave PIX fornecida
    $valor = number_format($servico['preco'], 2, '.', ''); // Valor do serviço
    
    // Gera o payload do PIX
    $payload = "00020126330014BR.GOV.BCB.PIX0111{$pixKey}520400005303986540{$valor}5802BR5913CorteFacil App6008Sao Paulo62070503***6304";
    
    echo json_encode([
        'success' => true,
        'payload' => $payload,
        'transactionId' => $transaction_id
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao gerar pagamento PIX']);
}