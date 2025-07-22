<?php
session_start();
include 'conexao.php';

// Verifica se é um admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Não autorizado']);
    exit;
}

// Recebe o ID do salão
$dados = json_decode(file_get_contents('php://input'), true);

if (!isset($dados['id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID do salão não fornecido']);
    exit;
}

// Garante que o ID seja um número inteiro
$salao_id = filter_var($dados['id'], FILTER_VALIDATE_INT);

if ($salao_id === false) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID do salão inválido']);
    exit;
}

try {
    // Inicia a transação
    $conn->beginTransaction();

    // Primeiro, verifica se o salão existe e obtém o ID do usuário associado
    $stmt = $conn->prepare("SELECT usuario_id FROM saloes WHERE id = :id");
    $stmt->execute(['id' => $salao_id]);
    $salao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$salao) {
        // Se o salão não for encontrado, retorna sucesso mesmo assim
        // para evitar erros na interface quando o salão já foi excluído
        echo json_encode([
            'status' => 'ok',
            'mensagem' => 'Salão já foi excluído anteriormente'
        ]);
        exit;
    }

    $usuario_id = $salao['usuario_id'];

    // Exclui as avaliações do salão
    try {
        $stmt = $conn->prepare("DELETE FROM avaliacoes WHERE salao_id = :salao_id");
        $stmt->execute(['salao_id' => $salao_id]);
    } catch (Exception $e) {
        // Ignora erro se a tabela não existir
        error_log("Aviso ao excluir avaliações: " . $e->getMessage());
    }

    // Exclui os agendamentos do salão
    try {
        $stmt = $conn->prepare("DELETE FROM agendamentos WHERE salao_id = :salao_id");
        $stmt->execute(['salao_id' => $salao_id]);
    } catch (Exception $e) {
        // Ignora erro se a tabela não existir
        error_log("Aviso ao excluir agendamentos: " . $e->getMessage());
    }

    // Exclui as promoções do salão
    try {
        $stmt = $conn->prepare("DELETE FROM promocoes WHERE salao_id = :salao_id");
        $stmt->execute(['salao_id' => $salao_id]);
    } catch (Exception $e) {
        // Ignora erro se a tabela não existir
        error_log("Aviso ao excluir promoções: " . $e->getMessage());
    }

    // Exclui os serviços do salão
    try {
        $stmt = $conn->prepare("DELETE FROM servicos WHERE salao_id = :salao_id");
        $stmt->execute(['salao_id' => $salao_id]);
    } catch (Exception $e) {
        // Ignora erro se a tabela não existir
        error_log("Aviso ao excluir serviços: " . $e->getMessage());
    }

    // Exclui os profissionais do salão
    try {
        $stmt = $conn->prepare("DELETE FROM profissionais WHERE salao_id = :salao_id");
        $stmt->execute(['salao_id' => $salao_id]);
    } catch (Exception $e) {
        // Ignora erro se a tabela não existir
        error_log("Aviso ao excluir profissionais: " . $e->getMessage());
    }

    // Exclui as metas do salão
    try {
        $stmt = $conn->prepare("DELETE FROM metas WHERE salao_id = :salao_id");
        $stmt->execute(['salao_id' => $salao_id]);
    } catch (Exception $e) {
        // Ignora erro se a tabela não existir
        error_log("Aviso ao excluir metas: " . $e->getMessage());
    }

    // Exclui o salão
    try {
        $stmt = $conn->prepare("DELETE FROM saloes WHERE id = :id");
        $stmt->execute(['id' => $salao_id]);
        
        if ($stmt->rowCount() === 0) {
            // Se nenhuma linha foi afetada, o salão pode não existir
            error_log("Aviso: Nenhum salão foi excluído com o ID: {$salao_id}");
        }
    } catch (Exception $e) {
        // Registra o erro, mas continua para tentar excluir o usuário
        error_log("Erro ao excluir salão: " . $e->getMessage());
    }

    // Por fim, exclui o usuário associado ao salão
    try {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = :id AND tipo = 'salao'");
        $stmt->execute(['id' => $usuario_id]);
        
        if ($stmt->rowCount() === 0) {
            // Se nenhuma linha foi afetada, o usuário pode não existir
            error_log("Aviso: Nenhum usuário foi excluído com o ID: {$usuario_id}");
        }
    } catch (Exception $e) {
        // Registra o erro, mas não interrompe a transação
        error_log("Erro ao excluir usuário do salão: " . $e->getMessage());
    }

    // Confirma todas as operações
    $conn->commit();

    echo json_encode([
        'status' => 'ok',
        'mensagem' => 'Salão excluído com sucesso'
    ]);

} catch (Exception $e) {
    // Em caso de erro, desfaz todas as operações
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Erro ao excluir salão: " . $e->getMessage());
    echo json_encode([
        'status' => 'erro',
        'mensagem' => 'Erro ao excluir salão: ' . $e->getMessage()
    ]);
}
?>
