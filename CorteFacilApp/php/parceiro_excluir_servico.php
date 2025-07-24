<?php
require_once 'conexao.php';
session_start();

header('Content-Type: application/json');

// Verifica se é um parceiro
if (!isset($_SESSION['id']) || !isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'salao') {
    http_response_code(401);
    echo json_encode([
        'status' => 'erro', 
        'mensagem' => 'Não autorizado'
    ]);
    exit;
}

// Verifica se salao_id existe na sessão
if (!isset($_SESSION['salao_id']) || empty($_SESSION['salao_id'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'erro', 
        'mensagem' => 'ID do salão não encontrado na sessão'
    ]);
    exit;
}

// Recebe os dados JSON
$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID inválido']);
    exit;
}

try {
    // Verifica se a conexão está funcionando
    if (!$conn) {
        throw new Exception("Erro na conexão com o banco de dados");
    }

    // Verifica se o serviço existe e pertence ao salão
    $stmt = $conn->prepare("
        SELECT id, nome FROM servicos 
        WHERE id = :id AND salao_id = :salao_id
    ");
    
    $stmt->execute([
        'id' => $id,
        'salao_id' => $_SESSION['salao_id']
    ]);
    
    $servico = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$servico) {
        http_response_code(404);
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Serviço não encontrado ou não pertence ao seu salão'
        ]);
        exit;
    }

    // Verifica se há agendamentos futuros para este serviço
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total FROM agendamentos 
        WHERE servico_id = :servico_id 
        AND data >= CURDATE() 
        AND status != 'cancelado'
    ");
    
    $stmt->execute(['servico_id' => $id]);
    $agendamentosFuturos = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($agendamentosFuturos['total'] > 0) {
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Não é possível excluir este serviço pois há agendamentos futuros. Desative-o em vez de excluir.'
        ]);
        exit;
    }

    // Inicia uma transação para garantir consistência
    $conn->beginTransaction();

    // Remove as associações do serviço com profissionais
    $stmt = $conn->prepare("
        DELETE FROM profissional_servicos 
        WHERE servico_id = :servico_id
    ");
    $stmt->execute(['servico_id' => $id]);

    // Exclui o serviço permanentemente
    $stmt = $conn->prepare("
        DELETE FROM servicos 
        WHERE id = :id 
        AND salao_id = :salao_id
    ");
    
    $stmt->execute([
        'id' => $id,
        'salao_id' => $_SESSION['salao_id']
    ]);
    
    if ($stmt->rowCount() > 0) {
        // Confirma a transação
        $conn->commit();
        echo json_encode([
            'status' => 'sucesso',
            'mensagem' => 'Serviço excluído com sucesso'
        ]);
    } else {
        // Desfaz a transação
        $conn->rollback();
        http_response_code(500);
        echo json_encode([
            'status' => 'erro',
            'mensagem' => 'Falha ao excluir o serviço'
        ]);
    }

} catch (PDOException $e) {
    // Desfaz a transação em caso de erro
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    error_log("Erro PDO ao excluir serviço: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro no banco de dados: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Desfaz a transação em caso de erro
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    error_log("Erro geral ao excluir serviço: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'erro',
        'mensagem' => $e->getMessage()
    ]);
}
?>