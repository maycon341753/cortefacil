<?php
header('Content-Type: application/json');
require_once 'conexao.php';
session_start();

try {
    // Verificar se o usuário está logado
    if (!isset($_SESSION['id']) || !isset($_SESSION['tipo'])) {
        throw new Exception('Usuário não autenticado');
    }
    
    // Verificar se é um cliente
    if ($_SESSION['tipo'] !== 'cliente') {
        throw new Exception('Acesso negado. Apenas clientes podem fazer agendamentos');
    }
    
    // Ler o corpo JSON da requisição
    $json = file_get_contents('php://input');
    $dados = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Erro ao decodificar JSON');
    }
    
    // Validação dos dados recebidos
    $usuario_id = $_SESSION['id'];
    $salao_id = isset($dados['salao_id']) ? filter_var($dados['salao_id'], FILTER_VALIDATE_INT) : null;
    $profissional_id = isset($dados['profissional_id']) ? filter_var($dados['profissional_id'], FILTER_VALIDATE_INT) : null;
    $servico_id = isset($dados['servico_id']) ? filter_var($dados['servico_id'], FILTER_VALIDATE_INT) : null;
    
    // Validação dos dados da requisição
    if (!isset($dados['data']) || !isset($dados['hora'])) {
        throw new Exception('Data e hora são obrigatórios');
    }

    $data = trim($dados['data']);
    $hora = trim($dados['hora']);

    if (!$salao_id || !$profissional_id || !$servico_id || !$data || !$hora) {
        throw new Exception('Todos os campos são obrigatórios');
    }

    // Valida o formato da data e hora
    $dataObj = DateTime::createFromFormat('Y-m-d', $data);
    if (!$dataObj || $dataObj->format('Y-m-d') !== $data) {
        throw new Exception('Formato de data inválido. Use o formato YYYY-MM-DD');
    }

    if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $hora)) {
        throw new Exception('Formato de hora inválido. Use o formato HH:mm');
    }

    $pdo = getConexao();

    // Verifica se o horário está disponível
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM agendamentos 
        WHERE profissional_id = ? 
        AND data = ? 
        AND hora = ?
        AND status NOT IN ('CANCELADO')
    ");
    $stmt->execute([$profissional_id, $data, $hora]);
    
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Este horário já está agendado');
    }

    // Inicia a transação
    $pdo->beginTransaction();

    try {
        // Insere o agendamento
        $stmt = $pdo->prepare("
            INSERT INTO agendamentos (
                cliente_id, 
                profissional_id, 
                salao_id, 
                servico_id, 
                data, 
                hora, 
                status, 
                criado_em
            ) VALUES (
                ?, ?, ?, ?, ?, ?, 'PENDENTE', NOW()
            )
        ");

        $stmt->execute([
            $usuario_id,
            $profissional_id,
            $salao_id,
            $servico_id,
            $data,
            $hora
        ]);

        $agendamento_id = $pdo->lastInsertId();

        // Busca o valor do serviço
        $stmt = $pdo->prepare("
            SELECT preco, nome 
            FROM servicos 
            WHERE id = ?
        ");
        $stmt->execute([$servico_id]);
        $servico = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$servico) {
            throw new Exception('Serviço não encontrado');
        }

        $pdo->commit();

        // Retorna os dados para gerar o pagamento
        echo json_encode([
            'status' => 'success',
            'data' => [
                'agendamento_id' => $agendamento_id,
                'requires_payment' => true,
                'valor' => $servico['preco'],
                'servico' => $servico['nome'],
                'data' => $data,
                'hora' => $hora
            ]
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao realizar agendamento'
    ]);
}
