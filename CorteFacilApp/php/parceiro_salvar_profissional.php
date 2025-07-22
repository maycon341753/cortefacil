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

// Recebe os dados do JSON
$json = file_get_contents('php://input');
$dados = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Dados inválidos']);
    exit;
}

// Validação básica
if (empty($dados['nome'])) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'Nome é obrigatório']);
    exit;
}

try {
    // Se tem ID, atualiza. Se não tem, insere
    if (!empty($dados['id'])) {
        $stmt = $conn->prepare("
            UPDATE profissionais 
            SET 
                nome = :nome,
                especialidade = :especialidade,
                valor_servico = :valor_servico
            WHERE id = :id AND salao_id = :salao_id
        ");
        
        $params = [
            'id' => $dados['id'],
            'nome' => $dados['nome'],
            'especialidade' => $dados['especialidade'],
            'valor_servico' => $dados['valor'],
            'salao_id' => $_SESSION['salao_id']
        ];
        
    } else {
        $stmt = $conn->prepare("
            INSERT INTO profissionais 
            (nome, especialidade, valor_servico, salao_id)
            VALUES 
            (:nome, :especialidade, :valor_servico, :salao_id)
        ");
        
        $params = [
            'nome' => $dados['nome'],
            'especialidade' => $dados['especialidade'],
            'valor_servico' => $dados['valor'],
            'salao_id' => $_SESSION['salao_id']
        ];
    }
    
    $stmt->execute($params);
    
    if (!empty($dados['id'])) {
        $mensagem = 'Profissional atualizado com sucesso';
        $id = $dados['id'];
    } else {
        $mensagem = 'Profissional cadastrado com sucesso';
        $id = $conn->lastInsertId();
    }
    
    echo json_encode([
        'status' => 'success',
        'mensagem' => $mensagem,
        'id' => $id
    ]);

} catch (PDOException $e) {
    error_log("Erro ao salvar profissional: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao salvar profissional'
    ]);
}
?>