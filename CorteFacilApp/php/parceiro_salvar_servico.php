<?php
require_once 'conexao.php';
session_start();

header('Content-Type: application/json');

// Verifica se é um parceiro
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'salao') {
    http_response_code(401);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

// Recebe os dados do POST
$dados = $_POST;

// Validação básica
if (empty($dados['nome']) || empty($dados['duracao_minutos']) || empty($dados['preco'])) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Dados incompletos']);
    exit;
}

try {
    // Se tem ID, atualiza. Se não tem, insere
    if (!empty($dados['id'])) {
        $stmt = $conn->prepare("
            UPDATE servicos 
            SET 
                nome = :nome,
                duracao_minutos = :duracao_minutos,
                preco = :preco
            WHERE id = :id AND salao_id = :salao_id
        ");
        
        $params = [
            'id' => $dados['id'],
            'nome' => $dados['nome'],
            'duracao_minutos' => $dados['duracao_minutos'],
            'preco' => $dados['preco'],
            'salao_id' => $_SESSION['salao_id']
        ];
        
    } else {
        $stmt = $conn->prepare("
            INSERT INTO servicos 
            (nome, duracao_minutos, preco, salao_id)
            VALUES 
            (:nome, :duracao_minutos, :preco, :salao_id)
        ");
        
        $params = [
            'nome' => $dados['nome'],
            'duracao_minutos' => $dados['duracao_minutos'],
            'preco' => $dados['preco'],
            'salao_id' => $_SESSION['salao_id']
        ];
    }
    
    $stmt->execute($params);
    
    if (!empty($dados['id'])) {
        $mensagem = 'Serviço atualizado com sucesso';
        $id = $dados['id'];
    } else {
        $mensagem = 'Serviço cadastrado com sucesso';
        $id = $conn->lastInsertId();
    }
    
    echo json_encode([
        'status' => 'success',
        'mensagem' => $mensagem,
        'id' => $id
    ]);

} catch (PDOException $e) {
    error_log("Erro ao salvar serviço: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao salvar serviço',
        'debug' => $e->getMessage()
    ]);
}
?> 