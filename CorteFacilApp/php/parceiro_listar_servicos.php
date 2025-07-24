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

// Verifica se salao_id existe na sessão
if (!isset($_SESSION['salao_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID do salão não encontrado na sessão']);
    exit;
}

try {
    // Verifica se a conexão está funcionando
    if (!$conn) {
        throw new Exception("Erro na conexão com o banco de dados");
    }

    // Verifica se a tabela servicos existe
    $stmt = $conn->prepare("SHOW TABLES LIKE 'servicos'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        throw new Exception("Tabela 'servicos' não existe");
    }

    // Consulta os serviços
    $stmt = $conn->prepare("
        SELECT id, nome, duracao_minutos, preco, 
               COALESCE(descricao, '') as descricao, 
               COALESCE(ativo, 1) as ativo
        FROM servicos 
        WHERE salao_id = :salao_id 
        ORDER BY ativo DESC, nome ASC
    ");
    
    $stmt->execute(['salao_id' => $_SESSION['salao_id']]);
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Converte valores para tipos corretos
    foreach ($servicos as &$servico) {
        $servico['id'] = (int)$servico['id'];
        $servico['duracao_minutos'] = (int)$servico['duracao_minutos'];
        $servico['preco'] = (float)$servico['preco'];
        $servico['ativo'] = (int)$servico['ativo'];
    }
    
    echo json_encode([
        'status' => 'sucesso',
        'data' => $servicos
    ]);

} catch (PDOException $e) {
    error_log("Erro PDO ao listar serviços: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro no banco de dados: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Erro geral ao listar serviços: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => $e->getMessage()
    ]);
}
?>